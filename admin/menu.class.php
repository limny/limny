<?php

class Menu extends Manage {
	public $manage_title = MENU;
	public $manage_icon = 'fa-navicon';

	public $manage_table = 'menu';

	public $manage_head = [
		USERNAME => 'name',
		STATUS => 'enabled',
		ORDER => 'sort'
	];
	public $manage_number = 999;

	public $manage_order = ['sort' => 'ASC'];

	public $manage_fields = [
		'name' => [
			'label' => NAME,
			'type' => 'text',
			'required' => true
		],
		'tooltip' => [
			'label' => TOOLTIP,
			'type' => 'text'
		],
		'address' => [
			'label' => ADDRESS,
			'type' => 'text',
			'help' => SENTENCE_36
		],
		'target' => [
			'label' => NEW_WINDOW,
			'type' => 'radio',
			'items' => ['_self' => NO, '_blank' => YES],
			'required' => true
		],
		'sort' => [
			'label' => ORDER,
			'type' => 'number',
			'required' => true
		],
		'enabled' => [
			'label' => STATUS,
			'type' => 'radio',
			'items' => ['1' => ENABLE, '0' => DISABLE],
			'required' => true
		],
	];
	
	public function Menu($parameters = []) {
		parent::__construct($parameters);

		$this->manage_action->list->name = 'menu_name';
		$this->manage_action->list->sort = 'menu_sort';
		$this->manage_action->list->enabled = 'menu_status';
	}

	private function menu_items() {
		global $db;

		$result = $db->query('SELECT id, sort FROM ' . DB_PRFX . 'menu ORDER BY sort ASC');
		while ($menu = $result->fetch(PDO::FETCH_ASSOC))
			$items[$menu['id']] = $menu;

		return isset($items) ? $items : [];
	}

	public function menu_name($name, $data) {
		if (empty($data['address']) === false)
			$name .= ' <a href="' . $data['address'] . '" target="_blank"><i class="fa fa-location-arrow"></i></a>';

		return $name;
	}

	public function menu_status($enabled) {
		if (empty($enabled))
			return '<i class="fa fa-times text-red"></i>';

		return '<i class="fa fa-check text-green"></i>';
	}

	public function menu_sort($order, $data, $files, $id) {
		$items = $this->menu_items();
		$orders = array_keys($items);
		$key = array_search($id, $orders);

		if ($key < count($orders) - 1)
			$icons[] = '<a href="' . BASE . '/' . ADMIN_DIR . '/menu/sort/' . $id . '/down"><i class="fa fa-lg fa-caret-square-o-down"></i></a>';
		else
			$icons[] = '<i class="fa fa-fw"></i>';

		if ($key > 0)
			$icons[] = '<a href="' . BASE . '/' . ADMIN_DIR . '/menu/sort/' . $id . '/up"><i class="fa fa-lg fa-caret-square-o-up"></i></a>';
		else
			$icons[] = '<i class="fa fa-fw"></i>';

		return implode(' ', $icons);
	}

	public function menu_sort_set($id, $place) {
		global $db;

		if (in_array($place, ['up', 'down']) === false)
			return false;

		foreach ($this->menu_items() as $item_id => $item_array)
			$items[$item_id] = $item_array['sort'];

		if (isset($items[$id]) === false)
			return false;

		if ($place == 'up')
			$new_order = (string) $items[$id] - 1;
		else if ($place == 'down')
			$new_order = (string) $items[$id] + 1;

		$db->prepare('UPDATE ' . DB_PRFX . 'menu SET sort = ? WHERE sort = ?')->execute([$items[$id], $new_order]);

		$db->prepare('UPDATE ' . DB_PRFX . 'menu SET sort = ? WHERE id = ?')->execute([$new_order, $id]);

		redirect(BASE . '/' . ADMIN_DIR . '/menu');

		return true;
	}
}

?>