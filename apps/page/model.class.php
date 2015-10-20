<?php

class PageModel {
	public static function page($id) {
		global $db;

		$result = $db->prepare('SELECT * FROM ' . DB_PRFX . 'pages WHERE id = ?');
		$result->execute([$id]);

		if ($page = $result->fetch(PDO::FETCH_ASSOC))
			return $page;

		return false;
	}
}

?>