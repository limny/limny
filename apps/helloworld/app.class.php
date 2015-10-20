<?php

/**
 * HelloworldApp class
 * application name is the same directory name
 * application class name must start in upper case and end with "App"
 */
class HelloworldApp {
	private $setup;

	// loads setup library for both install and uninstall methods
	public function __construct() {
		$setup = load_lib('setup', true, true);

		$this->setup = $setup;
	}

	/**
	 * install method
	 * all need for installing this app (e.g. create database tables, directories, navigation items, permissions)
	 * @return boolean
	 */
	public function install() {
		global $db;

		// add widget
		// look widget.class.php in app directory
		$db->exec('INSERT INTO ' . DB_PRFX . 'widgets (app, method, position, roles, languages, sort) VALUE (\'helloworld\', \'helloworld_foobar_widget\', \'none\', \'all\', \'all\', 1)');

		// items for admin panel navigation
		// shown in sidebar
		$this->setup->add_adminnav([
			['title' => 'Hello World!', 'query' => 'helloworld'],
			['title' => 'Foo', 'query' => 'helloworld/foo'],
			['title' => 'Bar', 'query' => 'helloworld/bar']
		]);

		/**
		 * permissions
		 * each page must (like "foo" and "bar" in above) must have a permission
		 * permission for a role can change under Roles administration
		 * sub_allowed true for "foo" means "helloworld/foo/any-child-page" is allowed
		 */
		$this->setup->add_permission([
			['name' => 'Hello World!', 'query' => 'helloworld', 'sub_allowed' => false],
			['name' => 'Foo', 'query' => 'helloworld/foo', 'sub_allowed' => false],
			['name' => 'Bar', 'query' => 'helloworld/bar', 'sub_allowed' => false]
		]);

		return true;
	}

	/**
	 * uninstall method
	 * restore system to earlier state before installing this app
	 * @return boolean
	 */
	public function uninstall() {
		global $db;

		// delete widget
		$db->exec('DELETE FROM ' . DB_PRFX . 'widgets WHERE app = \'helloworld\'');

		// remove admin navigation items
		$this->setup->adminnav_delete('helloworld');

		// remove permissions
		$this->setup->permission_delete('helloworld');

		return true;
	}
}

?>