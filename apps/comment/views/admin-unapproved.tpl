<div class="comment unapproved bg-warning" data-id="<?=$comment['id']?>">

	<ul class="manage">
		<li class="approve"><a href="#" title="<?=COMMENT_APPROVE?>" class="text-success"><i class="fa fa-check fa-lg"></i></a></li>
		<li class="reply"><a href="#" title="<?=COMMENT_REPLY?>" class="text-warning"><i class="fa fa-reply fa-lg"></i></a></li>
		<li class="edit"><a href="#" title="<?=COMMENT_EDIT?>" class="text-info"><i class="fa fa-pencil fa-lg"></i></a></li>
		<li class="delete"><a href="#" title="<?=COMMENT_DELETE?>" class="text-danger"><i class="fa fa-trash fa-lg"></i></a></li>
	</ul>

	<div class="header">
		<span class="name"><?=$comment['name']?></span>
		<a class="email" href="mailto:<?=$comment['email']?>"><i class="fa fa-envelope"></i></a>
		
		<?php if (empty($comment['website']) === false): ?>
		<a class="website" href="<?=$comment['website']?>" target="_blank"><i class="fa fa-globe"></i></a>
		<?php endif ?>

		<?php if (empty($comment['replyto']) === false): ?>
		<a class="parent" href="<?=url('post/' . $comment['post'])?>#comment-<?=$comment['replyto']?>" target="_blank"><i class="fa fa-comment"></i></a>
		<?php endif ?>
	</div>

	<div class="post-title"><a href="<?=url('post/' . $comment['post'])?>" target="_blank"><?=$comment['post_title']?></a></div>

	<div class="text"><?=nl2br($comment['comment'])?></div>

	<div style="clear:both"></div>
</div>