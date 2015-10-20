<?php

class Application {
	public $db;

	public function __construct() {
		global $db;

		$this->db = $db;
	}

	public function app_installed($name) {
		$result = $this->db->prepare('SELECT id FROM ' . DB_PRFX . 'apps WHERE name = ?');
		$result->execute([$name]);
		
		return $result->rowCount() > 0 ? true : false;
	}
	
	public function app_enabled($name) {
		$result = $this->db->prepare('SELECT enabled FROM ' . DB_PRFX . 'apps WHERE name = ?');
		$result->execute([$name]);
		
		if ($app = $result->fetch(PDO::FETCH_ASSOC))
			if ($app['enabled'] == '1')
				return true;
		
		return false;
	}
	
	public function apps($enabled = null) {
		if ($enabled === 0 || $enabled === 1)
			$clause = 'WHERE enabled = ?';
		else
			$clause = '';
		
		$result = $this->db->prepare('SELECT * FROM ' . DB_PRFX . 'apps ' . $clause);
		$values = empty($clause) ? [] : [$enabled];
		$result->execute($values);
		
		$apps = $result->fetchAll(PDO::FETCH_ASSOC);
		
		return $apps;
	}

	public function load_language($app_name) {
		global $config;

		$lang = $config->config->language;

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
}

?>