<?php

$admin->title = PROFILE;

$form = load_lib('form');

$divider = '<hr>';

$form->form_options = [
	'nick_name' => ['label' => NICK_NAME, 'type' => 'text'],
	'first_name' => ['label' => FIRST_NAME, 'type' => 'text'],
	'last_name' => ['label' => LAST_NAME, 'type' => 'text'],
	$divider,
	'password' => ['label' => PASSWORD, 'type' => 'password'],
	'new_password' => ['label' => NEW_PASSWORD, 'type' => 'password'],
	'repeat_new_password' => ['label' => REPEAT_NEW_PASSWORD, 'type' => 'password'],
];

if (isset($_POST['update'])) {
	$fields = array_keys(array_slice($form->form_options, 0, 3));

	foreach ($fields as $field)
		if (is_string($field) && isset($_POST[$field])) {
			$columns[] = $field . ' = ?';
			$values[] = $_POST[$field];
		}

	if (isset($columns) && isset($values)) {
		require_once PATH . DS . 'incs' . DS . 'passwordhash.class.php';
		$password_hash = new PasswordHash(8, false);

		$password = @$_POST['password'];
		$new_password = @$_POST['new_password'];
		$repeat_new_password = @$_POST['repeat_new_password'];

		if ($password_hash->CheckPassword($password, $_SESSION['limny']['admin']['password']) === true) {
			if (empty($new_password) === false || empty($repeat_new_password) === false) {
				if ($new_password === $repeat_new_password) {
					$new_password = $password_hash->HashPassword($new_password);

					$admin->db->prepare('UPDATE ' . DB_PRFX . 'users SET password = ? WHERE id = ?')->execute([$new_password, $_SESSION['limny']['admin']['id']]);

					$_SESSION['limny']['admin']['password'] = $new_password;
				} else
					redirect(BASE . '/' . ADMIN_DIR . '/profile/notmatch');
			}
				

			$values[] = $_SESSION['limny']['admin']['id'];

			$admin->db->prepare('UPDATE ' . DB_PRFX . 'profiles SET ' . implode(', ', $columns) . ' WHERE id = ?')->execute($values);

			redirect(BASE . '/' . ADMIN_DIR . '/profile/success');
		}

		redirect(BASE . '/' . ADMIN_DIR . '/profile/error');
	}
}

$result = $admin->db->prepare('SELECT * FROM ' . DB_PRFX . 'profiles WHERE user = ?');
$result->execute([$_SESSION['limny']['admin']['id']]);
$profile = $result->fetch(PDO::FETCH_ASSOC);

if (empty($profile) === false)
	$form->form_values = $profile;

$form_items = $form->fields();

if (count($form_items) > 0)
	foreach ($form_items as $label => $element) {
		if ($label == PASSWORD)
			$label .= ' <span class="text-red">*</span>';
		else if ($label == NEW_PASSWORD)
			$help = SENTENCE_21;
		else
			$help = '';

		$data[] = '<div class="form-group">
	<label class="col-sm-2 control-label">' . $label . '</label>
	<div class="col-sm-6">
		' . $element . '
		' . (empty($help) ? null : '<span class="help-block">' . $help . '</span>') . '
	</div>
</div>';
}

$messages = [
	'notmatch' => ['warning', SENTENCE_28],
	'error' => ['danger', SENTENCE_29],
	'success' => ['info', SENTENCE_27]
];

if (isset($admin->q[1]) && in_array($admin->q[1], array_keys($messages)))
	$message = $messages[$admin->q[1]];

$admin->content = '<form class="form-horizontal" role="form" method="post" action="' . BASE . '/' . ADMIN_DIR . '/profile">
' . (isset($message) ? '<div class="form-group">
	<div class="col-sm-offset-1 col-sm-7">
		<p class="message bg-' . $message[0] . '">' . $message[1] . '</p>
	</div>
</div>' : null) .
implode($data) . '
<div class="form-group">
	<div class="col-sm-offset-2 col-sm-10">
		<button name="update" type="submit" class="btn btn-primary">' . UPDATE . '</button>
	</div>
</div>
</form>';

?>