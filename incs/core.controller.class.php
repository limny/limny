<?php

/**
 * Limny core controller
 *
 * @package Limny
 * @author Hamid Samak <hamid@limny.org>
 * @copyright 2009-2015 Limny
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class CoreController {
	// language code (ISO 639-1)
	public $language;

	// view object
	public $view;

	// query parameter
	private $q;

	// predefined head tags
	private $head;

	// model object
	private $model;

	// application library
	private $app_lib;

	// registry
	private $registry;
	
	/**
	 * set necessary properties
	 * @param object $registry
	 * @return void
	 */
	public function __construct($registry) {
		$this->model = new CoreModel($registry);

		$this->q = $registry->q;
		$this->registry = $registry;
		$this->app_lib = load_lib('application');

		$this->view = new CoreView($registry);

		$this->head = '<link href="' . BASE . '/misc/css/style.css" rel="stylesheet">' . "\n" . '<script type="text/javascript" src="' . BASE . '/misc/js/script.js"></script>';
	}

	/**
	 * launch available startups for enabled applications
	 * @return void
	 */
	private function __startup() {
		$apps = $this->app_lib->apps('1');

		foreach ($apps as $app) {
			$startup = PATH . DS . 'apps' . DS . $app['name'] . DS . 'startup.php';
			
			if (file_exists($startup))
				include $startup;
		}
	}
	
	/**
	 * include system language file
	 * @return boolean
	 */
	private function set_language() {
		if (isset($this->q['lang']))
			$this->language = $this->q['lang'];
		
		if (empty($this->langauge)) {
			$this->language = $this->registry->config->language;

			$lang_file = PATH . DS . 'langs' . DS . $this->language . DS . 'main.php';
			
			if (file_exists($lang_file))
				require_once $lang_file;
			else
				die('Limny error: Language not found.');
		}

		return true;
	}

	/**
	 * set default content based configured options
	 * @return boolean
	 */
	private function default_content() {
		if (empty($this->q['param'][0])) {
			$default = $this->registry->config->default_content;
			
			if ($default == 'app')
				$this->q['param'] = [$this->registry->config->default_app];
			else if ($default == 'query')
				$this->q['param'] = explode('/', $this->registry->config->default_query);
			else if ($default == 'text') {
				$this->view->title = $this->registry->config->title;
				$this->view->content = $this->registry->config->default_text;
			}
		}

		return true;
	}

	/**
	 * load user related contents (sign-up, sign-in, forgot password and update profile)
	 * @return boolean
	 */
	private function user_content() {
		$user = load_lib('user');

		$user_method = 'page_' . (isset($this->q['param'][1]) ? $this->q['param'][1] : 'default');
		
		if (method_exists($user, $user_method)) {
			$user->{$user_method}();

			$this->view->title = $user->title . ' - ' . $this->registry->config->title;
			$this->view->content = $user->content;
		}

		return true;
	}

	/**
	 * set error message and HTTP head
	 * @param  string $title
	 * @param  string $content
	 */
	private function error_message($title, $content) {
		header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found', true, 404);
		
		$this->view->title = $title . ' - ' . $this->registry->config->title;
		$this->view->content = $content;
	}

	/**
	 * get cache file and return contents if exists
	 * @param  string         $cache_file cache file path
	 * @return string/boolean             boolean on error
	 */
	private function read_cache($cache_file) {
		$pragma = isset($_SERVER['HTTP_PRAGMA']) ? $_SERVER['HTTP_PRAGMA'] : null;

		if (file_exists($cache_file) && $pragma != 'no-cache' && time() - filemtime($cache_file) < 0)
			if ($data = unserialize(file_get_contents($cache_file)))
				return $data;
		
		return false;
	}

	/**
	 * load application libraries and create content
	 * @param  string  $app_name
	 * @param  string  $method_name
	 * @param  string  $cache_file
	 * @return boolean              true on page existence and false on page not found
	 */
	private function load_app($app_name, $method_name, $cache_file) {
		$app_path = PATH . DS . 'apps' . DS . $app_name . DS;
		$app_lang_file = $app_path . DS . 'langs' . DS . $this->language . '.php';

		if (file_exists($app_lang_file))
			require_once $app_lang_file;

		if (file_exists($app_path . 'model.class.php'))
			require_once $app_path . 'model.class.php';

		if (file_exists($app_path . 'controller.class.php') === false)
			return false;

		require_once $app_path . 'controller.class.php';
		$app_class = ucfirst($app_name) . 'Controller';
		
		if (class_exists($app_class)) {
			$app_controller = new $app_class($this->registry);
			$app_controller->q = $this->q;
		} else
			die('Limny error: Application controller class not found.');
		
		if (empty($method_name))
			$method_name = '__default';

		foreach (['__global', $method_name, str_replace('-', '_', $method_name), '__404'] as $value)
			if (method_exists($app_controller, $value) && is_callable([$app_controller, $value])) {
				if ($value === '__404') {
					header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found', true, 404);
					$is_404 = true;
				}

				$app_controller_result = $app_controller->{$value}();
				$method_exists = true;

				break;
			}

		if (isset($method_exists) === false || $app_controller_result === false) {
			$this->error_message(ERROR, SENTENCE_3);
			
			return false;
		}
		
		if (file_exists($app_path . 'model.class.php'))
			require_once $app_path . 'model.class.php';

		$this->view->head = $app_controller->head;
		$this->view->title =  $this->registry->config->title;
		
		if (empty($app_controller->title) === false)
			$this->view->title = $app_controller->title . ' - ' . $this->view->title;
		
		$this->view->content = $app_controller->content;
		
		$life_time = $app_controller->cache;
		
		if (is_numeric($life_time) || $life_time === true)
			$this->put_cache($cache_file, $life_time);

		return isset($is_404) ? fales : true;
	}

	/**
	 * put contents to cache file
	 * @param  string  $cache_file
	 * @param  integer $life_time
	 * @return boolean
	 */
	private function put_cache($cache_file, $life_time) {
		file_put_contents($cache_file, serialize([
			'head' => $this->view->head,
			'title' => $this->view->title,
			'content' => $this->view->content,
		]));
		
		$life_time = is_numeric($life_time) ? $life_time : $this->registry->config->cache_lifetime;
		touch($cache_file, time() + $life_time);

		return true;
	}

	/**
	 * initialize the system and show proper content
	 * @return void
	 */
	public function init() {
		$this->set_language();
		$this->__startup();
		$this->default_content();

		if (empty($this->view->title) && empty($this->view->content)) {

			$permalink = load_lib('permalink');

			if ($permalink_query = $permalink->permalink_exists($this->q['param']))
				$this->q['param'] = explode('/', $permalink_query);
			else
				unset($permalink);

			if (@$this->q['param'][0] === 'user')
				$this->user_content();
			else if ($this->app_lib->app_installed($this->q['param'][0]) === false)
				$this->error_message(ERROR, SENTENCE_1);
			else if ($this->app_lib->app_enabled($this->q['param'][0]) === false)
				$this->error_message(ERROR, SENTENCE_2);
			else {
				$cache_file = PATH . DS . 'cache' . DS . md5(implode('/', $this->q['param']));
				
				if ($data = $this->read_cache($cache_file)) {
					$this->view->head = $data['head'];
					$this->view->title = $data['title'];
					$this->view->content = $data['content'];

					return true;
				}
				
				if (isset($this->q['param'][1]) && empty($this->q['param'][1]) === false)
					$method_name = $this->q['param'][1];
				else
					$method_name = null;

				$load_app = $this->load_app($this->q['param'][0], $method_name, $cache_file);

				if (count($this->registry->q['param']) < 1)
					$this->view->title = $this->registry->config->title;

				if ($load_app === false)
					$this->error_message(ERROR, SENTENCE_3);
			}
		}

		$this->view->head .= $this->head;
	}
}

?>