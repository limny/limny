<?php

/**
 * Administration pages without file
 *
 * @package Limny
 * @author Hamid Samak <hamid@limny.org>
 * @copyright 2009-2016 Limny
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class Page {
	// database connection
	private $db;

	/**
	 * set database connection property
	 * @param  object $registry
	 * @return void
	 */
	public function Page($registry) {
		$this->db = $registry->db;
	}

	/**
	 * sign-in page and action
	 * @return void
	 */
	public function page_signin() {
		if (isset($_POST['limny_username']) && isset($_POST['limny_password']) && isset($_POST['limny_seccode'])) {
			$username = $_POST['limny_username'];
			$password = $_POST['limny_password'];
			$seccode = $_POST['limny_seccode'];
			$remember = isset($_POST['limny_remember']) ? true : false;
			
			if (empty($username))
				$this->message = SENTENCE_1;
			else if (empty($password))
				$this->message = SENTENCE_2;
			else if (empty($seccode) || isset($_SESSION['limny']['security_code']) === false || $seccode != $_SESSION['limny']['security_code'])
				$this->message = SENTENCE_37;
			else {
				$result = $this->db->prepare('SELECT * FROM ' . DB_PRFX . 'users WHERE username = ?');
				$result->execute([$username]);
				
				if ($admin = $result->fetch(PDO::FETCH_ASSOC)) {
					//print $admin['roles'];exit;
					$permitted = false;

					if (empty($admin['roles']) === false)
						foreach (explode(',', $admin['roles']) as $role_id) {
							$result = $this->db->prepare('SELECT permissions FROM ' . DB_PRFX . 'roles WHERE id = ?');
							$result->execute([$role_id]);

							if ($role = $result->fetch(PDO::FETCH_ASSOC)) {
								$role['permissions'] = trim($role['permissions']);

								if (empty($role['permissions']) === false) {
									$permitted = true;

									break;
								}
							}
						}
					
					require_once PATH . DS . 'incs' . DS . 'passwordhash.class.php';
					$password_hash = new PasswordHash(8, false);

					if ($permitted === true && $password_hash->CheckPassword($password, $admin['password']) === true) {
						$_SESSION['limny']['admin'] = $admin;

						if ($remember === true) {
							$hash = rand_hash(128);

							$this->db->prepare('UPDATE ' . DB_PRFX . 'users SET ip = INET_ATON(?), hash = ?, last_login = UNIX_TIMESTAMP(), last_activity = UNIX_TIMESTAMP() WHERE id = ?')->execute([$_SERVER['REMOTE_ADDR'], $hash, $admin['id']]);

							setcookie('limny_admin', $hash, time() + 2592000, '/');
						} else
							$this->db->prepare('UPDATE ' . DB_PRFX . 'users SET ip = INET_ATON(?), hash = NULL, last_login = UNIX_TIMESTAMP(), last_activity = UNIX_TIMESTAMP() WHERE id = ?')->execute([$_SERVER['REMOTE_ADDR'], $admin['id']]);

						redirect(BASE . '/' . ADMIN_DIR);
					}
				}

				$this->message = SENTENCE_3;
			}
		}
	}

	/**
	 * sign-out action
	 * @return void
	 */
	public function page_signout() {
		if (admin_signed_in() === true) {
			$this->db->prepare('UPDATE ' . DB_PRFX . 'users SET ip = NULL, hash = NULL WHERE id = ?')->execute([$_SESSION['limny']['admin']['id']]);
			
			unset($_SESSION['limny']['admin']);

			setcookie('limny_admin', '', time() - 2592000, '/');

			redirect(BASE . '/' . ADMIN_DIR);
		}
	}

	/**
	 * security image
	 * @return void
	 */
	public function page_secimage() {
		$security_code = load_lib('securitycode');

		$_SESSION['limny']['security_code'] = $security_code->code;
		$security_code->create();

		exit;
	}
}

?>