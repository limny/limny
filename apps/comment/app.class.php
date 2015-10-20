<?php

class CommentApp {
	private $setup;

	public function __construct() {
		$setup = load_lib('setup', true, true);

		$this->setup = $setup;
	}

	public function install() {
		global $db;

		// create comments table
		$db->exec('CREATE TABLE ' . DB_PRFX . 'comments (
	id int(11) NOT NULL,
	post int(11) NOT NULL,
	name varchar(256) NOT NULL,
	email varchar(256) DEFAULT NULL,
	website varchar(256) DEFAULT NULL,
	comment text NOT NULL,
	replyto int(11) DEFAULT NULL,
	approved tinyint(1) DEFAULT NULL,
	ip int(11) NOT NULL,
	time int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8');

		// modify id column as primary key
		$db->exec('ALTER TABLE ' . DB_PRFX . 'comments ADD PRIMARY KEY (id)');

		// set id columns as auto increment
		$db->exec('ALTER TABLE ' . DB_PRFX . 'comments MODIFY id int(11) NOT NULL AUTO_INCREMENT');

		// item(s) for admin panel navigation
		$this->setup->add_adminnav([
			['title' => 'COMMENT_COMMENT', 'query' => 'comment'],
			['title' => 'COMMENT_UNAPPROVED', 'query' => 'comment/unapproved'],
			['title' => 'COMMENT_ALL_COMMENTS', 'query' => 'comment/all']
		]);

		// permissions
		$this->setup->add_permission([
			['name' => 'COMMENT_COMMENT', 'query' => 'comment', 'sub_allowed' => false],
			['name' => 'COMMENT_UNAPPROVED', 'query' => 'comment/unapproved', 'sub_allowed' => false],
			['name' => 'COMMENT_ALL_COMMENTS', 'query' => 'comment/all', 'sub_allowed' => false],
			['name' => 'COMMENT_VIEW', 'query' => 'comment/all/view', 'sub_allowed' => true],
			['name' => 'COMMENT_EDIT', 'query' => 'comment/all/edit', 'sub_allowed' => true],
			['name' => 'COMMENT_DELETE', 'query' => 'comment/all/delete', 'sub_allowed' => true],
			['name' => 'COMMENT_SEARCH', 'query' => 'comment/all/search', 'sub_allowed' => false]
		]);

		return true;
	}

	public function uninstall() {
		global $db;

		// drop comments table
		$db->exec('DROP TABLE ' . DB_PRFX . 'comments');

		// remove admin navigation items
		$this->setup->adminnav_delete('comment');

		// remove permissions
		$this->setup->permission_delete('comment');

		return true;
	}
}

?>