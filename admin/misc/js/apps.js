$(function(){
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
				action: "app-uninstall",
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

	$(".enabled").click(function(){
		var _this = $(this);

		_this.prop("disabled", true);

		$.post(
			location.href,
			{
				action: "app-enabled",
				id: _this.attr("data-id"),
				enabled: _this.prop("checked")
			},
			function(data){
				if (data == "OK")
					_this.prop("disabled", false);
				else
					location.reload();
			}
		);
	});

	$(".install").click(function(){
		var _this = $(this);

		_this.prop("disabled", true);

		$.post(
			location.href,
			{
				action: "app-install",
				name: _this.attr("data-name")
			},
			function(data){
				location.reload();
			}
		);
	});
});