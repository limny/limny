<?php

/**
 * Post administration model
 *
 * @package Limny
 * @author Hamid Samak <hamid@limny.org>
 * @copyright 2009-2016 Limny
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class PostAdminModel {
	// database connection
	public static $db;

	/**
	 * get last post id
	 * @return integer
	 */
	public static function posts_last_id() {
		$result = self::$db->query('SELECT id FROM ' . DB_PRFX . 'posts ORDER BY id DESC LIMIT 1');
		$result->execute();
		if ($post = $result->fetch(PDO::FETCH_ASSOC))
			return $post['id'];
		
		return '0';
	}

	/**
	 * get number of uncategorized posts
	 * @return integer
	 */
	public static function num_uncategorized_posts() {
		$result = self::$db->query('SELECT COUNT(id) AS count FROM ' . DB_PRFX . 'posts WHERE category IS NOT NULL OR category != \'\'');
		$result->execute();

		$count = $result->fetch(PDO::FETCH_ASSOC);

		return $count['count'];
	}

	/**
	 * get number of unpublished posts
	 * @return integer
	 */
	public static function num_unpublished_posts() {
		$result = self::$db->query('SELECT COUNT(id) AS count FROM ' . DB_PRFX . 'posts WHERE published IS NULL OR published != 1');
		$result->execute();

		$count = $result->fetch(PDO::FETCH_ASSOC);

		return $count['count'];
	}

	/**
	 * get category record by id
	 * @param  integer       $cat_id category id
	 * @return array/boolean
	 */
	public static function category($cat_id) {
		$result = self::$db->prepare('SELECT * FROM ' . DB_PRFX . 'posts_cats WHERE id = ?');
		$result->execute([$cat_id]);
		if ($category = $result->fetch(PDO::FETCH_ASSOC))
			return $category;
		
		return false;
	}
}

?>