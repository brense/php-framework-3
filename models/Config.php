<?php

/**
 * Config (singleton)
 *
 * @package models
 */
class Config implements iSingleton {
	
	protected $_appPath;
	protected $_sitePath;
	protected $_rootUrl;
	protected $_theme;
	protected $_title;
	protected $_timezone;
	protected $_debug;
	protected $_db;
	protected $_cachePath;
	protected $_cacheTime;
	protected $_tplPath;
	protected $_filesPath;
	protected $_scriptPath;
	protected $_themesPath;
	protected $_encryption;
	protected $_hash;
	protected $_keys;
	
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