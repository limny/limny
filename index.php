<?php

// include initializing file
require_once 'init.php';

// load form library
load_lib('form');

// get query parameter
$q = query();

// include mvc objects
require_once PATH . DS . 'incs' . DS . 'core.model.class.php';
require_once PATH . DS . 'incs' . DS . 'core.view.class.php';
require_once PATH . DS . 'incs' . DS . 'core.controller.class.php';

// create new instance of controller with current query parameter
$controller = new CoreController($q);

// initialize controller
$controller->init();

// render and print page
echo $controller->view->render();

?>