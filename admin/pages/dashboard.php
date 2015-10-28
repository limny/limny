<?php

$admin->title = DASHBOARD;
$admin->head = '';

$application = load_lib('application', true, true);
$apps = $application->apps();

$notifications = '';

if (isset($apps['comment']) && empty($apps['comment']['enabled']) === false) {
	$admin->app_load_language('comment');

	require_once PATH . DS . 'apps' . DS . 'comment' . DS . 'admin.model.class.php';

	CommentAdminModel::$db = $admin->db;

	if ($unapproved_comments = CommentAdminModel::unapproved())
		$notifications .= '<li><i class="fa fa-comment"></i> <a href="' . BASE . '/' . ADMIN_DIR . '/comment/unapproved">' . str_replace('{NUMBER}', '<span>' . count($unapproved_comments) . '</span>', COMMENT_SENTENCE_9) . '</a></li>';
}

if (isset($apps['post']) && empty($apps['post']['enabled']) === false) {
	$admin->app_load_language('post');

	$admin->head .= '<script type="text/javascript">
$(function(){
	$("form#post").submit(function(event){
		if ($(this).find("iframe").length > 0)
			$("#text").val($(this).find("iframe").contents().find("body").text());
		
		if ($("#title").val().length < 1) {
			$("#title").focus();
			event.preventDefault();

			return false;
		} else if ($("#text").val().length < 1) {
			$("#text").focus();
			event.preventDefault();

			return false;
		}

		return true;
	})
});
</script>';

	if (isset($apps['ckeditor']) && empty($apps['ckeditor']['enabled']) === false)
		$admin->head .= '<script type="text/javascript" src="' . BASE . '/apps/ckeditor/ckeditor/ckeditor.js"></script>
<script type="text/javascript">
$(function(){
	if ($("#text").length > 0)
		CKEDITOR.replace("text");
});
</script>';

	$post = '<form id="post" method="post" action="' . BASE . '/' . ADMIN_DIR . '/post/posts/add">
	<input id="title" name="title" type="text" class="form-control" placeholder="' . TITLE . '&hellip;">
	<textarea id="text" name="text" class="form-control" placeholder="' . TEXT . '&hellip;"></textarea>
	<input name="add" type="hidden">
	<button class="btn btn-primary">' . POST_SAVE_DRAFT . '</button>
</form>';
	
	require_once PATH . DS . 'apps' . DS . 'post' . DS . 'admin.model.class.php';

	PostAdminModel::$db = $admin->db;

	if ($num_uncategorized_posts = PostAdminModel::num_uncategorized_posts())
		$notifications .= '<li><i class="fa fa-folder"></i> <a href="' . BASE . '/' . ADMIN_DIR . '/post/posts">' . str_replace('{NUMBER}', '<span>' . count($num_uncategorized_posts) . '</span>', POST_SENTENCE_4) . '</a></li>';

	if ($num_unpublished_posts = PostAdminModel::num_unpublished_posts())
		$notifications .= '<li><i class="fa fa-pencil"></i> <a href="' . BASE . '/' . ADMIN_DIR . '/post/posts">' . str_replace('{NUMBER}', '<span>' . count($num_unpublished_posts) . '</span>', POST_SENTENCE_5) . '</a></li>';
}

if (empty($notifications))
	$notifications = SENTENCE_34;
else
	$notifications = '<ul class="notifications">' . $notifications . '</ul>';

$admin->content = '<div class="dashboard">

	<div class="row">

		<div class="col-md-8">
			' . (isset($post) ? $post : null) . '
		</div>

		<div class="col-md-4">
			<strong>' . NOTIFICATIONS . ':</strong><br>
			' . $notifications . '
		</div>

	</div>

</div>';

?>