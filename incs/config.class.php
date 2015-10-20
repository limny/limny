<?php

class Config {
	public $config;

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