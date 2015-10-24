<?php

/**
 * fetch widgets from database and check is visble whether or not
 *
 * @package Limny
 * @author Hamid Samak <hamid@limny.org>
 * @copyright 2009-2015 Limny
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class Widget extends CoreModel {
	/**
	 * fetch widgest by position
	 * positions are defined in theme schema.tpl
	 * @param  string $position block position
	 * @return array
	 */
	public function widgets($position) {
		$result = $this->db->prepare('SELECT id, app, method, options, lifetime, roles, languages FROM ' . DB_PRFX . 'widgets WHERE position = ? ORDER BY sort');
		$result->execute([$position]);
		
		while ($widget = $result->fetch(PDO::FETCH_ASSOC)) {
			if ($this->is_visible($widget) === false)
				continue;

			$cache_file = PATH . DS . 'cache' . DS . md5($widget['id'] . $widget['app'] . $widget['method']);

			if ($widget['lifetime'] > time() && file_exists($cache_file) && $data = file_get_contents($cache_file))
				$widgets[] = unserialize($data);
			else
				$widgets[] = $widget;
		}
		
		return isset($widgets) ? $widgets : false;
	}

	/**
	 * check widget is visible in language or for role
	 * @param  array  $widget  widget row from widgets table
	 * @return boolean
	 */
	protected function is_visible($widget) {
		$roles = ['0'];
		$language = language();

		if (user_signed_in()) {
			$roles = $_SESSION['limny']['user']['roles'];
			$roles = empty($roles) ? [] : explode(',', $roles);
		}

		if ($widget['roles'] !== 'all') {
			$role_visible = false;
			$widget_roles = explode(',', $widget['roles']);

			foreach ($roles as $role)
				if (in_array($role, $widget_roles)) {
					$role_visible = true;
					break;
				}

			if ($role_visible === false)
				return false;
		}
		
		if ($widget['languages'] !== 'all') {
			$widget_langs = explode(',', $widget['languages']);
			
			if (in_array($language, $widget_langs) === false)
				return false;
		}

		return true;
	}
}

?>