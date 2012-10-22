<?php
class alert extends model{
	function __construct(){
		parent::__construct();
		$this->fields = array(
			'id'=>INT_ID,
			'userid'=>INT_ID,
			'planid'=>INT_ID,
			'msg'=>'',
			'time'=>'/^[^ ]+ [\d:]+$/',
		);
		$this->not_blank_fields = array('userid', 'msg', 'time');
	}
	
	function add($args){
		global $status;
		$data = $this->_get_add_field_data($args);
		if(! $this->_check_required_field($data) ) {die('some required fields lacked');};
		$this->_remove_some_field($data, array('id'));
		//print_r($data);die;
		$id = toolkit('db')->add('alert', $data);
		$status = 'alert_added';
		return array('alertid'=>$id);
	}
	
	function del($args){
		global $status;
		$alertid = empty($args['alertid']) ? $args['id'] : $args['alertid'];
		$alertid = intval($alertid);
		$userid = intval($args['userid']);
		if($alertid<=0 or $userid<=0){
			$status = 'no_id';
			return;
		}
		toolkit('db')->del('alert', array('id'=>$alertid, 'userid'=>$userid));
		$status = 'del_ok';
		return;
	}
	
	function get_alerts($args){
		global $status;
		$userid = intval($args['userid']);
		if($userid<=0){
			$status = 'no_id';
			return;
		}
		$alerts = toolkit('db')->getrows('alert', array('userid'=>$userid));
		if(empty($alerts)){
			$status = 'no_alert';
			return;
		}else{
			$status = 'got';
			return $alerts;
		}
	}
}
?>