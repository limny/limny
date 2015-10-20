<?php

class PostAdminController extends Manage {
	public $q;
	
	public $head;
	public $title;
	public $content;

	public function __construct() {
		parent::__construct();

		$this->manage_q = $this->q;
	}

	public function posts() {
		$this->manage_title = POST_POSTS;
		$this->manage_table = 'posts';
		$this->manage_head = [
			POST_TITLE => 'title',
			POST_CATEGORY => 'category',
			POST_DATE => 'time',
			POST_STATUS => 'published'
		];
		$this->manage_view = true;
		$this->manage_search = ['title', 'text', 'tags'];
		$this->manage_sort = ['title', 'category', 'time', 'published'];
		$this->manage_order = ['id' => 'DESC'];
		$this->manage_upload_path = PATH . DS . 'uploads';
		$this->manage_upload_base = BASE . '/uploads';
		$this->manage_fields = [
			'title' => [
				'label' => POST_TITLE,
				'type' => 'text',
				'required' => true
			],
			'text' => [
				'label' => POST_TEXT,
				'type' => 'textarea',
				'help' => POST_SENTENCE_2,
				'style' => 'min-height: 300px;',
				'id' => 'editor'
			],
			'category' => [
				'label' => POST_CATEGORY,
				'type' => 'checkbox'
			],
			'tags' => [
				'label' => POST_TAGS,
				'type' => 'text',
				'help' => POST_SENTENCE_1
			],
			'image' => [
				'label' => POST_IMAGE,
				'type' => 'file',
				'image' => true
			],
			'published' => [
				'label' => POST_STATUS,
				'type' => 'radio',
				'items' => ['0' => POST_DRAFT, '1' => POST_PUBLISHED],
				'required' => true
			]
		];
		$this->manage_fields_view = [
			'title' => ['label' => POST_TITLE],
			'text' => ['label' => POST_TEXT],
			'category' => ['label' => POST_CATEGORY],
			'tags' => ['label' => POST_TAGS],
			'image' => ['label' => POST_IMAGE, 'type' => 'file'],
			'time' => ['label' => POST_DATE],
			'updated' => ['label' => POST_UPDATE_DATE],
			'published' => ['label' => POST_STATUS],
		];

		$this->manage_fields['text']['help'] .= ' <a href="javascript:void(0);" onclick="var textarea = $(\'#editor\'); if (textarea.css(\'visibility\') == \'hidden\') { CKEDITOR.instances[\'editor\'].insertHtml(\'<\' + \'img src=\\\'{IMAGE}\\\'\' + \'>\') } else { textarea.val(textarea.val() + \'<\' + \'img src=\\\'{IMAGE}\\\'\' + \'>\'); }">' . POST_INSERT_IMAGE . '</a>';
		$this->manage_fields['category']['items'] = $this->table_to_array('posts_cats', 'id', 'name');

		$this->manage_action->add_value->user = $_SESSION['limny']['admin']['id'];
		$this->manage_action->add_value->time = time();
		$this->manage_action->edit_value->updated = time();
		$this->manage_action->view->published = 'post_status';
		$this->manage_action->view->text = 'post_text';
		$this->manage_action->view->category = 'post_category';
		$this->manage_action->view->time = 'system_date';
		$this->manage_action->view->updated = 'system_date';
		$this->manage_action->list->title = 'post_title';
		$this->manage_action->list->category = 'post_category';
		$this->manage_action->list->time = 'system_date';
		$this->manage_action->list->published = 'post_status';
		$this->manage_action->add->tags = 'post_tags';
		$this->manage_action->edit->tags = 'post_tags';
		$this->manage_action->add->title = 'post_permalink_add';
		$this->manage_action->edit->title = 'post_permalink_edit';

		$this->manage_action->delete = 'post_delete';
		
		$application = load_lib('application', true, true);
		$lib_apps_enabled = $application->apps(true, 'lib');

		if (isset($lib_apps_enabled['ckeditor']))
			$this->head = '<script type="text/javascript" src="' . BASE . '/apps/ckeditor/ckeditor/ckeditor.js"></script>
<script type="text/javascript">
$(function(){
	if ($("#editor").length > 0)
		CKEDITOR.replace("editor");
});
</script>';

		$this->title = POST_POSTS;
		$this->content = $this->manage();
	}

	public function cats() {
		$this->manage_title = POST_CATEGORIES;
		$this->manage_table = 'posts_cats';
		$this->manage_head = [POST_NAME => 'name'];
		$this->manage_sort = ['name'];
		$this->manage_fields = [
			'name' => [
				'label' => POST_NAME,
				'type' => 'text',
				'required' => true
			]
		];

		$this->title = POST_CATEGORIES;
		$this->content = $this->manage();
	}

	protected function post_status($status) {
		if (empty($status))
			return '<span class="text-red">' . POST_DRAFT . '</span>';
		
		return '<span class="text-green">' . POST_PUBLISHED . '</span>';
	}

	protected function post_text($text, $post) {
		return str_replace('{IMAGE}', $this->manage_upload_base . '/' . $post['image'], $text);
	}

	protected function post_category($cat_ids) {
		global $db;

		foreach (explode(',', $cat_ids) as $cat_id) {
			$result = $db->prepare('SELECT name FROM ' . DB_PRFX . 'posts_cats WHERE id = ?');
			$result->execute([$cat_id]);

			if ($category = $result->fetch(PDO::FETCH_ASSOC))
				$names[] = $category['name'];
		}

		return isset($names) ? implode(', ', $names) : false;
	}

	protected function post_tags($tags) {
		if (empty($tags))
			return false;

		$tags = explode(',', $tags);
		$tags = array_map('trim', $tags);
		$tags = array_filter($tags);
		$tags = implode(',', $tags);

		return $tags;
	}

	protected function post_permalink_add($title) {
		$permalink = load_lib('permalink');
		
		$last_id = PostAdminModel::posts_last_id();
		$permalink_str = $permalink->permalink_generate($title);

		$permalink->add_permalink('post/' . ($last_id + 1), $permalink_str);
		
		return $title;
	}

	protected function post_permalink_edit($title, $item = [], $files = [], $id) {
		$permalink = load_lib('permalink');

		$permalink_item = $permalink->permalink_by_query('post/' . $id);
		$permalink_str = $permalink->permalink_generate($title, $permalink_item['id']);

		$permalink->update_permalink($permalink_item['id'], $permalink_str);
		
		return $title;
	}

	protected function post_delete($ids) {
		$permalink = load_lib('permalink');

		foreach ($ids as $id) {
			if ($item = $this->get_item($id)) {
				if (empty($item['image']) === false && file_exists($this->manage_upload_path . DS . $item['image']))
					unlink($this->manage_upload_path . DS . $item['image']);

				$permalink->permalink_remove('post/' . $id);
			}
		}

		return true;
	}

	protected function post_title($title, $item) {
		return $title . ' <a href="' . url('post/' . $item['id']) . '" target="_blank"><i class="fa fa-location-arrow"></i></a>';
	}
}

?>