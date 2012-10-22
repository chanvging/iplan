<!DOCTYPE html>
<html>

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title>scheduled plans</title>
</head>
<body>

    <ul>
<?php foreach($tpl_data as $plan): ?>
        <li>
            <a href="index.php?cmd=showplan&id=<?php echo $plan['id']; ?>"><?php echo $plan['name']; ?></a>
            <?php if($plan['sche_info']['unit']==0): ?>周<?php else: ?>天<?php endif; ?> <?php echo $plan['sche_info']['eaten'] . '/' . $plan['sche_info']['tomatos']; ?>
            还有<?php echo $plan['sche_info']['left_days']; ?>天
            <a href="index.php?cmd=del_schedule&id=<?php echo $plan['id']; ?>">删除</a>
        </li>
<?php endforeach; ?>
    </ul>

</body>
</html>
