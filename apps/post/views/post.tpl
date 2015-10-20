<article>
	<div class="blog-post">
		<header>
			<h2 class="blog-post-title"><a href="<?=$post['url']?>"><?=$post['title']?></a></h2>
		</header>
		<p class="blog-post-meta"><?=system_date($post['time'])?> <?=BY?> <a href="<?=url('post/author/' . $post['user'])?>"><?=$post['username']?></a></p>
		<p><?=$post['text']?></p>
		<footer>
			<?php if (empty($post['updated']) === false) { ?>
			<span class="blog-post-lastupdate"><?=POST_LAST_UPDATE?>: <?=system_date($post['updated'])?></span><br>
			<?php } ?>
			<?php if (empty($category) === false) { ?>
			<span class="blog-post-category"><?=POST_CATEGORY?>: <?=$category?></span> &nbsp; 
			<?php } ?>
			<?php if (empty($tags) === false) { ?>
			<span class="blog-post-tags"><?=POST_TAGS?>: <?=$tags?></span>
			<?php } ?>
		</footer>
	</div>
</article>
<hr>