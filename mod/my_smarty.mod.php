<?php
	require('libs/Smarty.class.php');
	class my_smarty extends Smarty {
		function __construct() {
			$this->template_dir = 'themes';
			$this->compile_dir = 'temp/smarty_compile';
		}
	}
?>