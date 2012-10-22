var hovering = 0;
var timer = 0;
var focused = false;
function chg_visib(){
	if(timer>0){
		clearTimeout(timer);
		timer = 0;
	}
	var opc = hovering;
	if(hovering==0 && focused){
		opc = 1;
	}
	//$('#addmore').animate({opacity:opc});
	$('#addmore').fadeTo('slow', opc);
}

$(document).ready(function(){
	if($('#addmore').prev('li').length > 0){
		$('#addmore').hover(function(){
			hovering = 1;
			if(timer==0){
				timer = setTimeout('chg_visib();', 500);
			}
		}, function(){
			hovering = 0;
			if(timer==0){
				timer = setTimeout('chg_visib();', 1000);
			}
		});
		
		$('#addmore input:text').focus(function(){
			focused = true;
		}).blur(function(){
			focused = false;
			chg_visib();
		});
		
		$('#addmore input[name=preid]').val($('#addmore').prev('li').children('span:first').attr('stepid'));
	}else{
		$('#addmore').css({opacity:1});
	}
});