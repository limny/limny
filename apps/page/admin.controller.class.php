<?php

/**
 * Page administration controller
 *
 * @package Limny
 * @author Hamid Samak <hamid@limny.org>
 * @copyright 2009-2016 Limny
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class PageAdminController extends Manage {
	// page query parameter
	public $q;
	
	// page head tags
	public $head;

	// page title
	public $title;

	// page content
	public $content;

	/**
	 * call manage parent constructor
	 * set page model connection
	 * set query parameter to manage
	 * @param object $registry
	 */
	public function __construct($registry) {
		parent::__construct($registry);

		PageAdminModel::$db = $registry->db;

		$this->manage_q = $this->q;
	}

	/**
	 * pages list
	 * @return void
	 */
	public function __global() {
		$this->manage_title = PAGE_PAGES;
		$this->manage_table = 'pages';
		$this->manage_head = [
			PAGE_TITLE => 'title',
			PAGE_DATE => 'time',
		];
		$this->manage_view = true;
		$this->manage_search = ['title', 'text'];
		$this->manage_sort = ['title', 'time'];
		$this->manage_order = ['id' => 'DESC'];
		$this->manage_upload_path = PATH . DS . 'uploads';
		$this->manage_upload_base = BASE . '/uploads';
		$this->manage_fields = [
			'title' => [
				'label' => PAGE_TITLE,
				'type' => 'text',
				'required' => true
			],
			'text' => [
				'label' => PAGE_TEXT,
				'type' => 'textarea',
				'help' => PAGE_SENTENCE_1,
				'style' => 'min-height: 300px;',
				'id' => 'editor'
			],
			'image' => [
				'label' => PAGE_IMAGE,
				'type' => 'file',
				'image' => true
			]
		];
		$this->manage_fields_view = [
			'title' => ['label' => PAGE_TITLE],
			'text' => ['label' => PAGE_TEXT],
			'image' => ['label' => PAGE_IMAGE, 'type' => 'file'],
			'time' => ['label' => PAGE_DATE],
			'updated' => ['label' => PAGE_UPDATE_DATE]
		];

		$this->manage_fields['text']['help'] .= ' <a href="javascript:void(0);" onclick="var textarea = $(\'#editor\'); if (textarea.css(\'visibility\') == \'hidden\') { CKEDITOR.instances[\'editor\'].insertHtml(\'<\' + \'img src=\\\'{IMAGE}\\\'\' + \'>\') } else { textarea.val(textarea.val() + \'<\' + \'img src=\\\'{IMAGE}\\\'\' + \'>\'); }">' . PAGE_INSERT_IMAGE . '</a>';

		$this->manage_action->add_value->time = time();
		$this->manage_action->edit_value->updated = time();
		$this->manage_action->view->text = 'page_text';
		$this->manage_action->view->time = 'system_date';
		$this->manage_action->view->updated = 'system_date';
		$this->manage_action->list->title = 'page_title';
		$this->manage_action->list->time = 'system_date';
		$this->manage_action->add->title = 'page_permalink_add';
		$this->manage_action->edit->title = 'page_permalink_edit';

		$this->manage_action->delete = 'page_delete';
		
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

		$this->title = PAGE_PAGES;
		$this->content = $this->manage();
	}

	/**
	 * insert page permanent link
	 * @param  string $title page title
	 * @return string
	 */
	protected function page_permalink_add($title) {
		$permalink = load_lib('permalink');
		
		$last_id = PageAdminModel::pages_last_id();
		$permalink_str = $permalink->permalink_generate($title);

		$permalink->add_permalink('page/' . ($last_id + 1), $permalink_str);
		
		return $title;
	}

	/**
	 * update page permanent link
	 * @param  string  $title page title
	 * @param  array   $item  page item array
	 * @param  array   $files posted files
	 * @param  integer $id    record ID
	 * @return string
	 */
	protected function page_permalink_edit($title, $item = [], $files = [], $id) {
		$permalink = load_lib('permalink');

		$permalink_item = $permalink->permalink_by_query('page/' . $id);
		$permalink_str = $permalink->permalink_generate($title, $permalink_item['id']);

		$permalink->update_permalink($permalink_item['id'], $permalink_str);
		
		return $title;
	}

	/**
	 * replace image URL in text
	 * @param  string $text page content
	 * @param  array  $page page item array
	 * @return string
	 */
	protected function page_text($text, $page) {
		return str_replace('{IMAGE}', $this->manage_upload_base . '/' . $page['image'], $text);
	}

	/**
	 * delete page by given ID(s)
	 * @param  array   $ids array of ID(s)
	 * @return boolean
	 */
	protected function page_delete($ids) {
		$permalink = load_lib('permalink');

		foreach ($ids as $id) {
			if ($item = $this->get_item($id)) {
				if (empty($item['image']) === false && file_exists($this->manage_upload_path . DS . $item['image']))
					unlink($this->manage_upload_path . DS . $item['image']);

				$permalink->permalink_remove('page/' . $id);
			}
		}

		return true;
	}

	/**
	 * add link to page title
	 * @param  string $title page title
	 * @param  array  $item  page item array
	 * @return string
	 */
	protected function page_title($title, $item) {
		$permalink = load_lib('permalink');

		$permalink_item = $permalink->permalink_by_query('page/' . $item['id']);

		return $title . ' <a href="' . url($permalink_item['permalink']) . '" target="_blank"><i class="fa fa-location-arrow"></i></a>';
	}
}

?>