<?php

class PageAdminModel {
	public static $db;

	public static function pages_last_id() {
		$result = self::$db->query('SELECT id FROM ' . DB_PRFX . 'pages ORDER BY id DESC LIMIT 1');
		$result->execute();
		if ($post = $result->fetch(PDO::FETCH_ASSOC))
			return $post['id'];
		
		return '0';
	}
}

?>