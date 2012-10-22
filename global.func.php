<?php

	define('IS_POST', (strtoupper($_SERVER['REQUEST_METHOD']) == 'POST'));
	
	$tools = array();
	$loadfiles = array();
	
	include 'classes.php';
	
	function toolkit($tool) {
		$GLOBALS['switcher'][$tool] ? $tool=$GLOBALS['switcher'][$tool] : 1;
		if( $GLOBALS['tools'][$tool] != null) return $GLOBALS['tools'][$tool];
		
		load_required_files($tool);
		
/*		$parent = $tool;
		while(!empty($parent) && ($filename=$GLOBALS['classes'][$parent]['filename']) && !in_array($filename, $GLOBALS['loadfiles']) ) {
			$file = MODROOT.$filename;
			if(!file_exists($file)) {die('no model file found');}
			require $file; echo $file;
			$GLOBALS['loadfiles'][]=$filename;
			$parent = $GLOBALS['classes'][$parent]['parent'];			
		}
*/		
		$arg_count = 1; //本函数使用的参数个数.
		if(func_num_args() > $arg_count) {
			$args = array_slice(func_get_args(),$arg_count);
			$obj = new $tool($args);
		} else {
			$obj = new $tool();
		}
		$GLOBALS['tools'][$tool] =& $obj;
		return $GLOBALS['tools'][$tool];
	}
	
	function load_required_files($tool) {
		$info = $GLOBALS['classes'][$tool];
		empty($info) and die('no model for'.$tool);
		if(in_array($info['filename'], $GLOBALS['loadfiles'])) return;
		empty($info['parent']) or load_required_files($info['parent']);
		
		$file = MODROOT.$info['filename'];
		if(!file_exists($file)) {die('no model file found');}
		require $file;
		$GLOBALS['loadfiles'][]=$info['filename'];
	}
    
    function in_same_unit($time, $unit){
        switch($unit){
            case '0'://week
                return date('W', $time)==date('W');
                break;
            case '1'://day
                return date('z', $time)==date('z');
                break;
            default :
                return false;
        }
    }