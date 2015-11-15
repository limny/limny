<?php

/**
 * Administration user management
 *
 * @package Limny
 * @author Hamid Samak <hamid@limny.org>
 * @copyright 2009-2015 Limny
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class User extends Manage {
	// page title
	public $manage_title = USERS;

	// page icon
	// font-awesome icon
	public $manage_icon = 'fa-user';

	// database items table
	public $manage_table = 'users';

	// manage table heading row
	public $manage_head = [
		USERNAME => 'username',
		EMAIL => 'email',
		STATUS => 'enabled'
	];

	// number of items per page
	public $manage_number = 10;

	// enable view item
	public $manage_view = true;

	// enable search in items
	// search in two columns
	public $manage_search = ['username', 'email'];

	// enable sorting columns
	// sortable columns in table
	public $manage_sort = ['username', 'email'];

	// default items orders
	public $manage_order = ['id' => 'ASC'];

	// input form fields
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

	// fields int view mode
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
	
	/**
	 * call parent object construct
	 * set extending methods for manage library
	 * @param object $registry
	 * @param array  $parameters
	 */
	public function User($registry, $parameters = []) {
		parent::__construct($registry, $parameters);

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

	/**
	 * menu status as coloured icon
	 * @param  string $enabled
	 * @param  array  $data
	 * @return string
	 */
	public function user_status($enabled, $data = []) {
		if (empty($enabled))
			return '<i class="fa fa-times text-red"></i>';

		return '<i class="fa fa-check text-green"></i>';
	}

	/**
	 * check username length, availability and used characters
	 * @param  string         $username
	 * @param  array          $data
	 * @param  array          $files
	 * @param  integer        $id
	 * @return string/boolean
	 */
	public function username_check($username, $data = [], $files = [], $id = null) {
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

		$result = $this->db->prepare('SELECT COUNT(id) AS count FROM ' . DB_PRFX . 'users WHERE username = :username' . $edit_statement);
		$result->execute($values);

		if ($result->fetchColumn() > 0)
			return SENTENCE_20;

		return true;
	}

	/**
	 * check password length
	 * @param  string         $password
	 * @return string/boolean
	 */
	public function password_check($password) {
		if (empty($password) || strlen($password) < 6)
			return SENTENCE_24;

		return true;
	}

	/**
	 * check password in edit mode
	 * in edit mode password can be empty
	 * @param  string         $password
	 * @return string/boolean
	 */
	public function password_check_edit($password) {
		if (empty($password) === false && strlen($password) < 6)
			return SENTENCE_24;

		return true;
	}

	/**
	 * create password hash with given string
	 * @param  string         $password
	 * @return boolean/string
	 */
	public function hash_password($password) {
		if (empty($password))
			return false;

		require_once PATH . DS . 'incs' . DS . 'passwordhash.class.php';
		$password_hash = new PasswordHash(8, false);

		return $password_hash->HashPassword($password);
	}

	/**
	 * check email address
	 * @param  string         $email
	 * @param  array          $data
	 * @param  array          $files
	 * @param  integer        $id
	 * @return string/boolean
	 */
	public function email_check($email, $data = [], $files = [], $id = null) {
		if (filter_var($email, FILTER_VALIDATE_EMAIL) === false)
			return SENTENCE_26;

		$edit_mode = empty($id) ? false : true;
		$values = ['email' => $email];
		
		if ($edit_mode) {
			$edit_statement = ' AND id != :id';
			$values['id'] = $id;
		}

		$result = $this->db->prepare('SELECT COUNT(id) AS count FROM ' . DB_PRFX . 'users WHERE email = :email' . @$edit_statement);
		$result->execute($values);

		if ($result->fetchColumn() > 0)
			return SENTENCE_25;

		return true;
	}

	/**
	 * delete user
	 * do not delete user number one, that's administrator
	 * @param  array   $ids
	 * @return boolean
	 */
	public function user_delete($ids) {
		if (in_array('1', $ids))
			return false;

		foreach ($ids as $id)
			$this->db->prepare('DELETE FROM ' . DB_PRFX . 'profiles WHERE user = ?')->execute([$id]);

		return true;
	}

	/**
	 * check user account is enable or disable
	 * @param  string  $enabled
	 * @param  array   $data
	 * @param  array   $files
	 * @param  integer $id
	 * @return string
	 */
	public function user_enabled($enabled, $data = [], $files = [], $id = null) {
		if ($id == '1')
			return '1';

		return $enabled;
	}

	/**
	 * add user profile record
	 * @param  string $value
	 * @param  array  $data
	 * @param  array  $files
	 * @param  integer $id
	 * @return boolean
	 */
	protected function add_profile($value = null, $data = [], $files = [], $id = null) {
		if (empty($id))
			return false;

		return $this->db->prepare('INSERT INTO ' . DB_PRFX . 'profiles (user) VALUES (?)')->execute([$id]);
	}
}

?>