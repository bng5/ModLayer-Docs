$(document).ready(function(){
	

	jQuery('.codigo a').click(function(){
		jQuery(this).next().next().toggle(checkTree(jQuery(this)));
		return false;
	});

});

function checkTree(elem){
	elem.children('img').toggle();
}