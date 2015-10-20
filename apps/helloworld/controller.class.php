<?php

/**
 * hello world app controller
 * contents for (q=helloworld) and subsets
 */
class HelloworldController {
	public $q; // current query parameter
	public $cache; // cache duration
	
	public $head; // tags to <head></head>
	public $title; // page title
	public $content; // page content
	
	// q=helloworld
	public function __default() {
		$this->title = 'Hello World!';
		$this->content = 'Welcome to hello world app!';
		
		$this->cache = 10;
	}
	
	// q=helloworld/test-page
	public function test_page() {
		$this->title = 'Hello test';
		$this->content = 'This is the Hello World test page.';
	}
}

?>