<?php
class view {
	
/*
	protected $smarty;
	function __construct() {
		$this->smarty =& toolkit('smarty');
	}

	protected function _assign_all_data($args) {
		is_array($args) or die('assigning data error: in planview.view.php');
		foreach($args as $k=>$v) {
			$this->smarty->assign($k, $v);
		}
	}
*/
	function display($args){
		if(empty($args['tpl'])){
			die('there is no tpl val, the view model cant display');
		}
		$tpl = $args['tpl'];
		unset($args['tpl']);
		$file = THEMEROOT.'/'.$tpl.'.php';
		if(!file_exists($file)){
			die('the tpl file is not exist in themes folder');
		}else{
			$tpl_data = $args;
			include($file);
		}
		/*
		$this->_assign_all_data($args);
		$this->smarty->display($tpl);
		*/
	}
	
	function redirect($args){
		global $status;
		$url = $args['url'];
		if(empty($url) and !empty($args['id'])){
			$url = '/index.php?cmd=showplan&id='.$args['id'];
		}
		header("Location: $url");
		//echo "<script>location.href='$url';</script>";
		//die;
	}
	
	function p3p_header($args){
		global $status;
		header("P3P:CP=CAO PSA OUR");
		$status = 'ok';
	}
	
	function alert($args){
		$msg = $args['msg'];
		empty($msg) and die('error: not a valid yaml file for this system');
		echo "<script>alert('$msg');</script>";
		die;
	}
	
	function dump_data($args){
		var_dump($args);
	}
	
	function my_echo($args){
		echo $args['msg'];
	}
	
	function echo_json($args){
		echo json_encode($args);
	}

}
?>
