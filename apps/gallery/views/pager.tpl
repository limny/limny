<ul class="pager">
	<?php if ($page > 1) { ?>
	<li><a href="<?=url('gallery/cat/' . $cat_id . '/page/' . ($page - 1))?>"><?=GALLERY_PREVIOUS?></a></li>
	<?php } ?>
	<?php if ($page < $last_page) { ?>
	<li><a href="<?=url('gallery/cat/' . $cat_id . '/page/' . ($page + 1))?>"><?=GALLERY_NEXT?></a></li>
	<?php } ?>
</ul>