<form method="post" action="<?=$_SERVER['REQUEST_URI']?>#respond">

<div id="respond" class="comment form">
	<?php if (isset($message) && empty($message) === false): ?>
	<div class="message stable <?=$class?>"><?=$message?></div>
	<?php endif ?>

	<?php if (isset($replyto)): ?>
	<?=COMMENT_REPLY_TO?>: <a href="<?=$_SERVER['REQUEST_URI']?>#comment-<?=$replyto['id']?>"><?=$replyto['name']?></a> <a href="<?=$post_url?>#respond" class="cancel"><?=COMMENT_CANCEL_REPLY?></a>
	<?php endif ?>

	<div>
		<label><?=COMMENT_NAME?>: <span class="text-red">*</span></label>
		<input name="name" type="text" value="<?=isset($name) ? $name : null?>">
	</div>

	<div>
		<label><?=COMMENT_EMAIL?>: <span class="text-red">*</span></label>
		<input name="email" type="text" value="<?=isset($email) ? $email : null?>"> (<?=COMMENT_SENTENCE_5?>)
	</div>

	<div>
		<label><?=COMMENT_WEBSITE?>:</label>
		<input name="website" type="text" value="<?=isset($website) ? $website : null?>">
	</div>

	<div>
		<label><?=COMMENT_COMMENT?>: <span class="text-red">*</span></label>
		<textarea name="comment" rows="5" cols="40"><?=isset($comment) ? $comment : null?></textarea>
	</div>

	<div class="buttons">
		<button name="post_comment" type="submit"><?=COMMENT_POST_COMMENT?></button>
	</div>
</div>

</form>