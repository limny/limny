<?php

// set PHP errors reporting
// these type of errors cannot be handled with Limny error handler function
error_reporting(E_ERROR | E_PARSE | E_STRICT);

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

// make connection to database
$db = new Database(DB_HOST, DB_PORT, DB_USER, DB_PASS, DB_NAME);

// display error if install directory exists
if (file_exists(PATH . DS . 'install') && is_dir(PATH . DS . 'install'))
	die('Limny error: Please delete <em>install</em> directory.');

// include Limny main functions
require_once PATH . DS . 'incs' . DS . 'functions.php';

// set Limny error handler as default error handling function
set_error_handler('log_error', E_ALL);

// load config library
$config = load_lib('config');

// set default timezone from configuration
date_default_timezone_set($config->config->timezone);

?>