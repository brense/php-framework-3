<?php

/**
 * iObserver (interface)
 *
 * @package interfaces
 */
interface iObserver {
	
	public function update(Observable $observable, $changes);
	
}