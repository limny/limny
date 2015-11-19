<!DOCTYPE HTML>
<html>
<head>
<title>Limny installation</title>
<meta name="charset" content="utf8">
<style type="text/css">
body {
	background: #f5f5f5;
}

header {
	width: 50%;
	padding: 10px;
	margin: 10px auto;
	font-style: italic;
	border-bottom: 1px solid #ccc;
	font-family: "Lucida Console", "Lucida", "serif";
}

header h1 {
	display: inline;
	font-size: 20pt;
	text-shadow: 1px 1px 1px #ccc;
}

header h1 span.red { color: #c00000; }
header h1 span.yellow { color: #f8ce00; }
header h1 span.green { color: #00c244; }
header h1 span.blue { color: #3f53c8; }
header h1 span.black { color: #000000; }

header p {
	display: inline;

}

section {
	width: 50%;
	margin: 0 auto;
	padding: 10px;
	border-bottom: 1px solid #ccc;
}

.text-gray {
	color: gray;
}

.text-red {
	color: #dd0000;
}

ol li {
	margin-bottom: 10px;
}

hr {
	border: 0;
	border-bottom: 1px solid #ccc;
}
</style>
</head>
<body>
	<header>
		<h1><span class="red">L</span><span class="yellow">i</span><span class="green">m</span><span class="blue">n</span><span class="black">y</span></h1> <p>installation</p>
	</header>
	<section>
		<form method="post">
		<?php

		$page = isset($_POST['page']) ? $_POST['page'] : null;

		switch ($page) {
			default:
			?>
			<h6>Welcome to Limny installation. Please choose the install method:</h6>
			<label><input name="page" type="radio" value="manual"> Manual</label>
			<p class="text-gray">Installing using manual importing SQL file into database and writing database connection information to configuration file (config.php). All these steps have to be done manually by the user.</p>
			<label><input name="page" type="radio" value="wizard" checked> Wizard</label>
			<p class="text-gray">Easy installation using two steps.</p>
			<button type="submit">Next</button>
			<?php
				break;

			case 'manual':
			?>
			<h6>Manual installation:</h6>
			<ol>
				<li>Create a SQL database.</li>
				<li>Import <em>/install/database.sql</em> to database.</li>
				<li>Edit <em>/config.php</em> with a text editor.</li>
				<li>Write connection information to configuration file.</li>
				<li>Delete <em>/install</em> directory.</li>
			</ol>
			<button type="submit">Back</button><br>
			<br>
			<p class="text-red"><strong>NOTE:</strong> Do not forget to delete installation directory. This directory existence considered as security risk.</p>
			<?php
				break;

			case 'wizard':
			?>
			<h6>Please enter database information:</h6>
			<table>
				<tr>
					<td>Hostname:</td>
					<td><input name="hostname" type="text" value="localhost"> <span class="text-gray">default: localhost</span></td>
				</tr>
				<tr>
					<td>Port:</td>
					<td><input name="port" type="text" value="3306"> <span class="text-gray">default: 3306 for MySQL</span></td>
				</tr>
				<tr>
					<td>Username:</td>
					<td><input name="username" type="text" value=""></td>
				</tr>
				<tr>
					<td>Password:</td>
					<td><input name="password" type="password" value=""></td>
				</tr>
				<tr>
					<td colspan="2"><hr></td>
				</tr>
				<tr>
					<td>Database:</td>
					<td><input name="database" type="text" value=""></td>
				</tr>
				<tr>
					<td>Tables prefix:</td>
					<td><input name="prefix" type="text" value="lmn_"> <span class="text-gray">default: lmn_</span></td>
				</tr>
			</table><br>
			<button type="submit">Install</button>
			<input name="page" type="hidden" value="install">
			<?php
				break;

			case 'install':
				$hostname = $_POST['hostname'];
				$port = $_POST['port'];
				$username = $_POST['username'];
				$password = $_POST['password'];
				$database = $_POST['database'];
				$prefix = $_POST['prefix'];

				if (empty($database))
					$connection = false;
				else
					try {
						$db = new PDO('mysql:host=' . $hostname . ';port=' . $port . ';dbname=' . $database, $username, $password);
						$db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
						$connection = true;
					} catch (PDOException $error) {
						$connection = false;
					}
				
				if ($connection === true) {
					file_put_contents(__DIR__ . '/../config.php', "<?php\n\ndefine('DB_HOST', '{$hostname}');\ndefine('DB_PORT', '{$port}');\ndefine('DB_USER', '{$username}');\ndefine('DB_PASS', '{$password}');\ndefine('DB_NAME', '{$database}');\ndefine('DB_PRFX', '{$prefix}');\n\n?>");
					$data = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'database.sql');
					$data = str_replace('lmn_', $prefix, $data);
					$data = explode(";\n", $data);
					$data = array_map('trim', $data);
					$data = array_filter($data);
					
					foreach ($data as $value)
						$db->exec($value);
					?>
					<h6>Database imported successfully</h6>
					<p>Please enter settings for customizing system:</p>
					<table>
						<tr>
							<td>Title:</td>
							<td><input name="title" type="text" value="My website"></td>
						</tr>
						<tr>
							<td>Address:</td>
							<td><input name="address" type="text" value="http://<?=$_SERVER['HTTP_HOST']?><?=substr($_SERVER['SCRIPT_NAME'], 0, strpos($_SERVER['SCRIPT_NAME'], '/install'))?>"></td>
						</tr>
						<tr>
							<td colspan="2"><hr></td>
						</tr>
					</table>
					<p>Administrator:</p>
					<table>
						<tr>
							<td>Username:</td>
							<td><input name="admin_username" type="text" value="admin"></td>
						</tr>
						<tr>
							<td>Password:</td>
							<td><input name="admin_password" type="password" value=""></td>
						</tr>
						<tr>
							<td>Email:</td>
							<td><input name="admin_email" type="text" value=""></td>
						</tr>
					</table>
					<button type="submit">Update</button>
					<input name="page" type="hidden" value="update">
					<?php
				} else {
					?>
					<p class="text-red"><strong>Error:</strong> Cannot connect to database.</p>
					<p class="text-gray">Please click on Back button and check database connection information.</p>
					<button type="submit">Back</button>
					<input name="page" type="hidden" value="wizard">
					<?php
				}
				break;

			case 'update':
				date_default_timezone_set('UTC');

				$title = $_POST['title'];
				$address = $_POST['address'];
				$admin_username = $_POST['admin_username'];
				$admin_password = $_POST['admin_password'];
				$admin_email = $_POST['admin_email'];

				$footer = 'Copyright &copy ' . date('Y');
				$url_mode = function_exists('apache_get_modules') && in_array('mod_rewrite', apache_get_modules()) ? 'standard' : 'simple';

				require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'incs' . DIRECTORY_SEPARATOR . 'passwordhash.class.php';
				$password_hash = new PasswordHash(8, false);
				$password_length = strlen($admin_password);
				$admin_password = $password_hash->HashPassword($admin_password);

				require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'config.php';

				$db = new PDO('mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME, DB_USER, DB_PASS);
				$db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

				foreach (['title', 'address', 'footer', 'url_mode'] as $name)
					$db->prepare('UPDATE ' . DB_PRFX . 'config SET value = ? WHERE name = ?')->execute([${$name}, $name]);

				foreach (['username', 'password', 'email'] as $name)
					$db->prepare('UPDATE ' . DB_PRFX . 'users SET ' . $name . ' = ? WHERE id = 1')->execute([${'admin_' . $name}]);

				?> 
				<h6>Configuration has beed updated successfully</h6>
				<p>Please click <a href="../">here</a> to redirect to home page.</p>
				<h6>Administrator:</h6>
				<p>Username: <?=$admin_username?><br>Password: <?=str_repeat('*', $password_length)?></p>
				<?php

				foreach (scandir(__DIR__) as $file)
					if (empty($file) === false && in_array($file, ['.', '..']) === false)
						@unlink(__DIR__ . DIRECTORY_SEPARATOR . $file);

				if (@rmdir(__DIR__ . DIRECTORY_SEPARATOR) === false) {
					?>
					<p class="text-red"><strong>NOTE:</strong> Do not forget to delete installation directory. This directory existence considered as security risk.</p>
					<?php
				}
				break;
		}

		?>
	</form>
	</section>
</body>
</html>