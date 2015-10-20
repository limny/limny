<ul class="pager">
	<?php if ($page > 1) { ?>
	<li><a href="<?=url('post/page/' . ($page - 1))?>"><?=POST_PREVIOUS?></a></li>
	<?php } ?>
	<?php if ($page < $last_page) { ?>
	<li><a href="<?=url('post/page/' . ($page + 1))?>"><?=POST_NEXT?></a></li>
	<?php } ?>
</ul>