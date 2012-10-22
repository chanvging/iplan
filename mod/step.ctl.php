<?php
	class stepctl extends control {
		
		function addstep() {
			global $status;
			$rtn = array_merge($_POST, $_GET);
			//if(!isset($rtn['id'])){$rtn['id'] = $rtn['planid'];}
			$status = 'post';
			return $rtn;
		}
		
		function test_add() {
			global $status;
			if(IS_POST) {
				$args = array(
					'planid'=>'17',
					'preid'=>'',
					'action'=>'ased',
					'percent'=>'50'
				);
				$status = 'post';
				return $args;
			} else {
				$status = 'not_post';
				return;
			}
		}
		
		function editstep() {
			global $status;
			if(IS_POST) {
				$status = 'post';
				return array_merge($_POST, $_GET);
			} else {
				$status = 'not_post';
				return;
			}
		}
		
		function editable_info(){
			global $status;
			list($field, $id, $planid) = explode('_', $_POST['id']);
			if(empty($field) or empty($id)){
				$status = 'error';
				return;
			}else{
				$status = 'got_info';
				if($field=='step'){
					return array('id'=>$id, 'planid'=>$planid, 'action'=>$_POST['value'], 'field'=>'action');
				}
				return array('id'=>$id, 'planid'=>$planid, $field=>$_POST['value'], 'field'=>$field, $field.'_time'=>date('Y-m-d H:i:s'));
			}
		}
		
		function delstep() {
			global $status;
			$id = $this->_get_id();
			$id==-1 and die('error');
			$status = 'got_step_id';
			return array('id'=>$id);
		}
		
		function get_summary(){
			global $status;
			$id = $this->_get_id('stepid');
			$planid = $this->_get_id('planid');
			$summary = $_REQUEST['summary'];
			if(empty($id) or empty($summary)) $status = 'error';
			else $status = 'summary_ok';
			return array('id'=>$id, 'planid'=>$planid, 'status'=>'FINISH', 'summary'=>$summary, 'summary_time'=>date('Y-m-d H:i:s'));
		}
		
		function get_tomatos(){
			global $status;
			$stepid  = $this->_get_id('stepid');
			$tomatos = $this->_get_id('tomatos');
			if($tomatos<=0 or $stepid<=0){
				$status = 'error';
			}else{
				$status = 'got_tomatos';
				return array('stepid'=>$stepid, 'tomatos'=>$tomatos);
			}
		}
		
		function get_timer_data(){
			global $status;
			$stepid  = $this->_get_id('stepid');
			$act = trim($_REQUEST['act']);
			if($stepid<=0){
				$status = 'error';
				return;
			}
			if($act!='start' and $act!='stop'){
				$status = 'wrong_cmd';
				return;
			}
			$status = 'succ';
			return array('stepid'=>$stepid, 'act'=>$act);
		}
	}
?>
