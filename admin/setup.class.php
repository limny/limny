<?php

class Setup {
	private $db;

	public function __construct() {
		global $db;

		$this->db = $db;
	}

	public function add_adminnav($items) {
		foreach ($items as $item) {
			$this->db->prepare('INSERT INTO ' . DB_PRFX . 'adminnav (title, query) VALUES (?, ?)')->execute([$item['title'], $item['query']]);
		}

		return true;
	}

	public function adminnav_delete($app) {
		$result = $this->db->prepare('DELETE FROM ' . DB_PRFX . 'adminnav WHERE query = ? OR query LIKE ?');
		$result->execute([$app, $app . '/%']);

		return true;
	}

	public function add_permission($permissions) {
		$parent_id = '0';

		foreach ($permissions as $permission) {
			$this->db->prepare('INSERT INTO ' . DB_PRFX . 'permissions (name, parent, query, sub_allowed) VALUES (?, ?, ?, ?)')->execute([$permission['name'], $parent_id, $permission['query'], (int) $permission['sub_allowed']]);

			if ($parent_id === '0')
				$parent_id = $this->db->lastInsertId();
		}

		return true;
	}

	public function permission_delete($app) {
		$this->db->prepare('DELETE FROM ' . DB_PRFX . 'permissions WHERE query = ? OR query LIKE ?')->execute([$app, $app . '/%']);

		return true;
	}
}

?>