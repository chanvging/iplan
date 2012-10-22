<?php
class planview extends view {
	
	function addplan($args) {
		$is_post = $args['is_post'];
		if($is_post) {
			$this->smarty->display('addplan_ok.html');
		} else {
			$this->smarty->display('addplan_form.html');
		}
	}
	
	function editplan($info) {
		$this->_assign_all_data($info);
		$this->smarty->display('addplan_form.html');
	}
	
	function dropplan() {
		echo 'deleted';
	}
	
	function showplan($info) {
		global $status;
		$this->_assign_all_data($info);
		$this->smarty->display('showplan.html');
		$status = 'show_over';
	}
	
	function showplans($args){
		global $status;
//		$args['tpl'] = 'running_plans.html';
		$status = 'tpl_ready';
		return array('tpl'=>'running_plans.html', 'plans'=>$args);
	}
	
	function show_complete_plan($args){
		global $status;
		$args['tpl'] = 'complete_plan.html';
		$status = 'tpl_ready';
		return $args;
	}
	
	function show_combinable_plans($args){
		$json = $fuhao = '';
		foreach($args as $plan){
			$json .= $fuhao . '["'.$plan['id'].'", "'.$plan['name'].'"]';
			$fuhao = ',';
		}
		$json = "[$json]";
		echo $json;
	}
	
	function show_complete_plans($args){
		global $status;
//		$args['tpl'] = 'complete_plans.html';
		$status = 'tpl_ready';
		return array('tpl'=>'complete_plans.html', 'plans'=>$args);
	}
}
?>