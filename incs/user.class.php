<?php

class User extends Form {
	public $title;
	public $content;

	public function __construct() {
		if ($this->signed_in()) {
			global $db;

			$db->prepare('UPDATE ' . DB_PRFX . 'users SET last_activity = UNIX_TIMESTAMP() WHERE id = ?')->execute([$_SESSION['limny']['user']['id']]);
		}

		return true;
	}

	public function signed_in() {
		if (isset($_SESSION['limny']['user']))
			return true;

		return false;
	}

	public function user_block() {
		global $q, $config;

		if (isset($q['param'][1]) && $q['param'][0] == 'user' && $q['param'][1] == 'signin')
			return false;

		$data = '<div class="limny user-signin-block">';

		if ($this->signed_in()) {
			$data .= '<ul>';
			$data .= '<li><a href="' . url('user/profile') . '">' . PROFILE . '</a></li>';
			$data .= '<li><a href="' . url('user/signout') . '">' . SIGN_OUT . '</a></li>';
			$data .= '</ul>';
			$data .= '</div>';

			return ['title' => $_SESSION['limny']['user']['username'], 'content' => $data];
		}

		$required = ' <span class="text-red">*</span>';

		$this->form_options = [
			'limny_user' => ['label' => USERNAME . $required, 'type' => 'text'],
			'limny_pass' => ['label' => PASSWORD . $required, 'type' => 'password']
		];

		$this->form_values = @$_POST;

		if (empty($config->config->user_registration) === false)
			$user_registration = '<a href="' . BASE . '/user/signup">' . SIGN_UP . '</a><br>';
		else
			$user_registration = null;

		$button = '<div class="text-center">
			<label class="remember"><input name="remember" type="checkbox" value="1"> ' .REMEMBER_ME . '</label><br>
			' . $this->button('signin', SIGN_IN, ['type' => 'submit', 'class' => 'btn btn-primary']) . '<br>
			' . $user_registration . '
			<a href="' . url('user/forgotpassword') . '">' . FORGOT_PASSWORD . '</a>
		</div>';

		$data .= $this->make('post', url('user/signin'), null, $button);
		$data .= '</div>';

		return ['title' => SIGN_IN, 'content' => $data];
	}

	public function page_signin() {
		global $q, $config;

		if (isset($q['param'][2]))
			return false;

		if ($this->signed_in())
			redirect(BASE);

		if (isset($_POST['signin']) && isset($_POST['limny_user']) && isset($_POST['limny_pass']))
			if ($signin = $this->signin($_POST['limny_user'], $_POST['limny_pass'], isset($_POST['limny_remember'])))
				if ($signin === true)
					redirect(url('user'));
				else
					$message = $signin;

		$required = ' <span class="text-red">*</span>';

		$this->form_options = [
			'limny_user' => ['label' => USERNAME . $required, 'type' => 'text'],
			'limny_pass' => ['label' => PASSWORD . $required, 'type' => 'password']
		];

		$this->form_values = @$_POST;

		if (empty($config->config->user_registration) === false)
			$user_registration = '<a href="' . BASE . '/user/signup">' . SIGN_UP . '</a><br>';
		else
			$user_registration = null;

		$buttons = '<div>
			<label class="remember"><input name="limny_remember" type="checkbox" value="1"> ' .REMEMBER_ME . '</label><br>
			' . $this->button('signin', SIGN_IN, ['type' => 'submit', 'class' => 'btn btn-primary']) . '<br>
			' . $user_registration . '
			<a href="' . url('user/forgotpassword') . '">' . FORGOT_PASSWORD . '</a>
		</div>';

		$data = '<h1>' . SIGN_IN . '</h1>';

		if (isset($message))
			$data .= '<div class="message bg-' . $message[0] . '">' . $message[1] . '</div>';

		$data .= '<div class="limny section-half">';
		$data .= $this->make('post', url('user/signin'), null, $buttons);
		$data .= '</div>';

		$this->title = SIGN_IN;
		$this->content = $data;

		return true;
	}

	public function page_default() {
		redirect(BASE . '/');

		return true;
	}

	private function signin($username, $password, $remember = false) {
		if (empty($username) || empty($password))
			return ['warning', SENTENCE_4];

		global $db;

		$result = $db->prepare('SELECT * FROM ' . DB_PRFX . 'users WHERE username = ?');
		$result->execute([$username]);

		if ($user = $result->fetch(PDO::FETCH_ASSOC)) {
			if (empty($user['enabled']))
				return ['danger', SENTENCE_6];

			require_once PATH . DS . 'incs' . DS . 'passwordhash.class.php';
			$password_hash = new PasswordHash(8, false);

			if ($password_hash->CheckPassword($password, $user['password']) === true) {
				$_SESSION['limny']['user'] = $user;

				if (empty($remember) === false) {
					$hash = md5(uniqid(rand(), true));

					$db->prepare('UPDATE ' . DB_PRFX . 'users SET ip = INET_ATON(?), hash = ?, last_login = UNIX_TIMESTAMP(), last_activity = UNIX_TIMESTAMP() WHERE id = ?')->execute([$_SERVER['REMOTE_ADDR'], $hash, $user['id']]);

					setcookie('limny_user', $hash, time() + 2592000, '/');
				} else {
					$db->prepare('UPDATE ' . DB_PRFX . 'users SET ip = INET_ATON(?), hash = NULL, last_login = UNIX_TIMESTAMP(), last_activity = UNIX_TIMESTAMP() WHERE id = ?')->execute([$_SERVER['REMOTE_ADDR'], $user['id']]);
				}

				return true;
			}
		}

		return ['danger', SENTENCE_5];
	}

	public function page_signout() {
		if ($this->signed_in())
			unset($_SESSION['limny']['user']);

		redirect(BASE);
	}

	public function page_profile() {
		if ($this->signed_in() === false)
			redirect(BASE);

		global $db;

		if (isset($_POST['update']))
			if ($profile_update = $this->profile_update($_POST))
				if ($profile_update === true)
					redirect(url('user/profile'));
				else
					$message = $profile_update;

		$divider = '<hr>';

		$this->form_options = [
			'username' => ['label' => USERNAME, 'type' => 'text', 'disabled' => ''],
			'email' => ['label' => EMAIL, 'type' => 'text', 'disabled' => ''],
			$divider,
			'nick_name' => ['label' => NICK_NAME, 'type' => 'text'],
			'first_name' => ['label' => FIRST_NAME, 'type' => 'text'],
			'last_name' => ['label' => LAST_NAME, 'type' => 'text'],
			$divider,
			'password' => ['label' => PASSWORD . ' <span class="text-red">*</span>', 'type' => 'password'],
			'new_password' => ['label' => NEW_PASSWORD, 'type' => 'password'],
			'repeat_new_password' => ['label' => REPEAT_NEW_PASSWORD, 'type' => 'password'],
		];

		$result = $db->prepare('SELECT nick_name, first_name, last_name FROM ' . DB_PRFX . 'profiles WHERE user = ?');
		$result->execute([$_SESSION['limny']['user']['id']]);
		$profile = $result->fetch(PDO::FETCH_ASSOC);

		$this->form_values = [
			'username' => $_SESSION['limny']['user']['username'],
			'email' => $_SESSION['limny']['user']['email'],
			'nick_name' => $profile['nick_name'],
			'first_name' => $profile['first_name'],
			'last_name' => $profile['last_name']
		];

		$button = '<div class="col-sm-3"></div><div class="col-sm-9">' . $this->button('update', UPDATE, ['type' => 'submit', 'class' => 'btn btn-primary']) . '</div>';

		$data = '<h1>' . PROFILE . '</h1>';

		if (isset($message))
			$data .= '<div class="message bg-' . $message[0] . '">' . $message[1] . '</div>';

		$data .= '<div class="limny user-profile">';
		$data .= $this->make('post', url('user/profile'), ['form' => 'form-horizontal', 'label' => 'col-sm-3', 'element' => 'col-sm-9'], $button);
		$data .= '</div>';

		$password_input = '<input name="new_password" type="password" value="" class="form-control">';
		$data = substring($data, null, $password_input) . $password_input . '<p class="help-block">' . SENTENCE_29 . '</p>' . substring($data, $password_input, null);

		$this->title = PROFILE;
		$this->content = $data;

		return true;
	}

	private function profile_update($post) {
		global $db;

		$nick_name = @$post['nick_name'];
		$first_name = @$post['first_name'];
		$last_name = @$post['last_name'];

		$password = @$post['password'];
		$new_password = @$post['new_password'];
		$repeat_new_password = @$post['repeat_new_password'];

		require_once PATH . DS . 'incs' . DS . 'passwordhash.class.php';
		$password_hash = new PasswordHash(8, false);

		if ($password_hash->CheckPassword($password, $_SESSION['limny']['user']['password']) === true) {
			if (empty($new_password) === false || empty($repeat_new_password) === false) {
				if ($new_password === $repeat_new_password) {
					$new_password = $password_hash->HashPassword($new_password);

					$db->prepare('UPDATE ' . DB_PRFX . 'users SET password = ? WHERE id = ?')->execute([$new_password, $_SESSION['limny']['user']['id']]);

					$_SESSION['limny']['user']['password'] = $new_password;
				} else
					return ['danger', SENTENCE_8];
			}
				

			$db->prepare('UPDATE ' . DB_PRFX . 'profiles SET nick_name = ?, first_name = ?, last_name = ? WHERE id = ?')->execute([$nick_name, $first_name, $last_name, $_SESSION['limny']['user']['id']]);

			return ['success', SENTENCE_7];
		}

		return ['danger', SENTENCE_9];
	}

	public function page_signup() {
		$signed_in = $this->signed_in();
		
		global $q, $config;

		$this->title = SIGN_UP;
		$this->content = '<h1>' . SIGN_UP . '</h1>';

		if (empty($config->config->user_registration)) {
			$this->content .= '<div class="message bg-warning">' . SENTENCE_18 . '</div><br>
			<div class="text-center"><a href="' . BASE . '" class="btn btn-info">' . MAIN_PAGE . '</a></div>';

			return true;
		}

		if (isset($q['param'][2])) {
			if (in_array($q['param'][2], ['done', 'sent'])) {
				$message = $q['param'][2] == 'done' ? SENTENCE_10 : SENTENCE_20;

				$this->content .= '<div class="message bg-success stable">' . $message . '</div><br>
				<div class="text-center"><a href="' . BASE . '" class="btn btn-info">' . MAIN_PAGE . '</a></div>';

				return true;
			}

			if ($signed_in)
				redirect(BASE);

			global $db;

			$code = $q['param'][2];

			$this->delete_expired_codes();

			$result = $db->prepare('SELECT email FROM ' . DB_PRFX . 'codes WHERE type = ? AND code = ?');
			$result->execute(['signup', $code]);
			$signup_request = $result->fetch(PDO::FETCH_ASSOC);

			if ($signup_request === false)
				return false;
		}

		if ($signed_in)
			redirect(BASE);

		$email_confirmation = $config->config->email_confirmation;

		$required = ' <span class="text-red">*</span>';

		if (empty($email_confirmation) || isset($signup_request)) {

			if (isset($_POST['signup']))
				if ($signup = $this->signup($_POST, isset($signup_request['email']) ? $signup_request['email'] : null))
					if ($signup === true)
						redirect(url('user/signup/done'));
					else
						$message = $signup;

			$this->form_options = [
				'username' => ['label' => USERNAME . $required, 'type' => 'text'],
				'email' => ['label' => EMAIL . $required, 'type' => 'text'],
				'<hr>',
				'password' => ['label' => PASSWORD . $required, 'type' => 'password'],
				'repeat_password' => ['label' => REPEAT_PASSWORD . $required, 'type' => 'password'],
			];

			$this->form_values = @$_POST;

			if (isset($signup_request)) {
				$this->form_options['email']['disabled'] = 'disabled';
				$this->form_values['email'] = $signup_request['email'];	
			}
		} else {

			if (isset($_POST['signup']) && isset($_POST['email']))
				if ($email_confirmation = $this->email_confirmation($_POST['email']))
					if ($email_confirmation === true)
						redirect(url('user/signup/sent'));
					else
						$message = $email_confirmation;

			$this->form_options = [
				'email' => ['label' => EMAIL . $required, 'type' => 'text'],
			];

			$this->form_values = @$_POST;
		}

		$button = '<div class="col-sm-3"></div><div class="col-sm-9">' . $this->button('signup', SIGN_UP, ['type' => 'submit', 'class' => 'btn btn-success']) . '</div>';

		if (isset($message))
			$this->content .= '<div class="message bg-' . $message[0] . '">' . $message[1] . '</div>';

		$this->content .= '<div class="limny user-signup">';
		$this->content .= $this->make('post', url('user/signup' . (isset($code) ? '/' . $code : null)), ['form' => 'form-horizontal', 'label' => 'col-sm-3', 'element' => 'col-sm-9'], $button);
		$this->content .= '</div>';

		return true;
	}

	private function signup($post, $signup_request_email = null) {
		$username = @$post['username'];
		$email = empty($signup_request_email) ? @$post['email'] : $signup_request_email;
		$password = @$post['password'];
		$repeat_password = @$post['repeat_password'];

		if (empty($username) || empty($email) || empty($password) || empty($repeat_password))
			return ['warning', SENTENCE_11];

		if (preg_match('/^([a-z0-9])(([-a-z0-9._])*([a-z0-9]))([a-z0-9])*$/i' , $username) < 1)
			return ['danger', SENTENCE_12];

		if (filter_var($email, FILTER_VALIDATE_EMAIL) === false)
			return ['danger', SENTENCE_13];

		if (strlen($password) < 6)
			return ['danger', SENTENCE_16];

		if ($password !== $repeat_password)
			return ['danger', SENTENCE_17];

		global $db;

		$result = $db->prepare('SELECT username, email FROM ' . DB_PRFX . 'users WHERE username = ? OR email = ?');
		$result->execute([$username, $email]);

		while ($user = $result->fetch(PDO::FETCH_ASSOC)) {
			if (strcasecmp($username, $user['username']) === 0)
				return ['warning', SENTENCE_14];
			else if (strcasecmp($email, $user['email']) === 0)
				return ['warning', SENTENCE_15];
		}

		$this->delete_expired_codes();

		if (empty($signup_request_email)) {
			$result = $db->prepare('SELECT COUNT(id) AS count FROM ' . DB_PRFX . 'codes WHERE type = ? AND email = ?');
			$result->execute(['signup', $email]);
			$count = $result->fetch(PDO::FETCH_ASSOC);
			if ($count['count'] > 0)
				return ['warning', SENTENCE_19];
		} else 
			$db->prepare('DELETE FROM ' . DB_PRFX . 'codes WHERE type = ? AND email = ?')->execute(['signup', $email]);

		global $config;

		require_once PATH . DS . 'incs' . DS . 'passwordhash.class.php';
		$password_hash = new PasswordHash(8, false);

		$password = $password_hash->HashPassword($password);

		$db->prepare('INSERT INTO ' . DB_PRFX . 'users (username, password, email, enabled) VALUES (?, ?, ?, ?)')->execute([$username, $password, $email, '1']);

		$message = SENTENCE_23;
		$message = str_replace(
			['{TITLE}', '{USERNAME}'],
			[$config->config->title, $username],
			$message
		);

		send_mail($email, SIGN_UP, $this->email($message));

		$user_id = $db->lastInsertId();

		$db->prepare('INSERT INTO ' . DB_PRFX . 'profiles (user) VALUES (?)')->execute([$user_id]);

		$result = $db->prepare('SELECT * FROM ' . DB_PRFX . 'users WHERE id = ?');
		$result->execute([$user_id]);
		$user = $result->fetch(PDO::FETCH_ASSOC);

		$_SESSION['limny']['user'] = $user;

		return true;
	}

	private function email_confirmation($email) {
		if (empty($email))
			return ['warning', SENTENCE_11];

		if (filter_var($email, FILTER_VALIDATE_EMAIL) === false)
			return ['danger', SENTENCE_13];

		global $db, $config;

		$result = $db->prepare('SELECT COUNT(id) AS count FROM ' . DB_PRFX . 'users WHERE email = ?');
		$result->execute([$email]);
		$count = $result->fetch(PDO::FETCH_ASSOC);
		if ($count['count'] > 0)
			return ['warning', SENTENCE_15];

		$this->delete_expired_codes();

		$result = $db->prepare('SELECT COUNT(id) AS count FROM ' . DB_PRFX . 'codes WHERE type = ? AND email = ?');
		$result->execute(['signup', $email]);
		$count = $result->fetch(PDO::FETCH_ASSOC);
		if ($count['count'] > 0)
			return ['warning', SENTENCE_19];

		$result = $db->prepare('SELECT COUNT(id) AS count FROM ' . DB_PRFX . 'codes WHERE type = ? AND ip = INET_ATON(?)');
		$result->execute(['signup', $_SERVER['REMOTE_ADDR']]);
		$count = $result->fetch(PDO::FETCH_ASSOC);
		if ($count['count'] > 5)
			return ['danger', SENTENCE_21];

		while ($code = rand_hash(64)) {
			$result = $db->prepare('SELECT COUNT(id) AS count FROM ' . DB_PRFX . 'codes WHERE type = ? AND code = ?');
			$result->execute(['signup', $code]);
			$count = $result->fetch(PDO::FETCH_ASSOC);
			if ($count['count'] < 1)
				break;
		}

		$message = SENTENCE_22;
		$message = str_replace(
			['{TITLE}', '{LINK}'],
			[$config->config->title, url('user/signup/' . $code, true)],
			$message
		);

		$mail = send_mail($email, SIGN_UP, $this->email($message));

		if ($mail === true) {
			$db->prepare('INSERT INTO ' . DB_PRFX . 'codes (type, email, code, ip, time) VALUES (?, ?, ?, INET_ATON(?), UNIX_TIMESTAMP())')->execute(['signup', $email, $code, $_SERVER['REMOTE_ADDR']]);

			return true;
		} else
			return ['danger', SENTENCE_30];
	}

	public function email($message) {
		global $config;

		$data = '<html>';
		$data .= '<body>';
		$data .= '<h2>' . $config->config->title . '</h2>';
		$data .= '<div style="padding:10px;">' . $message . '</div>';
		$data .= '</body>';
		$data .= '</html>';

		return $data;
	}

	private function delete_expired_codes() {
		global $db;

		return $db->query('DELETE FROM ' . DB_PRFX . 'codes WHERE time < UNIX_TIMESTAMP() - 86400')->execute();
	}

	public function page_forgotpassword() {
		if ($this->signed_in())
			redirect(BASE);
	
		global $q;

		$this->title = FORGOT_PASSWORD;
		$this->content = '<h1>' . FORGOT_PASSWORD . '</h1>';

		if (isset($q['param'][2])) {
			if (in_array($q['param'][2], ['done', 'sent'])) {
				if ($q['param'][2] == 'sent')
					$message = SENTENCE_27;
				else if ($q['param'][2] == 'done') {
					$message = SENTENCE_28;
					$link = '<br><div class="text-center"><a href="' . url('user/signin') . '" class="btn btn-info">' . SIGN_IN . '</a></div>';
				}

				$this->content .= '<div class="message bg-success stable">' . $message . '</div>';

				if (isset($link))
					$this->content .= $link;

				return true;
			}

			global $db;

			$code = $q['param'][2];

			$this->delete_expired_codes();

			$result = $db->prepare('SELECT email FROM ' . DB_PRFX . 'codes WHERE type = ? AND code = ?');
			$result->execute(['forgotpassword', $code]);
			$resetpassword_request = $result->fetch(PDO::FETCH_ASSOC);

			if ($resetpassword_request === false)
				return false;
		}

		if (isset($resetpassword_request)) {
			if (isset($_POST['update']))
				if ($update_password = $this->update_password($resetpassword_request['email'], @$_POST))
					if ($update_password === true)
						redirect(url('user/forgotpassword/done'));
					else
						$message = $update_password;

			$result = $db->prepare('SELECT username FROM ' . DB_PRFX . 'users WHERE email = ?');
			$result->execute([$resetpassword_request['email']]);
			$user = $result->fetch(PDO::FETCH_ASSOC);

			$required = ' <span class="text-red">*</span>';

			$this->form_options = [
				'username' => ['label' => USERNAME, 'type' => 'text', 'disabled' => 'disabled'],
				'new_password' => ['label' => NEW_PASSWORD . $required, 'type' => 'password'],
				'repeat_new_password' => ['label' => REPEAT_NEW_PASSWORD . $required, 'type' => 'password']
			];

			$this->form_values['username'] = $user['username'];

			$button = $this->button('update', UPDATE, ['type' => 'submit', 'class' => 'btn btn-primary']);
		} else {
			if (isset($_POST['resetpassword']) && isset($_POST['email']))
				if ($reset_password = $this->reset_password($_POST['email']))
					if ($reset_password === true)
						redirect(url('user/forgotpassword/sent'));
					else
						$message = $reset_password;

			$this->form_options = [
				'email' => ['label' => EMAIL . ' <span class="text-red">*</span>', 'type' => 'text']
			];

			$this->form_values = @$_POST;

			$button = $this->button('resetpassword', RESET_PASSWORD, ['type' => 'submit', 'class' => 'btn btn-primary']);
		}

		$button = '<div class="col-sm-3"></div><div class="col-sm-9">' . $button . '</div>';

		if (isset($message))
			$this->content .= '<div class="message bg-' . $message[0] . '">' . $message[1] . '</div>';

		$this->content .= '<div class="limny user-forgotpassword">';
		$this->content .= $this->make('post', url('user/forgotpassword' . (isset($code) ? '/' . $code : null)), ['form' => 'form-horizontal', 'label' => 'col-sm-3', 'element' => 'col-sm-9'], $button);
		$this->content .= '</div>';

		return true;
	}

	private function reset_password($email) {
		if (empty($email))
			return ['warning', SENTENCE_11];

		if (filter_var($email, FILTER_VALIDATE_EMAIL) === false)
			return ['danger', SENTENCE_13];

		global $db, $config;

		$result = $db->prepare('SELECT COUNT(id) AS count FROM ' . DB_PRFX . 'users WHERE email = ?');
		$result->execute([$email]);
		$count = $result->fetch(PDO::FETCH_ASSOC);
		if ($count['count'] < 1)
			return ['warning', SENTENCE_24];

		$this->delete_expired_codes();

		$result = $db->prepare('SELECT COUNT(id) AS count FROM ' . DB_PRFX . 'codes WHERE type = ? AND email = ?');
		$result->execute(['forgotpassword', $email]);
		$count = $result->fetch(PDO::FETCH_ASSOC);
		if ($count['count'] > 0)
			return ['warning', SENTENCE_25];

		$result = $db->prepare('SELECT COUNT(id) AS count FROM ' . DB_PRFX . 'codes WHERE type = ? AND ip = INET_ATON(?)');
		$result->execute(['forgotpassword', $_SERVER['REMOTE_ADDR']]);
		$count = $result->fetch(PDO::FETCH_ASSOC);
		if ($count['count'] > 5)
			return ['danger', SENTENCE_21];

		while ($code = rand_hash(64)) {
			$result = $db->prepare('SELECT COUNT(id) AS count FROM ' . DB_PRFX . 'codes WHERE type = ? AND code = ?');
			$result->execute(['forgotpassword', $code]);
			$count = $result->fetch(PDO::FETCH_ASSOC);
			if ($count['count'] < 1)
				break;
		}

		$message = SENTENCE_26;
		$message = str_replace(
			['{TITLE}', '{LINK}'],
			[$config->config->title, url('user/forgotpassword/' . $code, true)],
			$message
		);

		$mail = send_mail($email, RESET_PASSWORD, $this->email($message));

		if ($mail === true) {
			$db->prepare('INSERT INTO ' . DB_PRFX . 'codes (type, email, code, ip, time) VALUES (?, ?, ?, INET_ATON(?), UNIX_TIMESTAMP())')->execute(['forgotpassword', $email, $code, $_SERVER['REMOTE_ADDR']]);

			return true;
		} else
			return ['danger', SENTENCE_30];
	}

	private function update_password($email, $post) {
		$new_password = @$post['new_password'];
		$repeat_new_password = @$post['repeat_new_password'];

		if (empty($new_password) || empty($repeat_new_password))
			return ['warning', SENTENCE_11];

		if (strlen($new_password) < 6)
			return ['danger', SENTENCE_16];

		if ($new_password !== $repeat_new_password)
			return ['danger', SENTENCE_17];

		global $db, $config;

		require_once PATH . DS . 'incs' . DS . 'passwordhash.class.php';
		$password_hash = new PasswordHash(8, false);

		$new_password = $password_hash->HashPassword($new_password);

		$db->prepare('UPDATE ' . DB_PRFX . 'users SET password = ? WHERE email = ?')->execute([$new_password, $email]);

		$db->prepare('DELETE FROM ' . DB_PRFX . 'codes WHERE type = ? AND email = ?')->execute(['forgotpassword', $email]);

		return true;
	}
}

?>