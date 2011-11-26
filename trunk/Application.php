<?php

/**
 * Application
 *
 */
class Application {
	
	/**
	 * Constructor
	 */
	public function __construct(){
		// set the exception handler
		set_exception_handler(array($this, 'handleExceptions'));
		// set the error handler
		set_error_handler(array($this, 'handleErrors'));
	}
	
	/**
	 * Starts the application and saves the config, output, and user objects in the app helper
	 */
	public function start(){
		// start a session
		session_start();
		
		// initialize the config object
		$config = Config::instance();
		AppHelper::instance()->cfg = $config;
		
		// load the config file
		if(file_exists(SITE_PATH . CONFIG_FILE)){
			include(SITE_PATH . CONFIG_FILE);
		} else {
			throw new Exception('config file not found');
		}
		foreach($cfg as $key => $value){
			AppHelper::instance()->cfg->$key = $value;
		}
		AppHelper::instance()->cfg->appPath = APP_PATH;
		AppHelper::instance()->cfg->sitePath = SITE_PATH;
		AppHelper::instance()->cfg->rootUrl = ROOT_URL;
		
		// set the timezone
		date_default_timezone_set(AppHelper::instance()->cfg->timezone);
		
		// initialize the user object
		if(isset($_SESSION['USER_LOGIN'])){
			$userid = substr($_SESSION['USER_LOGIN'], 0, -40);
			$user = new User();
			$user->id = $userid;
			$userController = new UserController($user);
			AppHelper::instance()->user = $userController->getModel();
		}
	}
		
	/**
	 * Request handler
	 * @param Request $request
	 */
	public function handleRequest(Request $request){
		// store the request object in the app helper
		AppHelper::instance()->request = $request;
		
		// check if the users ip is blocked
		if($request->ipBlock()){
			throw new Exception('your ip is blocked');
			exit;
		} else {
			if($request->method == 'GET'){
				// handle get
				if(substr($request->uri, 0, 3) == 'api'){
					// handle api requests
					$cmd = $request->uri;
				} else {
					// handle other requests
					if(strlen($request->uri) > 0){
						$cmd = $request->uri;
					} else {
						$cmd = 'home';
					}
				}
			} else if($request->method == 'POST' && substr($request->uri, 0, 4) == 'app/'){
				// handle post
				$cmd = substr($request->uri, 4);
			}
			// get the correct command object and execute the command
			$command = CommandFactory::getCommand($cmd, $request);
			$command->execute($request);
		}
	}
	
	/**
	 * Exception handler
	 * @param Exception $exception
	 */
	public static function handleExceptions(Exception $exception) {
		// TODO: log exceptions
		if(AppHelper::instance()->cfg->debug){
			echo '<pre>';
			print_r($exception);
			echo '</pre>';
		}
	}
	
	/**
	 * Error handler
	 */
	public static function handleErrors($errno, $errstr, $error_file = null, $error_line = null, Array $error_context = null) {
		// TODO: log errors
		if(AppHelper::instance()->cfg->debug){
			$error = array(
				'no' => $errno,
				'error' => $errstr,
				'file' => $error_file,
				'line' => $error_line,
				'context' => $error_context
			);
			echo '<pre>';
			print_r($error);
			echo '</pre>';
		}
	}

}