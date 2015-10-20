$(function(){
	$(".message").click(function(){
		if ($(this).hasClass("stable"))
			return true;
		
		$(this).slideUp();
	});
});