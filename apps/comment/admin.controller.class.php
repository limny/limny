<?php

/**
 * Comment administration controller
 *
 * @package Limny
 * @author Hamid Samak <hamid@limny.org>
 * @copyright 2009-2015 Limny
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class CommentAdminController extends Manage {
	// page query parameter
	public $q;

	// page head
	public $head;

	// configuration values
	public $config;

	/**
	 * call manage constructor
	 * set configuration values
	 * set model database connection and configuration
	 * set administration script tag
	 * @param  object $registry
	 * @return void
	 */
	public function __construct($registry) {
		parent::__construct($registry);

		$this->config = $registry->config;
		CommentAdminModel::$db = $registry->db;
		CommentAdminModel::$config = $registry->config;

		$this->manage_q = $this->q;

		$this->head = '<script type="text/javascript" src="' . BASE . '/apps/comment/js/admin.js"></script>';
	}

	/**
	 * unapproved comments page
	 * @return boolean
	 */
	public function unapproved() {
		if (isset($_POST['action']))
			$this->call_action($_POST['action'], $_POST);

		$this->title = COMMENT_UNAPPROVED;
		
		if ($comments = CommentAdminModel::unapproved()) {
			$this->content = '';
			$this->head .= load_css('comment', 'admin.css');

			foreach ($comments as $comment)
				$this->content .= load_view('comment', 'admin-unapproved.tpl', ['comment' => $comment]);

			$this->content .= load_view('comment', 'admin-reply.tpl');
			$this->content .= load_view('comment', 'admin-edit.tpl');
		} else
			$this->content = COMMENT_SENTENCE_6;

		return true;
	}

	/**
	 * call administration action
	 * @param  string $action (approve / reply / edit / delete)
	 * @param  array  $data
	 * @return void
	 */
	private function call_action($action, $data) {
		if (isset($data['id']) === false)
			exit;

		if ($action == 'approve') {
			CommentAdminModel::approve_comment($data['id']);

			$this->send_notification_mail($data['id'], true);
		} else if ($action == 'reply' && isset($data['reply']) && empty($data['reply']) === false) {
			CommentAdminModel::submit_reply($data['id'], $data['reply']);

			$this->send_notification_mail($data['id'], false);
		} else if ($action == 'edit' && isset($data['edit']) && empty($data['edit']) === false)
			CommentAdminModel::edit_reply($data['id'], $data['edit']);
		else if ($action == 'delete')
			CommentAdminModel::delete_comment($data['id']);

		exit;
	}

	/**
	 * send email to parent comment email address
	 * @param  integer  $comment_id
	 * @param  boolean  $to_parent  send email to parent
	 * @return boolean
	 */
	private function send_notification_mail($comment_id, $to_parent = true) {
		$comment = CommentAdminModel::comment($comment_id);

		if ($to_parent === true && empty($comment['replyto']) === false) {
			$parent = CommentAdminModel::comment($comment['replyto']);
			$email = $parent['email'];
		} else if ($to_parent === false)
			$email = $comment['email'];
		else
			return false;

		$link = url('post/' . $comment['post'] . '#comment-' . $comment['id'], true);
		$message = load_view('comment', 'email.tpl', ['config' => $this->config, 'link' => $link]);
				
		send_mail($email, COMMENT_SENTENCE_7, $message);

		if ($to_parent === false)
			$this->send_notification_mail($comment['id'], true);

		return true;
	}

	/**
	 * all comments page
	 * @return void
	 */
	public function all() {
		$this->manage_title = COMMENT_ALL_COMMENTS;
		$this->manage_table = 'comments';
		$this->manage_head = [
			COMMENT_NAME => 'name',
			COMMENT_DATE => 'time',
			COMMENT_POST => 'post',
			COMMENT_STATUS => 'approved'
		];
		$this->manage_add = false;
		$this->manage_view = true;
		$this->manage_search = ['name', 'email', 'website', 'comment'];
		$this->manage_sort = ['title', 'name', 'time', 'approved'];
		$this->manage_order = ['time' => 'DESC'];
		$this->manage_fields = [
			'post' => [
				'label' => COMMENT_POST,
				'type' => 'combo',
				'required' => true
			],
			'name' => [
				'label' => COMMENT_NAME,
				'type' => 'text',
				'required' => true
			],
			'email' => [
				'label' => COMMENT_EMAIL,
				'type' => 'text',
				'required' => true
			],
			'website' => [
				'label' => COMMENT_WEBSITE,
				'type' => 'text'
			],
			'comment' => [
				'label' => COMMENT_TEXT,
				'type' => 'textarea',
				'required' => true
			],
			'approved' => [
				'label' => COMMENT_STATUS,
				'type' => 'radio',
				'items' => ['0' => COMMENT_UNAPPROVED, '1' => COMMENT_APPROVED],
				'required' => true
			]
		];
		$this->manage_fields_view = [
			'post' => ['label' => COMMENT_POST],
			'name' => ['label' => COMMENT_NAME],
			'email' => ['label' => COMMENT_EMAIL],
			'website' => ['label' => COMMENT_WEBSITE],
			'comment' => ['label' => COMMENT_TEXT],
			'approved' => ['label' => COMMENT_STATUS],
			'ip' => ['label' => COMMENT_IP],
			'time' => ['label' => COMMENT_DATE],
		];

		$this->manage_fields['post']['items'] = $this->table_to_array('posts', 'id', 'title');

		$this->manage_action->list->post = 'post_title';
		$this->manage_action->list->time = 'system_date';
		$this->manage_action->list->approved = 'comment_status';
		$this->manage_action->view->comment = 'nl2br';
		$this->manage_action->view->approved = 'comment_status';
		$this->manage_action->view->ip = 'long2ip';
		$this->manage_action->view->time = 'system_date';

		$this->title = COMMENT_ALL_COMMENTS;
		$this->content = $this->manage();
	}

	/**
	 * get post title by id
	 * @param  integer $post_id
	 * @return string
	 */
	protected function post_title($post_id) {
		return '<a href="' . url('post/' . $post_id) . '" target="_blank">' . $this->get_item($post_id, 'title', 'posts') . '</a>';
	}

	/**
	 * get comment status in string with color
	 * @param  integer $status
	 * @return string
	 */
	protected function comment_status($status) {
		if (empty($status))
			return '<span class="text-red">' . COMMENT_UNAPPROVED . '</span>';
		
		return '<span class="text-green">' . COMMENT_APPROVED . '</span>';
	}
}

?>