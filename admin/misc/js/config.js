$(function(){
	var fields = ["app", "query", "text"];

	$("[name=default_content]").change(function(){
		for (i in fields) {
			element = $("[name=default_" + fields[i] + "]").closest(".form-group");

			if (fields[i] != $(this).val())
				element.hide();
			else
				element.fadeIn();
		}
	});

	$("[name=default_content]").change();
});