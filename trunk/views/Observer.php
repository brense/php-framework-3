<?php

/**
 * Observer (abstract view)
 *
 * @package views
 */
abstract class Observer implements iObserver, iView {
	
	protected $_model;
	protected $_controller;
	
	/**
	 * Constructor, sets the model to be observed
	 * @param Observable $model
	 * @param AbstractController $controller
	 */
	public function __construct(Observable $model, AbstractController $controller = null){
		$this->setModel($model);
		if(isset($controller)){
			$this->setController($controller);
		}
	}
	
	/**
	 * Returns the default controller for the model
	 * @param Observable $model
	 */
	public function defaultController(Observable $model){
		return null;
	}
	
	/**
	 * Set the model to be observed
	 * @param Observable $model
	 */
	public function setModel(Observable $model){
		$this->_model = $model;
	}
	
	/**
	 * Returns the observed model
	 * @return Observable
	 */
	public function getModel(){
		return $this->_model;
	}
	
	/**
	 * Set the controller for this view
	 * @param AbstractController $controller
	 */
	public function setController(AbstractController $controller){
		$this->_controller = $controller;
		$this->getController()->setView($this);
	}
	
	/**
	 * Returns the controller
	 * @return AbstractController
	 */
	public function getController(){
		if(!isset($this->_controller)){
			$this->setController($this->defaultController($this->getModel()));
		}
		return $this->_controller;
	}
	
	/**
	 * Update the model
	 * @param Observable $model
	 * @param Array $changes
	 */
	public function update(Observable $observable, $changes){
		foreach($changes as $key => $value){
			$observable->{$key} = $value;
		}
	}
}