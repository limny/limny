<?php

class User extends Manage {
	public $manage_title = USERS;
	public $manage_icon = 'fa-user';

	public $manage_table = 'users';

	// LIST
	public $manage_head = [
		USERNAME => 'username',
		EMAIL => 'email',
		STATUS => 'enabled'
	];
	public $manage_number = 10;
	public $manage_view = true;

	// SEARCH
	public $manage_search = ['username', 'email'];

	// SORTING
	public $manage_sort = ['username', 'email'];
	public $manage_order = ['id' => 'ASC'];

	// ADD & EDIT
	public $manage_fields = [
		'username' => [
			'label' => USERNAME,
			'type' => 'text',
			'required' => true
		],
		'password' => [
			'label' => PASSWORD,
			'type' => 'password'
		],
		'email' => [
			'label' => EMAIL,
			'type' => 'text',
			'required' => true
		],
		'roles' => [
			'label' => ROLES,
			'type' => 'checkbox'
		],
		'enabled' => [
			'label' => STATUS,
			'type' => 'radio',
			'items' => ['1' => ENABLE, '0' => DISABLE],
			'required' => true
		],
	];

	public $manage_fields_view = [
		'id' => ID,
		'username' => USERNAME,
		'email' => EMAIL,
		'roles' => ROLES,
		'enabled' => STATUS,
		'ip' => LAST_IP,
		'last_login' => LAST_LOGIN,
		'last_activity' => LAST_ACTIVITY
	];
	
	public function User($parameters = []) {
		parent::__construct($parameters);

		$this->manage_fields['roles']['items'] = $this->table_to_array('roles', 'id', 'name');

		if (count($this->manage_q) > 1 && $this->manage_q[count($this->manage_q) - 2] == 'edit')
			$this->manage_fields['password']['help'] = SENTENCE_21;
		else
			$this->manage_action->check->password = 'password_check';

		$this->manage_action->add->password = 'hash_password';
		$this->manage_action->add->function = 'add_profile';

		$this->manage_action->edit->password = 'hash_password';
		$this->manage_action->edit->enabled = 'user_enabled';

		$this->manage_action->list->enabled = 'user_status';

		$this->manage_action->check->username = 'username_check';
		$this->manage_action->check->email = 'email_check';

		$this->manage_action->delete = 'user_delete';

		$this->manage_action->view->ip = 'long2ip';
		$this->manage_action->view->last_login = 'system_date';
		$this->manage_action->view->last_activity = 'system_date';
		$this->manage_action->view->enabled = 'user_status';
	}

	public function user_status($enabled, $data = []) {
		if (empty($enabled))
			return '<i class="fa fa-times text-red"></i>';

		return '<i class="fa fa-check text-green"></i>';
	}

	public function username_check($username, $data = [], $files = [], $id = null) {
		global $db;

		if (strlen($username) < 3)
			return SENTENCE_22;

		if (preg_match('/^([a-z0-9])(([-a-z0-9._])*([a-z0-9]))([a-z0-9])*$/i' , $username) === 0)
			return SENTENCE_23;

		$edit_mode = empty($id) ? false : true;
		$values = ['username' => $username];
		
		if ($edit_mode) {
			$edit_statement = ' AND id != :id';
			$values['id'] = $id;
		} else
			$edit_statement = '';

		$result = $db->prepare('SELECT COUNT(id) AS count FROM ' . DB_PRFX . 'users WHERE username = :username' . $edit_statement);
		$result->execute($values);

		if ($result->fetchColumn() > 0)
			return SENTENCE_20;

		return true;
	}

	public function password_check($password) {
		if (empty($password) || strlen($password) < 6)
			return SENTENCE_24;

		return true;
	}

	public function password_check_edit($password) {
		if (empty($password) === false && strlen($password) < 6)
			return SENTENCE_24;

		return true;
	}

	public function hash_password($password) {
		if (empty($password))
			return false;

		require_once PATH . DS . 'incs' . DS . 'passwordhash.class.php';
		$password_hash = new PasswordHash(8, false);

		return $password_hash->HashPassword($password);
	}

	public function email_check($email, $data = [], $files = [], $id = null) {
		global $db;

		if (filter_var($email, FILTER_VALIDATE_EMAIL) === false)
			return SENTENCE_26;

		$edit_mode = empty($id) ? false : true;
		$values = ['email' => $email];
		
		if ($edit_mode) {
			$edit_statement = ' AND id != :id';
			$values['id'] = $id;
		}

		$result = $db->prepare('SELECT COUNT(id) AS count FROM ' . DB_PRFX . 'users WHERE email = :email' . @$edit_statement);
		$result->execute($values);

		if ($result->fetchColumn() > 0)
			return SENTENCE_25;

		return true;
	}

	public function user_delete($ids) {
		if (in_array('1', $ids))
			return false;

		global $db;

		foreach ($ids as $id) {
			$db->prepare('DELETE FROM ' . DB_PRFX . 'profiles WHERE user = ?')->execute([$id]);
		}

		return true;
	}

	public function user_enabled($enabled, $post = [], $id = null) {
		if ($id == '1')
			return '1';

		return $enabled;
	}

	protected function add_profile($value = null, $post = [], $files = [], $id = null) {
		if (empty($id))
			return false;

		global $db;

		return $db->prepare('INSERT INTO ' . DB_PRFX . 'profiles (user) VALUES (?)')->execute([$id]);
	}
}

?>