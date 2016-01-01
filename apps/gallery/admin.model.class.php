<?php

/**
 * Gallery administration model
 *
 * @package Limny
 * @author Hamid Samak <hamid@limny.org>
 * @copyright 2009-2016 Limny
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class GalleryAdminModel {
	// database connection
	public static $db;
	
	/**
	 * get category record
	 * @param  integer $cat_id category id
	 * @return array
	 */
	public static function cat($cat_id) {
		$result = self::$db->prepare('SELECT * FROM ' . DB_PRFX . 'gallery_cats WHERE id = ?');
		$result->execute([$cat_id]);
		return $result->fetch(PDO::FETCH_ASSOC);
	}

	/**
	 * get categories by parent id
	 * @param  integer       $parent_id category id
	 * @return array/boolean
	 */
	public static function cats($parent_id = null) {
		if (empty($parent_id)) {
			$result = self::$db->query('SELECT * FROM ' . DB_PRFX . 'gallery_cats');
			$result->execute();
		} else {
			$result = self::$db->prepare('SELECT * FROM ' . DB_PRFX . 'gallery_cats WHERE parent = ?');
			$result->execute([$parent_id]);
		}
		
		while ($cat = $result->fetch(PDO::FETCH_ASSOC))
			$cats[$cat['id']] = $cat;

		return isset($cats) ? $cats : false;
	}

	/**
	 * update picture thumbnail
	 * @param  integer $picture_id
	 * @param  string  $thumbnail_name thumbnail file name
	 * @return boolean
	 */
	public static function update_thumbnail($picture_id, $thumbnail_name) {
		return self::$db->prepare('UPDATE ' . DB_PRFX . 'gallery SET thumbnail = ? WHERE id = ?')->execute([$thumbnail_name, $picture_id]);
	}

	/**
	 * delete pictures of category
	 * @param  integer $cat_id      category id
	 * @param  string  $upload_path upload directory path
	 * @return boolean
	 */
	public static function pictures_delete($cat_id, $upload_path) {
		$result = self::$db->prepare('SELECT image, thumbnail FROM ' . DB_PRFX . 'gallery WHERE category = ?');
		$result->execute([$cat_id]);
		
		while ($gallery = $result->fetch(PDO::FETCH_ASSOC))
			foreach (['image', 'thumbnail'] as $column)
				if (file_exists($upload_path . DS . $gallery[$column]))
					unlink($upload_path . DS . $gallery[$column]);

		return self::$db->prepare('DELETE FROM ' . DB_PRFX . 'gallery WHERE category = ?')->execute([$cat_id]);
	}

	/**
	 * delete category record
	 * @param  integer $cat_id category id
	 * @return boolean
	 */
	public static function category_delete($cat_id) {
		return self::$db->prepare('DELETE FROM ' . DB_PRFX . 'gallery_cats WHERE id = ?')->execute([$cat_id]);
	}
}

?>