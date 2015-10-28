<?php

class PageController {
	public $q;
	public $cache;
	
	public $head;
	public $title;
	public $content;

	public function PageController($registry) {
		PageModel::$db = $registry->db;
	}

	public function __global() {
		if (isset($this->q['param'][1]) && is_numeric($this->q['param'][1]))
			return $this->page($this->q['param'][1]);

		return false;
	}

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

	private function page_text($text, $image) {
		if (strpos($text, '{IMAGE}') === false)
			return $text;

		$image = empty($image) ? '' : BASE . '/uploads/' . $image;
		
		return str_replace('{IMAGE}', $image, $text);
	}

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