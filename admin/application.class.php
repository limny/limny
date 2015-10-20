<?php

class Application {
	public $apps_path;

	public function __construct() {
		$this->apps_path = PATH . DS . 'apps';
	}

	public function all_apps() {
		$all_apps = [];

		foreach (scandir($this->apps_path) as $file)
			if (substr($file, 0, 1) !== '.' && file_exists($this->apps_path . DS . $file . DS . 'app.ini'))
				$all_apps[] = $file;

		return $all_apps;
	}

	public function apps($enabled = null, $type = null) {
		global $db;

		if (empty($enabled) === false) {
			$enabled = '1';
			$clause = 'WHERE enabled = ?';
		} else
			$clause = '';
		
		$result = $db->prepare('SELECT * FROM ' . DB_PRFX . 'apps ' . $clause);
		$binds = empty($clause) ? [] : [$enabled];
		$result->execute($binds);
		$apps = [];

		while ($app = $result->fetch(PDO::FETCH_ASSOC)) {
			if ($data = $this->app_info($app['name'])) {
				if (isset($data['type']) === false)
					$data['type'] = 'rich';

				if (empty($type) === false && $data['type'] !== $type)
					continue;
			}
			
			$apps[$app['name']] = [
				'id' => $app['id'],
				'name' => @$data['name'],
				'enabled' => $app['enabled'],
				'required_by' => $app['required_by'],
			];
		}
		
		return $apps;
	}

	public function update_enabled($app_id, $enabled) {
		global $db;

		if ($enabled === 'true')
			$enabled = '1';
		else
			$enabled = '0';

		$db->prepare('UPDATE ' . DB_PRFX . 'apps SET enabled = ? WHERE id = ?')->execute([$enabled, $app_id]);

		return 'OK';
	}

	private function app_setup($app_name) {
		$app_file = $this->apps_path . DS . $app_name . DS . 'app.class.php';

		if (file_exists($app_file)) {
			require $app_file;

			$app_class = ucfirst($app_name) . 'App';

			if (class_exists($app_class)) {
				$app_object = new $app_class();

				return $app_object;
			}
		}

		return false;
	}

	public function uninstall_app($app_id) {
		global $db;

		$result = $db->prepare('SELECT name, required_by FROM ' . DB_PRFX . 'apps WHERE id = ?');
		$result->execute([$app_id]);
		
		if ($app = $result->fetch(PDO::FETCH_ASSOC)) {

			if (empty($app['required_by']) === false)
				return false;
			
			$result = $db->prepare('SELECT id, required_by FROM ' . DB_PRFX . 'apps WHERE FIND_IN_SET(?, required_by)');
			$result->execute([$app['name']]);

			while ($dependent = $result->fetch(PDO::FETCH_ASSOC)) {
				$required_by = explode(',', $dependent['required_by']);

				if (($key = array_search($app['name'], $required_by)) !== false) {
					unset($required_by[$key]);

					$update = $db->prepare('UPDATE ' . DB_PRFX . 'apps SET required_by = ? WHERE id = ?');
					$update->execute([implode(',', $required_by), $dependent['id']]);
				}
			}

			if ($app_object = $this->app_setup($app['name']))
				if (method_exists($app_object, 'uninstall'))
					$app_object->uninstall();

			$db->prepare('DELETE FROM ' . DB_PRFX . 'apps WHERE id = ?')->execute([$app_id]);
		}
		
		return true;
	}

	public function install_app($app_name) {
		global $db, $admin;

		$apps = $this->apps();
		if (in_array($app_name, array_keys($apps)))
			return false;

		if ($data = $this->app_info($app_name)) {
			if (isset($data['dependson']) && empty($data['dependson']) === false) {
				$depends_on = explode(',', $data['dependson']);
				
				foreach ($depends_on as $app) {
					if (($space_pos = strpos($app, ' ')) !== false) {
						$dep_app_name = substr($app, 0, $space_pos);
						$dep_app_version = substr($app, $space_pos + 1);
					} else {
						$dep_app_name = $app;
						$dep_app_version = null;
					}

					$dep_app_name = trim($dep_app_name);

					if (in_array($dep_app_name, array_keys(array_keys($apps))) === false)
						return false;
					else if (empty($dep_app_version) === false)
						if ($dependent_data = $this->app_info($dep_app_name)) {
							if (isset($dependent_data['version']) === false || $admin->is_compatible($dep_app_version, $dependent_data['version']) === false)
								return false;
						} else
							return false;
				}

				foreach ($depends_on as $app) {
					if (($space_pos = strpos($app, ' ')) !== false)
						$app = trim(substr($app, 0, $space_pos));

					$db->prepare('UPDATE ' . DB_PRFX . 'apps SET required_by = IF(required_by IS NULL OR required_by = \'\', ?, CONCAT(required_by, \',\', ?)) WHERE name = ?')->execute([$app_name, $app_name, $app]);
				}

			}
		} else
			return false;
		
		if ($app_object = $this->app_setup($app_name))
			if (method_exists($app_object, 'install'))
				$app_object->install();

		$db->prepare('INSERT INTO ' . DB_PRFX . 'apps (name, enabled, required_by) VALUES (?, 0, null)')->execute([$app_name]);
		
		return true;
	}

	public function app_info($app_name) {
		$info_file = PATH . DS . DS . 'apps' . DS . $app_name . DS . 'app.ini';

		if (file_exists($info_file))
			return parse_ini_file($info_file);

		return false;
	}

	public function app_admin($q) {
		$app_name = $q[0];
		$app_method = isset($q[1]) ? $q[1] : '__default';
		
		$app_controller_file = $this->apps_path . DS . $app_name . DS . 'admin.controller.class.php';
		if (file_exists($app_controller_file)) {
			load_lib('manage', false, true);

			$app_model_file = $this->apps_path . DS . $app_name . DS . 'admin.model.class.php';
			if (file_exists($app_model_file))
				require_once $app_model_file;

			require_once $app_controller_file;

			$app_controller_name = ucfirst($app_name) . 'AdminController';

			if (class_exists($app_controller_name) === false)
				die('Limny error: Application admin controller not found.');

			$app_controller = new $app_controller_name();

			$app_controller->q = $q;

			foreach (['__global', $app_method, '__404'] as $method_name)
				if (method_exists($app_controller, $method_name) && is_callable([$app_controller, $method_name])) {
					if ($method_name === '__404') {
						header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
						$is_404 = true;
					}

					$app_controller->{$method_name}();
					
					$head = isset($app_controller->head) ? $app_controller->head : null;
					$title = isset($app_controller->title) ? $app_controller->title : null;
					$content = isset($app_controller->content) ? $app_controller->content : null;

					return [
						'head' => $head,
						'title' => $title,
						'content' => $content,
					];
				}
		} else
			die('Limny error: Application admin controller not found.');

		return false;
	}
}

?>