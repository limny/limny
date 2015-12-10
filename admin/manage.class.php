<?php

/**
 * Database records management
 *
 * @package Limny
 * @author Hamid Samak <hamid@limny.org>
 * @copyright 2009-2015 Limny
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class Manage extends Admin {
	// page query parameter
	public $manage_q = [];

	// database items table
	public $manage_table = null;

	// database ID column
	public $manage_id_col = 'id';

	// extended function or methods for input, list, add, edit and delete modes
	public $manage_action = null;
	
	// input mode fields array
	public $manage_fields = [];

	// base URL
	public $manage_base = null;
	
	// add button visibility
	public $manage_add = true;

	// delete selected button visibility
	public $manage_deletes = true;

	// edit button visibility
	public $manage_edit = true;

	// view button visibility
	public $manage_view = false;

	// delete button visibility
	public $manage_delete = true;

	// table heading items
	public $manage_head = [];

	// database query for selecting items
	public $manage_query = null;

	// items order
	public $manage_order = [];

	// number of items per page
	public $manage_number = 15;

	// array of sortable columns
	public $manage_sort = false;

	// array of columns to search
	public $manage_search = false;

	// page icon
	public $manage_icon = false;

	// upload path
	public $manage_upload_path = false;

	// upload base URL
	public $manage_upload_base = false;

	// delete uploaded file
	public $manage_delete_file = true;

	// allowed image extensions for upload
	private $manage_image_exts = ['png', 'gif', 'jpg', 'jpeg'];

	/**
	 * call parent constructor
	 * set manage actions
	 * load library language
	 * set page query parameter
	 * @param array $registry
	 * @param array $parameters
	 */
	public function __construct($registry = [], $parameters = []) {
		parent::__construct($registry);

		settype($this->manage_action, 'object');

		foreach (['add', 'view', 'edit', 'check', 'list', 'add_value', 'edit_value', 'field'] as $action)
			$this->manage_action->{$action} = new stdClass;

		$language = $this->config->language;
		require_once PATH . DS . 'langs' . DS . $language . DS . 'manage.php';

		if (isset($parameters['q']))
			$this->manage_q = $parameters['q'];
	}

	/**
	 * show manage page based on URL
	 * @return string
	 */
	public function manage() {
		if (end($this->manage_q) === 'add' || end($this->manage_q) === 'delete' || end($this->manage_q) === 'search')
			$this->manage_base = array_slice($this->manage_q, 0, -1);
		else if (isset($this->manage_q[count($this->manage_q) - 2]) && in_array($this->manage_q[count($this->manage_q) - 2], ['view', 'edit', 'delete']))
			$this->manage_base = array_slice($this->manage_q, 0, -2);
		else if (is_numeric(end($this->manage_q)))
			$this->manage_base = array_slice($this->manage_q, 0, -1);
		else
			$this->manage_base = $this->manage_q;

		$this->manage_base = BASE . '/' . ADMIN_DIR . '/' . implode('/', $this->manage_base);
		
		$post = isset($_POST) && is_array($_POST) ? $_POST : [];
		$files = isset($_FILES) && is_array($_FILES) ? $_FILES : [];

		if (count($this->manage_q) > 1) {
			$diff = array_values(array_diff($this->manage_q, explode('/', $this->manage_base)));

			if (isset($diff[0]) && empty($diff[0]) === false)
				$second_level = $diff[0];
		}

		if (end($this->manage_q) === 'add' && $this->manage_add === true)
			$data = $this->input_form($post, $files);
		else if (isset($second_level) && $second_level === 'edit' && $this->manage_edit === true) {
			$id = end($this->manage_q);

			$data = $this->input_form($post, $files, $id);
		} else if (isset($second_level) && $second_level === 'view' && $this->manage_view === true) {
			$id = end($this->manage_q);

			$data = $this->view($id);
		} else if (end($this->manage_q) === 'delete' || (isset($second_level) && $second_level === 'delete')) {
			$id = end($this->manage_q);

			if (is_numeric($id))
				$ids = [$id];
			else if (isset($post['items']))
				$ids = $post['items'];
			else
				$ids = [];

			$data = $this->delete($ids, $post);
		} else if (isset($second_level) && $second_level === 'sort') {
			$column = $this->manage_q[count($this->manage_q) - 2];
			$order = end($this->manage_q);

			$data = $this->sort($column, $order);
		}else if (end($this->manage_q) === 'search' && $this->manage_search !== false && isset($post['search']))
			$data = $this->search($post['search'], $post);
		else {
			$page = ceil(end($this->manage_q));

			if ($page < 1)
				$page = 1;
			
			$_SESSION['_manage_page'][$this->manage_table] = $page;

			$data = $this->list_table();
		}
		
		$data .= '<link rel="stylesheet" type="text/css" href="' . BASE . '/' . ADMIN_DIR . '/misc/css/manage' . ($this->direction ? '-' . $this->direction : null) . '.css" />';
		$data .= '<script type="text/javascript" src="' . BASE . '/' . ADMIN_DIR . '/misc/js/manage.js"></script>';

		return $data;
	}

	/**
	 * items list table
	 * @return string
	 */
	public function list_table() {
		$data = '';
		
		$message_success = [
			'add' => ['success', MANAGE_SENTENCE_4],
			'edit' => ['info', MANAGE_SENTENCE_5],
			'delete' => ['danger', MANAGE_SENTENCE_6]
		];

		foreach ($message_success as $type => $options)
			if (isset($_SESSION['_manage_' . $type . '_success'])) {
				$data .= '<div><div class="message message-half bg-' . $options[0] . '">' . $options[1] . '</div><div style="clear:both"></div></div>';
				unset($_SESSION['_manage_' . $type . '_success']);
				break;
			}

		$data .= '<form action="' . $this->manage_base . '/delete" method="post">';
		
		if ($this->manage_add === true)
			$add_button = '<a href="' . $this->manage_base . '/add" class="btn btn-success btn-sm btn-manage">' . ADD . '</a>';
		
		if ($this->manage_search !== false) {
			if (isset($_SESSION['_manage_search'][$this->manage_table])) {
				$query = htmlspecialchars($_SESSION['_manage_search'][$this->manage_table]['query']);
				$cancel_search = '<button name="cancel" class="btn btn-info btn-sm btn-manage"><i class="fa fa-times"></i></button>';
				$active = ' active';
			}

			$search_input = '<div id="manage_search">' . (isset($cancel_search) ? $cancel_search : null) . '<input name="search" type="text" value="' . (isset($query) ? $query : null) . '" class="form-control' . (isset($active) ? $active : null) . '" placeholder="' . SEARCH . '&hellip;"></div>';
		}
		
		if ($this->manage_deletes === true)
			$delete_selected_button = '<button id="delete_selected" name="delete_selected" type="submit" class="btn btn-danger btn-sm btn-manage">' . DELETE_SELECTED . '</button>';
		
		if (isset($add_button) || isset($search_input) || isset($delete_selected_button))
			$data .= '<div>' . (isset($add_button) ? $add_button : null) . (isset($search_input) ? $search_input : null) . (isset($delete_selected_button) ? $delete_selected_button : null) . '</div>';
		
		$data .= '<table class="table table-striped table-hover manage">
			<thead>
				<tr>';
		
		if ($this->manage_deletes === true)
			$data .= '<th><input id="check_all" type="checkbox"></th>';
		
		foreach ($this->manage_head as $label => $column) {
			if (is_array($this->manage_sort) && in_array($column, $this->manage_sort)) {
				$current_order = isset($_SESSION['_manage_sort'][$this->manage_table][$column]) ? $_SESSION['_manage_sort'][$this->manage_table][$column] : null;
				
				if (empty($current_order))
					$next_order = 'asc';
				else if ($current_order === 'asc')
					$next_order = 'desc';
				else
					$next_order = 'none';

				$label = '<a href="' . $this->manage_base . '/sort/' . $column . '/' . $next_order . '">' . $label . ' ' . ($current_order === 'asc' || $current_order === 'desc' ? '<i class="fa fa-sort-' . $current_order . ' manage-sort"></i>' : null) . '</a>';
			}

			$data .= '<th>' . $label . '</th>';
		}
		
		if ($this->manage_view === true || $this->manage_edit === true || $this->manage_delete === true)
			$data .= '<th></th>';
		
		$data .= '</tr>
			</thead>
			<tbody>';
		
		if (empty($this->manage_query))
			$this->manage_query = 'SELECT * FROM ' . DB_PRFX . $this->manage_table;

		if (isset($_SESSION['_manage_sort'][$this->manage_table]) && count($_SESSION['_manage_sort'][$this->manage_table]) > 0)
			$sorting = $_SESSION['_manage_sort'][$this->manage_table];
		else if (count($this->manage_order) > 0)
			$sorting = $this->manage_order;

		if (isset($sorting)) {
			foreach ($sorting as $column => $sort_type)
				$orders[] = $column . ' ' . $sort_type;
			
			$orders = ' ORDER BY ' . implode(', ', $orders);
		}
		
		$page = ceil(end($this->manage_q));

		if ($page < 1)
			$page = 1;

		$_SESSION['_manage_page'][$this->manage_table] = $page;

		$where_clause = null;

		if (isset($_SESSION['_manage_search'][$this->manage_table])) {
			$query = $_SESSION['_manage_search'][$this->manage_table]['query'];
			$has_where = strpos($this->manage_query, ' WHERE ') !== false ? true : false;

			$where_clause = $has_where ? ' AND (' : ' WHERE ';
			$where_clause .= $_SESSION['_manage_search'][$this->manage_table]['statements'];

			if ($has_where)
				$where_clause .= ')';
			
			$search_values = [];

			foreach ($this->manage_search as $column)
				$search_values = array_merge($search_values, [$query, '%' . $query, $query . '%', '%' . $query . '%']);
		}

		$limit = ' LIMIT ' . (($page - 1) * $this->manage_number) . ',' . $this->manage_number;
		
		$result = $this->db->prepare('SELECT COUNT(*) AS count FROM ' . DB_PRFX . $this->manage_table . $where_clause);
		$result->execute(isset($search_values) ? $search_values : []);
		
		$count = $result->fetchColumn();
		$pages = ceil($count / $this->manage_number);
		
		$page_items = '';
		if (isset($pages) && $pages > 1) {
			$page_items .= '<div class="manage-pagination text-center"><ul class="pagination">';
			
			$start = 1;

			if ($pages > 7)
				if ($pages - $page < 4)
					$start = $pages - 6;
				else if ($page > 4)
					$start = $page - 3;
			
			$end = $page < 4 ? 7 : $page + 3;
			
			if ($page > 4)
				$page_items .= '<li><a href="' . $this->manage_base . '/1"><i class="fa fa-angle-double-left"></i></a></li>
				<li><a href="' . $this->manage_base . '/' . ($page - 1) . '"><i class="fa fa-angle-left"></i></a></li>';

			for ($i = $start; $i <= $end; $i++) {
				if ($i > $pages)
					break;
				
				$page_items .= '<li' . ($i == $page ? ' class="active"' : null) . '><a href="' . $this->manage_base . '/' . $i . '">' . $i . '</a></li>';
			}

			if ($pages > 7 && $page < $pages && $pages - 4 >= $page) {
				$page_items .= '<li><a href="' . $this->manage_base . '/' . ($page + 1) . '"><i class="fa fa-angle-right"></i></a></li>';

				if ($pages - 1 !== $page)
					$page_items .= '<li><a href="' . $this->manage_base . '/' . $pages . '"><i class="fa fa-angle-double-right"></i></a></li>';
			}
			
			$page_items .= '</ul>
			<br>
			' . $count . ' ' . ITEMS_IN . ' ' . $pages . ' ' . strtolower(PAGES) . '
			</div>';
		}
		
		$result = $this->db->prepare($this->manage_query . (isset($where_clause) ? $where_clause : null) . (isset($orders) ? $orders : null) . $limit);
		$result->execute(isset($search_values) ? $search_values : []);
		
		if ($result->rowCount() > 0) {
			while ($item = $result->fetch(PDO::FETCH_ASSOC)) {
				$data .= '<tr>';
				
				if ($this->manage_deletes === true)
					$data .= '<td><input name="items[]" type="checkbox" value="' . $item[$this->manage_id_col] . '"></td>';
				
				foreach ($this->manage_head as $column) {
					$data .= '<td>';
					
					if (isset($this->manage_action->list->{$column}))
						$data .= $this->call_action('list', $column, (array) $item, [], $item[$this->manage_id_col]);
					else if (isset($item[$column]))
						$data .= $item[$column];

					$data .= '</td>';
				}
				
				if ($this->manage_view === true || $this->manage_edit === true || $this->manage_delete === true) {
					
					if ($this->manage_view === true)
						$view_button = '<a href="' . $this->manage_base . '/view/' . $item[$this->manage_id_col]  . '" class="btn btn-warning btn-xs btn-visible-hover btn-manage">' . VIEW . '</a>';

					if ($this->manage_edit === true)
						$edit_button = '<a href="' . $this->manage_base . '/edit/' . $item[$this->manage_id_col]  . '" class="btn btn-primary btn-xs btn-visible-hover btn-manage">' . EDIT . '</a>';
					
					if ($this->manage_delete === true)
						$delete_button = '<a href="' . $this->manage_base . '/delete/' . $item[$this->manage_id_col]  . '" class="btn btn-danger btn-xs btn-visible-hover btn-manage">' . DELETE . '</a>';
					
					
					$data .= '<td class="manage-buttons">' . (isset($view_button) ? $view_button : null) . (isset($edit_button) ? $edit_button : null) . (isset($delete_button) ? $delete_button : null) . '</td>';
				}
				
				$data .= '</tr>';
			}
		} else
			$data .= '<tr><td class="text-center" colspan="' . (count($this->manage_head) + 2) . '">' . MANAGE_SENTENCE_1 . '</td></tr>';
		
		$data .= '</tbody>
		</table>';
		
		if (empty($page_items) === false)
			$data .= $page_items;
		
		$data .= '</form>';

		return $data;
	}

	/**
	 * input form for add and edit modes
	 * @param  array   $post  post data
	 * @param  array   $files post files
	 * @param  integer $id    item id
	 * @return string
	 */
	public function input_form($post = [], $files = [], $id = null) {
		if (isset($post) && count($post) > 0) {
			foreach ($this->manage_fields as $column => $options)
				if (isset($post[$column]) || isset($files[$column])) {
					if (isset($options['required']) && $options['required'] === true && empty($post[$column]) && strlen($post[$column]) < 1)
						$message_required = true;
					else if (isset($options['image']) && $options['image'] === true && empty($files[$column]['name']) === false && in_array(strtolower(substr($files[$column]['name'], strrpos($files[$column]['name'], '.') + 1)), $this->manage_image_exts) === false) {
						$check = false;

						$message_check = MANAGE_SENTENCE_7;
						break;
					} else if (isset($this->manage_action->check->{$column})) {
						$check = $this->call_action('check', $column, $post, $files, $id);
						
						if ($check !== true) {
							$message_check = $check;
							$check = false;
							break;
						}
					}
				}
			
			if (isset($message_required) === false && (isset($check) === false || $check !== false)) {
				if (isset($post['add']) || isset($post['add_more']))
					$this->add($post, $files);
				else if (isset($post['edit']))
					$this->edit($post, $files, $id);
			}
		}
		
		if (count($this->manage_fields) > 0) {
			$form = load_lib('form');

			$data = $this->nav([
				$this->manage_title => ['icon' => $this->manage_icon, 'url' => $this->manage_base],
				empty($id) ? ADD : EDIT
			]);

			$data .= '<form class="form-horizontal" role="form" action="' . BASE . '/' . ADMIN_DIR . '/' . implode('/', $this->manage_q) . '" method="post" enctype="multipart/form-data">';
			
			if (($required_exists = isset($message_required)) === true || ($check_exists = isset($message_check)) === true) {
				$data .= '<div class="row">
				<div class="bg-danger message col-sm-7 col-sm-offset-1">';

				if ($required_exists)
					$data .= MANAGE_SENTENCE_2;
				else if ($check_exists)
					$data .= $message_check;

				$form->form_values = $post;
				
				$data .= '</div>
				</div>';
			}

			if (empty($id) === false) {
				if ($item = $this->get_item($id))
					$form->form_values = $item;
			}

			$manage_fields_no_help = [];

			foreach ($this->manage_fields as $name => $options) {
				if (isset($options['help']))
					unset($options['help']);

				$manage_fields_no_help[$name] = $options;
			}

			$form->form_options = $manage_fields_no_help;
			$fields = $form->fields();

			$i = 0;
			$field_names = array_keys($this->manage_fields);

			foreach ($fields as $label => $element) {
				$name = $field_names[$i];
				$options = $this->manage_fields[$name];

				if (isset($this->manage_action->field->{$name}))
					$element = $this->call_action('field', $name, @$item, [], @$id, $element);

				if (isset($options['type']) && $options['type'] === 'file' && isset($item) && empty($item[$name]) === false) {
					$file_extension = strtolower(substr($item[$name], strrpos($item[$name], '.') + 1));
					$file_value = '<a href="' . $this->manage_upload_base . '/' . $item[$name] . '" target="_blank">' . (in_array($file_extension, $this->manage_image_exts) ? '<img src="' . $this->manage_upload_base . '/' . $item[$name] . '" class="img-manage"><br>' : $item[$name]) . '</a>' . ($this->manage_delete_file === true ? '<label><input name="' . $name . '_delete" type="checkbox" value="1">' . DELETE . '</label>' : null);
				} else
					$file_value = '';

				$data .= '<div class="form-group">
					<label for="' . $name . '" class="col-sm-2 control-label">' . $label . (isset($options['required']) && $options['required'] === true ? ' <span class="text-red">*</span>' : null) . '</label>
					<div class="col-sm-6">
						' . $file_value . $element . '
						' . (isset($options['help']) ? '<p class="help-block">' . $options['help'] . '</p>' : null) . '
					</div>
				</div>';

				$i += 1;
			}

			$data .= '<div class="form-group">
						<div class="col-sm-offset-2 col-sm-10">';
			
			if (empty($id) === false)
				$data .= '<button name="edit" type="submit" class="btn btn-primary btn-sm btn-manage">' . EDIT . '</button>';
			else
				$data .= '<button name="add" type="submit" class="btn btn-success btn-sm btn-manage">' . ADD . '</button><button name="add_more" type="submit" class="btn btn-success btn-sm btn-manage">' . ADD_MORE . '</button>';
						  
			$data .= '<a href="' . $this->manage_base . '/' . @$_SESSION['_manage_page'][$this->manage_table] . '" class="btn btn-default btn-sm btn-manage">' . BACK . '</a>
					</div>
				</div>
			</form>';
			
			return $data;
		}
		
		return false;
	}

	/**
	 * database records to array
	 * @param  string $table
	 * @param  string $id_column
	 * @param  string $title_column
	 * @return array
	 */
	protected function table_to_array($table, $id_column, $title_column) {
		$table = str_replace(['\'', '"'], '', $table);

		$result = $this->db->query('SELECT * FROM ' . DB_PRFX . $table);
		$result->execute();

		while ($item = $result->fetch(PDO::FETCH_ASSOC))
			$items[$item[$id_column]] = $item[$title_column];

		return isset($items) ? $items : false;
	}

	/**
	 * add item to database and upload file if exists
	 * @param  array $post
	 * @param  array $files
	 * @return boolean
	 */
	private function add($post = [], $files = []) {
		if (isset($this->manage_action->add_value))
			foreach ((array) $this->manage_action->add_value as $column => $value) {
				$this->manage_fields[$column] = [];
				$post[$column] = $value;
			}

		foreach ($this->manage_fields as $column => $options)
			if (isset($post[$column]) || isset($files[$column])) {
				if (isset($options['type']) && $options['type'] == 'file' && $file_upload = $this->file_upload($files[$column]))
					$post[$column] = $file_upload;

				if (isset($this->manage_action->add->{$column})) {
					$post[$column] = $this->call_action('add', $column, $post, $files);
					
					if ($post[$column] === false)
						continue;
				} else if (is_array($post[$column]))
					$post[$column] = implode(',', $post[$column]);
				
				$columns[] = $column;
				$values[':' . $column] = $post[$column];
			}
		
		if (isset($values)) {
			$table = str_replace(['\'', '"'], '', $this->manage_table);
			$this->db->prepare('INSERT INTO ' . DB_PRFX . $this->manage_table . ' (' . implode(', ', $columns) . ') VALUES (' . implode(', ', array_keys($values)) . ')')->execute($values);

			if (isset($this->manage_action->add->function))
				$this->call_action('add', 'function', $post, $files, $this->db->lastInsertId());

			if (isset($_POST['add_more'])) {
				redirect($_SERVER['REQUEST_URI']);
			} else
				$_SESSION['_manage_add_success'] = true;
			
			redirect($this->manage_base . '/' . @$_SESSION['_manage_page'][$this->manage_table]);
			exit;
		}
		
		return false;
	}

	/**
	 * update existing database item
	 * @param  array   $post
	 * @param  array   $files
	 * @param  integer $id
	 * @return boolean
	 */
	private function edit($post, $files, $id) {
		if (isset($this->manage_action->edit_value))
			foreach ((array) $this->manage_action->edit_value as $column => $value) {
				$this->manage_fields[$column] = [];
				$post[$column] = $value;
			}

		$before_update = $this->get_item($id);
		
		foreach ($this->manage_fields as $column => $options)
			if (isset($post[$column]) || isset($files[$column])) {
				if (isset($options['type']) && $options['type'] == 'file')
					if (($file_upload = $this->file_upload($files[$column])) || ($this->manage_delete_file === true && isset($post[$column . '_delete']))) {
						if ($item = $before_update)
							if (empty($item[$column]) === false) {
								$old_file = $this->manage_upload_path . DS . $item[$column];

								if (file_exists($old_file))
									unlink($old_file);
							}

						$post[$column] = isset($post[$column . '_delete']) ? '' : $file_upload;
					} else
						continue;

				if (isset($this->manage_action->edit->{$column})) {
					$post[$column] = $this->call_action('edit', $column, $post, $files, $id, $before_update);
					
					if ($post[$column] === false)
						continue;
				} else if (is_array($post[$column]))
					$post[$column] = implode(',', $post[$column]);
				
				$columns[] = $column . ' = ?';
				$values[] = $post[$column];
			} else if (isset($options['type']) === false || $options['type'] === 'checkbox') {
				$columns[] = $column . ' = ?';
				$values[] = '';
			}
		
		if (isset($values)) {
			$values[] = $id;

			$table = str_replace(['\'', '"'], '', $this->manage_table);
			$id_column = str_replace(['\'', '"'], '', $this->manage_id_col);

			$this->db->prepare('UPDATE ' . DB_PRFX . $table . ' SET ' . implode(', ', $columns) . ' WHERE ' . $id_column . ' = ?')->execute($values);

			if (isset($this->manage_action->edit->function))
				$this->call_action('edit', 'function', $post, $files, $id, $before_update);
			
			$_SESSION['_manage_edit_success'] = true;
			
			redirect($this->manage_base . '/' . @$_SESSION['_manage_page'][$this->manage_table]);
		}
		
		return false;
	}

	/**
	 * delete item and uploaded file if exists
	 * @param  array  $ids
	 * @param  array  $post
	 * @return string
	 */
	private function delete($ids = [], $post = []) {
		if (is_array($ids) === false || count($ids) < 1)
			redirect($this->manage_base);

		if (isset($post['delete'])) {
			if (isset($this->manage_action->delete))
				if (empty($this->manage_action->delete) === false && method_exists($this, $this->manage_action->delete) && is_callable([$this, $this->manage_action->delete])) {
					$delete = $this->{$this->manage_action->delete}($ids);

					if (is_bool($delete) && $delete === false)
						redirect($this->manage_base);
					else if (is_array($delete))
						$ids = $delete;
				} else
					die('Limny error: Call undefined method <em>' . $this->manage_action->delete . '</em>.');

			$table = str_replace(['\'', '"'], '', $this->manage_table);
			$id_column = str_replace(['\'', '"'], '', $this->manage_id_col);

			foreach ($this->manage_fields as $column => $options)
				if (isset($options['type']) && $options['type'] == 'file')
					$file_columns[] = $column;

			foreach ($ids as $id) {
				if (isset($file_columns) && $item = $this->get_item($id))
					foreach ($file_columns as $file_column)
						if (file_exists($this->manage_upload_path . DS . $item[$file_column]))
							unlink($this->manage_upload_path . DS . $item[$file_column]);

				$this->db->prepare('DELETE FROM ' . DB_PRFX . $table . ' WHERE ' . $id_column . ' = ?')->execute([$id]);
			}
			
			$_SESSION['_manage_delete_success'] = true;
			
			redirect($this->manage_base . '/' . @$_SESSION['_manage_page'][$this->manage_table]);
		}
		
		$data = $this->nav([
			$this->manage_title => ['icon' => $this->manage_icon, 'url' => $this->manage_base],
			DELETE
		]);

		$data .= '<form action="' . $this->manage_base. '/delete' . (count($ids) === 1 ? '/' . current($ids) : null) . '" method="post">
	<p class="text-center">' . str_replace('{NUMBER}', count($ids), MANAGE_SENTENCE_3) . '<br>
		<br>';

		foreach ($ids as $id)
			$data .= '<input name="items[]" type="hidden" value="' . $id . '">';

		$data .= '<button name="delete" type="submit" class="btn btn-danger btn-sm">' . YES . '</button> &nbsp;
		<a href="' . $this->manage_base . '" class="btn btn-default btn-sm">' . NO . '</a>
	</p>
</form>';
		
		return $data;
	}

	/**
	 * page navigation
	 * @param  array  $items
	 * @return string
	 */
	protected function nav($items = []) {
		if (is_array($items) === false || count($items) < 1)
			return false;

		$i = 0;
		$data = '<ol class="breadcrumb">';

		foreach ($items as $title => $options) {
			$i += 1;
			$icon = null;

			if (is_numeric($title) && is_string($options))
				$title = $options;

			if (isset($options['icon']) && strlen($options['icon']) > 2)
				if (substr($options['icon'], 0, 3) === 'fa-')
					$icon = '<i class="fa ' . $options['icon'] . '"></i> ';
				else
					$icon = '<img src="' . $options['icon'] . '" /> ';

			if (isset($options['url']))
				$title = '<a href="' . $options['url'] . '">' . $title . '</a>';

			$data .= '<li' . ($i == count($items) ? ' class="actives"' : null) . '>' . $icon . $title . '</li>';
		}

		$data .= '</ol>';

		return $data;
	}

	/**
	 * set search query statement to session
	 * @param  string $query
	 * @param  array  $post
	 * @return boolean
	 */
	private function search($query, $post = []) {
		if (is_array($this->manage_search) === false || count($this->manage_search) < 1)
			return false;

		if (isset($post['cancel']) && isset($_SESSION['_manage_search'][$this->manage_table])) {
			unset($_SESSION['_manage_search'][$this->manage_table]);

			redirect($this->manage_base);
		}

		foreach ($this->manage_search as $column) {
			$column = str_replace(['\'', '"'], '', $column);

			$statements[] = $column . ' = ? OR ' . $column . ' LIKE ? OR ' . $column . ' LIKE ? OR ' . $column . ' LIKE ?';
		}

		$_SESSION['_manage_search'][$this->manage_table] = [
			'query' => $query,
			'statements' => implode(' OR ', $statements)
		];

		redirect($this->manage_base);

		return true;
	}

	/**
	 * set column sorting to session
	 * @param  string $column
	 * @param  string $order
	 * @return boolean
	 */
	private function sort($column, $order = 'asc') {
		$order = strtolower($order);

		if (empty($column) || empty($order) || in_array($order, ['asc', 'desc', 'none']) === false || in_array($column, $this->manage_head) === false){
			redirect($this->manage_base . '/' . @$_SESSION['_manage_page'][$this->manage_table]);

			return false;
		}

		if ($order === 'none' && isset($_SESSION['_manage_sort'][$this->manage_table][$column]))
			unset($_SESSION['_manage_sort'][$this->manage_table][$column]);
		else
			$_SESSION['_manage_sort'][$this->manage_table][$column] = $order;

		redirect($this->manage_base);
		
		return true;
	}

	/**
	 * item in view mode
	 * @param  integer $id
	 * @return string/boolean
	 */
	public function view($id = null) {
		if (count($this->manage_fields) > 0) {
			$data = $this->nav([
				$this->manage_title => ['icon' => $this->manage_icon, 'url' => $this->manage_base],
				VIEW
			]);
			
			if (empty($id) === false) {
				$table = str_replace(['\'', '"'], '', $this->manage_table);
				$id_column = str_replace(['\'', '"'], '', $this->manage_id_col);

				$result = $this->db->prepare('SELECT * FROM ' . DB_PRFX . $table . ' WHERE ' . $id_column . ' = ?');
				$result->execute([$id]);

				if ($item = $result->fetch(PDO::FETCH_ASSOC)) {
					$data .= '<form class="form-horizontal" role="form">';

					$fields = isset($this->manage_fields_view) && is_array($this->manage_fields_view) ? $this->manage_fields_view : $this->manage_fields;

					foreach ($fields as $column => $options) {
						if (is_string($options))
							$options = ['label' => $options];

						if (isset($options['type']) && $options['type'] === 'file' && empty($item[$column]) === false) {
							$file_extension = strtolower(substr($item[$column], strrpos($item[$column], '.') + 1));
							$item[$column] = '<a href="' . $this->manage_upload_base . '/' . $item[$column] . '" target="_blank">' . (in_array($file_extension, $this->manage_image_exts) ? '<img src="' . $this->manage_upload_base . '/' . $item[$column] . '" class="img-manage"><br>' : $item[$column]) . '</a>';
						}

						if (isset($this->manage_action->view->{$column}))
							$item[$column] = $this->call_action('view', $column, $item, [], $id);
						
						if (empty($item[$column]))
							$item[$column] = '<em class="text-gray">-</em>';

						$data .= '<div class="form-group">
							<label for="' . $column . '" class="col-sm-2 control-label">' . $options['label'] . '</label>
							<div class="col-sm-6">
								<p class="form-control-static">' . $item[$column] . '</p>
							</div>
						</div>';
					}

					$data .= '<div class="form-group">
							<div class="col-sm-offset-2 col-sm-10">';
					
					if ($this->manage_edit === true)
						$data .= '<a href="' . $this->manage_base . '/edit/' . $id . '" class="btn btn-primary btn-sm btn-manage">' . EDIT . '</a>';
					
					$data .= '<a href="' . $this->manage_base . '/delete/' . $id . '" class="btn btn-danger btn-sm btn-manage">' . DELETE . '</a><a href="' . $this->manage_base . '/' . @$_SESSION['_manage_page'][$this->manage_table] . '" class="btn btn-default btn-sm btn-manage">' . BACK . '</a>
							</div>
						</div>
					</form>';

					if (isset($this->manage_action->view->function))
						$this->call_action('view', 'function', [], [], $id);

					return $data;
				}
			}
		}

		return false;
	}

	/**
	 * call action function or method
	 * @param  string  $action         action type
	 * @param  string  $column
	 * @param  array   $post
	 * @param  array   $files
	 * @param  integer $id
	 * @param  *       $optional_value
	 * @return string
	 */
	private function call_action($action, $column, $post = [], $files = [], $id = null, $optional_value = null) {
		if (isset($post[$column]))
			$data[$column] = $post[$column];
		else if (isset($files[$column]))
			$data[$column] = $files[$column];

		if (isset($data) === false)
			$data[$column] = null;

		if (method_exists($this, $this->manage_action->{$action}->{$column}) && is_callable([$this, $this->manage_action->{$action}->{$column}]))
			$data[$column] = $this->{$this->manage_action->{$action}->{$column}}($data[$column], $post, $files, $id, $optional_value);
		else if (function_exists($this->manage_action->{$action}->{$column}))
			$data[$column] = call_user_func($this->manage_action->{$action}->{$column}, $data[$column]);
		else
			die('Limny error: Call undefined function or method <em>' . $this->manage_action->{$action}->{$column} . '</em>.');

		return $data[$column];
	}

	/**
	 * upload file
	 * @param  array          $file
	 * @return stirng/boolean
	 */
	private function file_upload($file) {
		if ($this->manage_upload_path === false || file_exists($this->manage_upload_path) === false || is_dir($this->manage_upload_path) === false)
			return false;

		if (empty($file['error']) === false && $file['size'] < 1)
			return false;

		$file_name = $file['name'];
		$file_name_new = $file_name;

		if (($position = strpos($file_name, '.')) !== false) {
			$file_base = substr($file_name, 0, $position);
			$file_extension = substr($file_name, $position);
		} else {
			$file_base = $file_name;
			$file_extension = '';
		}

		$i = 1;
		while (file_exists($this->manage_upload_path . DS . $file_name_new)) {
			$file_name_new = $file_base . ' (' . $i . ')' . $file_extension;

			$i += 1;
		}
		
		if (move_uploaded_file($file['tmp_name'], $this->manage_upload_path . DS . $file_name_new))
			return $file_name_new;
		
		return false;
	}

	/**
	 * get item by id
	 * @param  integer              $id
	 * @param  string               $column
	 * @param  string               $table
	 * @return array/string/boolean
	 */
	protected function get_item($id, $column = null, $table = null) {
		if (empty($table))
			$table = $this->manage_table;

		$table = str_replace(['\'', '"'], '', $table);
		$id_column = str_replace(['\'', '"'], '', $this->manage_id_col);

		$result = $this->db->prepare('SELECT * FROM ' . DB_PRFX . $table . ' WHERE ' . $id_column . ' = ?');
		$result->execute([$id]);

		if ($item = $result->fetch(PDO::FETCH_ASSOC))
			return empty($column) ? $item : $item[$column];

		return false;
	}
}

?>