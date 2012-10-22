$(document).ready(function(){
	$('a.mol').click(function(){
		$(this).parent().children('div.plan_content').toggle();
	});
	var mao = location.hash;
	if(mao){
		mao = mao.split('#')[1];
		$('a.mol[stepid='+mao+']').click();
	}
});