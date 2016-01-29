


$(document).ready(function(){

	$('.version .trigger').click(function(){
		$('.version ul').toggle();
	});

	$('a.menu').click(function(e){
		e.preventDefault();

		$('.navpane').toggleClass('open');
	});

});