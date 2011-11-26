<?php

/**
 * Request
 *
 * @package models
 */
class Test extends Observable {
	
	protected $_fun;
	protected $_tests;
	
	public static function create(Array $args = array()){
		parent::setClass('Test');
		return parent::create($args);
	}
		
	public static function find(Array $args = array(), $all = false, $sort = null, $limit = null){
		parent::setClass('Test');
		return parent::find($args, $all, $sort, $limit);
	}

}