<?php

/**
 * Application helper
 *
 * @package models
 */
class AppHelper implements iSingleton {
	
	protected $_cfg;
	protected $_user;
	protected $_request;
	
	private static $_instance;
	
	private function __construct(){}

	public static function instance(){
		if(empty(self::$_instance)){
			self::$_instance = new self();
		}
		return self::$_instance;
	}
		
	/**
	 * Magic setter
	 * @param string $name
	 * @param string $value
	 */
	public function __set($name, $value){
		if(substr($name, 0, 1) != '_'){
			$name = '_' . $name;
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
			return $this->$name;
		}
	}
	
}