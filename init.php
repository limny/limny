<?php

error_reporting(E_ERROR | E_PARSE | E_STRICT);

session_name('limny');
session_start();

define('PATH', __DIR__);
define('DS', DIRECTORY_SEPARATOR);
define('BASE', substr(PATH, strlen($_SERVER['DOCUMENT_ROOT'])));

require_once PATH . DS . 'config.php';

if (file_exists(PATH . DS . 'security.php'))
	require_once PATH . DS . 'security.php';

require_once PATH . DS . 'incs' . DS . 'database.class.php';

$db = new Database(DB_HOST, DB_PORT, DB_USER, DB_PASS, DB_NAME);

if (file_exists(PATH . DS . 'install') && is_dir(PATH . DS . 'install'))
	die('Limny error: Please delete <em>install</em> directory.');

require_once PATH . DS . 'incs' . DS . 'functions.php';

set_error_handler('log_error', E_ALL);

$config = load_lib('config');

date_default_timezone_set($config->config->timezone);

?>