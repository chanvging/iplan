<!DOCTYPE html>
<html>

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title>tongji</title>
</head>
<body>

<ul>
<?php foreach($tpl_data['tongji'] as $plan_tj): ?>
<li><a href="index.php?cmd=showplan&id=<?php echo $plan_tj['id']; ?>"><?php echo $plan_tj['name']; ?></a> <span><?php echo $plan_tj['count']; ?></span> <span><?php echo $plan_tj['percent']; ?></span></li>
<?php endforeach; ?>
</ul>

</body>
</html>
