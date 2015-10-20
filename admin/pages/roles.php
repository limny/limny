<?php

load_lib('manage', false, true);
$role = load_lib('role', true, true, ['q' => $admin->q]);

$admin->title = ROLES;
$admin->content = $role->manage();

?>