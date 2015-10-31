<?php

/**
 * Model object
 *
 * @package Limny
 * @author Hamid Samak <hamid@limny.org>
 * @copyright 2009-2015 Limny
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class CoreModel {
	/**
	 * create connection to database
	 * check for install directory existence
	 * load configuration
	 * @param object $registry
	 * @return void
	 */
	public function __construct($registry) {
		$db = new Database(DB_HOST, DB_PORT, DB_USER, DB_PASS, DB_NAME);
		
		if (file_exists(PATH . DS . 'install') && is_dir(PATH . DS . 'install'))
			die('Limny error: Please delete <em>install</em> directory.');

		$registry->db = $db;
		
		$this->config($registry);
	}

	/**
	 * load configuration values and set to registry
	 * @param  object $registry
	 * @return void
	 */
	private function config($registry) {
		$registry->config = (object) ['core' => 'limny'];

		$result = $registry->db->query('SELECT * FROM ' . DB_PRFX . 'config');
		while ($config = $result->fetch(PDO::FETCH_ASSOC))
			$registry->config->{$config['name']} = $config['value'];
	}
}

?>