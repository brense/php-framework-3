<?php

/**
 * Abstract controller
 *
 * @package controllers
 */
abstract class AbstractController implements iController {
	
	protected $_model;
	protected $_models;
	protected $_view;
	
	/**
	 * Constructor, can be used to assign a model to the controller on initialization
	 * @param Observable $model
	 */
	public function __construct(Observable $model = null){
		if(isset($model)){
			$this->setModel($model);
		}
	}
	
	/**
	 * Assign a model to the Controller, this empties the models array
	 * @param Observable $model
	 */
	public function setModel(Observable $model){
		if(!empty($this->_models)){
			unset($this->_models);
		}
		// get the model data from the db
		$model = $this->modelData($model);
		$this->_model = $model;
	}
	
	/**
	 * Add a model to the assigned models array of the constructor, this empties the model variable
	 * @param Observable $model
	 */
	public function addModel(Observable $model){
		if(isset($this->_model)){
			$this->_models[] = $this->_model;
			unset($this->_model);
		}
		// get the model data from the db
		$model = $this->modelData($model);
		$this->_models[] = $model;
	}
	
	/**
	 * Get the currently assigned model
	 * @return Observable
	 */
	public function getModel(){
		return $this->_model;
	}
	
	/**
	 * Get the currently assigned models
	 * @return Array
	 */
	public function getModels(){
		return $this->_models;
	}
	
	/**
	 * Remove a model from the assigned models array
	 * @param Observable model
	 */
	public function removeModel(Observable $model){
		foreach($this->_models as &$m){
			if($m === $model){
				unset($m);
			}
		}
	}
	
	/**
	 * Get the model data from the db
	 * @param Observable model
	 */
	private function modelData(Observable $model){
		$mapper = new $this->_mapper();
		if(isset($mapper->uniques)){
			foreach($mapper->uniques as $unique){
				if(isset($model->$unique)){
					$model = $mapper->get(array($unique => $model->$unique));
					$model = $mapper->toObject($model[0], $mapper->defaultModel());
					break;
				}
			}
		}
		return $model;
	}
	
	/**
	 * Assign a view to the constructor
	 * @param AbstractView view
	 */
	public function setView(AbstractView $view){
		$this->_view = $view;
	}
	
	/**
	 * Get the currently assigned view
	 * @return AbstractView
	 */
	public function getView(){
		return $this->_view;
	}
	
}