<h2><?=$picture['title']?></h2>
<a href="<?=BASE . '/uploads/' . $picture['image']?>"><img src="<?=BASE . '/uploads/' . $picture['image']?>" alt="<?=$picture['title']?>" class="gallery-picture"></a><br>
<br>
<?=system_date($picture['time'])?><br>
<?=GALLERY_CATEGORY?>: <a href="<?=url('gallery/cat/' . $cat['id'])?>"><?=$cat['name']?></a>