<?php

/**
 * Administration widget methods
 *
 * @package Limny
 * @author Hamid Samak <hamid@limny.org>
 * @copyright 2009-2015 Limny
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class Widget extends Form {
	// widgets directory
	public $widgets_path;

	// database connection
	private $db;

	/**
	 * set database connection property and widgets directory path
	 * @param  object
	 * @return void
	 */
	public function __construct($registry) {
		$this->db = $registry->db;

		$this->widgets_path = PATH . DS . 'widgets';
	}
	
	/**
	 * get single widget by id
	 * @param  integer       $widget_id
	 * @return array/boolean
	 */
	public function widget($widget_id = null) {
		if (empty($widget_id))
			return false;

		$result = $this->db->prepare('SELECT * FROM ' . DB_PRFX . 'widgets WHERE id = ?');
		$result->execute([$widget_id]);
		
		if ($widget = $result->fetch(PDO::FETCH_ASSOC))
			return $widget;
		
		return false;
	}

	/**
	 * get widgets
	 * @param  string $app all widgets or widgets of a specific application
	 * @return array
	 */
	public function widgets($app = 'all') {
		$widgets = [];
		$is_all = $app === 'all' ? true : false;

		$result = $this->db->prepare('SELECT * FROM ' . DB_PRFX . 'widgets ' . ($is_all ? null : 'WHERE app = ?') . ' ORDER BY sort');
		
		$array = $is_all !== true ? [$app] : [];

		$result->execute($array);
		
		$data = $result->fetchAll(PDO::FETCH_ASSOC);

		foreach ($data as $widget)
			$widgets[] = $widget;

		return $widgets;
	}

	/**
	 * set widget position
	 * @param  integer $widget_id
	 * @param  string  $position  new position
	 * @param  integer $sort      order in position
	 * @return boolean
	 */
	public function update_position($widget_id, $position, $sort) {
		$this->db->prepare('UPDATE ' . DB_PRFX . 'widgets SET position = ? WHERE id = ?')->execute([$position, $widget_id]);

		if (is_array($sort) && count($sort) > 0) {
			$i = 1;
			
			foreach ($sort as $widget_id) {
				$this->db->prepare('UPDATE ' . DB_PRFX . 'widgets SET sort = ? WHERE id = ?')->execute([$i, $widget_id]);

				$i = $i + 1;
			}
		}

		return true;
	}

	/**
	 * make widget options elements
	 * @param  integer        $widget_id
	 * @return string/boolean            HTML elements in string result
	 */
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

	/**
	 * set widget options
	 * @param  integer $widget_id
	 * @param  array   $options
	 * @return boolean
	 */
	public function update_options($widget_id, $options) {
		parse_str($options, $options);
		$options = serialize($options);

		return $this->db->prepare('UPDATE ' . DB_PRFX . 'widgets SET options = ? WHERE id = ?')->execute([$options, $widget_id]);
	}

	/**
	 * set widget visibility for given roles or languages
	 * @param  integer $widget_id
	 * @param  array   $roles     ids in array
	 * @param  array   $langs     language codes in array
	 * @return boolean
	 */
	public function update_visibility($widget_id, $roles = null, $langs = null) {
		global $admin;

		if (empty($roles) === false) {
			$all_roles = $admin->roles();

			foreach ($roles as $id => $status)
				if ($status != 'true')
					unset($roles[$id]);

			$roles = count($roles) == count($all_roles) + 1 ? 'all' : implode(',', array_keys($roles));

			return $this->db->prepare('UPDATE ' . DB_PRFX . 'widgets SET roles = ? WHERE id = ?')->execute([$roles, $widget_id]);
		} else if (empty($langs) === false) {
			$all_langs = $admin->languages();

			foreach ($langs as $code => $status)
				if ($status != 'true')
					unset($langs[$code]);

			$langs = count($langs) == count($all_langs) ? 'all' : implode(',', array_keys($langs));

			return $this->db->prepare('UPDATE ' . DB_PRFX . 'widgets SET languages = ? WHERE id = ?')->execute([$langs, $widget_id]);
		}

		return false;
	}

	/**
	 * install widget
	 * @param  string  $widget_name
	 * @return boolean
	 */
	public function install_widget($widget_name) {
		return $this->db->prepare('INSERT INTO ' . DB_PRFX . 'widgets (app, method, position, roles, languages) VALUES (?, ?, ?, ?, ?)')->execute(['widget', $widget_name, 'none', 'all', 'all']);
	}

	/**
	 * uninstall widget
	 * @param  integer $widget_id
	 * @return boolean
	 */
	public function uninstall_widget($widget_id) {
		if ($widget = $this->widget($widget_id)) {

			$widget_directory = PATH . DS . 'widgets' . DS . $widget['method'];

			if (file_exists($widget_directory) && is_dir($widget_directory)) {
				$files = [$widget['method'] . '.php', 'widget.ini'];

				foreach ($files as $file)
					if (file_exists($widget_directory . DS . $file))
						unlink($widget_directory . DS . $file);

				rmdir($widget_directory);
			}

			return $this->db->prepare('DELETE FROM ' . DB_PRFX . 'widgets WHERE id = ?')->execute([$widget_id]);
		}

		return false;
	}
}

?>