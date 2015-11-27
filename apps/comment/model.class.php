<?php

/**
 * Comment application model
 *
 * @package Limny
 * @author Hamid Samak <hamid@limny.org>
 * @copyright 2009-2015 Limny
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class CommentModel {
	// database connection
	public static $db;

	/**
	 * add new comment
	 * @param  integer $post_id
	 * @param  string  $name
	 * @param  string  $email
	 * @param  string  $website
	 * @param  string  $comment comment text
	 * @param  integer $replyto parent comment id
	 * @return boolean         
	 */
	public static function insert($post_id, $name, $email, $website, $comment, $replyto = null) {
		if (empty($website) === false && (stripos($website, 'http://') !== 0 || stripos($website, 'https://') !== 0))
			$website = 'http://' . $website;

		foreach (['name', 'email', 'website', 'comment'] as $field_name)
			${$field_name} = htmlspecialchars(${$field_name});

		return self::$db->prepare('INSERT INTO ' . DB_PRFX . 'comments (post, name, email, website, comment, replyto, ip, time) VALUES (?, ?, ?, ?, ?, ?, INET_ATON(?), UNIX_TIMESTAMP())')->execute([$post_id, $name, $email, $website, $comment, $replyto, $_SERVER['REMOTE_ADDR']]);
	}

	/**
	 * get number of comments
	 * @param  string  $ip
	 * @param  integer $period seconds
	 * @return boolean
	 */
	public static function count($ip, $period = 86400) {
		$result = self::$db->prepare('SELECT COUNT(id) AS count FROM ' . DB_PRFX . 'comments WHERE ip = INET_ATON(?) AND UNIX_TIMESTAMP() - time < ? AND approved IS NULL');
		$result->execute([$ip, $period]);
		
		return $result->fetch(PDO::FETCH_ASSOC)['count'];
	}

	/**
	 * get comments by post id and parent comment (for replies)
	 * @param  integer $post_id
	 * @param  integer $parent
	 * @return array
	 */
	public static function comments($post_id, $parent = null) {
		$values[] = $post_id;

		if (empty($parent) === false) {
			$values[] = $parent;
			$parent = ' AND replyto = ?';
		} else
			$parent = ' AND replyto IS NULL';

		$result = self::$db->prepare('SELECT id, name, website, comment, replyto, time FROM ' . DB_PRFX . 'comments WHERE post = ? AND approved = 1 ' . $parent . ' ORDER BY time');
		$result->execute($values);

		while ($comment = $result->fetch(PDO::FETCH_ASSOC))
			$comments[] = $comment;

		return isset($comments) ? $comments : false;
	}

	/**
	 * get comment by id
	 * @param  integer       $comment_id
	 * @return array/boolean
	 */
	public static function comment($comment_id) {
		$result = self::$db->prepare('SELECT * FROM ' . DB_PRFX . 'comments WHERE id = ? AND approved = 1');
		$result->execute([$comment_id]);

		if ($comment = $result->fetch(PDO::FETCH_ASSOC))
			return $comment;

		return false;
	}
}

?>