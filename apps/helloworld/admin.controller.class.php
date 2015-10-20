<?php

/**
 * Hello world admin controller
 * class name start in upper case and end with "AdminController"
 */
class HelloworldAdminController {
	// foo page
	public function foo() {
		$this->title = 'Foo';
		$this->content = 'This is <strong>foo</strong> page content.<br>';

		$this->content .= '<br><a href="' . url('helloworld') . '" target="_blank">Hello World!</a>';
		$this->content .= '<br><a href="' . url('helloworld/test-page') . '" target="_blank">Hello World! test page</a>';
	}

	// bar page
	public function bar() {
		$this->title = 'Bar';
		$this->content = 'This is <strong>bar</strong> page content.';
	}
}

?>