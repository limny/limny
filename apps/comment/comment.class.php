<?php

class Comment {
	public $head;

	public function __construct($registry) {
		$this->head = load_css('comment', 'style.css');

		require_once PATH . DS . 'apps' . DS . 'comment' . DS . 'model.class.php';
		
		CommentModel::$db = $registry->db;
	}

	public function comment($post, $replyto = null) {
		$data = $this->comments($post['id']);
		$data .= $this->form($post, (isset($_GET['replyto']) ? $_GET['replyto'] : null));

		return load_view('comment', 'container.tpl', ['comments' => $data]);
	}

	private function form($post, $replyto = null) {
		$vars = ['post' => $post];

		if (isset($_POST['post_comment'])) {
			$name = @$_POST['name'];
			$email = @$_POST['email'];
			$website = @$_POST['website'];
			$comment = @$_POST['comment'];

			list($result, $message) = $this->check($name, $email, $website, $comment);

			if ($result === true) {
				$_SESSION['limny']['comment']['success'] = true;
				
				CommentModel::insert($post['id'], $name, $email, $website, $comment, $replyto);

				redirect($post['url'] . '#respond');
			} else {
				/*foreach (['name', 'email', 'website', 'comment'] as $field_name)
					$vars[$field_name] = htmlspecialchars(${$field_name});*/

				$vars['class'] = 'error';
				$vars['message'] = $message;
			}
		}

		if (isset($_SESSION['limny']['comment']['success'])) {
			unset($_SESSION['limny']['comment']['success']);

			$vars['class'] = 'success';
			$vars['message'] = COMMENT_SENTENCE_1;
		}

		if (empty($replyto) === false && $replyto = CommentModel::comment($replyto)) {
			$vars['replyto'] = $replyto;
			$vars['post_url'] = substr($_SERVER['REQUEST_URI'], 0, strpos($_SERVER['REQUEST_URI'], 'replyto=') - 1);
		}

		return load_view('comment', 'form.tpl', $vars);
	}

	private function check($name, $email, $website, $comment) {
		if (empty($name) || empty($email) || empty($comment))
			return [false, COMMENT_SENTENCE_2];
		else if (filter_var($email, FILTER_VALIDATE_EMAIL) === false)
			return [false, COMMENT_SENTENCE_3];

		if (CommentModel::count($_SERVER['REMOTE_ADDR']) >= 5)
			return [false, COMMENT_SENTENCE_4];

		return [true, true];
	}

	private function comments($post_id, $parent = null) {
		$comments = CommentModel::comments($post_id, $parent);
		
		if ($comments !== false && count($comments) > 0) {
			$data = '';

			foreach ($comments as $comment) {
				/*foreach ($comment as $key => $value)
					$comment[$key] = htmlspecialchars($value);*/

				$comment['url'] = $_SERVER['REQUEST_URI'] . '#comment-' . $comment['id'];
				$comment['reply_url'] = $this->reply_url($comment, $_SERVER['REQUEST_URI']);

				/*if (empty($comment['website']) === false && (strpos($comment['website'], 'http://') !== 0 || strpos($comment['website'], 'https://') !== 0))
					$comment['website'] = 'http://' . $comment['website'];*/

				$vars['comment'] = $comment;
				$vars['replies'] = null;
				
				if ($replies = $this->comments($post_id, $comment['id']))
					$vars['replies'] = $replies;

				$data .= load_view('comment', 'comment.tpl', $vars);
			}

			return $data;
		}

		return false;
	}

	private function reply_url($comment, $current_page) {
		foreach (['&', '?'] as $char)
			if (($position = strpos($current_page, $char . 'replyto=')) !== false) {
				$current_page = substr($current_page, 0, $position);
				break;
			}
		
		return $current_page . $char . 'replyto=' . $comment['id'] . '#respond';
	}
}

?>