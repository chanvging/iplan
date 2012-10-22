function addplan(stepid, name){
	if(stepid<=0) return false;
	if(submitting) return false;
	submitting = true;
	$.post('index.php?cmd=addplan', {stepid:stepid,parid:the_plan_id,name:name}, function(rst){
		if(rst.indexOf('succ')>0){
			location.href='/index.php?cmd=showplan&id='+rst.replace('succ','');
		}else{
			alert(rst);
			submitting = false;
		}
	});
	//alert($(data).serialize());
	return false;
}

function addstep(data){
	var planid = the_plan_id;
	var preid = insert_after_step;
	if(typeof(data)=='object'){
		planid = data.planid.value;
		preid = data.preid.value;
		action = data.action.value;
	}else if(typeof(data)=='string'){
		action = data;
	}else{
		return false;
	}

	if(action==''){
		alert('write something');
		return false;
	}

	if(submitting) return false;
	submitting = true;
	var url = '/index.php?cmd=addstep';
	$.post(url, {action:action, planid:planid, preid:preid}, function(data){
		if(data=='add_ok'){
			location.href=location.href;
		}else{
			submitting = false;
		}
	});
	return false;
}

function delstep(stepid){
	var is_del = confirm('确实要删除该步骤吗?');
	if(is_del){
		$.get('index.php?cmd=delstep&id='+stepid, function(){
			location.href=location.href;
		});
	}
}

function cmp_plan(data){
	if(submitting) return false;
	submitting = true;
	var planid = data.planid.value;
	var summary = data.summary.value;
	if(summary==''){
		summary = '完成';
	}
	var submit_btn = $(data).children('input[type=submit]');
	submit_btn.attr('disabled', 'disabled');
	$.post('/index.php?cmd=summary', {planid:planid, summary:summary}, function(rst){
		if(rst=='succ'){
			location.href=location.href;
		}else{
			alert(rst);
			submit_btn.removeAttr('disabled');
			submitting = false;
		}
	});
	return false;
}

function finish_step(stepid){
	if(submitting) return false;
	submitting = true;
	var planid = the_plan_id;
	var summary = '完成';
	if(stepid=='' || planid==''){return false;}
	$.post('/index.php?cmd=stepfinish', {stepid:stepid, planid:planid, summary:summary}, function(data){
		if(data=='succ'){
			location.href=location.href;
		}else{
			alert(data);
			submitting = false;
		}
	});
}

function tomato(form_obj){
	var counts = parseInt(form_obj.tomatos.value, 10);
	if(counts>0){
		if(counts>9){
			alert('每个步骤分配的番茄时间不可多于10个，多于10个应考虑进行分解');
			return false;
		}
		$.post('/index.php?cmd=tomato', $(form_obj).serialize(), function(rst){
			if(rst=='succ'){
				location.href = location.href;
			}else{alert(rst);}
		});
	}
	return false;
}
