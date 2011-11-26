<?php

/**
 * Object Mapper
 *
 * @package models/mappers
 */
class ObjectMapper {
	
	protected $_db;
	protected $_table;
	protected $_class;
	
	/**
	 * Constructor, initialize the db handle
	 * @param String $class
	 */
	public function __construct($class){
		$this->_class = ucfirst($class);
		$this->_db = DbControllerFactory::getDbController();
		$this->_table = strtolower($class);
		$this->_db->setTable($this->_table);
	}
	
	/**
	 * Create an object
	 * @param Observable $obj
	 * @param string $table
	 */
	public function create(Observable &$obj, $table = null){
		// set the correct table
		if(!isset($table)){
			$table = $this->_table;
		}
		// create the db table if needed
		$cols = $this->createTable($obj->getProperties(), $table);
		// substract properties from the db table columns
		$properties = array();
		foreach($cols as $col => $params){
			if($params['type'] != 'subtbl'){
				$properties[$col] = $params['value'];
			}
		}
		// set the correct table name
		$this->_db->setTable($table);
		// save the object properties in the db table
		$id = $this->_db->create($properties);
		// set the table name back to default
		$this->_db->setTable($this->_table);
		// check if a new object id has been returned
		if($id > 0){
			$obj->id = $id;
		}
		// create sub tables if needed
		$this->createSubTables($cols, $obj->id);
		// return the new object id
		return $obj->id;
	}
	
	public function read(Array $args, $sort = null, $limit = null){
		// get the correct records from the db table
		return $this->_db->read($args, $sort, $limit);
	}
	
	public function update(Observable $obj){
		// update the db table if needed
		$cols = $this->createTable($obj->getProperties());
		// substract properties from the db table columns
		$properties = array();
		foreach($cols as $col => $params){
			if($params['type'] != 'subtbl'){
				$properties[$col] = $params['value'];
			}
		}
		// save the updated object
		$this->_db->update(array('id' => $obj->id), $properties);
		// update the sub tables if needed
		$this->createSubTables($cols, $obj->id);
	}
	
	public function delete(Observable $obj){
		// delete the object from the db table
		$this->_db->delete(array('id' => $obj->id));
		// update the sub tables if needed
		$this->createSubTables($cols, $obj->id);
	}
	
	/**
	 * Retrieve the linked objects for a given object
	 * @param Object $obj
	 * @param Array $params
	 */
	private function getLinkedObjects(&$obj, $params){
		// set the correct table name
		$this->_db->setTable('linked_tables');
		// get the links for the given object
		$links = $this->_db->read(array('parent' => strtolower(get_class($obj))));
		foreach($links as $link){
			$arr = array();
			// set the correct table name
			$this->_db->setTable(strtolower($link['link']));
			// get the ids of the linked objects
			$results = $this->_db->read(array('parent' => $params['id']));
			// determine the table name for the link
			$field = substr($link['link'], 0, -1);
			$field = explode('_', $field);
			$field = array_pop($field);
			foreach($results as $result){
				// set the correct table name
				$this->_db->setTable($field);
				// get the child object
				$children = $this->_db->read(array('id' => $result['child']));
				// populate the child object
				$child = ucfirst($field);
				$child = new $child(false);
				$this->populateObject($child, $children[0]);
				$arr[] = $child;
			}
			if(count($arr) > 0){
				$obj->$link['name'] = $arr;
			}
		}
		// set the table name back to default
		$this->_db->setTable($this->_table);
	}
	
	/**
	 * Parse flat arrays from the db table
	 * @param string $value
	 */
	private function parseStoredArray(&$value){
		$val = str_replace('[array]', '', $value);
		$arr = explode('&', $val);
		$value = array();
		foreach($arr as $q){
			$v = explode('=', $q);
			$value[$v[0]] = $v[1];
		}
	}
	
	/**
	 * Parse flat objects from the db table
	 * @param string $value
	 */
	private function parseLinkedObject(&$value){
		// determine the class/table name of the object and the object id
		$link = explode('=', str_replace('[link]', '', $value));
		$class = ucfirst($link[0]);
		$id = $link[1];
		// set the correct table name
		$this->_db->setTable($class);
		// get the linked object from the db table
		$result = $this->_db->read(array('id' => $id));
		// set the table name back to default
		$this->_db->setTable($this->_table);
		// populate the linked object
		$model = new $class(false);
		$this->populateObject($model, $result[0]);
		$value = $model;
	}
	
	/**
	 * Populate the properties of a given object
	 * @param Object $obj
	 * @param Array $params
	 */
	public function populateObject(&$obj, $params){
		// get linked objects
		$this->getLinkedObjects($obj, $params);
		
		foreach($params as $key => $value){
			if(!is_numeric($key)){
				// parse stored array
				if(substr($value, 0, 7) == '[array]'){
					$this->parseStoredArray($value);
				}
				// parse linked object
				if(substr($value, 0, 6) == '[link]'){
					$this->parseLinkedObject($value);
				}
				// populate the model property with the parsed value
				$obj->$key = $value;
			}
		}
	}
	
	/**
	 * Create the table if it doesn't exist
	 * @param Array $args
	 * @params string $table
	 */
	public function createTable(Array $args, $table = null){
		// TODO: find a way to remove old columns (mysql> SHOW COLUMNS FROM table;)
		if(!isset($table)){
			$table = $this->_table;
		}
		// substract the column details from the arguments array
		$cols = $this->substractColumns($args);
		// create the table if it doesn't exist
		$this->_db->query('CREATE TABLE IF NOT EXISTS `' . AppHelper::instance()->cfg->db['prfx'] . $table . '` (id int NOT NULL AUTO_INCREMENT, PRIMARY KEY(id))');
		// create or update the table columns
		foreach($cols as $col => $params){
			if($params['type'] != 'subtbl'){
				$this->_db->query('ALTER TABLE `' . AppHelper::instance()->cfg->db['prfx'] . $table . '` ADD COLUMN `' . $col . '` ' . $params['type'] . ' (' . $params['length'] . ')');
				$this->_db->query("SET SESSION sql_mode = 'STRICT_ALL_TABLES'");
				$this->_db->query('ALTER TABLE `' . AppHelper::instance()->cfg->db['prfx'] . $table . '` MODIFY `' . $col . '` ' . $params['type'] . ' (' . $params['length'] . ')');
			}
		}
		return $cols;
	}
	
	/**
	 * Create the sub tables, populate them and delete old records
	 * @param Array $cols
	 * @param int $id
	 */
	public function createSubTables(Array $cols, $id){
		foreach($cols as $col => $params){
			if($params['type'] == 'subtbl'){
				// add the link to the linked table records
				$this->_db->query('CREATE TABLE IF NOT EXISTS `' . AppHelper::instance()->cfg->db['prfx'] . 'linked_tables` (id int NOT NULL AUTO_INCREMENT, `parent` varchar (255), `link` varchar (255), `name` varchar (255), PRIMARY KEY(id))');
				$this->_db->setTable('linked_tables');
				$rec = $this->_db->read(array('parent' => strtolower($this->_table), 'link' => strtolower($params['tblname']), 'name' => $col));
				if(!isset($rec[0]['id'])){
					$this->_db->create(array('parent' => strtolower($this->_table), 'link' => strtolower($params['tblname']), 'name' => $col));
				}
				// create the sub table
				$this->_db->query('CREATE TABLE IF NOT EXISTS `' . AppHelper::instance()->cfg->db['prfx'] . $params['tblname'] . '` (id int NOT NULL AUTO_INCREMENT, `parent` int (11), `child` int (11), PRIMARY KEY(id))');
				// fetch old results from sub table for the current object
				$this->_db->setTable($params['tblname']);
				$rec = $this->_db->read(array('parent' => $id));
				$old = array();
				if(is_array($rec) && isset($rec[0]['id'])){
					foreach($rec as $row){
						$old[] = $row['child'];
					}
				}
				// insert new links
				foreach($params['ids'] as $child){
					if(!in_array($child, $old)){
						$this->_db->create(array('parent' => $id, 'child' => $child));
					}
				}
				// delete old links
				foreach($old as $child){
					if(!in_array($child, $params['ids'])){
						$this->_db->delete(array('parent' => $id, 'child' => $child));
					}
				}
				$this->_db->setTable($this->_table);
			}
		}
	}
	
	/**
	 * Substract the db table colums based on an array of arguments
	 * @param Array $args
	 * @return Array
	 */
	private function substractColumns(Array $args){
		$cols = array();
		foreach($args as $key => $value){
			if($key != 'id'){
				// process floats
				if(is_float($value)){
					$cols[$key] = array(
						'type' => 'float',
						'length' => strlen($value),
						'value' => $value
					);
				}
				// process integers
				if(is_int($value)){
					if(strlen($value) > 11){
						$type = 'bigint';
					} else if(strlen($value) > 8){
						$type = 'int';
					} else if(strlen($value) > 5){
						$type = 'mediumint';
					} else if(strlen($value) > 3){
						$type = 'smallint';
					} else {
						$type = 'tinyint';
					}
					$cols[$key] = array(
						'type' => $type,
						'length' => strlen($value),
						'value' => $value
					);
				}
				// process strings (varchars)
				if(is_string($value)){
					if(strlen($value) > 255){
						$type = 'text';
					} else {
						$type = 'varchar';
					}
					$cols[$key] = array(
						'type' => $type,
						'length' => strlen($value),
						'value' => $value
					);
				}
				// process boolean
				if(is_bool($value)){
					$cols[$key] = array(
						'type' => 'tinyint',
						'length' => 1,
						'value' => $value
					);
				}
				// process array
				if(is_array($value)){
					$arr = array();
					$objs = false;
					foreach($value as $k => $v){
						if($v instanceof Observable){
							$objs = true;
						} else {
							$arr[] = $k . '=' . $v;
						}
					}
					if($objs){
						// process array with objects
						$objName = '';
						$ids = array();
						foreach($value as $k => $v){
							if($v instanceof Observable){
								if($v->id > 0){
									$ids[] = $v->id;
									$v->update();
								} else {
									$ids[] = $this->create($v, get_class($v));
								}
								$objName = get_class($v);
							}
						}
						$cols[$key] = array(
							'type' => 'subtbl',
							'tblname' => $this->_class . '_' . $objName . 's',
							'ids' => $ids
						);
					} else {
						// process normal array
						$cols[$key] = array(
							'type' => 'varchar',
							'length' => 255,
							'value' => '[array]' . implode('&', $arr)
						);
					}
				}
				// process Observable
				if($value instanceof Observable){
					if($value->id > 0){
						$id = $value->id;
						$value->update();
					} else {
						$id = $this->create($value, get_class($value));
					}
					$cols[$key] = array(
						'type' => 'varchar',
						'length' => 255,
						'value' => '[link]' . strtolower(get_class($value)) . '=' . $id
					);
				}
			}
		}
		return $cols;
	}
}