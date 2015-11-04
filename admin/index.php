<?php

/**
 * Administration index file
 *
 * @package Limny
 * @author Hamid Samak <hamid@limny.org>
 * @copyright 2009-2015 Limny
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// include initializing file
require_once '..' . DIRECTORY_SEPARATOR . 'init.php';

// define administration directory
def('ADMIN_DIR', 'admin');

// load model object
// prepare database connection
require_once PATH . DS . 'incs' . DS . 'core.model.class.php';
$model = new CoreModel($registry);

// load administration library
$admin = load_lib('admin', true, true);

// load system language
$admin->load_language();

// check administrator is signed-in
if (admin_signed_in() !== true) {
	$admin->is_remembered();

	if ($admin->q[0] !== 'signin')
		redirect(BASE . '/' . ADMIN_DIR . '/signin');
}

// load permission library
$permission = load_lib('permission', true, true);

// check browsing page is method based or not
if (in_array($admin->q[0], $admin->pages_in_method)) {
	// load page library
	$page = load_lib('page', true, true);

	// check page method exists
	if (method_exists($page, 'page_' . $admin->q[0]))
		$page->{'page_' . $admin->q[0]}();

	// include page template
	$page_file = PATH . DS . 'admin' . DS . 'pages' . DS . $admin->q[0] . '.tpl';
	include $page_file;
} else {
	// check is current administrator permitted to browse this page query parameter
	if ($permission->is_permitted($admin->q)) {
		if (in_array($admin->q[0], $admin->pages_in_file)) {
			$page_in_file = PATH . DS . 'admin' . DS . 'pages' . DS . $admin->q[0] . '.php';
			if (file_exists($page_in_file))
				include $page_in_file;

			include PATH . DS . 'admin' . DS . 'pages' . DS . 'template.tpl';
		} else if ($admin->is_app($admin->q)) {
			$application = load_lib('application', true, true);

			$admin->app_load_language($admin->q[0]);
			$app_admin = $application->app_admin($admin->q);
			
			if ($app_admin) {
				foreach (['head', 'title', 'content'] as $value)
					if (isset($app_admin[$value]))
						$admin->{$value} = $app_admin[$value];
			} else {
				$admin->title = ERROR;
				$admin->content = SENTENCE_33;
			}

			include PATH . DS . 'admin' . DS . 'pages' . DS . 'template.tpl';
		} else {
			$admin->title = ERROR;
			$admin->content = SENTENCE_32;

			include PATH . DS . 'admin' . DS . 'pages' . DS . 'template.tpl';
		}

	} else {
		$admin->title = ERROR;
		$admin->content = SENTENCE_31;

		include PATH . DS . 'admin' . DS . 'pages' . DS . 'template.tpl';
	}
}

?>