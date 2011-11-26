<?php

/**
 * iCommand (interface)
 *
 * @package interfaces
 */
interface iCommand {
	
	public function execute(Request $request);

}