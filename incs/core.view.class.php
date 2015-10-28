<?php

class CoreView {
	public $head;
	public $title;
	public $content;

	public $language;
	public $direction = false;
	
	public $cache_path;

	private $theme;
	private $theme_path;

	private $registry;

	private $widget_lib;
	
	public function __construct($registry) {
		$this->registry = $registry;

		if (isset($registry->q['lang']))
			$this->language = $registry->q['lang'];

		if (empty($this->language))
			$this->language = $registry->config->language;

		$lang_file = PATH . DS . 'langs' . DS . $this->language . DS . 'main.php';
		
		if (file_exists($lang_file)) {
			require_once $lang_file;

			$lang_info_file = PATH . DS . 'langs' . DS . $this->language . DS . 'lang.ini';

			if (file_exists($lang_info_file) && $data = parse_ini_file($lang_info_file))
				if (isset($data['direction']) && empty($data['direction']) === false && strtolower($data['direction'] != 'ltr'))
					$this->direction = $data['direction'];
		}
		
		if (admin_signed_in() === true && isset($_GET['theme']))
			$theme = $_GET['theme'];
		else
			$theme = $registry->config->theme;

		$this->cache_path = PATH . DS . 'cache';

		$this->theme = preg_replace('/[^a-zA-Z0-9]/', '', $theme);
		$this->theme_path = PATH . DS . 'themes' . DS . $this->theme . DS;

		$this->widget_lib = load_lib('widget');
	}
	
	public function __get($widgets_position) {
		if ($widgets = $this->widget_lib->widgets($widgets_position)) {
			foreach ($widgets as $widget_item) {
				$widget = (object) [];

				if (isset($widget_item['cache']) === false || $widget_item['cache'] !== true) {

					if ($widget_item['app'] === 'limny') {
						if ($widget_item['method'] == 'content_widget')
							echo $this->{$widget_item['method']}();
						else {
							list($object_name, $widget_method) = explode('_', $widget_item['method']);
							$object_file = PATH . DS . 'incs' . DS . $object_name . '.class.php';

							if (file_exists($object_file)) {
								require_once $object_file;

								$object_name = ucfirst($object_name);

								if (class_exists($object_name)) {
									$widget_object = new $object_name($this->registry);

									if (method_exists($widget_object, $widget_item['method'])) {
										$widget_array = $widget_object->{$widget_item['method']}();

										$widget->title = $widget_array['title'];
										$widget->content = $widget_array['content'];

										$continue = false;
									} else
										echo "Limny error: Limny widget method <em>{$widget_item['method']}</em> is undefined.";
								} else
									echo "Limny error: Limny widget object <em>{$object_name}</em> is undefined.";
							} else
								echo "Limny error: Limny widget file <em>{$object_file}</em> is undefined.";
						}	
						
						if (isset($continue) === false || $continue !== false)
							continue;
						else
							unset($continue);

					} else if ($widget_item['app'] === 'widget') {
						$widget_file = PATH . DS . 'widgets' . DS . $widget_item['method'] . DS . $widget_item['method'] . '.php';

						if (file_exists($widget_file))
							include $widget_file;
						else {
							echo "Limny error: Widget method <em>{$widget_item['method']}</em> not found.";
							continue;
						}
					} else {

						$app_path = PATH . DS . 'apps' . DS . $widget_item['app'] . DS;
						
						if (file_exists($app_path . 'widget.class.php') === false)
							echo 'Limny error: Application widget file not found.';

						require_once $app_path . 'widget.class.php';
						$class_name = ucfirst($widget_item['app']) . 'Widget';
						
						if (class_exists($class_name)) {
							$widget_class = new $class_name();
							$widget_class->{$widget_item['method']}(unserialize($widget_item['options']));
							
							$widget->title = $widget_class->title;
							$widget->content = $widget_class->content;

							if (isset($widget_class->lifetime) && $widget_class->lifetime > 0)
								$widget_item['lifetime'] = $widget_class->lifetime;
						} else {
							echo "Limny error: Application widget class <em>{$class_name}</em> not found.";
							continue;
						}
					}

					if (isset($widget_item['lifetime']) && $widget_item['lifetime'] > 0) {
						$this->registry->db->prepare('UPDATE ' . DB_PRFX . 'widgets SET lifetime = UNIX_TIMESTAMP() + ? WHERE id = ?')->execute([$widget_item['lifetime'], $widget_item['id']]);
						
						file_put_contents($this->cache_path . DS . md5($widget_item['id'] . $widget_item['app'] . $widget_item['method']), serialize(['title' => $widget->title, 'content' => $widget->content, 'cache' => true]));
					}
				} else {
					$widget->title = $widget_item['title'];
					$widget->content = $widget_item['content'];
				}

				include $this->theme_path . 'widget.tpl';
			}
		}
		
		return false;
	}
	
	public function render() {
		if (count($this->registry->q['param']) > 0 && file_exists($this->theme_path . 'page.tpl'))
			include $this->theme_path . 'page.tpl';
		else if (file_exists($this->theme_path . 'index.tpl'))
			include $this->theme_path . 'index.tpl';
		else
			die("Limny error: Theme files for <em>{$this->theme}</em> not found.");
	}

	public function content_widget() {
		return $this->content;
	}
}

?>