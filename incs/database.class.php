<?php

class Database extends PDO {
	public $max_logs = 100;
	
	/**
	 * Database connection
	 * @param string $host host name
	 * @param string $port port number
	 * @param string $user username
	 * @param string $pass password
	 */
	public function __construct($host, $port, $user, $pass) {
		try {
			$db = parent::__construct('mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME, DB_USER, DB_PASS);
			parent::setAttribute(parent::ATTR_EMULATE_PREPARES, false);
			
			return $db;
		} catch (PDOException $error) {
			die('Limny error: Cannot connect to database. Click <a href="' . substr(__DIR__, strlen($_SERVER['DOCUMENT_ROOT'])) . '/../install/install.php">here</a> to install.');
		}
	}

	public function query($statement) {
		if ($result = parent::query($statement))
			return $result;

		$error = parent::errorInfo();
		$this->log_error($error[2], $error[1], $statement);

		return false;
	}

	public function prepare($statement, $options = array()) {
		if ($result = parent::prepare($statement, $options))
			return $result;

		$error = parent::errorInfo();
		$this->log_error($error[2], $error[1], $statement);

		return false;
	}

	private function log_error($message, $code, $statement) {
		if (defined('ERROR_LOG') === true && ERROR_LOG === false)
			return false;

		$log_file = PATH . DS . '.limny_error';

		$data = file_exists($log_file) ? file_get_contents($log_file) : '';
		$data = explode("\n\n", $data);
		$data = array_filter($data);
		
		if (count($data) >= $this->max_logs)
			$data = array_slice($data, 1);

		$error = "[DB ERROR]\n";
		$error .= "CODE = {$code}\n";
		$error .= "MESG = {$message}\n";

		$debug = debug_backtrace();

		if (isset($debug[2]) && $debug = $debug[2]) {
			if (isset($debug['file']))
				$error .= "FILE = {$debug['file']}\n";

			if (isset($debug['line']))
				$error .= "LINE = {$debug['line']}\n";

			if (isset($debug['function']))
				$error .= "FUNC = {$debug['function']}\n";

			if (isset($debug['class']))
				$error .= "CLAS = {$debug['class']}\n";
		}

		$error .= "QUER = {$statement}\n";
		$error .= "DATE = " . date('r') . "\n";

		$error = trim($error);

		$data[] = $error;
		$data = implode("\n\n", $data);
		file_put_contents($log_file, $data);

		if (defined('ERROR_SHOW') === true && ERROR_SHOW === true)
			die("<pre>{$error}</p>");

		return true;
	}
}

?>