<?php

class GalleryAdminController extends Manage {
	public $q;
	
	public $head;
	public $title;
	public $content;

	public $manage_upload_path = PATH . DS . 'uploads';

	public function __construct($registry) {
		parent::__construct($registry);

		$this->manage_q = $this->q;
	}

	public function pics() {
		$this->manage_title = GALLERY_PICTURES;
		$this->manage_table = 'gallery';
		$this->manage_head = [
			GALLERY_TITLE => 'title',
			GALLERY_CATEGORY => 'category'
		];
		$this->manage_sort = ['title'];
		$this->manage_order = ['id' => 'DESC'];
		$this->manage_upload_base = BASE . '/uploads';
		$this->manage_delete_file = false;
		$this->manage_fields = [
			'title' => [
				'label' => GALLERY_TITLE,
				'type' => 'text',
				'required' => true
			],
			'category' => [
				'label' => GALLERY_CATEGORY,
				'type' => 'combo',
				'items' => $this->table_to_array('gallery_cats', 'id', 'name'),
				'required' => true
			],
			'image' => [
				'label' => GALLERY_IMAGE,
				'type' => 'file'
			]
		];

		$this->manage_action->add_value->time = time();
		$this->manage_action->list->category = 'picture_category';
		$this->manage_action->add->function = 'generate_thumbnail';
		$this->manage_action->edit->function = 'generate_thumbnail';
		$this->manage_action->check->image = 'image_check';

		$this->manage_action->delete = 'picture_delete';

		$this->title = GALLERY_PICTURES;
		$this->content = $this->manage();
	}

	public function cats() {
		$this->manage_title = GALLERY_CATEGORIES;
		$this->manage_table = 'gallery_cats';
		$this->manage_head = [
			GALLERY_NAME => 'name',
			GALLERY_PARENT => 'parent'
		];
		$this->manage_order = ['parent' => 'ASC'];
		$this->manage_fields = [
			'name' => [
				'label' => GALLERY_NAME,
				'type' => 'text',
				'required' => true,
			],
			'parent' => [
				'label' => GALLERY_PARENT,
				'type' => 'combo',
				'items' => $this->cat_items(),
			]
		];

		$this->manage_action->list->parent = 'category_parent';
		$this->manage_action->delete = 'category_delete';

		$this->title = GALLERY_CATEGORIES;
		$this->content = $this->manage();
	}

	protected function cat_items() {
		$items[0] = '[' . GALLERY_NO_PARENT . ']';

		if ($cats = $this->table_to_array('gallery_cats', 'id', 'name'))
			$items = array_merge($items, $cats);

		if (isset($this->q[3]) && is_numeric($this->q[3])) {
			unset($items[$this->q[3]]);

			$parent_ids = [$this->q[3]];

			while (count($parent_ids) > 0) {
				$child_ids = [];

				foreach ($parent_ids as $parent_id) {
					if ($cats = GalleryAdminModel::cats($parent_id))
						foreach ($cats as $cat_id => $cat) {
							unset($items[$cat_id]);

							$child_ids[] = $cat_id;
						}
				}

				$parent_ids = count($child_ids) > 0 ? $child_ids : [];
			}
		}	

		return $items;
	}

	protected function category_parent($cat_id) {
		if (empty($cat_id))
			return '<em class="text-gray">' . GALLERY_NO_PARENT . '</em>';

		$cat = GalleryAdminModel::cat($cat_id);

		return $cat['name'];
	}

	public function generate_thumbnail($function, $post, $files, $id, $item = []) {
		if (isset($item) && count($item) > 0) {
			if (isset($files['image']) && empty($files['image']['name']))
				return true;

			$files = [$item['image'], $item['thumbnail']];
			foreach ($files as $file)
				if (file_exists($this->manage_upload_path . DS . $file))
					unlink($this->manage_upload_path . DS . $file);
		}

		$item = $this->get_item($id);
		$image_file = $this->manage_upload_path . DS . $item['image'];

		if (empty($item['image']) === false && file_exists($image_file)) {
			$extension = strtolower(substr($item['image'], strrpos($item['image'], '.') + 1));

			list($width, $height) = getimagesize($image_file);
			$new_width = 100;
			$new_height = 100 * $height / $width;

			switch ($extension) {
				case 'jpeg': $image = imagecreatefromjpeg($image_file); break;
				case 'gif': $image = imagecreatefromgif($image_file); break;
				case 'jpg': $image = imagecreatefromjpeg($image_file); break;
				case 'png': $image = imagecreatefrompng($image_file); break;
			}

			$new_image = imagecreatetruecolor($new_width, $new_height);
			imagecopyresampled($new_image, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);

			$thumbnail_name = substr($item['image'], 0, strrpos($item['image'], '.')) . '_thumb.' . $extension;
			$thumbnail_file = $this->manage_upload_path . DS . $thumbnail_name;

			switch ($extension) {
				case 'jpeg': imagejpeg($new_image, $thumbnail_file); break;
				case 'gif': imagegif($new_image, $thumbnail_file); break;
				case 'jpg': imagejpeg($new_image, $thumbnail_file); break;
				case 'png': imagepng($new_image, $thumbnail_file); break;
			}

			GalleryAdminModel::update_thumbnail($id, $thumbnail_name);

			return true;
		}
		
		return false;
	}

	protected function picture_category($cat_id) {
		$cat = GalleryAdminModel::cat($cat_id);

		return $cat['name'];
	}

	protected function image_check($column, $post, $files, $id = null) {
		if (isset($id) && $id > 0)
			return true;

		if (isset($files['image']) && empty($files['image']['name']) === false)
			return true;

		return GALLERY_SENTENCE_1;
	}

	protected function picture_delete($ids) {
		foreach ($ids as $id)
			if ($item = $this->get_item($id))
				if (empty($item['thumbnail']) === false && file_exists($this->manage_upload_path . DS . $item['thumbnail']))
					unlink($this->manage_upload_path . DS . $item['thumbnail']);

		return true;
	}

	protected function category_delete($ids) {
		$parent_ids = $ids;

		while (count($parent_ids) > 0) {
			$child_ids = [];

			foreach ($parent_ids as $parent_id) {
				if ($cats = GalleryAdminModel::cats($parent_id))
					foreach ($cats as $cat_id => $cat)
						$child_ids[] = $cat_id;

				GalleryAdminModel::pictures_delete($parent_id, $this->manage_upload_path);
				GalleryAdminModel::category_delete($parent_id);
			}

			$parent_ids = count($child_ids) > 0 ? $child_ids : [];
		}

		return true;
	}
}

?>