<?php

load_lib('manage', false, true);
$menu = load_lib('menu', true, true, ['q' => $admin->q]);

$admin->title = MENU;

if (isset($admin->q[1]) && $admin->q[1] == 'sort')
	$menu->menu_sort_set(@$admin->q[2], @$admin->q[3]);

$admin->content = $menu->manage();

?>