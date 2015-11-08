<?php

/**
 * Administration get and check permissions
 *
 * @package Limny
 * @author Hamid Samak <hamid@limny.org>
 * @copyright 2009-2015 Limny
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class Permission {
	// database connection
	private $db;
	
	// pages with no need to checking for permissions
	private $exception = ['profile', 'profile/success', 'profile/notmatch', 'profile/error'];

	/**
	 * set database connection property
	 * @param  object $registry
	 * @return void
	 */
	public function Permission($registry) {
		$this->db = $registry->db;
	}

	/**
	 * get permissions list
	 * @param  integer $parent parent permission id
	 * @return array
	 */
	public function permissions($parent = null) {
		$result = $this->db->prepare('SELECT id, name, query FROM ' . DB_PRFX . 'permissions WHERE parent ' . (empty($parent) ? 'IS NULL OR parent < 1' : '= ?') . ' ORDER BY id ASC');
		$result->execute(empty($parent) ? [] : [$parent]);

		while ($permission = $result->fetch(PDO::FETCH_ASSOC))
			$permissions[$permission['id']] = $permission;

		return isset($permissions) ? $permissions : [];
	}

	/**
	 * check given page query parameter permission for current user
	 * @param  array   $q page query parameter
	 * @return boolean
	 */
	public function is_permitted($q) {
		if (admin_signed_in() !== true)
			return false;
		
		if (count($q) > 1 && is_numeric(end($q)))
			$q = array_slice($q, 0, -1);

		if (is_array($q))
			$q = implode('/', $q);

		if (in_array($q, $this->exception))
			return true;
		
		$result = $this->db->prepare('SELECT id FROM ' . DB_PRFX . 'permissions WHERE ((sub_allowed IS NULL OR sub_allowed != 1) AND query = ?) OR (sub_allowed = 1 AND SUBSTRING(?, 1, CHAR_LENGTH(query)) = query)');
		$result->execute([$q, $q]);

		$permission_id = $result->fetchColumn();

		if ($permission_id === false)
			return false;

		$user_id = $_SESSION['limny']['admin']['id'];

		$result = $this->db->prepare('SELECT COUNT(' . DB_PRFX . 'users.id) AS count FROM ' . DB_PRFX . 'users INNER JOIN ' . DB_PRFX . 'roles ON FIND_IN_SET(' . DB_PRFX . 'roles.id, ' . DB_PRFX . 'users.roles) > 0 WHERE ' . DB_PRFX . 'users.id = ? AND (' . DB_PRFX . 'roles.permissions = ? OR FIND_IN_SET(?, ' . DB_PRFX . 'roles.permissions) > 0)');
		$result->execute([$user_id, 'all', $permission_id]);
		$count = $result->fetch(PDO::FETCH_ASSOC);

		if ($count['count'] > 0)
			return true;
		
		return false;
	}
}

?>