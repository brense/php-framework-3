<?php
$cfg = array(
	'theme'			=> 'default',
	'title'			=> 'Framework demo',
	'timezone'		=> 'UTC',
	'debug'			=> false,
	'db' => array(
		'type'		=> 'pdo',
		'host'		=> 'localhost',
		'name'		=> 'framework_demo',
		'user'		=> 'framework_demo',
		'pswd'		=> 'demo_framework',
		'prfx'		=> ''
	),
	'cachePath'		=> 'cache/',
	'tplPath'		=> 'templates/',
	'filesPath'		=> 'public/files/',
	'scriptPath'	=> 'public/js/',
	'themesPath'	=> 'public/themes/',
	'encryption'	=> 'MCRYPT_RIJNDAEL_256',
	'hash'			=> 'sha1',
	'cacheTime'		=> 120,
	'keys' => array(
		'secret'	=> 'framework_demo',
	)
);