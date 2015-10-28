<?php

class Role extends Manage {
	public $manage_title = ROLES;
	public $manage_icon = 'fa-users';

	public $manage_table = 'roles';

	public $manage_head = [NAME => 'name'];

	public $manage_view = true;

	public $manage_order = ['id' => 'ASC'];

	public $manage_fields = [
		'name' => [
			'label' => NAME,
			'type' => 'text',
			'required' => true
		],
		'permissions' => [
			'label' => PERMISSIONS
		]
	];
	
	public $manage_fields_view = [
		'id' => ID,
		'name' => NAME,
		'permissions' => PERMISSIONS
	];
	
	public function Role($registry, $parameters = []) {
		parent::__construct($registry, $parameters);

		$this->manage_action->list->name = 'role_name';

		$this->manage_action->field->permissions = 'role_permissions_input';
		$this->manage_action->view->permissions = 'role_permissions_view';
		
		$this->manage_action->delete = 'do_not_delete_admin';
	}

	public function role_permissions_input($permissions, $data = [], $files = [], $id = null) {
		global $admin;

		$permissions = trim($permissions);
		$permissions = explode(',', $permissions);

		$granted = in_array('all', $permissions) ? true : false;

		$permission = load_lib('permission', true, true);

		$data = '<ul class="permissions">';

		foreach ($permission->permissions() as $permission_id => $permission_object) {
			$permission_category = $this->permission_category($permission_id);
			
			$is_admin = $permission_category == ADMIN ? true : false;
			$permission_name = $this->permission_name($permission_object, $is_admin);

			$data .= '<li class="parent"><label><input name="permissions[]" type="checkbox" value="' . $permission_id . '"' . (in_array($permission_id, $permissions) || $granted ? ' checked' : null) . ($id == '1' ? ' disabled' : null) . '> ' . $permission_category . ' : ' . $permission_name . '</label></li>';

			foreach ($permission->permissions($permission_id) as $permission_id => $permission_object)
				$data .= '<li class="child"><label><input name="permissions[]" type="checkbox" value="' . $permission_id . '"' . (in_array($permission_id, $permissions) || $granted ? ' checked' : null) . ($id == '1' ? ' disabled' : null) . '> ' . $this->permission_name($permission_object, $is_admin) . '</label></li>';
		}

		$data .= '</ul>';

		return $data;
	}

	public function do_not_delete_admin($ids = []) {
		if (in_array('1', $ids))
			return false;

		return true;
	}

	public function role_permissions_view($permissions) {
		$permissions = trim($permissions);
		$permissions = explode(',', $permissions);

		$granted = in_array('all', $permissions) ? true : false;

		$permission = load_lib('permission', true, true);

		$data = '<ul class="permissions">';

		foreach ($permission->permissions() as $permission_id => $permission_object) {
			$permission_category = $this->permission_category($permission_id);
			
			$is_admin = $permission_category == ADMIN ? true : false;
			$permission_name = $this->permission_name($permission_object, $is_admin);

			$data .= '<li class="parent"><label><i class="fa ' . (in_array($permission_id, $permissions) || $granted ? 'fa-check text-green' : 'fa-times text-red') . '"></i> ' . $permission_category . ' : ' . $permission_name . '</label></li>';

			foreach ($permission->permissions($permission_id) as $permission_id => $permission_object)
				$data .= '<li class="child"><label><i class="fa ' . (in_array($permission_id, $permissions) || $granted ? 'fa-check text-green' : 'fa-times text-red') . '"></i> ' . $this->permission_name($permission_object, $is_admin) . '</label></li>';
		}

		$data .= '</ul>';

		return $data;
	}

	public function role_name($name, $data = [], $files = [], $id = null) {
		$result = $this->db->prepare('SELECT COUNT(*) AS count FROM ' . DB_PRFX . 'users WHERE FIND_IN_SET(?, roles) > 0');
		$result->execute([$id]);

		return $name . ' <span class="text-gray">(' . $result->fetchColumn() . ')</span>';
	}

	private function permission_name($permission_item, $is_admin) {
		if ($is_admin === true)
			return constant($permission_item['name']);

		global $admin;

		if (ctype_upper(str_replace('_', '', $permission_item['name']))) {
			$app_name = $permission_item['query'];

			if (($slash_pos = strpos($app_name, '/')) !== false)
				$app_name = substr($app_name, 0, $slash_pos);

			$admin->app_load_language($app_name);

			if (defined($permission_item['name']))
				return constant($permission_item['name']);
		}

		return $permission_item['name'];
	}

	private function permission_category($permission_id) {
		if ($permission_id > 23)
			return APP;

		return $category_label = ADMIN;
	}
}

?>