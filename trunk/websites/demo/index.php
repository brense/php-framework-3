<?php

// initial configuration
define('CONFIG_FILE', 'configuration/demo.php');
define('AUTO_LOADER', 'library/Autoloader.php');
define('APP_PATH', str_replace('index.php', '', $_SERVER['SCRIPT_FILENAME']) . 'application/');
define('SITE_PATH', str_replace('index.php', '', $_SERVER['SCRIPT_FILENAME']) . '');
define('ROOT_URL', 'http://' . $_SERVER['HTTP_HOST'] . str_replace('index.php', '', $_SERVER['PHP_SELF']) . 'index');
ini_set('include_path', str_replace('index.php', '', $_SERVER['SCRIPT_FILENAME']) . 'application/library/');
$class_sources = '';

// set the autoloader and start the application
include(APP_PATH . AUTO_LOADER);
$app = new Application();
$app->start();

// handle the request
$request = Request::init();
$app->handleRequest($request);