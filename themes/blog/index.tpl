<!DOCTYPE html>
<html lang="<?=$this->registry->config->language?>">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="description" content="<?=$this->registry->config->description?>">

	<title><?=$this->title?></title>

	<link href="<?=BASE?>/themes/blog/bootstrap.min.css" rel="stylesheet">
	<?php if ($this->direction) { ?>
	<link href="<?=BASE?>/themes/blog/bootstrap-<?=$this->direction?>.min.css" rel="stylesheet">
	<?php } ?>
	<link href="<?=BASE?>/themes/blog/blog.css" rel="stylesheet">

	<!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
	<!--[if lt IE 9]>
		<script src="<?=BASE?>/themes/blog/js/html5shiv.js"></script>
		<script src="<?=BASE?>/themes/blog/js/respond.min.js"></script>
	<![endif]-->

	<script src="<?=BASE?>/themes/blog/js/jquery.min.js"></script>
	<script src="<?=BASE?>/themes/blog/js/bootstrap.min.js"></script>
	<script src="<?=BASE?>/themes/blog/js/docs.min.js"></script>

<?=$this->head?>
</head>

<body>

	<div class="blog-masthead">
		<div class="container">
			<nav class="blog-nav">
		<?php

		$menu = load_lib('menu', true, false, $this->registry);
		
		foreach ($menu->items() as $selected => $item)
			echo '<a class="blog-nav-item' . ($selected === 'selected' ? ' active' : null) . '" href="' . (empty($item['address']) ? BASE : $item['address']) . '">' . $item['name'] . '</a>';
		
		?>
			</nav>
		</div>
	</div>

	<div class="container">

		<div class="blog-header">
			<h1 class="blog-title"><?=$this->registry->config->header?></h1>
			<p class="lead blog-description"><?=$this->registry->config->motto?></p>
			<?=$this->header?>
		</div>

		<div class="row">

			<div class="col-sm-8 blog-main">
				<?=$this->main?>
			</div>

			<div class="col-sm-3 col-sm-offset-1 blog-sidebar">
				<?=$this->sidebar?>
			</div>

		</div>

	</div>

	<div class="blog-footer">
		<?=$this->footer?>
		<p><?=$this->registry->config->footer?></p>
		<p>Powered by <a href="http://www.limny.org">Limny</a><br>Blog template by <a href="https://twitter.com/mdo">@mdo</a></p>
		<p><a href="#">Back to top</a></p>
	</div>
 
</body>

</html>
