<?php

class Widget extends Form {
	public $widgets_path;

	public function __construct() {
		$this->widgets_path = PATH . DS . 'widgets';
	}
	
	public function widget($widget_id = null) {
		if (empty($widget_id))
			return false;

		global $db;

		$result = $db->prepare('SELECT * FROM ' . DB_PRFX . 'widgets WHERE id = ?');
		$result->execute([$widget_id]);
		
		if ($widget = $result->fetch(PDO::FETCH_ASSOC))
			return $widget;
		
		return false;
	}

	public function widgets($app = 'all') {
		global $db;

		$widgets = [];
		$is_all = $app === 'all' ? true : false;

		$result = $db->prepare('SELECT * FROM ' . DB_PRFX . 'widgets ' . ($is_all ? null : 'WHERE app = ?') . ' ORDER BY sort');
		
		$array = $is_all !== true ? [$app] : [];

		$result->execute($array);
		
		$data = $result->fetchAll(PDO::FETCH_ASSOC);

		foreach ($data as $widget)
			$widgets[] = $widget;

		return $widgets;
	}

	public function update_position($widget_id, $position, $sort) {
		global $db;

		$db->prepare('UPDATE ' . DB_PRFX . 'widgets SET position = ? WHERE id = ?')->execute([$position, $widget_id]);

		if (is_array($sort) && count($sort) > 0) {
			$i = 1;
			
			foreach ($sort as $widget_id) {
				$db->prepare('UPDATE ' . DB_PRFX . 'widgets SET sort = ? WHERE id = ?')->execute([$i, $widget_id]);

				$i = $i + 1;
			}
		}

		return true;
	}

	public function options_list($widget_id) {
		$widget = $this->widget($widget_id);
		
		$app_path = PATH . DS . 'apps' . DS . $widget['app'] . DS;
		
		if (file_exists($app_path . 'widget.class.php') === false)
			return 'Limny error: Application widget file not found.';

		require_once $app_path . 'widget.class.php';
		$class_name = ucfirst($widget['app']) . 'Widget';
		
		if (class_exists($class_name)) {
			$widget_class = new $class_name();

			if (property_exists($class_name, $widget['method']) === false)
				return 'Limny error: Application widget property not found.';

			$widget_options = $widget_class->{$widget['method']};

			$this->form_options = $widget_options;
			$this->form_values = empty($widget['options']) ? [] : unserialize($widget['options']);

			if ($form = $this->fields()) {
				foreach ($form as $label => $element)
					$data[] = '<label>' . $label . ':</label>' . $element;

				$data = implode('<hr>', $data);
				$data = '<form>' . $data . '<button id="update-options" type="button" class="btn btn-primary">' . UPDATE . '</button><div style="clear:both;"></div></form>';

				return $data;
			}

		} else
			return 'Limny error: Application widget class not found.';

		return false;
	}

	public function update_options($widget_id, $options) {
		global $db;

		parse_str($options, $options);
		$options = serialize($options);

		return $db->prepare('UPDATE ' . DB_PRFX . 'widgets SET options = ? WHERE id = ?')->execute([$options, $widget_id]);

		return $result;
	}

	public function update_visibility($widget_id, $roles = null, $langs = null) {
		global $db, $admin;

		if (empty($roles) === false) {
			$all_roles = $admin->roles();

			foreach ($roles as $id => $status)
				if ($status != 'true')
					unset($roles[$id]);

			$roles = count($roles) == count($all_roles) + 1 ? 'all' : implode(',', array_keys($roles));

			return $db->prepare('UPDATE ' . DB_PRFX . 'widgets SET roles = ? WHERE id = ?')->execute([$roles, $widget_id]);
		} else if (empty($langs) === false) {
			$all_langs = $admin->languages();

			foreach ($langs as $code => $status)
				if ($status != 'true')
					unset($langs[$code]);

			$langs = count($langs) == count($all_langs) ? 'all' : implode(',', array_keys($langs));

			return $db->prepare('UPDATE ' . DB_PRFX . 'widgets SET languages = ? WHERE id = ?')->execute([$langs, $widget_id]);
		}

		return false;
	}

	public function install_widget($widget_name) {
		global $db;

		return $db->prepare('INSERT INTO ' . DB_PRFX . 'widgets (app, method, position, roles, languages) VALUES (?, ?, ?, ?, ?)')->execute(['widget', $widget_name, 'none', 'all', 'all']);
	}

	public function uninstall_widget($widget_id) {
		global $db;

		if ($widget = $this->widget($widget_id)) {

			$widget_directory = PATH . DS . 'widgets' . DS . $widget['method'];

			if (file_exists($widget_directory) && is_dir($widget_directory)) {
				$files = [$widget['method'] . '.php', 'widget.ini'];

				foreach ($files as $file)
					if (file_exists($widget_directory . DS . $file))
						unlink($widget_directory . DS . $file);

				rmdir($widget_directory);
			}

			$db->prepare('DELETE FROM ' . DB_PRFX . 'widgets WHERE id = ?')->execute([$widget_id]);

			return true;
		}

		return false;
	}
}

?>