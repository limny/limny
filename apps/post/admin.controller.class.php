<?php

/**
 * Post administration controller
 *
 * @package Limny
 * @author Hamid Samak <hamid@limny.org>
 * @copyright 2009-2016 Limny
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class PostAdminController extends Manage {
	// page query parameter
	public $q;
	
	// page head tags
	public $head;

	// page title
	public $title;

	// page content
	public $content;

	/**
	 * call manage constructor
	 * set post model database connection
	 * set manage page query parameter
	 * @param [type] $registry [description]
	 */
	public function __construct($registry) {
		parent::__construct($registry);

		PostAdminModel::$db = $registry->db;

		$this->manage_q = $this->q;
	}

	/**
	 * posts list
	 * @return void
	 */
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

	/**
	 * categories list
	 * @return void
	 */
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

	/**
	 * post status in coloured text
	 * @param  integer $status
	 * @return string
	 */
	protected function post_status($status) {
		if (empty($status))
			return '<span class="text-red">' . POST_DRAFT . '</span>';
		
		return '<span class="text-green">' . POST_PUBLISHED . '</span>';
	}

	/**
	 * replace image address in text
	 * @param  string $text
	 * @param  array  $post post item array
	 * @return string
	 */
	protected function post_text($text, $post) {
		return str_replace('{IMAGE}', $this->manage_upload_base . '/' . $post['image'], $text);
	}

	/**
	 * get post category names
	 * @param  string         $cat_ids comma separated ids
	 * @return string/boolean
	 */
	protected function post_category($cat_ids) {
		foreach (explode(',', $cat_ids) as $cat_id)
			if ($category = PostAdminModel::category($cat_id))
				$names[] = $category['name'];

		return isset($names) ? implode(', ', $names) : false;
	}

	/**
	 * generate post tags
	 * remove spaces
	 * @param  string $tags comma separated
	 * @return string
	 */
	protected function post_tags($tags) {
		if (empty($tags))
			return false;

		$tags = explode(',', $tags);
		$tags = array_map('trim', $tags);
		$tags = array_filter($tags);
		$tags = implode(',', $tags);

		return $tags;
	}

	/**
	 * insert permanent link by title
	 * @param  string $title post title
	 * @return string
	 */
	protected function post_permalink_add($title) {
		$permalink = load_lib('permalink');
		
		$last_id = PostAdminModel::posts_last_id();
		$permalink_str = $permalink->permalink_generate($title);

		$permalink->add_permalink('post/' . ($last_id + 1), $permalink_str);
		
		return $title;
	}

	/**
	 * update post permanent link
	 * @param  string $title post title
	 * @param  array  $item  posted data
	 * @param  array  $files posted files
	 * @param  integer $id    post id
	 * @return string
	 */
	protected function post_permalink_edit($title, $item = [], $files = [], $id) {
		$permalink = load_lib('permalink');

		$permalink_item = $permalink->permalink_by_query('post/' . $id);
		$permalink_str = $permalink->permalink_generate($title, $permalink_item['id']);

		$permalink->update_permalink($permalink_item['id'], $permalink_str);
		
		return $title;
	}

	/**
	 * delete post image and permanent link
	 * @param  array $ids array of posts ids
	 * @return boolean
	 */
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

	/**
	 * post title with navigation icon and url
	 * @param  string $title post title
	 * @param  array  $item  post item array
	 * @return string
	 */
	protected function post_title($title, $item) {
		return $title . ' <a href="' . url('post/' . $item['id']) . '" target="_blank"><i class="fa fa-location-arrow"></i></a>';
	}
}

?>