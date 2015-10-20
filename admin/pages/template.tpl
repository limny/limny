<!DOCTYPE html>
<html lang="<?=$config->config->language?>">

<head>

	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="description" content="">
	<meta name="author" content="">

	<title><?=isset($admin->title) && empty($admin->title) === false ? $admin->title . ' - ' : null?><?=LIMNY_ADMINISTRATION?></title>

	<link href="<?=BASE?>/<?=ADMIN_DIR?>/misc/css/bootstrap.min.css" rel="stylesheet">
	<?php if ($admin->direction) { ?>
	<link href="<?=BASE?>/<?=ADMIN_DIR?>/misc/css/bootstrap-<?=$admin->direction?>.min.css" rel="stylesheet">
	<?php } ?>
	<link href="<?=BASE?>/<?=ADMIN_DIR?>/misc/css/plugins/metisMenu/metisMenu.min.css" rel="stylesheet">
	<link href="<?=BASE?>/<?=ADMIN_DIR?>/misc/css/sb-admin-2.css" rel="stylesheet">
	<?php if ($admin->direction) { ?>
	<link href="<?=BASE?>/<?=ADMIN_DIR?>/misc/css/sb-admin-2-<?=$admin->direction?>.css" rel="stylesheet">
	<?php } ?>
	<link href="<?=BASE?>/<?=ADMIN_DIR?>/misc/css/limny.css" rel="stylesheet">
	<?php if ($admin->direction) { ?>
	<link href="<?=BASE?>/<?=ADMIN_DIR?>/misc/css/limny-<?=$admin->direction?>.css" rel="stylesheet">
	<?php } ?>
	<link href="<?=BASE?>/<?=ADMIN_DIR?>/misc/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">

	<!--[if lt IE 9]>
		<script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
		<script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
	<![endif]-->

	<script src="<?=BASE?>/<?=ADMIN_DIR?>/misc/js/jquery-1.11.0.js"></script>
	<script src="<?=BASE?>/<?=ADMIN_DIR?>/misc/js/bootstrap.min.js"></script>
	<script src="<?=BASE?>/<?=ADMIN_DIR?>/misc/js/plugins/metisMenu/metisMenu.min.js"></script>
	<script src="<?=BASE?>/<?=ADMIN_DIR?>/misc/js/sb-admin-2.js"></script>

	<?=$admin->head?>
</head>

<body>

	<div id="wrapper">

		<nav class="navbar navbar-default navbar-static-top" role="navigation" style="margin-bottom: 0">
			<div class="navbar-header">
				<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
					<span class="sr-only">Toggle navigation</span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</button>
				<a class="navbar-brand" href="<?=BASE?>/<?=ADMIN_DIR?>"><?= LIMNY_ADMINISTRATION; ?></a>
			</div>

			<ul class="nav navbar-top-links"> <!-- class <navbar-right> removed -->

				<li><a href="<?=$config->config->address;?>" target="_blank"><i class="fa fa-globe fa-fw"></i></a></li>

				<li class="dropdown">
					<a class="dropdown-toggle" data-toggle="dropdown" href="#">
						<strong><?=$_SESSION['limny']['admin']['username']?></strong>
						<i class="fa fa-user fa-fw"></i>  <i class="fa fa-caret-down"></i>
					</a>
					<ul class="dropdown-menu dropdown-user">
						<li><a href="<?=BASE?>/<?=ADMIN_DIR?>/profile"><i class="fa fa-user fa-fw"></i> <?=PROFILE?></a>
						</li>
						<li class="divider"></li>
						<li><a href="<?=BASE?>/<?=ADMIN_DIR?>/signout"><i class="fa fa-sign-out fa-fw"></i> <?=SIGN_OUT?></a>
						</li>
					</ul>
				</li>

				<li class="dropdown">
					<a class="dropdown-toggle" data-toggle="dropdown" href="#">
						<i class="fa fa-gear fa-fw"></i>  <i class="fa fa-caret-down"></i>
					</a>
					<ul class="dropdown-menu dropdown-user">
						<li><a href="<?=BASE?>/<?=ADMIN_DIR?>/menu"><i class="fa fa-navicon fa-fw"></i> <?=MENU?></a></li>
						<li><a href="<?=BASE?>/<?=ADMIN_DIR?>/blocks"><i class="fa fa-cubes fa-fw"></i> <?=BLOCKS?></a></li>
						<li><a href="<?=BASE?>/<?=ADMIN_DIR?>/widgets"><i class="fa fa-file-code-o fa-fw"></i> <?=WIDGETS?></a></li>
						<li><a href="<?=BASE?>/<?=ADMIN_DIR?>/themes"><i class="fa fa-desktop fa-fw"></i> <?=THEMES?></a></li>
						<li class="divider"></li>
						<li><a href="<?=BASE?>/<?=ADMIN_DIR?>/users"><i class="fa fa-user fa-fw"></i> <?=USERS?></a></li>
						<li><a href="<?=BASE?>/<?=ADMIN_DIR?>/roles"><i class="fa fa-users fa-fw"></i> <?=ROLES?></a></li>
						<li class="divider"></li>
						<li><a href="<?=BASE?>/<?=ADMIN_DIR?>/apps"><i class="fa fa-code fa-fw"></i> <?=APPLICATIONS?></a></li>
						<li><a href="<?=BASE?>/<?=ADMIN_DIR?>/config"><i class="fa fa-wrench fa-fw"></i> <?=CONFIGURATION?></a></li>
					</ul>
				</li>
			</ul>
			<div class="navbar-default sidebar" role="navigation">
				<div class="sidebar-nav navbar-collapse">
					<ul class="nav" id="side-menu">
						<li>
							<a class="<?php if ($admin->q[0] == 'dashboard') echo 'active' ?>" href="<?=BASE?>/<?=ADMIN_DIR?>"> <?=DASHBOARD?></a>
						</li>
						<?=$admin->navigation()?>
					</ul>
				</div>
			</div>
		</nav>

		<div id="page-wrapper">
			<div class="row">
				<div class="col-lg-12">
					<h1 class="page-header"><?=$admin->title?></h1>
					<?=$admin->content?>
				</div>
			</div>
		</div>

	</div>

</body>

</html>