<?php

/**
 * Page controller
 *
 * @package Limny
 * @author Hamid Samak <hamid@limny.org>
 * @copyright 2009-2016 Limny
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class PageController {
	// page query parameter
	public $q;

	// page cache options
	public $cache;
	
	// page head tags
	public $head;

	// page title
	public $title;

	// page content
	public $content;

	/**
	 * set model database connection
	 * @param  object $registry
	 * @return void
	 */
	public function PageController($registry) {
		PageModel::$db = $registry->db;
	}

	/**
	 * main and global page
	 * @return boolean
	 */
	public function __global() {
		if (isset($this->q['param'][1]) && is_numeric($this->q['param'][1]))
			return $this->page($this->q['param'][1]);

		return false;
	}

	/**
	 * set title and content by page id
	 * @param  integer $id page id
	 * @return boolean
	 */
	private function page($id) {
		if ($page = PageModel::page($id)) {
			$page['text'] = $this->page_text($page['text'], $page['image']);
			$page['url'] = $this->page_permalink($id);

			$this->title = $page['title'];
			$this->content = load_view('page', 'page.tpl', ['page' => $page]);

			return true;
		}

		return false;
	}

	/**
	 * replace image address in text
	 * @param  string $text  page content
	 * @param  string $image image address
	 * @return string
	 */
	private function page_text($text, $image) {
		if (strpos($text, '{IMAGE}') === false)
			return $text;

		$image = empty($image) ? '' : BASE . '/uploads/' . $image;
		
		return str_replace('{IMAGE}', $image, $text);
	}

	/**
	 * get page permanent link URL
	 * @param  integer $page_id
	 * @return string
	 */
	private function page_permalink($page_id) {
		$permalink = load_lib('permalink');

		if ($permalink_item = $permalink->permalink_by_query('page/' . $page_id))
			$page['url'] = url($permalink_item['permalink']);
		else
			$page['url'] = url('page/' . $page_id);

		return $page['url'];
	}
}

?>