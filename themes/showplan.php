<!DOCTYPE HTML>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<link type="text/css" href="themes/css/css.css" rel="stylesheet" />	
<link type="text/css" href="myjs/jeegoocontext.css" rel="stylesheet" />	
<link type="text/css" href="myjs/jeegoocontext-skin/style.css" rel="stylesheet" />	
<!--<link type="text/css" href="myjs/tinybox.css" rel="stylesheet" />-->
<link type="text/css" href="myjs/bootstrap/css/bootstrap.min.css" rel="stylesheet" />
<link type="text/css" href="myjs/bootstrap/css/bootstrap-responsive.min.css" rel="stylesheet" />
<link type="text/css" href="themes/css/showplan.css" rel="stylesheet" />	

<script type="text/javascript">
	var the_plan_id = '<?php echo $tpl_data['id']; ?>';
	var the_par_id = '<?php echo $tpl_data['par_plan']; ?>';
	var now = <?php echo time(); ?>;
	var submitting = false;
	var insert_after_step = '';
	var context_menu = {};
</script>

<title><?php echo $tpl_data['name']; ?></title>
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
					<li><a href="index.php?cmd=schedule">My Schedule</a></li>
					<li><a href="index.php?cmd=logout">Logout</a></li>
				</ul>
			</div>
			<div class="nav-collapse">
				<ul class="nav">
					<li><a href="/">我的计划</a></li>
<?php foreach($tpl_data['plan_path'] as $path): ?>
					<li><a href="index.php?cmd=showplan&id=<?php echo $path[0]; ?>"><?php echo $path[1]; ?></a></li>
<?php endforeach; ?>
				</ul>
			</div>
		</div>
	</div>
</div>

<div class="container">
	<div class="row">
		<div class="span3">

			<div class="well">
<p><span class="label label-success">愿景</span> <span id="target_editable"><?php echo $tpl_data['target']; ?></span></p>
<p><span class="label label-info">描述</span> <span id="desc_editable"><?php echo $tpl_data['description']; ?></span></p>
<p><span class="label">开始</span> <span id="starttime"><?php echo $tpl_data['start']; ?></span></p>
<p><span class="label label-important">截止</span> <span id="endtime"><?php echo $tpl_data['deadline']; ?></span></p>
<p><span class="label label-warning">状态</span> <span id="status"><?php echo $tpl_data['status']; ?></span></p>
			</div>
			
			<div style="display:none;" class="well" id="tomato_timer" planid="<?php echo $tpl_data['tomato_timer']['planid'];?>" stepid="<?php echo $tpl_data['tomato_timer']['stepid'];?>" startime="<?php echo $tpl_data['tomato_timer']['startime'];?>" today_tomato="<?php echo $tpl_data['tomato_timer']['counts']; ?>"></div>

<?php if($tpl_data['tomato_timer']['planid']): ?>
			<div class="well">
<div id="timming"></div>
			</div>
<?php endif; ?>

		</div>
		<div class="span9">
		
		<h1><span class="planame"><?php echo $tpl_data['name']; ?></span>
<?php if($tpl_data['par_plan']): ?>
<a href="/index.php?cmd=showplan&id=<?php echo $tpl_data['par_plan']; ?>" title="转到父计划"><img src="themes/pic/up.png" style="border:none;height:15px;"/></a>
<?php endif; ?>
		</h1>

<ol class="steps">
<?php foreach($tpl_data['steps'] as $step): ?>
	<li class="step">
	<?php if($step['subplanid']): ?>
	<span stepid="<?php echo $step['id']; ?>"><a href="index.php?cmd=showplan&id=<?php echo $step['subplanid']; ?>"><?php echo $step['action']; ?></a></span>
	<?php else: ?>
	<span class="step_editable" id="step_<?php echo $step['id'];?>_<?php echo $tpl_data['id'];?>" stepid="<?php echo $step['id']; ?>"><?php echo $step['action']; ?></span>
	<?php endif; ?>
	<?php if($step['status']!="FINISH" and $step['do_it']!='NO'): ?>
		<img class="doit" stepid="<?php echo $step['id']; ?>" height="16px" src="themes/pic/do_it/<?php echo $step['do_it']; ?>.png"/>
	<?php endif; ?>
	<a href="javascript:;" class="mol" style="display:none;" stepid="<?php echo $step['id']; ?>">+</a>
	<?php if($step['red_tomato']>0): ?>
		<?php if($step['status']!="FINISH"): ?>
		<a href="javascript:;" style="display:none;" title="开始" class="tomato_alert" stepid="<?php echo $step['id']; ?>"></a>
		<?php endif; ?>
		<div class="tomato_time" style="display:none;" tomato_time="<?php echo $step['red_tomato']; ?> <?php echo $step['green_tomato']; ?> <?php echo $step['eaten']; ?>">
			<p><a href="javascript:;" class="red_tomato"></a><a href="javascript:;" class="green_tomato"></a></p>
		</div>
	<?php endif;?>
	
	<div class="plan_content">
		<?php if($step['status']=="FINISH"): ?>
		<h4>SUMMARY: <span class="time"><?php echo $step['summary_time']; ?></span></h4><p id="summary_<?php echo $step['id']; ?>_<?php echo $tpl_data['id']; ?>" class="sm_editable"><?php echo $step['summary'];?></p>
		<?php else: ?>
		<h4>MEMO:<?php if($step['memo_time']):?> <span class="time"><?php echo $step['memo_time'];?></span><?php endif; ?></h4><p id="memo_<?php echo $step['id'];?>_<?php echo $tpl_data['id'];?>" class="memo_editable"><?php echo $step['memo'];?></p>
		<h4>DONE:<?php if($step['done_time']):?> <span class="time"><?php echo $step['done_time'];?></span><?php endif; ?></h4><p id="done_<?php echo $step['id'];?>_<?php echo $tpl_data['id'];?>" class="memo_editable"><?php echo $step['done'];?></p>
		<h4>TODO:<?php if($step['todo_time']):?> <span class="time"><?php echo $step['todo_time']; ?></span><?php endif; ?></h4><p id="todo_<?php echo $step['id'];?>_<?php echo $tpl_data['id'];?>" class="memo_editable"><?php echo $step['todo'];?></p>
		<?php endif; ?>
	</div>
	<?php if($step['status']=="FINISH"): ?><img class="finish_icon" src="/themes/pic/eaten.png"/><?php endif; ?>
	</li>
<?php endforeach; ?>

	<li id="addmore">
		<form onsubmit="return addstep(this);">
			<input type="hidden" name="planid" value="<?php echo $tpl_data['id'];?>"/>
			<input type="hidden" name="preid" value=""/>
			<input type="text" name="action"/>
		</form>
	</li>
	
</ol>

<?php if($tpl_data['status']=='COMPLETE'): ?>
<p>总结：<?php echo $tpl_data['summary'];?></p>
<?php endif; ?>
		
		</div>
	</div>
	
</div>

<ul id="contextmenu" class="jeegoocontext cm_default">
	<li id="menu_tomato">分配番茄时间</li>
	<li id="menu_subit">设为子计划</li>
	<li id="menu_addnext">下一步</li>
	<li id="menu_doit">今天做</li>
	<li id="menu_reset">重置番茄时间</li>
	<li id="menu_finish">完成</li>
	<li id="menu_redo">重做</li>
	<li id="menu_del">删除</li>
</ul>

<ul id="plan_menu" class="jeegoocontext cm_default">
	<li id="plan_step">第一步</li>
	<li>
		查看方式
		<ul>
			<!--<li id="ppt">PPT</li>-->
			<li id="html">提纲</li>
			<li id="doc">文档</li>
		</ul>
	</li>
	<li id="plan_over">完成</li>
	<li id="plan_redo">重做</li>
	<li id="plan_hide">隐藏完成</li>
	<li id="plan_del">删除</li>
	<li id="plan_schedule">Schedule</li>
</ul>

<div class="modal hide fade in" id="add_step_form">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal">×</button>
		<h3>添加步骤</h3>
	</div>
	<div class="modal-body">
		<input id="step_action" type="text" name="action"/>
	</div>
	<div class="modal-footer">
		<a href="javascript:;" onclick="return addstep($('#step_action').val());" class="btn btn-primary">Save Step</a>
	</div>
</div>

<div class="modal hide fade in" id="tomato_setup">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal">×</button>
		<h3>完成该任务需要<span id="tomato_timer_count">0</span>个番茄时间</h3>
	</div>
	<div class="modal-body">
		<div id="tomato_board"></div>
	</div>
</div>

<div class="modal hide fade in" id="schedule_form">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal">×</button>
		<h3>加入Schedule</h3>
	</div>
	<div class="modal-body">
		<form method="POST" action="index.php?cmd=add_schedule">
			<input type="hidden" name="id" value="<?php echo $tpl_data['id']; ?>"/>
			<p>每<select name="unit"><option value="0">周</option><option value="1">天</option></select>至少完成<input name="tomatos" type="number"/>个番茄时间</p>
		</form>
	</div>
	<div class="modal-footer">
		<a href="javascript:;" onclick="$('#schedule_form form').submit();" class="btn btn-primary">确定</a>
	</div>
</div>

<script type="text/javascript" src="myjs/jquery-1.7.2.min.js"></script>
<script type="text/javascript" src="myjs/jquery.jeditable.mini.js"></script>
<script type="text/javascript" src="myjs/mydate.js"></script>
<!--<script type="text/javascript" src="myjs/tinybox.js"></script>-->
<script type="text/javascript" src="myjs/jquery.jeegoocontext.min.js"></script>
<script type="text/javascript" src="myjs/jquery.countdown.min.js"></script>
<script type="text/javascript" src="myjs/myfunc.js"></script>
<script type="text/javascript" src="myjs/drag_drop.js"></script>
<script type="text/javascript" src="myjs/editable.js"></script>
<script type="text/javascript" src="myjs/tomato_timer.js"></script>
<script type="text/javascript" src="myjs/mol.js"></script>
<script type="text/javascript" src="myjs/add_more.js"></script>
<script type="text/javascript" src="myjs/context_menu.js"></script>
<script type="text/javascript" src="myjs/showplan.js"></script>
<script type="text/javascript" src="myjs/bootstrap/js/bootstrap.min.js"></script>

</body>
</html>
