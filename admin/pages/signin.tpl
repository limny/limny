<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="description" content="<?=LIMNY_ADMINISTRATION?> page">
	<meta name="author" content="">
	<link rel="icon" href="../../favicon.ico">

	<title><?=LIMNY_ADMINISTRATION?></title>

	<link href="misc/css/bootstrap.min.css" rel="stylesheet">
	<?php if ($admin->direction) { ?>
	<link href="<?=BASE?>/<?=ADMIN_DIR?>/misc/css/bootstrap-<?=$admin->direction?>.min.css" rel="stylesheet">
	<?php } ?>
	<link href="misc/css/signin.css" rel="stylesheet">

	<!--[if lt IE 9]>
		<script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
		<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
	<![endif]-->

</head>

<body>

	<div class="container">

		<form name="singin" action="<?=BASE?>/<?=ADMIN_DIR?>/signin" method="post" class="form-signin" role="form">
			<h2 class="form-signin-heading"><?=LIMNY_ADMINISTRATION?></h2>
			<?php if (isset($page->message)) { ?>
			<p class="message bg-danger"><?=$page->message?></p>
			<?php } ?>
			<input name="limny_username" type="text" class="form-control" placeholder="<?=USERNAME; ?>" value="<?php if (isset($_POST['limny_username'])) echo htmlentities($_POST['limny_username']); ?>" required autofocus>
			<input name="limny_password" type="password" class="form-control" placeholder="<?=PASSWORD?>" required>
			<p class="text-center"><img src="<?=BASE?>/<?=ADMIN_DIR?>/secimage" alt="Security code"></p>
			<input name="limny_seccode" type="text" class="form-control" placeholder="<?=SECURITY_CODE?>" value="" required autocomplete="off">
			<label class="checkbox">
				<input name="limny_remember" type="checkbox" value="1"> <?=REMEMBER_ME?>
			</label>
			<button class="btn btn-lg btn-primary btn-block" type="submit"><?=SIGN_IN?></button>
		</form>

	</div>

	<!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
	<script src="misc/js/ie10-viewport-bug-workaround.js"></script>
</body>
</html>