<?php

/**
 * Gallery controller
 *
 * @package Limny
 * @author Hamid Samak <hamid@limny.org>
 * @copyright 2009-2015 Limny
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class GalleryController {
	// page query parameter
	public $q;

	// page cache setting
	public $cache;
	
	// page head tags
	public $head;

	// page title
	public $title;

	// page content
	public $content;

	/**
	 * set database connection for gallery model
	 * @param [type] $registry [description]
	 */
	public function GalleryController($registry) {
		GalleryModel::$db = $registry->db;
	}

	/**
	 * gallery page detector
	 * detects proper page by given query parameter
	 * @return boolean
	 */
	public function __global() {
		$this->head = load_css('gallery', 'style.css');

		if (count($this->q['param']) < 2)
			return $this->__default();
		else if (isset($this->q['param'][1])) {
			if (is_numeric($this->q['param'][1]))
				return $this->picture($this->q['param'][1]);
			else if ($this->q['param'][1] == 'cat' && isset($this->q['param'][2]) && is_numeric($this->q['param'][2]))
				return $this->cat($this->q['param'][2]);
		}

		return false;
	}

	/**
	 * gallery default page
	 * @return boolean
	 */
	private function __default() {

		if ($cats = GalleryModel::cats()) {
			$this->title = GALLERY_GALLERY;

			$this->content = nav([
				GALLERY_GALLERY
			]);
			$this->content .= '<ul class="gallery categories">';

			foreach ($cats as $cat)
				$this->content .= '<li><a href="' . url('gallery/cat/' . $cat['id']) . '">' . $cat['name'] . '</a></li>';

			$this->content .= '</ul>';
		} else {
			$this->title = ERROR;
			$this->content = GALLERY_SENTENCE_2;
		}

		return true;
	}

	/**
	 * gallery category page
	 * @param  integer $cat_id category id
	 * @return boolean
	 */
	private function cat($cat_id) {
		$cat = GalleryModel::cat($cat_id);

		if ($cat === false)
			return false;

		$this->title = $cat['name'];

		$nav['gallery'] = GALLERY_GALLERY;
		if ($parent_cats = $this->parent_cats($cat['parent']))
			$nav = array_merge($nav, $parent_cats);
		$nav[] = $cat['name'];

		$this->content = nav($nav);

		if ($cats = GalleryModel::cats($cat_id)) {
			$this->content .= '<ul class="gallery categories">';

			foreach ($cats as $cat)
				$this->content .= '<li><a href="' . url('gallery/cat/' . $cat['id']) . '">' . $cat['name'] . '</a></li>';

			$this->content .= '<li style="clear:both"></li>';
			$this->content .= '</ul>';
			$this->content .= '<hr>';

			unset($cats);
		}

		$num_pictures = GalleryModel::num_pictures($cat_id);

		if ($num_pictures > 0) {
			$page = isset($this->q['param'][3]) && $this->q['param'][3] == 'page' && isset($this->q['param'][4]) ? ceil($this->q['param'][4]) : 1;
			$last_page = ceil($num_pictures / 10);

			if ($page < 1 || $page > $last_page)
				return false;

			$pictures = GalleryModel::pictures($cat_id, 10, ($page - 1) * 10);

			$this->content .= '<ul class="gallery pictures">';

			foreach ($pictures as $picture)
				$this->content .= load_view('gallery', 'thumbnail.tpl', ['picture' => $picture]);

			$this->content .= '<li style="clear:both"></li>';
			$this->content .= '</ul>';

			if ($num_pictures > 10)
				$this->content .= load_view('gallery', 'pager.tpl', ['cat_id' => $cat_id, 'page' => $page, 'last_page' => $last_page]);
		}

		return true;
	}

	/**
	 * gallery picture page
	 * @param  integer $picture_id picture id
	 * @return boolean
	 */
	private function picture($picture_id) {
		if ($picture = GalleryModel::picture($picture_id)) {
			$cat = GalleryModel::cat($picture['category']);

			$this->title = $picture['title'];

			$nav['gallery'] = GALLERY_GALLERY;
			if ($parent_cats = $this->parent_cats($cat['parent']))
				$nav = array_merge($nav, $parent_cats);
			$nav['gallery/cat/' . $cat['id']] = $cat['name'];
			$nav[] = $picture['title'];

			$this->content = nav($nav);
			$this->content .= load_view('gallery', 'picture.tpl', ['cat' => $cat, 'picture' => $picture]);

			return true;
		}

		return false;
	}

	/**
	 * get parent cat for navigation
	 * @param  integer $cat_id category id
	 * @return array
	 */
	private function parent_cats($cat_id) {
		if (empty($cat_id))
			return false;

		$cat = GalleryModel::cat($cat_id);

		$nav = [];

		if (empty($cat['parent']) === false)
			$nav = array_merge($nav, $this->parent_cats($cat['parent']));

		$nav = array_merge($nav, ['gallery/cat/' . $cat['id'] => $cat['name']]);

		return $nav;
	}
}

?>