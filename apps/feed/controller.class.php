<?php

class FeedController {
	public $q;
	public $cache;
	
	public $head;
	public $title;
	public $content;

	private $config;

	public function FeedController($registry) {
		$this->config = $registry->config;

		require_once PATH . '/apps/post/model.class.php';
		PostModel::$db = $registry->db;
	}

	public function __global() {
		if (isset($this->q['param']) && count($this->q['param']) > 1)
			redirect(url('feed'));

		$items = '';
		$last_build_date = '';
		foreach (PostModel::posts() as $post) {

			if (empty($last_build_date))
				$last_build_date = date('r', empty($post['update']) ? $post['time'] : $post['updated']);

			$categories = '';
			if (empty($post['category']) === false)
				foreach (explode(',', $post['category']) as $cat_id)
					if ($cat = PostModel::cat_by_id($cat_id))
						$categories .= load_view('feed', 'category.tpl', ['name' => $cat['name']]) . "\n";

			$params = [
				'post_title' => $post['title'],
				'post_url' => $this->post_permalink($post['id']),
				'post_date' => date('r', $post['time']),
				'post_author' => $post['username'],
				'post_category' => $categories,
				'post_url_by_id' => url('post/' . $post['id'], true),
				'post_text' => strip_tags($post['text'], '<p><br>')
			];

			$items .= load_view('feed', 'item.tpl', $params) . "\n";
		}

		$params = [
			'title' => $this->config->title,
			'feed_address' => url('feed', true),
			'address' => $this->config->address,
			'description' => $this->config->description,
			'items' => $items,
			'last_build_date' => $last_build_date,
			'language' => $this->config->language
		];

		header('Content-Type: text/xml;');

		print load_view('feed', 'feed.tpl', $params);

		exit;
	}

	private function post_permalink($post_id) {
		$permalink = load_lib('permalink');

		if ($permalink_item = $permalink->permalink_by_query('post/' . $post_id))
			$post['url'] = url($permalink_item['permalink'], true);
		else
			$post['url'] = url('post/' . $post_id, true);

		return $post['url'];
	}
}

?>