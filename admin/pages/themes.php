<?php

$theme = load_lib('theme', true, true);

$security_theme_edit = defined('THEME_EDIT') === false || THEME_EDIT === true;

$themes = $theme->themes();

if (isset($admin->q[1]) === false)
	$admin->q[1] = '';

switch ($admin->q[1]) {
	default:
		$admin->title = THEMES;

		$current_theme = $admin->config->theme;

		unset($themes[array_search($current_theme, $themes)]);

		array_unshift($themes, $current_theme);

		if (count($themes) > 0) {
			foreach ($themes as $theme_name) {
				$files = [];
				$is_current_theme = ($theme_name == $current_theme) ? true : false;
				
				$theme_info = $theme->theme_info($theme_name);

				if (isset($theme_info['creator']) && empty($theme_info['creator']) === false)
					$theme_creator = BY . ' ' . $theme_info['creator'];

				if ($theme_files = $theme->theme_files($theme_name))
					foreach ($theme_files as $file) {
						$files[] = '<li><a href="' . BASE . '/' . ADMIN_DIR . '/themes/edit/' . $theme_name . '/' . $file . '">' . $file . '</a></li>';

						if (count($files) === 7) {
							$files[] = '<li><a href="' . BASE . '/' . ADMIN_DIR . '/themes/edit/' . $theme_name . '">&hellip;</a></li>';
							break;
						}
					}

				if ($security_theme_edit)
					$theme_edit = (count($files) > 0 ? '<a href="' . BASE . '/' . ADMIN_DIR . '/themes/edit/' . $theme_name . '" class="btn btn-primary">' . EDIT . '</a>' : null) . '
					' . ($is_current_theme === true && count($files) > 0 ? '<ul class="files">' . implode("\n", $files) . '</ul>' : null);
				else
					$theme_edit = '<span class="text-red">' . SENTENCE_35 . '</span>';

				$admin->content .= '<div class="theme ' . ($is_current_theme ? 'current' : null) . '">
		<div class="theme-screenshot">
			<a href="' . BASE . '/' . ADMIN_DIR . '/themes/edit/' . $theme_name . '"><img src="' . BASE . '/themes/' . $theme_name . '/screenshot.png" /></a>
		</div>

			<div class="row">
				<div class="col-md-10">
					<h3>' . $theme_info['name'] . '</h3> ' . @$theme_creator . '
					<p>' . @$theme_info['description'] . '</p>
				</div>
				<div class="manage col-md-2">
					' . ($is_current_theme === false ? '<a href="' . BASE . '/' . ADMIN_DIR . '/themes/set/' . $theme_name . '" class="btn btn-success">' . SET_AS_DEFAULT . '</a> <br>
						<a href="' . BASE . '/?theme=' . $theme_name . '" target="_blank" class="btn btn-warning">' . PREVIEW . '</a>
					<br>' : null) . '
					' . $theme_edit . '
				</div>
			</div>

		<div style="clear:both;"></div>
		</div>';
			}
		} else
			$admin->content = SENTENCE_8;

		break;

	case 'edit':
		if ($security_theme_edit !== true)
			redirect(BASE . '/' . ADMIN_DIR . '/themes');
		
		if (isset($admin->q[2]) && in_array($admin->q[2], $themes)) {
			$theme_name = $admin->q[2];

			$files = [];
			$file_name = isset($admin->q[3]) ? implode('/', array_slice($admin->q, 3)) : false;

			if ($theme_files = $theme->theme_files($theme_name, null, true)) {
				if (count($theme_files) < 1)
					redirect(BASE . '/' . ADMIN_DIR . '/themes');
				else if ($file_name === false)
					redirect(BASE . '/' . ADMIN_DIR . '/themes/edit/' . $theme_name . '/' . current($theme_files));

				if (isset($_POST['text']) && $file_name !== false) {
					$is_ajax = (isset($_POST['action']) && $_POST['action'] == 'save') ? true : false;

					if (file_put_contents(PATH . DS . 'themes' . DS . $theme_name . DS . $file_name, $_POST['text']))
						if ($is_ajax === true)
							die('OK');
						else
							$ajax_message = SENTENCE_12;
					else
						if ($is_ajax === true)
							exit;
						else
							$ajax_message = SENTENCE_11;
				}

				foreach ($theme_files as $file) {
					$files[] = '<li' . ($file == $file_name ? ' class="selected"' : null) . '><a href="' . BASE . '/' . ADMIN_DIR . '/themes/edit/' . $theme_name . '/' . $file . '">' . $file . '</a></li>';

					if (count($files) === 7) {
						$files[] = '<li><a href="' . BASE . '/' . ADMIN_DIR . '/themes/edit/' . $theme_name . '">&hellip;</a></li>';
						break;
					}
				}
			} else
				redirect(BASE . '/' . ADMIN_DIR . '/themes');


			$theme_info = $theme->theme_info($theme_name);

			$admin->title = $theme_info['name'];

			$admin->head = '<script type="text/javascript" src="' . BASE . '/' . ADMIN_DIR . '/misc/js/themes.js"></script>';

			$data = file_get_contents(PATH . DS . 'themes' . DS . $theme_name . DS . $file_name);
			$data = htmlspecialchars($data);

			$admin->content = '<ol class="breadcrumb">
					<li>
						<i class="fa fa-desktop"></i> <a href="' . BASE . '/' . ADMIN_DIR . '/themes">' . THEMES . '</a>
					</li>
					<li class="actives">
						' . $theme_info['name'] . '
					</li>
			</ol>

			<div class="edit-theme">
			<form name="edit-theme" action="' . BASE . '/' . ADMIN_DIR . '/themes/edit/' . $theme_name . '/' . $file_name . '" method="post">
			<div>
				<ul class="files list-unstyled">' . (count($files) > 0 ? implode("\n", $files) : null) . '</ul>
				<textarea id="text" name="text" class="form-control" data-change="false">' . $data . '</textarea>
				<div style="clear:both;"></div>
			</div>
			<div class="action-bar">
				<button id="save" class="btn btn-primary">' . SAVE_CHANGES . '</button>
				<span class="status" data-saving="' . SAVING . '&hellip;" data-saved="' . SENTENCE_12 . '" data-error="' . SENTENCE_11 . '" data-nochange="' . SENTENCE_10 .'">' . (isset($ajax_message) ? $ajax_message : null) . '</span>
			</div>
			</form>
			</div>';
		} else {
			$admin->title = ERROR;
			$admin->content = SENTENCE_9;
		}
		break;

	case 'set':
		if (isset($admin->q[2]) && in_array($admin->q[2], $themes)) {
			$theme_name = $admin->q[2];

			$admin->db->prepare('UPDATE ' . DB_PRFX . 'config SET value = ? WHERE name = ?')->execute([$theme_name, 'theme']);
		}

		redirect(BASE . '/' . ADMIN_DIR . '/themes');
		break;
}

?>