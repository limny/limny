<?php

/**
 * System configuration object
 *
 * @package Limny
 * @author Hamid Samak <hamid@limny.org>
 * @copyright 2009-2015 Limny
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class Config {
	public $config;

	/**
	 * fetch config table and set values as property
	 * @param object $db database connection object
	 */
	public function __construct($db = null) {
		if (empty($db))
			global $db;
		
		settype($this->config, 'object');

		$result = $db->query('SELECT * FROM ' . DB_PRFX . 'config');
		
		while ($item = $result->fetch(PDO::FETCH_OBJ))
			$this->config->{$item->name} = $item->value;
	}
}

?>