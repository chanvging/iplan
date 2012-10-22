$(document).ready(function(){
	var timer_obj = $('#tomato_timer');
	var timer_plan = timer_obj.attr('planid');
	var timer_step = timer_obj.attr('stepid');
	if(timer_plan>0 && timer_step>0){
		var timer_startime = timer_obj.attr('startime');
		var intval = 1500 - ( now - Date.parse(timer_startime.replace(/-/g, '/'))/1000 );
		window.timer_data = [Math.floor(intval/60), intval%60];
		if(intval>0){
			$('#timming').countdown({
			  image: '/themes/pic/digits.png',
			  startTime: (timer_data[0]<10?'0':'')+timer_data[0]+':'+(timer_data[1]<10?'0':'')+timer_data[1],
			  format: 'mm:ss',
			  timerEnd: function(){alert('番茄时间到！休息一下吧！');},
			});
		}
		$('a.tomato_alert[stepid='+timer_step+']').attr('title', '停止').css('background-image', 'url("/themes/pic/red_alert.png")')
	}
	
	$('.tomato_time').each(function(){
		var times_str = $(this).attr('tomato_time');
		var times = times_str.split(' ');
		if(times.length<=0) return;
		$(this).find('a.red_tomato').css('width', times[0]*16 + 'px');
		$(this).find('a.green_tomato').css('width', times[1]*16 + 'px');
		var checked = $('<img src="/themes/pic/eaten.png"/>');
		for(var i=0; i<times[2]; i++){
			$(this).append(checked.clone());
		}
	});
	
	$('.tomato_alert').click(function(){
		var stepid = $(this).attr('stepid');
		var alert_stepid =  $('#tomato_timer').attr('stepid');
		var cmd = 'start';
		if(typeof(alert_stepid)!='undefined' && stepid==alert_stepid){
			cmd = 'stop';
		}
		$.get('index.php?cmd=tomato_timer&act='+cmd+'&stepid='+ $(this).attr('stepid') , function(rst){
			if(rst=='succ'){
				location.href = location.href;
			}else{
				alert(rst);
			}
		});
	});
	
cur_tomato_count = 0;
tomatos = [];
	var tomato_obj = $('<a href="javascript:;" tomato="0"></a>');
	var tomato_board = $('#tomato_board');
	for(var i=1; i<10; i++){
		tomatos[i] = tomato_obj.clone().attr('tomato', i);
		tomatos[i].hover(function(){
			var tomato_count = $(this).attr('tomato');
			if(cur_tomato_count==tomato_count) return false;
			if(cur_tomato_count>tomato_count){
				for(var j=tomato_count+1; j<=cur_tomato_count; j++){
					tomatos[j].removeClass('tomato_active');
				}
			}else{
				for(var j=cur_tomato_count+1; j<=tomato_count; j++){
					tomatos[j].addClass('tomato_active');
				}
			}
			cur_tomato_count = tomato_count;
			$('#tomato_timer_count').text(cur_tomato_count);
		}, function(){
				for(var j=1; j<=cur_tomato_count; j++){
					tomatos[j].removeClass('tomato_active');
				}
				cur_tomato_count = 0;
		});
		tomatos[i].click(function(){
			var tomato_count = $(this).attr('tomato');
			if(tomato_count>0){
				$.post('/index.php?cmd=tomato', {stepid:context_menu.stepid, tomatos:tomato_count}, function(rst){
					if(rst=='succ'){
						location.href = location.href;
					}else{alert(rst);}
				});
			}
			return false;
		});
		tomatos[i].appendTo(tomato_board);
	}
	
	var eaten_tomatos = $('#tomato_timer').attr('today_tomato');
	var timing_plan_id = $('#tomato_timer').attr('planid');
	var timing_step_id = $('#tomato_timer').attr('stepid');

	if(eaten_tomatos>0 || timing_step_id>0){
		$('#tomato_timer').show();
		for(var i=0; i<eaten_tomatos; i++){
			$('#tomato_timer').append('<a href="javascript:;" class="tomato_count_show"></a>');
		}
		if(timing_step_id>0){
			var timing_alarm = $('<a href="javascript:;"></a>').addClass('tomato_count_show').css('background-image', 'url(/themes/pic/red_alert32.png)');
			if(timing_plan_id==the_plan_id){
				timing_alarm.click(function(){
					$('a.tomato_alert[stepid='+timing_step_id+']').click();
				}).hover(function(){
					$('a.tomato_alert[stepid='+timing_step_id+']').show();
				}, function(){
					$('a.tomato_alert[stepid='+timing_step_id+']').hide();
				});
			}else{
				timing_alarm.click(function(){
					window.location.href = '/index.php?cmd=showplan&id='+timing_plan_id;
				});
			}
			timing_alarm.appendTo('#tomato_timer');
		}
	}
	
});
