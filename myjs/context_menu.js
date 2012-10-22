$(document).ready(function(){
	$('.planame').jeegoocontext('plan_menu', {
		onSelect:function(e, context){
			var cmd = $(this).attr('id');
			switch(cmd){
				case 'plan_del':
					var yes = confirm('确实要删除该计划吗？');
					if(!yes) return false;
					location.href = 'index.php?cmd=delplan&id='+the_plan_id;
					break;
				case 'plan_over':
					var html = '<form onsubmit="return cmp_plan(this);">';
					html += '<input type="hidden" name="planid" value="'+the_plan_id+'"/>';
					html += '<label>总结:</label><br/><textarea name="summary"></textarea><br/>';
					html += '<input type="submit" value="OK"/><br/>';
					html += '</form>';
					TINY.box.show(html,0,0,0,1);
					return false;
					break;
				case 'plan_redo':
					var sure = confirm('确实要重做该计划吗？');
					if(!sure) return false;
					$.get('index.php?cmd=redo_plan&id='+the_plan_id, function(data){
						if(data=='succ'){location.reload();}
						else{alert(data);}
					});
					break;
				case 'plan_step':
					insert_after_step = '';
					$('#add_step_form').modal('show');
					//var html = '<form onsubmit="return addstep(this);"><input type="hidden" name="planid" value="'+the_plan_id+'"/><input type="hidden" name="preid" value=""/>步骤:<input type="text" name="action"/><input type="submit" value="OK"/></form>';
					//TINY.box.show(html,0,0,0,1);
					break;
				case 'plan_hide':
					break;
				case 'html':
					window.open('/index.php?cmd=export&type=html&id='+the_plan_id);
					break;
				case 'doc':
					window.open('/index.php?cmd=export&type=doc&id='+the_plan_id);
					break;
				case 'plan_schedule':
					$('#schedule_form').modal('show');
					break;
				default:
					return false;
			}
		}
	});
	
	$('ol.steps li>span').jeegoocontext('contextmenu', {
		onSelect : function(e, context){
			var cmd = $(this).attr('id');
			var stepid = $(context).attr('stepid');
			context_menu['stepid'] = stepid;
			if(cmd=='' || stepid<=0) return false;
			switch(cmd){
				case 'menu_doit':
					$.get('index.php?cmd=do_it&id='+stepid, function(data){
						location.href = location.href;
					});
					break;
				case 'menu_addnext':
					insert_after_step = stepid;
					$('#add_step_form').modal('show');
					break;
				case 'menu_tomato':
					$('#tomato_setup').modal('show');
					//var html = '<form onsubmit="return tomato(this);"><input type="hidden" name="stepid" value="'+stepid+'"/>需要<input type="text" name="tomatos" size="2"/>个番茄时间<input type="submit" value="OK"/></form>';
					//TINY.box.show(html,0,0,0,1);
					break;
				case 'menu_reset':
					$.get('index.php?cmd=tomato_reset&id='+stepid, function(data){
						location.href = location.href;
					});
					break;
				case 'menu_del':
					delstep(stepid);
					break;
				case 'menu_finish':
					finish_step(stepid);
					break;
				case 'menu_redo':
					$.get('index.php?cmd=redo_step&id='+stepid, function(data){
						location.href = location.href;
					});
					break;
				case 'menu_subit':
					var plan_name = $(context).text();
					if(plan_name=='') return false;
					addplan(stepid, plan_name);
					break;
				default:
					return false;
			}
		}
	});
});