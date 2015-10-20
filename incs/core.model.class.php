<?php

class CoreModel {
	public $db;
	public $lib;
	
	public function __construct() {
		global $db;
		
		$this->db = $db;
		
	}

	public function __get($lib_name) {
		if (isset($this->lib->lib_name))
			return $this->lib->$lib_name;

		$lib_name = str_replace(['.', '/', '\\'], '', $lib_name);
		$lib_file = PATH . DS . 'incs' . DS . $lib_name . '.class.php';
		
		if (file_exists($lib_file)) {
			require_once $lib_file;

			$class_name = ucfirst($lib_name);
			
			if (class_exists($class_name)) {
				global $db;

				$lib_class = new $class_name($db);

				if (property_exists($lib_class, 'db'))
					$lib_class->db = $this->db;
				
				settype($this->lib, 'object');

				$this->lib->$lib_name = $lib_class;

				return $this->lib->$lib_name;
			} else
				die("Limny error: Library class <em>$lib_name</em> not found.");
		} else
			die("Limny error: Library file <em>$lib_name</em> not found.");
	}
}

?>