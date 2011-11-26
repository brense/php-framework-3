<?php

/**
 * Observable (abstract model)
 *
 * @package models
 */
abstract class Observable {
	
	protected $_id;
	protected $_observers = array();
	protected $_saveable = true;
	public static $class;
	
	/**
	 * Constructor
	 */
	public function __construct($create = true){
		// create the object only if create parameter is true to prevent recursion
		if($create){
			self::$class = get_class($this);
			$model = self::create();
			if($model->id > 0){
				$this->id = $model->id;
			}
		}
	}
	
	/**
	 * Set a class name for static functions (fix for < php5.3)
	 * @param String $class
	 */
	public static function setClass($class){
		self::$class = $class;
	}
	
	/**
	 * Create a model
	 * @param Array $args
	 * @return Observable
	 */
	public static function create(Array $args = array()){
		// determine which class made the call
		if(function_exists('get_called_class')){
			$class = get_called_class();
		} else {
			$class = self::$class;
		}
		// create a new instance of the model
		$model = new $class(false);
		// populate the model properties with the args
		foreach($args as $key => $value){
			$model->$key = $value;
		}
		// initiate the mapper and save the model in the db
		$mapper = new ObjectMapper($class);
		$mapper->create($model);
		// return the created model
		return $model;
	}
	
	/**
	 * Find a model
	 * @param Array $args
	 * @return Observable
	 */
	public static function find(Array $args = array(), $all = false, $sort = null, $limit = null){
		// determine which class made the call
		if(function_exists('get_called_class')){
			$class = get_called_class();
		} else {
			$class = self::$class;
		}
		// initiate the mapper
		$mapper = new ObjectMapper($class);
		// find the correct model in the db
		$results = $mapper->read($args, $sort, $limit);
		if($all){
			$models = array();
			foreach($results as $result){
				// create a new instance of the model
				$model = new $class(false);
				// populate the model properties with the args
				$mapper->populateObject($model, $result);
				// put the model in the array
				if($model->id > 0){
					$models[] = $model;
				}
			}
			// return the models
			return $models;
		} else {
			// create a new instance of the model
			$model = new $class(false);
			// populate the model properties with the args
			if(isset($results[0])){
				$mapper->populateObject($model, $results[0]);
			}
			// return the model
			if($model->id > 0){
				return $model;
			} else {
				return false;
			}
		}
	}
	
	/**
	 * Save changes in a model
	 */
	public function update(){
		$class = get_class($this);
		$mapper = new ObjectMapper($class);
		$mapper->update($this);
	}
	
	/**
	 * Delete a model
	 */
	public function delete(){
		$class = get_class($this);
		$mapper = new ObjectMapper($class);
		$mapper->delete($this);
	}
	 
	/**
	 * Assign an observer to the model
	 * @param AbstractView $view
	 */
	public function addObserver(Observer $view){
		$this->_observers[] = $view;
	}
	
	/**
	 * Remove an observer from the models observer list
	 * @param AbstractView $view
	 */
	public function removeObserver(Observer $view){
		foreach($this->_observers as &$observer){
			if($observer === $view){
				unset($observer);
			}
		}
	}
	
	/**
	 * Notify observers of changes to the model
	 */
	public function notifyObservers($changes){
		foreach($this->_observers as $observer){
			$observer->update($this, $changes);
		}
	}
	
	/**
	 * Magic setter
	 * @param string $name
	 * @param string $value
	 */
	public function __set($name, $value){
		if(substr($name, 0, 1) != '_'){
			$name = '_' . $name;
			$changes = array(
				'function'	=> 'set',
				'params'	=> $name,
				'values'	=> $value,
				'timestamp'	=> date('U')
			);
			$this->notifyObservers($changes);
			$this->$name = $value;
		}
	}
	
	/**
	 * Magic getter
	 * @param string $name
	 */
	public function __get($name){
		if(substr($name, 0, 1) != '_'){
			$name = '_' . $name;
			$changes = array(
				'function'	=> 'get',
				'params'	=> $name,
				'values'	=> $this->$name,
				'timestamp'	=> date('U')
			);
			$this->notifyObservers($changes);
			return $this->$name;
		}
	}
		
	/**
	 * Return the models properties
	 * @return Array
	 */
	public function getProperties(){
		$ref = new ReflectionObject($this);
		$properties = array();
		foreach($ref->getProperties() as $prop){
			if(substr($prop->name, 0, 1) == '_' && $prop->name != '_history' && $prop->name != '_observers' && $prop->name != '_saveable'){
				$prop = substr($prop->name, 1);
				$properties[$prop] = $this->$prop;
			}
		}
		return $properties;
	}

}