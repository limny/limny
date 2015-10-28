<?php

/**
 * Applications list and checking methods
 *
 * @package Limny
 * @author Hamid Samak <hamid@limny.org>
 * @copyright 2009-2015 Limny
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class Application {
	// database connection
	private $db;

	// configuration values
	private $config;

	/**
	 * define database connection as a property
	 * @param object $registry
	 * @return void
	 */
	public function __construct($registry) {
		$this->db = $registry->db;
		$this->config = $registry->config;
	}

	/**
	 * check application is installed or not
	 * @param  string $name application name
	 * @return boolean
	 */
	public function app_installed($name) {
		$result = $this->db->prepare('SELECT id FROM ' . DB_PRFX . 'apps WHERE name = ?');
		$result->execute([$name]);
		
		return $result->rowCount() > 0 ? true : false;
	}
	
	/**
	 * check application is enabled or not
	 * @param  string $name application name
	 * @return boolean
	 */
	public function app_enabled($name) {
		$result = $this->db->prepare('SELECT enabled FROM ' . DB_PRFX . 'apps WHERE name = ?');
		$result->execute([$name]);
		
		if ($app = $result->fetch(PDO::FETCH_ASSOC))
			if ($app['enabled'] == '1')
				return true;
		
		return false;
	}
	
	/**
	 * list of installed applications
	 * @param  integer $enabled 0 for disabled and 1 for enabled applications
	 * @return array
	 */
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

	/**
	 * load application language
	 * @param  string $app_name application name
	 * @return boolean
	 */
	public function load_language($app_name) {
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
}

?>