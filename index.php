<?php

require_once 'init.php';

load_lib('form');
$q = query();

require_once PATH . DS . 'incs' . DS . 'core.model.class.php';
require_once PATH . DS . 'incs' . DS . 'core.view.class.php';
require_once PATH . DS . 'incs' . DS . 'core.controller.class.php';

$controller = new CoreController($q);

$controller->init();

echo $controller->view->render();

?>