<?php

/**
 * Page application
 *
 * @package Limny
 * @author Hamid Samak <hamid@limny.org>
 * @copyright 2009-2016 Limny
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class PageApp {
	// database connection
	private $db;

	// setup library
	private $setup;

	/**
	 * set database connection property
	 * load setup library
	 * set setup library property
	 * @param  object $registry
	 * @return void
	 */
	public function __construct($registry) {
		$this->db = $registry->db;

		$setup = load_lib('setup', true, true);

		$this->setup = $setup;
	}

	/**
	 * page install
	 * @return boolean
	 */
	public function install() {
		// create pages table
		$this->db->exec('CREATE TABLE ' . DB_PRFX . 'pages (
	id int(11) NOT NULL,
	title varchar(256) NOT NULL,
	text longtext NOT NULL,
	image varchar(128) DEFAULT NULL,
	time int(11) NOT NULL,
	updated int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8');

		// modify id column as primary key
		$this->db->exec('ALTER TABLE ' . DB_PRFX . 'pages ADD PRIMARY KEY (id)');

		// set id columns as auto increment
		$this->db->exec('ALTER TABLE ' . DB_PRFX . 'pages MODIFY id int(11) NOT NULL AUTO_INCREMENT');

		// item(s) for admin panel navigation
		$this->setup->add_adminnav([
			['title' => 'PAGE_PAGES', 'query' => 'page']
		]);

		// permissions
		$this->setup->add_permission([
			['name' => 'PAGE_PAGES', 'query' => 'page', 'sub_allowed' => false],
			['name' => 'PAGE_ADD', 'query' => 'page/add', 'sub_allowed' => false],
			['name' => 'PAGE_VIEW', 'query' => 'page/view', 'sub_allowed' => true],
			['name' => 'PAGE_EDIT', 'query' => 'page/edit', 'sub_allowed' => true],
			['name' => 'PAGE_DELETE', 'query' => 'page/delete', 'sub_allowed' => true],
			['name' => 'PAGE_SEARCH', 'query' => 'page/search', 'sub_allowed' => false]
		]);

		return true;
	}

	/**
	 * page uninstall
	 * @return boolean
	 */
	public function uninstall() {
		// delete uploaded files
		$result = $this->db->query('SELECT image FROM ' . DB_PRFX . 'pages');
		$result->execute();
		while ($page = $result->fetch(PDO::FETCH_ASSOC)) {
			if (empty($page['image']))
				continue;

			$image_file = PATH . DS . 'uploads' . DS . $page['image'];
			
			if (file_exists($image_file))
				unlink($image_file);
		}

		// delete permalinks
		$this->db->exec('DELETE FROM ' . DB_PRFX . 'permalinks WHERE query = \'page\' OR query LIKE \'page%\'');

		// drop pages table
		$this->db->exec('DROP TABLE ' . DB_PRFX . 'pages');

		// remove admin navigation items
		$this->setup->adminnav_delete('page');

		// remove permissions
		$this->setup->permission_delete('page');

		return true;
	}
}

?>