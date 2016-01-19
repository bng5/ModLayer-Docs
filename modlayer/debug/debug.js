$(document).ready(function() {
	showDebug();
	var isCtrl = false;
	$(document).keyup(function (e){
		if(e.which == 18) isCtrl=false;
	}).keydown(function (e) {
	    if(e.which == 18) isCtrl=true;
	    if(e.which == 68 && isCtrl == true) {
			//alert('guardando... Con jQuery');
			openDebug();
	 	return false;
	 }
	});

	$(".techoDebug a").click(function(e){
		e.preventDefault();
		openDebug()
	});
});

function showDebug(){


	$(document).scroll(function(){
		var offset = $(window).scrollTop();
		$(".xmlContentBack").css("top", offset);
		$(".xmlContent").css("top", offset);
		$(".debug").css("top", offset);
	})
	
	$('.codigo a').click(function(){
		$(this).next().next().toggle(checkTree($(this)));
		return false;
	});

}

function checkTree(elem){
	elem.children('img').toggle();
}


function openDebug()
{
	$(".techoDebug a").unbind('click');
	$(".techoDebug a").click(function(e){
		e.preventDefault();
		closeDebug();
	});

	$('body').css({overflow:'hidden'});
	
	var offset = $(window).scrollTop();
	var h = $(window).height();
	var w = $(window).width();
	
	$(".debug").css("height", h);
	$(".debug").css("top", offset);
	
	$(".debug").show();
	$(".xmlContentBack").css("height", h - 27);
	$(".xmlContentBack").css("width", w);
	
	$(".techoDebug").css({top:"24px",bottom:'auto'});
	$(".xmlContentBack").css("top", offset + 27);
	$(".xmlContentBack").show()
	$(".xmlContent").css("height", h - 27);
	$(".xmlContent").css("width", w - 20);
	$(".xmlContent").css("top", offset + 27);
	$(".xmlContent").fadeIn('fast');

	document.onkeydown = function(e){ 	
		if (e == null) { // ie
			keycode = event.keyCode;
		} else { // mozilla
			keycode = e.which;
		}
		//alert(keycode);
		if(keycode == 27){ // close
			closeDebug();
		} 
	};
}

function closeDebug()
{
	$(".techoDebug a").unbind('click');
	$(".techoDebug a").click(function(e){
		e.preventDefault();
		openDebug();
	});

	$('body').css({overflow:'auto'});
	$(".techoDebug").css({top:'auto',bottom:0});
	$(".xmlContent, .debug, .xmlContentBack").hide();
}










