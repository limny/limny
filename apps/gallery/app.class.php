<?php

/**
 * Gallery application
 *
 * @package Limny
 * @author Hamid Samak <hamid@limny.org>
 * @copyright 2009-2015 Limny
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class GalleryApp {
	// database connection
	private $db;

	// setup library
	private $setup;

	/**
	 * set database connection
	 * load and set setup library
	 * @param [type] $registry [description]
	 */
	public function __construct($registry) {
		$this->db = $registry->db;

		$setup = load_lib('setup', true, true);

		$this->setup = $setup;
	}

	/**
	 * application install
	 * @return boolean
	 */
	public function install() {
		// create gallery table
		$this->db->exec('CREATE TABLE ' . DB_PRFX . 'gallery (
	id int(11) NOT NULL,
	title varchar(256) NOT NULL,
	image varchar(128) DEFAULT NULL,
	thumbnail varchar(128) DEFAULT NULL,
	category text DEFAULT NULL,
	time int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8');

		// modify id column as primary key
		$this->db->exec('ALTER TABLE ' . DB_PRFX . 'gallery ADD PRIMARY KEY (id)');

		// set id columns as auto increment
		$this->db->exec('ALTER TABLE ' . DB_PRFX . 'gallery MODIFY id int(11) NOT NULL AUTO_INCREMENT');

		// create categories table
		$this->db->exec('CREATE TABLE ' . DB_PRFX . 'gallery_cats (
	id int(11) NOT NULL,
	name varchar(256) NOT NULL,
	parent int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8');

		// modify id column as primary key
		$this->db->exec('ALTER TABLE ' . DB_PRFX . 'gallery_cats ADD PRIMARY KEY (id)');

		// set id columns as auto increment
		$this->db->exec('ALTER TABLE ' . DB_PRFX . 'gallery_cats MODIFY id int(11) NOT NULL AUTO_INCREMENT');

		// item(s) for admin panel navigation
		$this->setup->add_adminnav([
			['title' => 'GALLERY_GALLERY', 'query' => 'gallery'],
			['title' => 'GALLERY_PICTURES', 'query' => 'gallery/pics'],
			['title' => 'GALLERY_CATEGORIES', 'query' => 'gallery/cats']
		]);

		// permissions
		$this->setup->add_permission([
			['name' => 'GALLERY_GALLERY', 'query' => 'gallery', 'sub_allowed' => false],
			['name' => 'GALLERY_PICTURES', 'query' => 'gallery/pics', 'sub_allowed' => false],
			['name' => 'GALLERY_ADD', 'query' => 'gallery/pics/add', 'sub_allowed' => false],
			['name' => 'GALLERY_EDIT', 'query' => 'gallery/pics/edit', 'sub_allowed' => true],
			['name' => 'GALLERY_DELETE', 'query' => 'gallery/pics/delete', 'sub_allowed' => true],
			['name' => 'GALLERY_CATEGORIES', 'query' => 'gallery/cats', 'sub_allowed' => false],
			['name' => 'GALLERY_CATEGORIES_ADD', 'query' => 'gallery/cats/add', 'sub_allowed' => false],
			['name' => 'GALLERY_CATEGORIES_EDIT', 'query' => 'gallery/cats/edit', 'sub_allowed' => true],
			['name' => 'GALLERY_CATEGORIES_DELETE', 'query' => 'gallery/cats/delete', 'sub_allowed' => true]
		]);

		return true;
	}

	/**
	 * application uninstall
	 * @return boolean
	 */
	public function uninstall() {
		// delete uploaded files
		$result = $this->db->query('SELECT image FROM ' . DB_PRFX . 'gallery');
		$result->execute();
		while ($post = $result->fetch(PDO::FETCH_ASSOC)) {
			$image_file = PATH . DS . 'uploads' . DS . $post['image'];
			$thumbnail_file = PATH . DS . 'uploads' . DS . $post['image'];

			foreach ([$image_file, $thumbnail_file] as $file)
				if (file_exists($file))
					unlink($file);
		}

		// drop gallery table
		$this->db->exec('DROP TABLE ' . DB_PRFX . 'gallery');

		// drop categories table
		$this->db->exec('DROP TABLE ' . DB_PRFX . 'gallery_cats');

		// remove admin navigation items
		$this->setup->adminnav_delete('gallery');

		// remove permissions
		$this->setup->permission_delete('gallery');

		return true;
	}
}

?>