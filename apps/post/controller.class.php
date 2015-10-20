<?php

class PostController {
	public $q;
	public $cache;
	
	public $head;
	public $title;
	public $content;

	public function __global() {
		if (count($this->q['param']) < 2 || (count($this->q['param']) === 3 && $this->q['param'][1] == 'page' && is_numeric($this->q['param'][2])))
			return $this->__default();
		else if (is_numeric($this->q['param'][1]))
			return $this->post($this->q['param'][1]);
		else if ($this->q['param'][1] == 'cat' && isset($this->q['param'][2]) && is_numeric($this->q['param'][2]))
			return $this->cat($this->q['param'][2]);
		else if ($this->q['param'][1] == 'author' && isset($this->q['param'][2]) && is_numeric($this->q['param'][2]))
			return $this->author($this->q['param'][2]);
		else if ($this->q['param'][1] == 'tag' && isset($this->q['param'][2]))
			return $this->tag($this->q['param'][2]);

		return false;
	}

	private function __default() {
		$num_posts = PostModel::num_posts();

		if ($num_posts > 0) {
			$page = isset($this->q['param'][2]) ? ceil($this->q['param'][2]) : 1;
			$last_page = ceil($num_posts / 10);

			if ($page < 1 || $page > $last_page)
				return false;

			$posts = PostModel::posts(10, ($page - 1) * 10);

			$this->title = POST_POSTS;

			foreach ($posts as $post) {
				$post['text'] = $this->post_text($post['text'], $post['image']);
				$category = $this->post_category($post['category']);
				$tags = $this->post_tags($post['tags']);
				$post['url'] = $this->post_permalink($post['id']);

				$this->content .= load_view('post', 'post.tpl', ['post' => $post, 'category' => $category, 'tags' => $tags]);
			}

			if ($num_posts > 10)
				$this->content .= load_view('post', 'pager.tpl', ['page' => $page, 'last_page' => $last_page]);

			return true;
		} else
			return $this->no_post();
	}

	private function post($id) {
		if ($post = PostModel::post($id)) {
			$post['text'] = $this->post_text($post['text'], $post['image']);
			$category = $this->post_category($post['category']);
			$tags = $this->post_tags($post['tags']);
			$post['url'] = $this->post_permalink($id);

			$this->title = $post['title'];
			$this->content = load_view('post', 'post.tpl', ['post' => $post, 'category' => $category, 'tags' => $tags]);

			$application = load_lib('application');

			if ($application->app_installed('comment') && $application->app_enabled('comment')) {
				$application->load_language('comment');

				require_once PATH . DS . 'apps' . DS . 'comment' . DS . 'comment.class.php';
				$comment = new Comment;

				$this->head .= $comment->head;
				$this->content .= $comment->comment($post, (isset($_GET['replyto']) ? $_GET['replyto'] : null));
			}

			return true;
		}
	}

	private function cat($cat_id) {
		$cat_item = PostModel::cat_by_id($cat_id);

		if ($cat_item === false)
			return false;

		$num_posts = PostModel::num_posts($cat_id);

		if ($num_posts > 0) {
			$page = isset($this->q['param'][4]) ? ceil($this->q['param'][4]) : 1;
			$last_page = ceil($num_posts / 10);

			if ($page < 1 || $page > $last_page)
				return false;

			$posts = PostModel::posts_by_cat($cat_id, 10, ($page - 1) * 10);

			$this->title = $cat_item['name'];

			foreach ($posts as $post) {
				$post['text'] = $this->post_text($post['text'], $post['image']);
				$category = $this->post_category($post['category']);
				$tags = $this->post_tags($post['tags']);
				$post['url'] = $this->post_permalink($post['id']);

				$this->content .= load_view('post', 'post.tpl', ['post' => $post, 'category' => $category, 'tags' => $tags]);
			}

			if ($num_posts > 10)
				$this->content .= load_view('post', 'pager.tpl', ['page' => $page, 'last_page' => $last_page]);

			return true;
		} else
			return $this->no_post();
	}

	private function tag($tag) {
		$tag = str_replace('-', ' ', $tag);

		$num_posts = PostModel::num_posts(null, $tag);

		if ($num_posts > 0) {
			$page = isset($this->q['param'][4]) ? ceil($this->q['param'][4]) : 1;
			$last_page = ceil($num_posts / 10);

			if ($page < 1 || $page > $last_page)
				return false;

			$posts = PostModel::posts_by_tag($tag, 10, ($page - 1) * 10);

			$this->title = $tag;

			foreach ($posts as $post) {
				$post['text'] = $this->post_text($post['text'], $post['image']);
				$category = $this->post_category($post['category']);
				$tags = $this->post_tags($post['tags']);
				$post['url'] = $this->post_permalink($post['id']);

				$this->content .= load_view('post', 'post.tpl', ['post' => $post, 'category' => $category, 'tags' => $tags]);
			}

			if ($num_posts > 10)
				$this->content .= load_view('post', 'pager.tpl', ['page' => $page, 'last_page' => $last_page]);

			return true;
		} else
			return $this->no_post();
	}

	private function post_text($text, $image) {
		if (strpos($text, '{IMAGE}') === false)
			return $text;

		$image = empty($image) ? '' : BASE . '/uploads/' . $image;
		
		return str_replace('{IMAGE}', $image, $text);
	}

	private function no_post() {
		$this->title = ERROR;
		$this->content = POST_SENTENCE_3;
	}

	private function post_category($category) {
		if (empty($category) === false) {
			foreach (explode(',', $category) as $cat_id) {
				$cat_item = PostModel::cat_by_id($cat_id);
				$categories[] = '<a href="' . url('post/cat/' .$cat_id) . '">' . $cat_item['name'] . '</a>';
			}

			return implode(', ', $categories);
		}

		return false;
	}

	private function post_tags($tags) {
		if (empty($tags) === false) {
			foreach (explode(',', $tags) as $tag) {
				$tag = trim($tag);
				$tag_words[] = '<a href="' . url('post/tag/' . str_replace(' ', '-', $tag)) . '">' . $tag . '</a>';
			}

			return implode(', ', $tag_words);
		}

		return false;
	}

	private function post_permalink($post_id) {
		$permalink = load_lib('permalink');

		if ($permalink_item = $permalink->permalink_by_query('post/' . $post_id))
			$post['url'] = url($permalink_item['permalink'], true);
		else
			$post['url'] = url('post/' . $post_id, true);

		return $post['url'];
	}

	private function author($user_id) {
		$num_posts = PostModel::num_posts(null, null, $user_id);

		if ($num_posts > 0) {
			$page = isset($this->q['param'][4]) ? ceil($this->q['param'][4]) : 1;
			$last_page = ceil($num_posts / 10);

			if ($page < 1 || $page > $last_page)
				return false;

			$posts = PostModel::posts_by_author($user_id, 10, ($page - 1) * 10);

			$this->title = $posts[0]['username'];

			foreach ($posts as $post) {
				$post['text'] = $this->post_text($post['text'], $post['image']);
				$category = $this->post_category($post['category']);
				$tags = $this->post_tags($post['tags']);
				$post['url'] = $this->post_permalink($post['id']);

				$this->content .= load_view('post', 'post.tpl', ['post' => $post, 'category' => $category, 'tags' => $tags]);
			}

			if ($num_posts > 10)
				$this->content .= load_view('post', 'pager.tpl', ['page' => $page, 'last_page' => $last_page]);

			return true;
		} else
			return $this->no_post();
	}
}

?>