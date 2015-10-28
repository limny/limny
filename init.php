<?php

// set PHP errors reporting
// these type of errors cannot be handled with Limny error handler function
error_reporting(E_ALL);//E_ERROR | E_PARSE | E_STRICT);

// set session name & start session
session_name('limny');
session_start();

// define running directory path, OS directory separator and base path for URLs
define('PATH', __DIR__);
define('DS', DIRECTORY_SEPARATOR);
define('BASE', substr(PATH, strlen($_SERVER['DOCUMENT_ROOT'])));

// include database connection information
require_once PATH . DS . 'config.php';

// include security file if exists
// this is an optional file
// look Limny documentation for more information
if (file_exists(PATH . DS . 'security.php'))
	require_once PATH . DS . 'security.php';

// include database connection object
require_once PATH . DS . 'incs' . DS . 'database.class.php';

// include registry object and create new registry instance
require_once PATH . DS . 'incs' . DS . 'core.registry.class.php';
$registry = new CoreRegistry;

// set default timezone for preventing PHP warning
date_default_timezone_set('UTC');

// include Limny main functions
require_once PATH . DS . 'incs' . DS . 'functions.php';

// set Limny error handler as default error handling function
set_error_handler('log_error', E_ALL);

?>