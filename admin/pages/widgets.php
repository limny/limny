<?php

load_lib('form', false);
$widget = load_lib('widget', true, true);

$security_widget_install = defined('WIDGET_INSTALL') === false || WIDGET_INSTALL === true;
$security_widget_uninstall = defined('WIDGET_UNINSTALL') === false || WIDGET_UNINSTALL === true;

if (isset($_POST['action']))
	if ($_POST['action'] == 'widget-uninstall' && $security_widget_uninstall)
		die($widget->uninstall_widget(@$_POST['id']));

$admin->title = WIDGETS;

$installed_widgets = $widget->widgets('widget');

$admin->content = '';

if (isset($_FILES['widget_file']) && $security_widget_install) {
	$file = $_FILES['widget_file'];

	if (empty($file['name']) || $file['size'] < 1 || (stripos($file['name'], '.zip') === false || strtolower(substr($file['name'], strrpos($file['name'], '.'))) !== '.zip'))
		exit;

	$zip = new ZipArchive;

	if ($zip->open($file['tmp_name']) !== false) {
		$files = ['main' => false, 'ini' => false];

		if ($zip->numFiles > 0) {
			for ($i = 0; $i < $zip->numFiles; $i++) {
				$file_name = $zip->getNameIndex($i);

				if (empty($file_name) === false && strpos($file_name, '/') === false)
					if (strpos($file_name, '.php'))
						$files['main'] = $file_name;
					else if (strpos($file_name, '.ini'))
						$files['ini'] = $file_name;
			}

			if ($files['main'] !== false && $files['ini'] !== false) {
				$widget_name = substr($files['main'], 0, strpos($files['main'], '.php'));
				
				$directory = PATH . DS . 'widgets' . DS . $widget_name;

				if (file_exists($directory) === false && is_dir($directory) === false)
					mkdir($directory);

				$zip->extractTo($directory, array_values($files));
				$zip->close();

				$widget->install_widget($widget_name);
				
				redirect(BASE . '/' . ADMIN_DIR . '/widgets');
			} else
				$error = SENTENCE_19;
		} else 
			$error = SENTENCE_18;
	} else
		$error = SENTENCE_17;
}

if (count($installed_widgets) > 0) {

	$admin->content .= '<br><h3>' . INSTALLED . '</h3><table class="table table-hover widgets">';

		foreach ($installed_widgets as $widget_object) {
			$data = parse_ini_file($widget->widgets_path . DS . $widget_object['method'] . DS . 'widget.ini');

			$admin->content .= '<tr class="active">
				<td class="col-md-1">' . @$data['name'] . '</td>
				<td class="col-md-2 text-gray">(' . @$data['creator'] . ')</td>
				<td class="text-gray">' . @$data['description'] . '</td>
				<td class="col-md-3 manage-buttons">
					' . ($security_widget_uninstall ? '<span class="text-red" style="display:none">' . SENTENCE_16 . '</span> <button class="btn btn-danger btn-xs btn-visible-hover uninstall">' . UNINSTALL . '</button> <button class="btn btn-danger btn-xs uninstall-confirm" style="display:none" data-id="' . $widget_object['id'] . '">' . YES . '</button> <button class="btn btn-info btn-xs uninstall-cancel" style="display:none">' . NO . '</button>' : '<span class="text-red">' . SENTENCE_35 . '</span>') . '
				</td>
			</tr>';
		}

	$admin->content .= '</table>';

} else 
	$admin->content = SENTENCE_13;

$show_error = isset($error);

if ($security_widget_install)
	$admin->content .= '<div class="widget-install">
<a href="#"' . ($show_error ? ' style="display:none;"' : null) . '><i class="fa fa-plus-square"></i> ' . INSTALL_NEW . '</a>
<div class="install-new" style="' . ($show_error ? null : 'display:none;') . '">
	<h3>' . INSTALL_NEW . '</h3>
	<p class="message bg-danger"' . ($show_error ? null : ' style="display:none;"') . ' data-extension="' . SENTENCE_15 . '">' . (isset($error) ? $error : null) . '</p>
	<form action="' . BASE . '/' . ADMIN_DIR . '/widgets" method="post" enctype="multipart/form-data" role="form" class="form-inline">
		<div class="form-group">
			<label for="widget_file" class="col-sm-2 control-label">' . FILE . '</label>
			<div class="col-sm-10">
				<input id="widget_file" name="widget_file" type="file" />
			</div>
		</div>
		<button id="widget_install" name="widget_install" type="submit" class="btn btn-success">' . INSTALL . '</button>
	</form>
	<p class="help-block">' . SENTENCE_14 . '</p>
</div>
</div>';
else
	$admin->content .= '<br><br><span class="text-red">' . SENTENCE_35 . '</span>';

$admin->head = '<script type="text/javascript" src="' . BASE . '/' . ADMIN_DIR . '/misc/js/widgets.js"></script>';

?>