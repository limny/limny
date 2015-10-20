<?php

class PostAdminModel {
	public static function posts_last_id() {
		global $db;

		$result = $db->query('SELECT id FROM ' . DB_PRFX . 'posts ORDER BY id DESC LIMIT 1');
		$result->execute();
		if ($post = $result->fetch(PDO::FETCH_ASSOC))
			return $post['id'];
		
		return '0';
	}

	public static function num_uncategorized_posts() {
		global $db;

		$result = $db->query('SELECT COUNT(id) AS count FROM ' . DB_PRFX . 'posts WHERE category IS NOT NULL OR category != \'\'');
		$result->execute();

		$count = $result->fetch(PDO::FETCH_ASSOC);

		return $count['count'];
	}

	public static function num_unpublished_posts() {
		global $db;

		$result = $db->query('SELECT COUNT(id) AS count FROM ' . DB_PRFX . 'posts WHERE published IS NULL OR published != 1');
		$result->execute();

		$count = $result->fetch(PDO::FETCH_ASSOC);

		return $count['count'];
	}
}

?>