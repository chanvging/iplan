<!DOCTYPE html>
<html>

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title>我的计划</title>
<link type="text/css" href="themes/css/css.css" rel="stylesheet" />	
<!--<link type="text/css" href="myjs/tinybox.css" rel="stylesheet" />	-->
<link type="text/css" href="myjs/bootstrap/css/bootstrap.min.css" rel="stylesheet" />
<link type="text/css" href="myjs/bootstrap/css/bootstrap-responsive.min.css" rel="stylesheet" />


	<style>
		a{text-decoration:none;color:#0192b8;}
		.time{float:right;color:gray;}
		#newplan{display:block;}
	</style>


</head>	
	
<body>
<?php include('header.html'); ?>

<div class="navbar navbar-fixed-top">
	<div class="navbar-inner">
		<div class="container">
			<a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</a>
			<a class="brand" id="logo" href="javascript:;">iPlan<span class="beta">beta</span></a>
			<div class="btn-group pull-right">
				<a class="btn dropdown-toggle" data-toggle="dropdown" href="#">
					<i class="icon-user"></i> <?php echo $_SESSION['user']; ?> <span class="caret"></span>
				</a>
				<ul class="dropdown-menu">
					<li><a href="index.php?cmd=logout">Logout</a></li>
				</ul>
			</div>
			
			<div class="nav-collapse">
				<ul class="nav">
                    <!--
					<li><a href="/">我的计划</a></li>
					<li><a href="index.php?cmd=tongji">番茄时间统计</a></li>
                    -->
                    <li><a href="index.php?cmd=schedule">schedule</a></li>
				</ul>
			</div>
			
		</div>
	</div>
</div>


<div class="container">
	<div class="row">
		<div class="span3">
			<div class="well sidebar-nav">
				<ul id="plan_cates" class="nav nav-list">
					<li><a cate="running" href="top">所有计划</a></li>
					<li><a cate="todolist" href="index.php?cmd=todo_list">To Do List</a></li>
				<!--<li><a cate="finished" href="cmplan">已经完成的计划</a></li>
					<li><a cate="all" href="all">所有的计划</a></li>-->
				</ul>		
			</div>
		</div>
		<div class="span7">
			<div class="well">
				<ul class="nav nav-pills nav-stacked" id="planlist"><li></li></ul>
			</div>
		</div>
		<div class="span2">
			<a href="#add_plan_dialog" class="btn btn-success btn-large" data-toggle="modal" id="newplan">制定新计划</a>
		</div>
	</div>	
</div>

<div class="modal hide fade in" id="add_plan_dialog">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal">×</button>
		<h3>勇敢的骚年，写下你伟大的计划吧！</h3>
	</div>
	<div class="modal-body">
		<input id="plan_name" type="text" placeholder="我的计划是..."/>
	</div>
	<div class="modal-footer">
		<a href="#" class="btn btn-primary" onclick="return addplan($('#plan_name').val());">Save Plan</a>
	</div>
</div>

<script type="text/javascript" src="myjs/jquery-1.7.2.min.js"></script>
<script type="text/javascript" src="myjs/mydate.js"></script>
<script type="text/javascript" src="myjs/chen_content.js"></script>
<!--<script type="text/javascript" src="myjs/tinybox.js"></script>-->
<script type="text/javascript" src="myjs/bootstrap/js/bootstrap.min.js"></script>
	<script type="text/javascript">
		var submitting = false;
		function addplan(plan_name){
			if(typeof(plan_name)=='undefined' || plan_name==''){
				alert('写点东西吧！');
				return false;
			}
			if(submitting) return false;
			submitting = true;
			$.post('index.php?cmd=addplan', {name:plan_name}, function(rst){
				if(rst=='succ'){
					location.href=location.href;
				}else{
					alert(rst);
					submitting = false;
				}
			});
			return false;
		}
		
		//var temp_mb = '<li><span class="time">#start#</span>[#status#] <a href="index.php?cmd=showplan&id=#id#">#name#</a></li>';
		var temp_mb = '<li><a href="index.php?cmd=showplan&id=#id#">#name#</a></li>';
		var chen_option = {
			'running':{url:'index.php?cmd=top',boxid:'planlist',mbstr:temp_mb,count:50},
			'finished':{url:'index.php?cmd=cmplan',boxid:'planlist',mbstr:temp_mb,count:20},
			'all':{url:'index.php?cmd=all',boxid:'planlist',mbstr:temp_mb,count:50}
		}
		
		$(document).ready(function(){

			var now = '<?php echo date('Y-m-d'); ?>';
/*
			$('#newplan').click(function(){
				var form_html = '<form onsubmit="return addplan(this);"><p style="margin:5px;">输入你的计划:</p><input type="text" name="name"/><input type="submit" value="OK"/></form>';
				TINY.box.show(form_html,0,0,0,1);
				return false;
			});
*/			
			$('#plan_cates li a').click(function(){
				var cate = $(this).attr('cate');
				if(typeof(cate)=='undefined' || !cate) return true;
				if(cate=='todolist'){
					if(chen_pack.cur_status=='todolist') return false;
					$.get('index.php?cmd=todo_list', function(list){
						var html = '';
						var len = list.length;
						var last = 0;
						for(var i=0; i<len; i++){
							if(last!=list[i]['planid']){
								html += '<h3><a href="index.php?cmd=showplan&id='+list[i]['planid']+'">'+list[i]['name']+'</a></h3>';
								last = list[i]['planid'];
							}
							html += '<p><a href="index.php?cmd=showplan&id='+list[i]['planid']+'#'+list[i]['stepid']+'">'+list[i]['action']+'</p>';
						}
						$('#planlist').html(html);
						chen_pack.cur_status = 'todolist';
					}, 'json');
				}else if(typeof(chen_option[cate])!='undefined'){
					chen_content(cate);
				}
				return false;
			});
			
			$('#plan_cates li:first a').click();
			
		});
	</script>

</body>
</html>
