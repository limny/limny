<?php

class GalleryModel {
	public static function cats($parent_id = '0') {
		global $db;

		$result = $db->prepare('SELECT * FROM ' . DB_PRFX . 'gallery_cats WHERE parent = ? ORDER BY id ASC');
		$result->execute([$parent_id]);

		return $result->fetchAll(PDO::FETCH_ASSOC);
	}

	public static function num_pictures($cat_id) {
		global $db;
		
		$result = $db->prepare('SELECT COUNT(id) AS count FROM ' . DB_PRFX . 'gallery WHERE category = ?');
		$result->execute([$cat_id]);

		$count = $result->fetch(PDO::FETCH_ASSOC);

		return $count['count'];
	}

	public static function cat($cat_id) {
		global $db;

		$result = $db->prepare('SELECT * FROM ' . DB_PRFX . 'gallery_cats WHERE id = ?');
		$result->execute([$cat_id]);

		if ($cat = $result->fetch(PDO::FETCH_ASSOC))
			return $cat;

		return false;
	}

	public static function pictures($cat_id, $count = 10, $offset = 0) {
		global $db;

		$count = ceil($count);
		$offset = ceil($offset);

		$result = $db->prepare('SELECT * FROM ' . DB_PRFX . 'gallery WHERE category = ? ORDER BY time DESC LIMIT ' . $offset . ',' . $count);
		$result->execute([$cat_id]);

		return $result->fetchAll(PDO::FETCH_ASSOC);
	}

	public static function picture($picture_id) {
		global $db;

		$result = $db->prepare('SELECT * FROM ' . DB_PRFX . 'gallery WHERE id = ?');
		$result->execute([$picture_id]);

		if ($picture = $result->fetch(PDO::FETCH_ASSOC))
			return $picture;

		return false;
	}
}

?>