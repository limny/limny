<?php

require_once '..' . DIRECTORY_SEPARATOR . 'init.php';

def('ADMIN_DIR', 'admin');

require_once PATH . DS . 'incs' . DS . 'core.model.class.php';
$model = new CoreModel($registry);

$admin = load_lib('admin', true, true);

$admin->load_language();

if (admin_signed_in() !== true) {
	$admin->is_remembered();

	if ($admin->q[0] !== 'signin')
		redirect(BASE . '/' . ADMIN_DIR . '/signin');
}

$permission = load_lib('permission', true, true);

if (in_array($admin->q[0], $admin->pages_in_method)) {
	//require_once PATH . DS . 'admin' . DS . 'page.class.php';
	//$page = new Page($registry);
	$page = load_lib('page', true, true);

	if (method_exists($page, 'page_' . $admin->q[0]))
		$page->{'page_' . $admin->q[0]}();

	$page_file = PATH . DS . 'admin' . DS . 'pages' . DS . $admin->q[0] . '.tpl';
	include $page_file;
} else {
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