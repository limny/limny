<?php

/**
 * Administration setup methods for installing and uninstalling applications
 *
 * @package Limny
 * @author Hamid Samak <hamid@limny.org>
 * @copyright 2009-2015 Limny
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class Setup {
	// database connection
	private $db;

	/**
	 * set database connection property
	 * @param  object
	 * @return void
	 */
	public function __construct($registry) {
		$this->db = $registry->db;
	}

	/**
	 * add new administration navigation item(s)
	 * @param  array   $items [title => item_title, query => page_query_parameter]
	 * @return boolean
	 */
	public function add_adminnav($items) {
		foreach ($items as $item) {
			$this->db->prepare('INSERT INTO ' . DB_PRFX . 'adminnav (title, query) VALUES (?, ?)')->execute([$item['title'], $item['query']]);
		}

		return true;
	}

	/**
	 * delete application administrator navigation items
	 * @param  string  $app application name
	 * @return boolean
	 */
	public function adminnav_delete($app) {
		$result = $this->db->prepare('DELETE FROM ' . DB_PRFX . 'adminnav WHERE query = ? OR query LIKE ?');
		$result->execute([$app, $app . '/%']);

		return true;
	}

	/**
	 * add application permissions
	 * @param  array $permissions [name => permission_name, query => permission_query_parameter, sub_allowed => is subsets allowed (0 or 1)]
	 * @return boolean
	 */
	public function add_permission($permissions) {
		$parent_id = '0';

		foreach ($permissions as $permission) {
			$this->db->prepare('INSERT INTO ' . DB_PRFX . 'permissions (name, parent, query, sub_allowed) VALUES (?, ?, ?, ?)')->execute([$permission['name'], $parent_id, $permission['query'], (int) $permission['sub_allowed']]);

			if ($parent_id === '0')
				$parent_id = $this->db->lastInsertId();
		}

		return true;
	}

	/**
	 * delete application permissions
	 * @param  string  $app application name
	 * @return boolean
	 */
	public function permission_delete($app) {
		$this->db->prepare('DELETE FROM ' . DB_PRFX . 'permissions WHERE query = ? OR query LIKE ?')->execute([$app, $app . '/%']);

		return true;
	}
}

?>