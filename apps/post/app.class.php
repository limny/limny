<?php

/**
 * Post application
 *
 * @package Limny
 * @author Hamid Samak <hamid@limny.org>
 * @copyright 2009-2016 Limny
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class PostApp {
	// database connection
	private $db;

	// setup library object
	private $setup;

	/**
	 * set database connection
	 * load setup library
	 * set setup object
	 * @param object $registry
	 */
	public function __construct($registry) {
		$this->db = $registry->db;

		$setup = load_lib('setup', true, true);

		$this->setup = $setup;
	}

	/**
	 * create posts table
	 * create categories table
	 * insert navigation items and permissions
	 * @return boolean
	 */
	public function install() {
		// create posts table
		$this->db->exec('CREATE TABLE ' . DB_PRFX . 'posts (
	id int(11) NOT NULL,
	title varchar(256) NOT NULL,
	text longtext NOT NULL,
	category text DEFAULT NULL,
	tags text DEFAULT NULL,
	image varchar(128) DEFAULT NULL,
	user int(11) NOT NULL,
	time int(11) NOT NULL,
	updated int(11) DEFAULT NULL,
	published tinyint(1) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8');

		// modify id column as primary key
		$this->db->exec('ALTER TABLE ' . DB_PRFX . 'posts ADD PRIMARY KEY (id)');

		// set id columns as auto increment
		$this->db->exec('ALTER TABLE ' . DB_PRFX . 'posts MODIFY id int(11) NOT NULL AUTO_INCREMENT');

		// create categories table
		$this->db->exec('CREATE TABLE ' . DB_PRFX . 'posts_cats (
	id int(11) NOT NULL,
	name varchar(256) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8');

		// modify id column as primary key
		$this->db->exec('ALTER TABLE ' . DB_PRFX . 'posts_cats ADD PRIMARY KEY (id)');

		// set id columns as auto increment
		$this->db->exec('ALTER TABLE ' . DB_PRFX . 'posts_cats MODIFY id int(11) NOT NULL AUTO_INCREMENT');

		// item(s) for admin panel navigation
		$this->setup->add_adminnav([
			['title' => 'POST_POST', 'query' => 'post'],
			['title' => 'POST_POSTS', 'query' => 'post/posts'],
			['title' => 'POST_CATEGORIES', 'query' => 'post/cats']
		]);

		// permissions
		$this->setup->add_permission([
			['name' => 'POST_POST', 'query' => 'post', 'sub_allowed' => false],
			['name' => 'POST_POSTS', 'query' => 'post/posts', 'sub_allowed' => false],
			['name' => 'POST_ADD', 'query' => 'post/posts/add', 'sub_allowed' => false],
			['name' => 'POST_VIEW', 'query' => 'post/posts/view', 'sub_allowed' => true],
			['name' => 'POST_EDIT', 'query' => 'post/posts/edit', 'sub_allowed' => true],
			['name' => 'POST_DELETE', 'query' => 'post/posts/delete', 'sub_allowed' => true],
			['name' => 'POST_SEARCH', 'query' => 'post/posts/search', 'sub_allowed' => false],
			['name' => 'POST_CATEGORIES', 'query' => 'post/cats', 'sub_allowed' => false],
			['name' => 'POST_CATEGORIES_ADD', 'query' => 'post/cats/add', 'sub_allowed' => false],
			['name' => 'POST_CATEGORIES_EDIT', 'query' => 'post/cats/edit', 'sub_allowed' => true],
			['name' => 'POST_CATEGORIES_DELETE', 'query' => 'post/cats/delete', 'sub_allowed' => true]
		]);

		return true;
	}

	/**
	 * delete image files
	 * delete posts, categories and permanent links
	 * @return boolean
	 */
	public function uninstall() {
		// delete uploaded files
		$result = $this->db->query('SELECT image FROM ' . DB_PRFX . 'posts');
		$result->execute();
		while ($post = $result->fetch(PDO::FETCH_ASSOC)) {
			if (empty($post['image']))
				continue;

			$image_file = PATH . DS . 'uploads' . DS . $post['image'];
			
			if (file_exists($image_file))
				unlink($image_file);
		}

		// delete permalinks
		$this->db->exec('DELETE FROM ' . DB_PRFX . 'permalinks WHERE query = \'post\' OR query LIKE \'post%\'');
		
		// drop posts table
		$this->db->exec('DROP TABLE ' . DB_PRFX . 'posts');

		// drop categories table
		$this->db->exec('DROP TABLE ' . DB_PRFX . 'posts_cats');

		// remove admin navigation items
		$this->setup->adminnav_delete('post');

		// remove permissions
		$this->setup->permission_delete('post');

		return true;
	}
}

?>