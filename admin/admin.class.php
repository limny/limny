<?php

/**
 * Administration methods
 *
 * @package Limny
 * @author Hamid Samak <hamid@limny.org>
 * @copyright 2009-2016 Limny
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class Admin {
	// page query parameter
	public $q;

	// database connection
	public $db;

	// pages inside page.class.php
	public $pages_in_method = [
		'signin',
		'signout',
		'forgotpassword',
		'secimage'
	];

	// pages inside pages directory
	public $pages_in_file = [
		'dashboard',
		'blocks',
		'config',
		'apps',
		'themes',
		'widgets',
		'users',
		'roles',
		'profile',
		'menu'
	];

	// HTML tags inside <head>
	public $head;
	
	// page title
	public $title;

	// page content
	public $content;

	// language direction
	// false means left-to-right
	public $direction = false;

	/**
	 * set database, configuration and page query parameter
	 * @param object $registry
	 */
	public function __construct($registry){
		$this->db = $registry->db;
		$this->config = $registry->config;

		if (isset($_GET['q']) && empty($_GET['q']) === false)
			$this->q = array_filter(explode('/', $_GET['q']));

		if (empty($this->q[0]))
			$this->q[0] = 'dashboard';
		
		return true;
	}

	/**
	 * load proper language file
	 * @return boolean
	 */
	public function load_language() {
		$lang = $this->config->language;

		if (strlen($lang) === 2) {
			$lang_file = PATH . DS . 'langs' . DS . $lang . DS . 'admin.php';
			
			if (file_exists($lang_file)) {
				require_once $lang_file;

				if ($data = $this->lang_info($lang))
					if (isset($data['direction']) && empty($data['direction']) === false && strtolower($data['direction']) != 'ltr')
						$this->direction = $data['direction'];
				
				return true;
			}
		}

		die('Limny error: Language not found.');
	}

	/**
	 * navigation panel in sidebar
	 * @return boolean/string
	 */
	public function navigation() {
		$result = $this->db->query('SELECT title, query FROM ' . DB_PRFX . 'adminnav ORDER BY id ASC');
		
		while ($adminnav = $result->fetch(PDO::FETCH_ASSOC)) {
			if (($position = strpos($adminnav['query'], '/')) === false)
				$app = $adminnav['query'];
			else
				$app = substr($adminnav['query'], 0, $position);

			if (isset($items[$app]) === false)
				$items[$app]['parent'] = $adminnav;
			else
				$items[$app]['childs'][] = $adminnav;
		}

		if (isset($items) === false)
			return false;

		$data = '';
		$current_q = implode('/', $this->q);

		foreach ($items as $app => $item) {
			if (isset($app_load_language[$app]) === false) {
				$this->app_load_language($app);

				$app_load_language[$app] = true;
			}

			$item['parent']['title'] = $this->item_title($app, $item['parent']['title'], isset($app_load_language[$app]));

			$data .= '<li class="{PARENT_ACTIVE}"><a href="' . BASE . '/' . ADMIN_DIR . '/' . $item['parent']['query'] . '" class="' . ($current_q == $item['parent']['query'] ? 'active' : '{PARENT_ACTIVE}') . '"> ' . $item['parent']['title'];

			if (isset($item['childs'])) {
				$data .= '<span class="fa arrow"></span></a>';
				$data .= '<ul class="nav nav-second-level' . ($this->q[0] == $item['parent']['query'] ? ' collapse in' : null) . '">';

				foreach ($item['childs'] as $child) {
					$child['title'] = $this->item_title($app, $child['title'], true);

					if ($current_q == $child['query'] || strpos($current_q, $child['query']) !== false) {
						$active = 'active';
					} else
						$active = null;

					$data .= '<li><a href="' . BASE . '/' . ADMIN_DIR . '/' . $child['query'] . '" class="' . $active . '">' . $child['title'] . '</a></li>';
				}

				$data .= '</ul>';
			} else
				$data .= '</a>';

			$replace = (strpos($data, '"active"') === false && strpos($current_q, $item['parent']['query'] . '/') !== false);
			$data = str_replace('{PARENT_ACTIVE}', ($replace ? 'active' : ''), $data);

			$data .= '</li>';
		}

		return $data;
	}

	/**
	 * read constant from application language file
	 * @param  string  $app            application name
	 * @param  string  $title          constant name
	 * @param  boolean $lang_is_loaded is language file already loaded
	 * @return string
	 */
	public function item_title($app, $title, $lang_is_loaded = false) {
		if (ctype_upper(str_replace('_', '', $title))) {
			if ($lang_is_loaded === false)
				$this->app_load_language($app);

			if (defined($title))
				return constant($title);
		}

		return $title;
	}

	/**
	 * set administrator authentication if sign-in information is stored
	 * @return boolean
	 */
	public function is_remembered() {
		if (isset($_COOKIE['limny_admin'])) {
			$hash = $_COOKIE['limny_admin'];

			$result = $this->db->prepare('SELECT * FROM ' . DB_PRFX . 'users WHERE ip = INET_ATON(?) AND hash = ?');
			$result->execute([$_SERVER['REMOTE_ADDR'], $hash]);

			if ($admin = $result->fetch(PDO::FETCH_ASSOC)) {
				$_SESSION['limny']['admin'] = $admin;

				$new_hash = rand_hash(128);

				$this->db->prepare('UPDATE ' . DB_PRFX . 'users SET hash = ? WHERE id = ?')->execute([$new_hash, $admin['id']]);

				setcookie('limny_admin', $new_hash);

				redirect(BASE . '/' . ADMIN_DIR);
			}

			setcookie('limny_admin', '', time() - 2592000, '/');
		}

		return false;
	}

	/**
	 * get available languages
	 * @return array
	 */
	public function languages() {
		$langs_path = PATH . DS . 'langs';
		
		$langs = scandir($langs_path);
		$langs = array_diff($langs, ['.', '..']);

		foreach ($langs as $key => $lang) {
			if ($data = $this->lang_info($lang))
				if (isset($data['name']) && empty($data['name']) === false) {
					$langs[$lang] = $data['name'];
					
					unset($langs[$key]);
					continue;
				}

			unset($langs[$key]);
		}

		return $langs;
	}

	/**
	 * get language information
	 * @param  string        $lang language code
	 * @return array/boolean
	 */
	public function lang_info($lang) {
		$lang_info_file = PATH . DS . 'langs' . DS . $lang . DS . 'lang.ini';
		
		if (file_exists($lang_info_file))
			return parse_ini_file($lang_info_file);

		return false;
	}

	/**
	 * get system roles
	 * @return array
	 */
	public function roles() {
		$roles = [];
		
		$result = $this->db->query('SELECT id, name FROM ' . DB_PRFX . 'roles ORDER BY id');
		
		while ($role = $result->fetch(PDO::FETCH_ASSOC))
			$roles[$role['id']] = $role['name'];

		return $roles;
	}

	/**
	 * check compatibility version with core version
	 * @param  string  $version
	 * @param  string  $core
	 * @return boolean
	 */
	public function is_compatible($version, $core) {
		$version = (string) $version;
		$core = (string) $core;

		$star_position = strpos($version, '*');

		if ($star_position !== false) {
			$part = substr($core, 0, $star_position);

			if ($part != substr($version, 0, $star_position))
				return false;
			else if (strlen($version) != strlen($core))
				return false;
		} else if ($core !== $version)
			return false;

		return true;
	}

	/**
	 * get available themes
	 * @return array
	 */
	public function themes() {
		$themes_path = PATH . DS . 'themes';
		
		$themes = scandir($themes_path);
		$themes = array_diff($themes, ['.', '..']);

		foreach ($themes as $key => $theme) {
			if (is_dir($themes_path . DS . $theme) === false)
				continue;
			
			$theme_info_file = $themes_path . DS . $theme . DS . 'theme.ini';

			if (file_exists($theme_info_file)) {
				$data = parse_ini_file($theme_info_file);
				
				if (isset($data['name']) && empty($data['name']) === false) {
					$themes[$theme] = $data['name'];
					
					unset($themes[$key]);
					continue;
				}
			}

			unset($themes[$key]);
		}

		return $themes;
	}

	/**
	 * load application language
	 * @param  string $app_name
	 * @return boolean
	 */
	public function app_load_language($app_name) {
		$lang = $this->config->language;

		foreach ([$lang, 'en'] as $lang) {
			$app_name = str_replace(['.', '/', '\\'], '', $app_name);
			$lang_file = PATH . DS . 'apps' . DS . $app_name . DS . 'langs' . DS . $lang . '.php';
			
			if (file_exists($lang_file)) {
				require_once $lang_file;
				
				return true;
			}
		}
		
		return false;
	}

	/**
	 * is there any application installed with given page query parameter
	 * @param  array   $q page query parameter
	 * @return boolean
	 */
	public function is_app($q = []) {
		if (count($q) < 1)
			$q = $this->q;

		$application = load_lib('application', true, true);
		$apps = $application->apps(null);
		$app_names = array_keys($apps);
		
		if (isset($q[0]) && in_array($q[0], $app_names))
			return true;

		return false;
	}
}

?>