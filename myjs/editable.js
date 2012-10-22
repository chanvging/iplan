$(document).ready(function(){
	$('.sm_editable, .memo_editable').editable('/index.php?cmd=editstep',{
		type:'textarea',
		submit:'<button class="btn btn-success btn-mini" type="button">保存</button>',
		indicator:'Saving...'
	});
	$('.step_editable').editable('/index.php?cmd=editstep',{
		indicator:'Saving...'
	});
	
	$('#desc_editable').editable('index.php?cmd=editplan&ajax&editable&id='+the_plan_id,
	{
		id:'fuck',
		name:'description',
	});
	$('#target_editable').editable('index.php?cmd=editplan&ajax&editable&id='+the_plan_id,
	{
		id:'fuck',
		name:'target',
	});
	$('.planame').editable('index.php?cmd=editplan&ajax&editable&id='+the_plan_id,
	{
		id:'fuck',
		name:'name',
	});
	$('#status').editable('index.php?cmd=editplan&ajax&editable&id='+the_plan_id,
	{
		id:'fuck',
		data:"{'RUNNING':'RUNNING', 'PAUSE':'PAUSE', 'WAITING':'WAITING', 'HALT':'HALT'}",
		type:'select',
		name:'status',
		submit:'<button class="btn btn-success btn-mini" type="button">确定</button>',
	});
	
	var show_sel = false;
	$('#endtime').click(function(){
		if(!show_sel){
			var cur_date = $(this).text();
			$(this).html('');
			mydate('#endtime', 'endtime', {default_val:cur_date});
			$('<a class="btn btn-success btn-mini" href="javascript:;">确定</a>').click(function(event){
				var new_date = $(this).prev().val();
				var cont = $(this).parent();
				
				if(new_date==cur_date){
					cont.html(new_date);
					show_sel = false;
					event.stopPropagation();
					return false;
				}

				$.get('index.php?cmd=editplan&id='+the_plan_id+'&ajax&deadline='+new_date, function(data)

				{	if(data=='succ'){
						cont.html(new_date);
						show_sel = false;
					}else{
						alert(data);
					}
				});
				event.stopPropagation();
			}).appendTo(this);
			show_sel = true;
		}
	});

	
});
