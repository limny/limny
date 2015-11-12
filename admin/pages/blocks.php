<?php

$theme = $admin->config->theme;

$theme_schema = PATH . DS . 'themes' . DS . $theme . DS . 'schema.tpl';

$admin->title = BLOCKS;

if (file_exists($theme_schema)) {

	$xml = new DOMDocument();
	$theme_positions = [];
	
	$xml->loadHTML(file_get_contents($theme_schema));
	
	$divs = $xml->getElementsByTagName('div');
	foreach ($divs as $div) {
		$position = $div->getAttribute('data-position');

		if (empty($position) === false && in_array($position, $theme_positions) === false)
			$theme_positions[] = $position;
	}

	load_lib('form');
	$widget = load_lib('widget', true, true);

	if (isset($_POST['action']))
		switch ($_POST['action']) {
			case 'widget-position':
				die($widget->update_position($_POST['id'], $_POST['position'], $_POST['sort']));
				break;

			case 'widget-options':
				if (isset($_POST['options']))
					die($widget->update_options($_POST['id'], $_POST['options']));

				die($widget->options_list($_POST['id']));
				break;

			case 'widget-visibility':
				$roles = isset($_POST['roles']) ? $_POST['roles'] : null;
				$langs = isset($_POST['langs']) ? $_POST['langs'] : null;

				die($widget->update_visibility($_POST['id'], $roles, $langs));
				break;
		}

	$all_widgets = $widget->widgets();

	$available_widgets = [];

	$roles = $admin->roles();
	$languages = $admin->languages();

	if (count($all_widgets) > 0)
		foreach ($all_widgets as $widget) {
			if ($widget['app'] === 'limny') {
				$length = strpos($widget['method'], '_');

				if ($length === false)
					$length = strlen($widget['method']);
				
				$widget_name = substr($widget['method'], 0, $length);
			} else if ($widget['app'] == 'widget')
				$widget_name = $widget['method'];
			else
				$widget_name = $widget['app'];

			$widget_roles = $widget['roles'] == 'all' ? [] : explode(',', $widget['roles']);
			$widget_languages = $widget['languages'] == 'all' ? [] : explode(',', $widget['languages']);
			
			$visibility = '<ul class="visibility">';

			foreach ($languages as $code => $name) {
				$status = empty($widget_languages) || in_array($code, $widget_languages) ? 'on' : 'off';

				$visibility .= '<li class="item">' . $name . ' (' . $code . ') <a href="#" class="lang ' . $status . '" data-code="' . $code . '" data-disabled="false"><i class="fa fa-toggle-' . $status . ' fa-fw fa-lg"></i></a></li>';
			}
			
			$visibility .= '<li class="sep"></li>';

			$status =  empty($widget_roles) || in_array('0', $widget_roles) ? 'on' : 'off';

			$visibility .= '<li class="item text-gray">' . UNSIGNED . ' <a href="#" class="role ' . $status . '" data-id="0" data-disabled="false"><i class="fa fa-toggle-' . $status . ' fa-fw fa-lg"></i></a></li>';

			if (count($roles) > 0)
				foreach ($roles as $id => $name) {
					$status =  empty($widget_roles) || in_array($id, $widget_roles) ? 'on' : 'off';

					$visibility .= '<li class="item">' . $name . ' <a href="#" class="role ' . $status . '" data-id="' . $id . '" data-disabled="false"><i class="fa fa-toggle-' . $status . ' fa-fw fa-lg"></i></a></li>';
				}
			
			$visibility .= '</ul>';

			$options = '<a href="#" class="visibility-toggle" title="' . VISIBILITY . '"><i class="fa fa-flag fa-fw"></i></a><div class="visibility">' . $visibility . '</div>';

			$show_options = false;

			if ($widget['app'] == 'widget')
				$show_options = true;
			else if ($widget['app'] != 'limny') {
				$widget_file = PATH . DS . 'apps' . DS . $widget['app'] . DS . 'widget.class.php';

				if (file_exists($widget_file)) {
					require_once $widget_file;

					$widget_class = ucfirst($widget['app']) . 'Widget';

					if (class_exists($widget_class)) {
						$widget_object = new $widget_class();

						if (property_exists($widget_object, $widget['method']))
							$show_options = true;
					}
				}
			}

			if ($show_options === true)
				$options = ' <a href="#" class="option-toggle"><i class="fa fa-gear fa-fw"></i></a> ' . $options . '<div class="options" data-empty="true"><div class="loading"></div></div>';

			$widget_item = '<li data-id="' . $widget['id'] . '"><span>' . ucfirst($widget_name) . '</span>' . $options . '</li>';

			if (in_array($widget['position'], $theme_positions))
				$widgets[$widget['position']][] = $widget_item;
			else
				$available_widgets[] = $widget_item;
		}


	foreach ($theme_positions as $position) {
		$widgets[$position] = '<ul class="placeholder" data-position="' . $position . '">' . (isset($widgets[$position]) ? implode("\n", $widgets[$position]) : null) . '</ul>';
	}
	
	ob_start();
	include($theme_schema);
	$contents = ob_get_contents();
	ob_end_clean();
	
	$admin->content = '<div id="schema">' . $contents . ' <div style="clear:both;"></div> </div>';

	$admin->content .= '<ul id="available-widgets" class="placeholder" data-position="none">
		' . implode("\n", $available_widgets) . '
</ul>';
	
	$admin->head = '<script type="text/javascript" src="' . BASE . '/' . ADMIN_DIR . '/misc/jquery-ui/jquery-ui.min.js"></script>';
	$admin->head .= '<script type="text/javascript" src="' . BASE . '/' . ADMIN_DIR . '/misc/js/blocks.js"></script>';
} else
	$admin->content = 'Theme schema.tpl not found.';

?>