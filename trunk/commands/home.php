<?php

/**
 * Home command
 *
 * @package commands
 */
class Commands_Home implements iCommand {
	
	/**
	 * Constructor
	 */	
	public function __construct(){}
	
	/**
	 * Execute
	 * @param Request $request
	 */
	public function execute(Request $request){
		echo '<pre>';
		$test = Test::find(array('fun' => 'bla'));
		if($test instanceof Observable && $test->id > 0){} else {
			$test = new Test();
			$test2 = new Test();
			$test3 = new Test();
			$test3->fun = 'test';
			$test2->tests = array($test3);
			$test->tests = array($test2, $test3);
			$test->fun = 'bla';
			$test->update();
		}
		print_r($test);
		echo '</pre>';
		exit;
	}
	
}