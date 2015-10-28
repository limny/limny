<?php

class PostAdminModel {
	public static $db;

	public static function posts_last_id() {
		$result = self::$db->query('SELECT id FROM ' . DB_PRFX . 'posts ORDER BY id DESC LIMIT 1');
		$result->execute();
		if ($post = $result->fetch(PDO::FETCH_ASSOC))
			return $post['id'];
		
		return '0';
	}

	public static function num_uncategorized_posts() {
		$result = self::$db->query('SELECT COUNT(id) AS count FROM ' . DB_PRFX . 'posts WHERE category IS NOT NULL OR category != \'\'');
		$result->execute();

		$count = $result->fetch(PDO::FETCH_ASSOC);

		return $count['count'];
	}

	public static function num_unpublished_posts() {
		$result = self::$db->query('SELECT COUNT(id) AS count FROM ' . DB_PRFX . 'posts WHERE published IS NULL OR published != 1');
		$result->execute();

		$count = $result->fetch(PDO::FETCH_ASSOC);

		return $count['count'];
	}

	public static function category($cat_id) {
		$result = self::$db->prepare('SELECT * FROM ' . DB_PRFX . 'posts_cats WHERE id = ?');
		$result->execute([$cat_id]);
		if ($category = $result->fetch(PDO::FETCH_ASSOC))
			return $category;
		
		return false;
	}
}

?>