<?php

load_lib('manage', false, true);
$user = load_lib('user', true, true, ['q' => $admin->q]);

$admin->title = USERS;
$admin->content = $user->manage();

?>