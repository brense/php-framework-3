<?php

/**
 * Db Controller Factory
 *
 * @package controllers
 */
class DbControllerFactory {
	
	private function __construct(){}
	
	/**
	 * Returns the correct db controller
	 * @param string $table
	 * @param string $dbType
	 */
	public static function getDbController($dbType = null) {
		// determine the correct db type
		if(!isset($dbType)){
			$dbType = AppHelper::instance()->cfg->db['type'];
		}
		$class = ucfirst($dbType) . 'Controller';
		// return the correct db controller object
		if(class_exists($class)){
			return new $class();
		} else {
			throw new Exception('db controller of type "' . $dbType . '" not found');
		}
	}
	
}