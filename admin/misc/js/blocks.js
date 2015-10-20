$(function(){
	$("#schema ul, #available-widgets").sortable({
		cursorAt: { top: 16, left: 16 },
		connectWith: ".placeholder",
		cancel: "input,textarea,button,select,option,label,hr,ul.visibility li",
		start: function( event, ui ) {
			$(ui.item).css("width", "200px");
		},
		stop: function(event, ui) {
			ui.item.addClass("light");
			// ADD A LINE OF CODE HERE TO LOCK THIS ELEMENT FOR FURTHER DRAG ACTIONS

		var sort = [];
		ui.item.parent().children("li").each(function(){
			sort.push($(this).attr("data-id"));
		});

			$.post(
				location.href,
				{
					action: "widget-position",
					id: ui.item.attr("data-id"),
					position: ui.item.parent().attr("data-position"),
					sort: sort
				},
				function(data){
					ui.item.removeClass("light");

					// UNLOCK ELEMENT HERE
				}
			);
		}
	});//.disableSelection();

	$(".option-toggle").click(function(event){
		event.preventDefault();
		
		var _this = $(this);
		var options = _this.parent().find(".options");
		var display = options.css("display");

		options.stop().slideToggle();
		_this.parent().find(".visibility").slideUp(250);

		if (display == "none" && options.attr("data-empty") == "true") {
			$.post(
				location.href,
				{
					action: "widget-options",
					id: _this.parent().attr("data-id")
				},
				function(data) {
					options.html(data).attr("data-empty", "false");
				}
			);
		}
	});

	$("ul.placeholder > li span").dblclick(function(){
		$(this).next().click();
	});

	$("body").on("click", "#update-options", function(){
		var _this = $(this);
		var data = _this.parent().serialize();
		
		_this.prop("disabled", true);

		$.post(
			location.href,
			{
				action: "widget-options",
				id: _this.closest("li[data-id]").attr("data-id"),
				options: data,
			},
			function(data){
				_this.prop("disabled", false);
				
				_this.closest("li[data-id]").find(".option-toggle").click();
			}
		);
	});

	$(".visibility-toggle").click(function(event){
		event.preventDefault();
		
		var _this = $(this);
		var visibility = _this.parent().find(".visibility");
		var display = visibility.css("display");

		visibility.stop().slideToggle();
		_this.parent().find(".options").slideUp(250);
	});

	$(".role, .lang").click(function(event){
		event.preventDefault();

		var _this = $(this);

		if (_this.attr("data-disabled") == "true")
			return false;

		_this.attr("data-disabled", "true");
		
		if (_this.hasClass("on")) {
			_this.removeClass("on").addClass("off");
			_this.find("i").removeClass("fa-toggle-on").addClass("fa-toggle-off");
		} else {
			_this.removeClass("off").addClass("on");
			_this.find("i").removeClass("fa-toggle-off").addClass("fa-toggle-on");
		}

		var roles = {}, langs = {};
		if (_this.hasClass("role"))
			_this.closest("ul").find(".role").each(function(){
				roles[$(this).attr("data-id")] = $(this).hasClass("on");
			});
		else if (_this.hasClass("lang"))
			_this.closest("ul").find(".lang").each(function(){
				langs[$(this).attr("data-code")] = $(this).hasClass("on");
			});
		//console.log(roles);return false;
		$.post(
			location.href,
			{
				action: "widget-visibility",
				id: _this.closest("li[data-id]").attr("data-id"),
				roles: roles,
				langs: langs
			},
			function(data){
				_this.attr("data-disabled", "false");
			}
		);
	});
});