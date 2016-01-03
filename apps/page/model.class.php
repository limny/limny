<?php

/**
 * Page model
 *
 * @package Limny
 * @author Hamid Samak <hamid@limny.org>
 * @copyright 2009-2016 Limny
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class PageModel {
	// database connection
	public static $db;

	/**
	 * get page record by id
	 * @param  integer       $id page id
	 * @return array/boolean
	 */
	public static function page($id) {
		$result = self::$db->prepare('SELECT * FROM ' . DB_PRFX . 'pages WHERE id = ?');
		$result->execute([$id]);

		if ($page = $result->fetch(PDO::FETCH_ASSOC))
			return $page;

		return false;
	}
}

?>