<?php

/**
 * Page administration model
 *
 * @package Limny
 * @author Hamid Samak <hamid@limny.org>
 * @copyright 2009-2016 Limny
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class PageAdminModel {
	// database connection
	public static $db;

	/**
	 * get last inserted page id
	 * @return string
	 */
	public static function pages_last_id() {
		$result = self::$db->query('SELECT id FROM ' . DB_PRFX . 'pages ORDER BY id DESC LIMIT 1');
		$result->execute();
		if ($post = $result->fetch(PDO::FETCH_ASSOC))
			return $post['id'];
		
		return '0';
	}
}

?>