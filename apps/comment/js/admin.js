function reload_on_empty() {
	if ($("div.comment").length < 1)
		location.reload(true);
}

$(function(){
	// reload page on ajax error
	$(document).ajaxError(function(){
		location.reload(true);
	});

	// approve comment
	$("ul.manage li.approve").click(function(event){
		event.preventDefault();

		var comment = $(this).closest("div.comment");
		var id = comment.attr("data-id");

		comment.fadeOut();
		$.post(location.href, { action: "approve", id: id }, function(){
			comment.remove();
			
			reload_on_empty();
		});
	});

	// show reply form
	$("ul.manage li.reply").click(function(event){
		event.preventDefault();

		var comment = $(this).closest("div.comment");
		var manage = $(this).parent();
		var id = comment.attr("data-id");

		manage.hide();
		comment.append($("div.reply").html());
		comment.find("textarea").focus();
	});

	// cancel reply form
	$("div.comment").on("click", ".reply-cancel", function(){
		var _parent = $(this).parent();

		_parent.find("ul.manage").show();
		_parent.find("textarea, button").remove();
	});

	// submit reply
	$("div.comment").on("click", ".reply-submit", function(){
		var _this = $(this);
		var id = _this.closest("div.comment").attr("data-id");
		var textarea = _this.parent().find("textarea");

		if (textarea.val().length < 1) {
			textarea.focus();

			return false;
		}
		
		_this.parent().find("textarea, button").prop("disabled", true);

		$.post(location.href, { action: "reply", id: id, reply: textarea.val() }, function(){
			_this.closest("div.comment").fadeOut();

			reload_on_empty();
		})
	});

	// show edit form
	$("ul.manage li.edit").click(function(event){
		event.preventDefault();

		var comment = $(this).closest("div.comment");
		var manage = $(this).parent();
		var id = comment.attr("data-id");

		manage.hide();
		comment.append($("div.edit").html());
		comment.find("textarea").val(comment.find("div.text").html().replace(/<br>/gi, "")).focus();
		comment.find("div.text").hide();
	});

	// cancel edit form
	$("div.comment").on("click", ".edit-cancel", function(){
		var _parent = $(this).parent();

		_parent.find("ul.manage").show();
		_parent.find("div.text").show();
		_parent.find("textarea, button").remove();
	});

	// save edit
	$("div.comment").on("click", ".edit-save", function(){
		var _this = $(this);
		var id = _this.closest("div.comment").attr("data-id");
		var textarea = _this.parent().find("textarea");

		if (textarea.val().length < 1) {
			textarea.focus();

			return false;
		}
		
		_this.parent().find("textarea, button").prop("disabled", true);

		$.post(location.href, { action: "edit", id: id, edit: textarea.val() }, function(){
			var comment = _this.closest("div.comment");

			comment.find("div.text").html(textarea.val().replace(/\n/gi, "<br>")).show();
			comment.find("textarea, button").remove();
		})
	});

	// delete item
	$("ul.manage li.delete").click(function(event){
		event.preventDefault();

		var comment = $(this).closest("div.comment");
		var id = comment.attr("data-id");

		comment.fadeOut();
		$.post(location.href, { action: "delete", id: id }, function(){
			comment.remove();

			reload_on_empty();
		});
	});
});