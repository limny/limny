<?php

/**
 * Post model
 *
 * @package Limny
 * @author Hamid Samak <hamid@limny.org>
 * @copyright 2009-2016 Limny
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class PostModel {
	// database connection
	public static $db;

	/**
	 * get posts
	 * @param  integer $count  number of posts
	 * @param  integer $offset offset from beginning
	 * @return array
	 */
	public static function posts($count = 10, $offset = 0) {
		$count = ceil($count);
		$offset = ceil($offset);

		$result = self::$db->query('SELECT ' . DB_PRFX . 'posts.*, ' . DB_PRFX . 'users.username FROM ' . DB_PRFX . 'posts INNER JOIN ' . DB_PRFX . 'users ON ' . DB_PRFX . 'users.id = ' . DB_PRFX . 'posts.user WHERE published = 1 ORDER BY time DESC LIMIT ' . $offset . ',' . $count);
		$result->execute();

		return $result->fetchAll(PDO::FETCH_ASSOC);
	}

	/**
	 * get post by id
	 * @param  integer       $id post id
	 * @return array/boolean
	 */
	public static function post($id) {
		$result = self::$db->prepare('SELECT ' . DB_PRFX . 'posts.*, ' . DB_PRFX . 'users.username FROM ' . DB_PRFX . 'posts INNER JOIN ' . DB_PRFX . 'users ON ' . DB_PRFX . 'users.id = ' . DB_PRFX . 'posts.user WHERE ' . DB_PRFX . 'posts.id = ? AND published = 1');
		$result->execute([$id]);

		if ($post = $result->fetch(PDO::FETCH_ASSOC))
			return $post;

		return false;
	}

	/**
	 * get posts by category
	 * @param  integer $cat_id category id
	 * @param  integer $count  number of posts
	 * @param  integer $offset offset from beginning
	 * @return array
	 */
	public static function posts_by_cat($cat_id, $count = 10, $offset = 0) {
		$count = ceil($count);
		$offset = ceil($offset);

		$result = self::$db->prepare('SELECT ' . DB_PRFX . 'posts.*, ' . DB_PRFX . 'users.username FROM ' . DB_PRFX . 'posts INNER JOIN ' . DB_PRFX . 'users ON ' . DB_PRFX . 'users.id = ' . DB_PRFX . 'posts.user WHERE published = 1 AND FIND_IN_SET(?, category) > 0 ORDER BY time DESC LIMIT ' . $offset . ',' . $count);
		$result->execute([$cat_id]);

		return $result->fetchAll(PDO::FETCH_ASSOC);
	}

	/**
	 * get posts by tag name
	 * @param  string  $tag    tag name
	 * @param  integer $count  number of posts
	 * @param  integer $offset offset from beginning
	 * @return array
	 */
	public static function posts_by_tag($tag, $count = 10, $offset = 0) {
		$count = ceil($count);
		$offset = ceil($offset);

		$result = self::$db->prepare('SELECT ' . DB_PRFX . 'posts.*, ' . DB_PRFX . 'users.username FROM ' . DB_PRFX . 'posts INNER JOIN ' . DB_PRFX . 'users ON ' . DB_PRFX . 'users.id = ' . DB_PRFX . 'posts.user WHERE published = 1 AND FIND_IN_SET(?, tags) > 0 ORDER BY time DESC LIMIT ' . $offset . ',' . $count);
		$result->execute([$tag]);

		return $result->fetchAll(PDO::FETCH_ASSOC);
	}

	/**
	 * get number of posts by given category, tag or author id
	 * @param  integer $cat_id
	 * @param  string  $tag
	 * @param  integer $user_id
	 * @return integer
	 */
	public static function num_posts($cat_id = null, $tag = null, $user_id = null) {
		if (empty($cat_id) && empty($tag) && empty($user_id)) {
			$result = self::$db->query('SELECT COUNT(id) AS count FROM ' . DB_PRFX . 'posts WHERE published = 1');
			$result->execute();

			$count = $result->fetch(PDO::FETCH_ASSOC);

			return $count['count'];
		}

		$db_query = 'SELECT COUNT(id) AS count FROM ' . DB_PRFX . 'posts WHERE published = 1 ';

		if (empty($cat_id) === false) {
			$values[] = $cat_id;
			$db_query .= ' AND FIND_IN_SET(?, category) > 0';
		}

		if (empty($tag) === false) {
			$values[] = $tag;
			$db_query .= ' AND FIND_IN_SET(?, tags) > 0';
		}
		
		if (empty($user_id) === false) {
			$values[] = $user_id;
			$db_query .= ' AND user = ?';
		}

		$result = self::$db->prepare($db_query);
		$result->execute($values);

		$count = $result->fetch(PDO::FETCH_ASSOC);

		return $count['count'];
	}

	/**
	 * get category record
	 * @param  integer $cat_id category id
	 * @return array
	 */
	public static function cat_by_id($cat_id) {
		$result = self::$db->prepare('SELECT * FROM ' . DB_PRFX . 'posts_cats WHERE id = ?');
		$result->execute([$cat_id]);

		return $result->fetch(PDO::FETCH_ASSOC);
	}

	/**
	 * get posts by author user id
	 * @param  integer $user_id
	 * @param  integer $count   number of posts
	 * @param  integer $offset  offset from beginning
	 * @return array
	 */
	public static function posts_by_author($user_id, $count = 10, $offset = 0) {
		$count = ceil($count);
		$offset = ceil($offset);

		$result = self::$db->prepare('SELECT ' . DB_PRFX . 'posts.*, ' . DB_PRFX . 'users.username FROM ' . DB_PRFX . 'posts INNER JOIN ' . DB_PRFX . 'users ON ' . DB_PRFX . 'users.id = ' . DB_PRFX . 'posts.user WHERE published = 1 AND ' . DB_PRFX . 'posts.user = ? ORDER BY time DESC LIMIT ' . $offset . ',' . $count);
		$result->execute([$user_id]);

		return $result->fetchAll(PDO::FETCH_ASSOC);
	}
}

?>