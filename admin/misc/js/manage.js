$(function(){
	$("#manage_search > input").keydown(function(event){
		var key = event.which || event.keyCode || 0;
		
		if (key == 13) {
			if ($(this).val().length > 0) {
				$(this).prev().attr("name", "empty");

				var form = $(this).closest("form");
				var action = form.attr("action");

				form.attr("action", action.substring(0, action.lastIndexOf("/")) + "/search");

				return true;
			}

			event.preventDefault();
		}
	});

	$("#manage_search > button").click(function(event){
		var form = $(this).closest("form");
		var action = form.attr("action");

		form.attr("action", action.substring(0, action.lastIndexOf("/")) + "/search");

		return true;
	});

	$("#check_all").click(function(){
		$(".table.manage > tbody > tr").click();
	});

	$("table.table > tbody > tr").click(function(){
		if ($(this).attr("class") == "warning") {
			$(this).removeClass("warning");
			$(this).find("input[type=checkbox]").prop("checked", false);
		} else {
			$(this).addClass("warning");
			$(this).find("input[type=checkbox]").prop("checked", true);
		}
	});

	$("p.bg-danger, p.bg-info").delay(5000).slideUp();
});