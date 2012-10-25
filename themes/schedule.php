<!DOCTYPE html>
<html>

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title>我的计划</title>
	<link type="text/css" href="themes/css/css.css" rel="stylesheet" />	
	<link type="text/css" href="myjs/bootstrap/css/bootstrap.min.css" rel="stylesheet" />
	<link type="text/css" href="myjs/bootstrap/css/bootstrap-responsive.min.css" rel="stylesheet" />
	<style>
	.table td{vertical-align: middle;}
	</style>
</head>

<body>

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
                    <li><a href="index.php?cmd=schedule">schedule</a></li>
				</ul>
			</div>
			
		</div>
	</div>
</div>

<div class="container">
	<div class="row">
		<div class="span8 offset2">
			<table class="table table-hover">
<?php foreach($tpl_data as $plan): ?>
				<tr>
					<td><a href="index.php?cmd=showplan&id=<?php echo $plan['id']; ?>"><?php echo $plan['name']; ?></a></td>
					<td><?php echo $plan['sche_info']['eaten'] . '/' . $plan['sche_info']['tomatos']; ?></td>
					<td><?php if($plan['sche_info']['unit']==0): ?>周<?php else: ?>天<?php endif; ?></td>
					<td>还有<?php echo $plan['sche_info']['left_days']; ?>天</td>
					<td><a class="btn btn-small btn-danger" href="index.php?cmd=del_schedule&id=<?php echo $plan['id']; ?>">删除</a></td>
				</tr>
<?php endforeach; ?>
			</table>	
		</div>
	</div>
</div>

<script type="text/javascript" src="myjs/jquery-1.7.2.min.js"></script>
<script type="text/javascript" src="myjs/bootstrap/js/bootstrap.min.js"></script>

</body>
</html>
