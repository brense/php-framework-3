<?php

/**
 * iController (interface)
 *
 * @package interfaces
 */
interface iController {
	
	public function setModel(models_Observable $model);
	public function getModel();
	
	public function setView(views_AbstractView $view);
	public function getView();
	
}