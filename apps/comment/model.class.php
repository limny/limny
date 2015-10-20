<?php

class CommentModel {
	public static function insert($post_id, $name, $email, $website, $comment, $replyto = null) {
		global $db;

		if (empty($website) === false && (stripos($website, 'http://') !== 0 || stripos($website, 'https://') !== 0))
			$website = 'http://' . $website;

		foreach (['name', 'email', 'website', 'comment'] as $field_name)
			${$field_name} = htmlspecialchars(${$field_name});

		return $db->prepare('INSERT INTO ' . DB_PRFX . 'comments (post, name, email, website, comment, replyto, ip, time) VALUES (?, ?, ?, ?, ?, ?, INET_ATON(?), UNIX_TIMESTAMP())')->execute([$post_id, $name, $email, $website, $comment, $replyto, $_SERVER['REMOTE_ADDR']]);
	}

	public static function count($ip, $period = 86400) {
		global $db;

		$result = $db->prepare('SELECT COUNT(id) AS count FROM ' . DB_PRFX . 'comments WHERE ip = INET_ATON(?) AND UNIX_TIMESTAMP() - time < ? AND approved IS NULL');
		$result->execute([$ip, $period]);
		
		return $result->fetch(PDO::FETCH_ASSOC)['count'];
	}

	public static function comments($post_id, $parent = null) {
		global $db;

		$values[] = $post_id;

		if (empty($parent) === false) {
			$values[] = $parent;
			$parent = ' AND replyto = ?';
		} else
			$parent = ' AND replyto IS NULL';

		$result = $db->prepare('SELECT id, name, website, comment, replyto, time FROM ' . DB_PRFX . 'comments WHERE post = ? AND approved = 1 ' . $parent . ' ORDER BY time');
		$result->execute($values);

		while ($comment = $result->fetch(PDO::FETCH_ASSOC))
			$comments[] = $comment;

		return isset($comments) ? $comments : false;
	}

	public static function comment($comment_id) {
		global $db;

		$result = $db->prepare('SELECT * FROM ' . DB_PRFX . 'comments WHERE id = ? AND approved = 1');
		$result->execute([$comment_id]);

		if ($comment = $result->fetch(PDO::FETCH_ASSOC))
			return $comment;

		return false;
	}
}

?>