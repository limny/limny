<?php

class CommentAdminModel {
	public static $db;
	public static $config;

	public static function unapproved() {
		$result = self::$db->query('SELECT ' . DB_PRFX . 'comments.*, ' . DB_PRFX . 'posts.title AS post_title FROM ' . DB_PRFX . 'comments INNER JOIN ' . DB_PRFX . 'posts ON ' . DB_PRFX . 'posts.id = ' . DB_PRFX . 'comments.post WHERE (' . DB_PRFX . 'comments.approved IS NULL OR ' . DB_PRFX . 'comments.approved = 0) ORDER BY ' . DB_PRFX . 'comments.time');
		$result->execute();

		while ($comment = $result->fetch(PDO::FETCH_ASSOC))
			$comments[] = $comment;

		return isset($comments) ? $comments : false;
	}

	public static function approve_comment($comment_id) {
		return self::$db->prepare('UPDATE ' . DB_PRFX . 'comments SET approved = 1 WHERE id = ?')->execute([$comment_id]);
	}

	public static function submit_reply($comment_id, $reply) {
		$parent = self::comment($comment_id);

		if ($parent === false)
			return false;

		$admin = $_SESSION['limny']['admin'];

		$name = self::profile_name($admin['id']);
		$email = $admin['email'];
		$website = self::$config->address;

		$db->prepare('INSERT INTO ' . DB_PRFX . 'comments (post, name, email, website, comment, replyto, approved, ip, time) VALUES (?, ?, ?, ?, ?, ?, 1, INET_ATON(?), UNIX_TIMESTAMP())')->execute([$parent['post'], $name, $email, $website, $reply, $comment_id, $_SERVER['REMOTE_ADDR']]);

		self::approve_comment($comment_id);

		return true;
	}

	public static function comment($comment_id) {
		$result = self::$db->prepare('SELECT * FROM ' . DB_PRFX . 'comments WHERE id = ?');
		$result->execute([$comment_id]);

		if ($comment = $result->fetch(PDO::FETCH_ASSOC))
			return $comment;

		return false;
	}

	private static function profile_name($user_id) {
		$result = self::$db->prepare('SELECT nick_name, first_name, last_name FROM ' . DB_PRFX . 'profiles WHERE user = ?');
		$result->execute([$user_id]);

		if ($profile = $result->fetch(PDO::FETCH_ASSOC)) {
			if (empty($profile['nick_name']) === false)
				return $profile['nick_name'];

			$name = $profile['first_name'] . ' ' . $profile['last_name'];

			return empty($name) ? false : $name;
		}

		return false;
	}

	public static function edit_reply($comment_id, $text) {
		return self::$db->prepare('UPDATE ' . DB_PRFX . 'comments SET comment = ? WHERE id = ?')->execute([$text, $comment_id]);
	}

	public static function delete_comment($comment_id) {
		$db->prepare('DELETE FROM ' . DB_PRFX . 'comments WHERE id = ?')->execute([$comment_id]);

		$result = self::$db->prepare('SELECT id FROM ' . DB_PRFX . 'comments WHERE replyto = ?');
		$result->execute([$comment_id]);

		if ($result->rowCount() > 0)
			while ($comment = $result->fetch(PDO::FETCH_ASSOC))
				self::delete_comment($comment['id']);
	}
}

?>