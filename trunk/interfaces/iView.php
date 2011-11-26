<?php

/**
 * iView (interface)
 *
 * @package interfaces
 */
interface iView {
	
	public function setModel(Observable $model);
	public function getModel();
	
	public function setController(AbstractController $controller);
	public function getController();
	
	public function defaultController (Observable $model);
	
}