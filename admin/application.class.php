<?php

/**
 * Administration application methods
 *
 * @package Limny
 * @author Hamid Samak <hamid@limny.org>
 * @copyright 2009-2015 Limny
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class Application extends Admin {
	// applications path
	public $apps_path;

	// database connection
	public $db;

	// registry object
	private $registry;

	/**
	 * set database connection and registry property
	 * @param void
	 */
	public function __construct($registry) {
		$this->db = $registry->db;
		$this->registry = $registry;

		$this->apps_path = PATH . DS . 'apps';
	}

	/**
	 * get list of all applications
	 * @return array
	 */
	public function all_apps() {
		$all_apps = [];

		foreach (scandir($this->apps_path) as $file)
			if (substr($file, 0, 1) !== '.' && file_exists($this->apps_path . DS . $file . DS . 'app.ini'))
				$all_apps[] = $file;

		return $all_apps;
	}

	/**
	 * get list of applications by given status and type
	 * @param  boolean $enabled application status
	 * @param  string  $type    rich/lib
	 * @return array           
	 */
	public function apps($enabled = null, $type = null) {
		if (empty($enabled) === false) {
			$enabled = '1';
			$clause = 'WHERE enabled = ?';
		} else
			$clause = '';
		
		$result = $this->db->prepare('SELECT * FROM ' . DB_PRFX . 'apps ' . $clause);
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

	/**
	 * set application status
	 * @param  integer $app_id
	 * @param  string  $enabled true/false
	 * @return string
	 */
	public function update_enabled($app_id, $enabled) {
		if ($enabled === 'true')
			$enabled = '1';
		else
			$enabled = '0';

		$this->db->prepare('UPDATE ' . DB_PRFX . 'apps SET enabled = ? WHERE id = ?')->execute([$enabled, $app_id]);

		return 'OK';
	}

	/**
	 * load application setup library
	 * @param  string $app_name
	 * @return object/boolean
	 */
	private function app_setup($app_name) {
		$app_file = $this->apps_path . DS . $app_name . DS . 'app.class.php';

		if (file_exists($app_file)) {
			require $app_file;

			$app_class = ucfirst($app_name) . 'App';

			if (class_exists($app_class)) {
				$app_object = new $app_class($this->registry);

				return $app_object;
			}
		}

		return false;
	}

	/**
	 * remove application
	 * @param  integer $app_id
	 * @return boolean
	 */
	public function uninstall_app($app_id) {
		$result = $this->db->prepare('SELECT name, required_by FROM ' . DB_PRFX . 'apps WHERE id = ?');
		$result->execute([$app_id]);
		
		if ($app = $result->fetch(PDO::FETCH_ASSOC)) {

			if (empty($app['required_by']) === false)
				return false;
			
			$result = $this->db->prepare('SELECT id, required_by FROM ' . DB_PRFX . 'apps WHERE FIND_IN_SET(?, required_by)');
			$result->execute([$app['name']]);

			while ($dependent = $result->fetch(PDO::FETCH_ASSOC)) {
				$required_by = explode(',', $dependent['required_by']);

				if (($key = array_search($app['name'], $required_by)) !== false) {
					unset($required_by[$key]);

					$update = $this->db->prepare('UPDATE ' . DB_PRFX . 'apps SET required_by = ? WHERE id = ?');
					$update->execute([implode(',', $required_by), $dependent['id']]);
				}
			}

			if ($app_object = $this->app_setup($app['name']))
				if (method_exists($app_object, 'uninstall'))
					$app_object->uninstall();

			$this->db->prepare('DELETE FROM ' . DB_PRFX . 'apps WHERE id = ?')->execute([$app_id]);
		}
		
		return true;
	}

	/**
	 * install new application
	 * @param  string $app_name
	 * @return boolean
	 */
	public function install_app($app_name) {
		global $admin;

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

					$this->db->prepare('UPDATE ' . DB_PRFX . 'apps SET required_by = IF(required_by IS NULL OR required_by = \'\', ?, CONCAT(required_by, \',\', ?)) WHERE name = ?')->execute([$app_name, $app_name, $app]);
				}

			}
		} else
			return false;
		
		if ($app_object = $this->app_setup($app_name))
			if (method_exists($app_object, 'install'))
				$app_object->install();

		$this->db->prepare('INSERT INTO ' . DB_PRFX . 'apps (name, enabled, required_by) VALUES (?, 0, null)')->execute([$app_name]);
		
		return true;
	}

	/**
	 * get application information
	 * @param  string $app_name
	 * @return array/boolean
	 */
	public function app_info($app_name) {
		$info_file = PATH . DS . DS . 'apps' . DS . $app_name . DS . 'app.ini';

		if (file_exists($info_file))
			return parse_ini_file($info_file);

		return false;
	}

	/**
	 * load application files and prepare proper page by given page query parameter
	 * @param  array $q page query parameter
	 * @return array/boolean
	 */
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

			$app_controller = new $app_controller_name($this->registry);

			$app_controller->q = $q;

			foreach (['__global', $app_method, '__404'] as $method_name)
				if (method_exists($app_controller, $method_name) && is_callable([$app_controller, $method_name])) {
					if ($method_name === '__404') {
						header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found', true, 404);
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