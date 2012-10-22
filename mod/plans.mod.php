<?php

class plans extends control {

	function __construct() {$this->plans();}

	function plans() {
	}

	function addplan(){
		global $status;
		if(IS_POST) {
			session_start();
			$owner = $_SESSION['id'];
			if($owner<=0){die('you havnt login?');}
			$args = array_merge($_POST, $_GET);
			$args['owner'] = $owner;
			$status = 'post';
			return $args;
		} else {
			$status = 'not_post';
			return array('is_post'=>false); //false stands for not post
		}
	}
	
	function is_sub_plan($args){
		global $status;
		if(empty($_POST['parid']) or empty($_POST['stepid'])) $status = 'not_sub_plan';
		else $status = 'sub_plan';
		return array_merge($args, array('parid'=>$_POST['parid'], 'stepid'=>$_POST['stepid']));
	}

	function editplan() {
		global $status;
		if(IS_POST) {
			$args = array_merge($_POST, $_GET);
			$status = 'data_checked';
			return $args;
		}
	}

	function dropplan(){
		global $status;
		$id = $this->_get_id();
		if($id==-1) die('no id proved');
		$status = 'get_plan_data';
		return array('id'=>$id);
	}
	
	function getplan() {
		$id = $this->_get_id();
		if($id==-1) die('no id proved');
		$this->showplan($id);
	}
	
	function showplan($id) {
		$info = toolkit('plan')->get_plan_info($id);
		if(empty($info)) {die('no plan found');}
		$this->_remove_null_field($info);
		toolkit('planview')->showplan($info);
	}

	function getplans(){
/*		$userid = $this->_get_id('userid');
		$info = toolkit('plan')->get_all_plans($userid);
		toolkit('planview')->show_plan_list($info); */
		foreach(array(11,12,13) as $id) {
			$this->showplan($id);
		}
	}
	
	function get_plan_summary(){
		global $status;
		$planid = $this->_get_id('planid');
		$summary = $_REQUEST['summary'];
		$status = 'got_summ';
		return array('planid'=>$planid, 'summary'=>$summary);
	}
	
	function get_export_type($args){
		global $status;
		$type = trim($_REQUEST['type']);
		if(empty($type)){
			$status = 'notype';
		}else{
			$status = $type;
		}
		return $args;
	}
	
	function get_tongji(){
		session_start();
		global $status;
		if(empty($_SESSION['id'])){
			$status = 'not_login';
			return;
		}
		$from_date = empty($_REQUEST['from']) ? date('Y-m-d H:i:s', strtotime('today')) : date('Y-m-d H:i:s', strtotime($_REQUEST['from']));
		$to_date = empty($_REQUEST['to']) ? date('Y-m-d H:i:s') : date('Y-m-d H:i:s', strtotime($_REQUEST['to']));
		$status = 'got_data';
		return array('from_date'=>$from_date, 'to_date'=>$to_date, 'userid'=>$_SESSION['id']);
	}

}

?>
