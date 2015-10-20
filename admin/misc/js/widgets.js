$(function(){
	$(".widget-install a:first").click(function(event){
		event.preventDefault();

		$(this).hide().delay(500).next().fadeIn();
	});

	$("#widget_file").change(function(){
		var value = $(this).val().toLowerCase();
		var extension = value.substring(value.lastIndexOf(".") + 1);
		
		if (extension.length > 0 && extension != "zip") {
			var message = $(".message");

			message.html(message.attr("data-extension")).fadeIn();
			$(this).val("");
		}
	});

	$("#widget_install").click(function(event){
		$(".message").slideUp();

		if ($("#widget_file").val().length < 1) {
			$("#widget_file").focus();

			event.preventDefault();
		}
	});

	$(".uninstall").click(function(){
		$(this).prev().css("opacity", "0").css("display", "inline").css("margin-right", "-10px").animate({ opacity: "1", "margin-right": "5px" });
		$(this).next().fadeIn().next().fadeIn();
		$(this).hide();
	});

	$(".uninstall-confirm").click(function(){
		var _this = $(this);

		_this.prop("disabled", true);

		$.post(
			location.href,
			{
				action: "widget-uninstall",
				id: _this.attr("data-id")
			},
			function(data){
				location.reload();
			}
		);
	});

	$(".uninstall-cancel").click(function(){
		$(this).hide().prev().hide().prev().fadeIn().prev().hide();
	});
})