<article>
	<div class="blog-post">
	<header>
		<h2 class="blog-post-title"><a href="<?=$page['url']?>"><?=$page['title']?></a></h2>
	</header>
	<p class="blog-post-meta"><?=system_date($page['time'])?></p>
	<p><?=$page['text']?></p>
	<footer>
		<?php if (empty($page['updated']) === false) { ?>
		<span class="blog-post-lastupdate"><?=PAGE_UPDATE_DATE?>: <?=system_date($page['updated'])?></span>
		<?php } ?>
	</footer>
</article>
<hr>