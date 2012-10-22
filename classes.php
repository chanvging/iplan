<?php
	
	//类的相关信息，包括定义的文件及其父类
	$classes = array(
		'plan'=>array('filename'=>'plan.mod.php', 'parent'=>'model'),
		'plans'=>array('filename'=>'plans.mod.php', 'parent'=>'control'),
		'planview'=>array('filename'=>'planview.view.php', 'parent'=>'view'),
		'stepctl'=>array('filename'=>'step.ctl.php', 'parent'=>'control'),
		'step'=>array('filename'=>'step.mod.php', 'parent'=>'model'),
		'stepview'=>array('filename'=>'step.view.php', 'parent'=>'view'),
		'user'=>array('filename'=>'user.mod.php', 'parent'=>'model'),
		'userctl'=>array('filename'=>'user.ctl.php', 'parent'=>'control'),
		'model'=>array('filename'=>'model.php'),
		'control'=>array('filename'=>'control.php'),
		'view'=>array('filename'=>'view.php'),
		'db'=>array('filename'=>'db.mod.php'),
		'minupage'=>array('filename'=>'page.class.php'),
		'my_smarty'=>array('filename'=>'my_smarty.mod.php'),
		'phppt'=>array('filename'=>'phppt.mod.php'),
		'alert'=>array('filename'=>'alert.mod.php', 'parent'=>'model'),
		'alertctl'=>array('filename'=>'alert.ctl.php', 'parent'=>'control'),
		'db_cache'=>array('filename'=>'db_cache.mod.php', 'parent'=>'model'),
	);
	
	//转换器，用于对象的替换
	$switcher = array(
		'smarty' => 'my_smarty', 
	);
?>
