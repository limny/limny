<div id="comment-<?=$comment['id']?>" class="comment item">
	<div class="header">
		<span class="name">
		<?php if (empty($comment['website']) === false): ?><a href="<?=$comment['website']?>" rel="external nofollow" target="_blank"><?php endif ?>
		<?=$comment['name']?>
		<?php if (empty($comment['website']) === false): ?></a><?php endif ?>
		</span>
		<span class="url"><a href="<?=$comment['url']?>">#</a></span>
		<span class="time"><?=system_date($comment['time'])?></span>
	</div>
	<div class="text"><?=nl2br($comment['comment'])?><br><a href="<?=$comment['reply_url']?>"><?=COMMENT_REPLY?></a></div>
	<?=$replies?>
</div>