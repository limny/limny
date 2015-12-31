<?php

/**
 * Gallery model
 *
 * @package Limny
 * @author Hamid Samak <hamid@limny.org>
 * @copyright 2009-2015 Limny
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class GalleryModel {
	// database  connection
	public static $db;
	
	/**
	 * get gallery categories
	 * @param  string $parent_id parent category id
	 * @return array
	 */
	public static function cats($parent_id = '0') {
		$result = self::$db->prepare('SELECT * FROM ' . DB_PRFX . 'gallery_cats WHERE parent = ? ORDER BY id ASC');
		$result->execute([$parent_id]);

		return $result->fetchAll(PDO::FETCH_ASSOC);
	}

	/**
	 * get number of pictures in category
	 * @param  integer $cat_id category id
	 * @return integer
	 */
	public static function num_pictures($cat_id) {		
		$result = self::$db->prepare('SELECT COUNT(id) AS count FROM ' . DB_PRFX . 'gallery WHERE category = ?');
		$result->execute([$cat_id]);

		$count = $result->fetch(PDO::FETCH_ASSOC);

		return $count['count'];
	}

	/**
	 * get category record
	 * @param  integer       $cat_id category id
	 * @return array/boolean
	 */
	public static function cat($cat_id) {
		$result = self::$db->prepare('SELECT * FROM ' . DB_PRFX . 'gallery_cats WHERE id = ?');
		$result->execute([$cat_id]);

		if ($cat = $result->fetch(PDO::FETCH_ASSOC))
			return $cat;

		return false;
	}

	/**
	 * get pictures by category id
	 * @param  integer $cat_id category id
	 * @param  integer $count  number of pictures
	 * @param  integer $offset offset from first record
	 * @return array
	 */
	public static function pictures($cat_id, $count = 10, $offset = 0) {
		$count = ceil($count);
		$offset = ceil($offset);

		$result = self::$db->prepare('SELECT * FROM ' . DB_PRFX . 'gallery WHERE category = ? ORDER BY time DESC LIMIT ' . $offset . ',' . $count);
		$result->execute([$cat_id]);

		return $result->fetchAll(PDO::FETCH_ASSOC);
	}

	/**
	 * get picture by id
	 * @param  integer       $picture_id picture id
	 * @return array/boolean
	 */
	public static function picture($picture_id) {
		$result = self::$db->prepare('SELECT * FROM ' . DB_PRFX . 'gallery WHERE id = ?');
		$result->execute([$picture_id]);

		if ($picture = $result->fetch(PDO::FETCH_ASSOC))
			return $picture;

		return false;
	}
}

?>