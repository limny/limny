$(function(){
	$("ul.files li").click(function(){
		location.href = $(this).find("a").attr("href");
	});

	$(window).resize(function(){
		$("#text").css("height", ($(window).height() - $("#text").position().top - 150) + "px");
	});

	$(window).resize();

	$("#save").click(function(event){
		event.preventDefault();

		var _this = $(this);
		var status = $(".status");

		status.hide().removeClass("text-red text-green");

		if ($("#text").attr("data-change") === "false") {
			status.html(status.attr("data-nochange")).fadeIn(250);

			return false;
		}

		_this.prop("disabled", true);
		status.addClass("loading").html(status.attr("data-saving")).fadeIn(250);

		var _error = function(){
			status.removeClass("loading").addClass("text-red").html(status.attr("data-error"));
			_this.prop("disabled", false);
		}

		$.ajax({
			type: "POST",
			url: location.href,
			data: { action: "save", text: $("#text").val() },
			success: function(data){
				if (data == "OK") {
					status.removeClass("loading").addClass("text-green").html(status.attr("data-saved"));
					_this.prop("disabled", false);
					$("#text").attr("data-change", "false");
				} else
					_error();
			},
			error: function(){
				_error();
			}
		});
	});

	$("#text").click(function(){
		$(".status").fadeOut(250);
	}).change(function(){
		$(this).attr("data-change", "true");
	});

	$("#text").keydown(function(event){
		if (event.which == 9) {
			event.preventDefault();

			var value = $(this).val();
			var position = $(this).prop("selectionStart");

			if (event.shiftKey) {
				if (value.substring(position - 1, position) == "\t") {
					$(this).val(value.substring(0, position - 1) + value.substring(position));

					if ($(this)[0].setSelectionRange)
						$(this)[0].setSelectionRange(position - 1, position - 1);
				}
			} else {
				$(this).val(value.substring(0, position) + "\t" + value.substring(position));

				if ($(this)[0].setSelectionRange)
					$(this)[0].setSelectionRange(position + 1, position + 1);
			}
		}
	});
})