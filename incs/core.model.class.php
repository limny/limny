<?php

class CoreModel {
	public function __construct($registry) {
		$db = new Database(DB_HOST, DB_PORT, DB_USER, DB_PASS, DB_NAME);
		
		if (file_exists(PATH . DS . 'install') && is_dir(PATH . DS . 'install'))
			die('Limny error: Please delete <em>install</em> directory.');

		$registry->db = $db;
		
		$this->config($registry);
	}

	private function config($registry) {
		$registry->config = (object) ['core' => 'limny'];

		$result = $registry->db->query('SELECT * FROM ' . DB_PRFX . 'config');
		while ($config = $result->fetch(PDO::FETCH_ASSOC))
			$registry->config->{$config['name']} = $config['value'];
	}
}

?>