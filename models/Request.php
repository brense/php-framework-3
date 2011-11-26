<?php

/**
 * Request
 *
 * @package models
 */
class Request extends Observable {
	
	protected $_uri;
	protected $_params;
	protected $_method;
	protected $_useragent;
	protected $_referer;
	protected $_timestamp;
	protected $_ip;

	public function __construct(){}
	
	/**
	 * Check if a requester ip is blocked
	 */
	public function ipBlock(){
		// TODO: implement blacklist from db table
		$blacklist = array('127.0.0');
		if(in_array($this->ip, $blacklist)){
			return true;
		} else {
			return false;
		}
	}
	
	public static function init(){
		parent::setClass('Request');
		
		// sanitize the requested uri
		$uri = str_replace(AppHelper::instance()->cfg->rootUrl, '', 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
		$arr = explode('?', $uri);
		$uri = array_shift($arr);
		if(substr($uri, -1, 1) == '/'){
			$uri = substr($uri, 0, -1);
		}
		// find the requesters ip
		if(!empty($_SERVER['HTTP_CLIENT_IP'])){
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		} else if(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else {
			$ip = $_SERVER['REMOTE_ADDR'];
		}
		// set the request object variables
		if(isset($_SERVER['REQUEST_METHOD'])) {
			$properties = array(
				'uri'		=> $uri,
				'params'	=> $_REQUEST,
				'method'	=> $_SERVER['REQUEST_METHOD'],
				'useragent'	=> $_SERVER['HTTP_USER_AGENT'],
				'referer'	=> isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '',
				'timestamp'	=> $_SERVER['REQUEST_TIME'],
				'ip'		=> trim($ip)
			);
		}
		
		return parent::create($properties);
	}
		
	public static function find(Array $args = array(), $all = false, $sort = null, $limit = null){
		parent::setClass('Request');
		return parent::find($args, $all, $sort, $limit);
	}

}