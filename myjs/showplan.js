$(document).ready(function(){
	$('li').hover(function(){
		$(this).children('a.mol').show();
		$(this).children('a.tomato_alert').show();
		$(this).children('.tomato_time').show();
	},
	function(){
		$(this).children('a.mol').hide();
		$(this).children('a.tomato_alert').hide();
		$(this).children('.tomato_time').hide();
	});

	$('#addstep').click(function(){
		var html = '<form onsubmit="return addstep(this);"><input type="hidden" name="planid" value="'+the_plan_id+'"/><input type="hidden" name="preid" value=""/>步骤:<input type="text" name="action"/><input type="submit" value="OK"/></form>';
		TINY.box.show(html,0,0,0,1);
	});

	$('.doit').click(function(){
		var stepid = $(this).attr('stepid');
		var pic = $(this);
		var sure = confirm('确实要改变步骤的状态吗？');
		if(!sure) return false;
		$.get('index.php?cmd=do_it&id='+stepid, function(data){
			//pic.attr('src', 'themes/pic/do_it/'+data+'.png');
			pic.remove();
		});
		return false;
	});
});
