<?php
class control {
		function __construct() { $this->control(); }
		function control() {
			define(INT_ID, '/^\d+$/');
			define(WORD, '/^\w+$/');
			define(NOT_BLANK, '/\S/');
		}

	function _get_field_data() {
			$args = array();
			foreach($this->fields as $field=>$info) {
				$regex = $info[0];
				$default = $info[1];
				//if(!empty($tmp=$_POST[$field]) && preg_match($regex, $_POST[$field])) $args[$field] = $_POST[$field];
				//($tmp=$_POST[$field] or $tmp=$_GET[$field]) and preg_match($regex, $tmp) and $args[$field]=$this->_safe_field($tmp);
				//($tmp=$_POST[$field] or $tmp=$_GET[$field]) and (empty($regex) or preg_match($regex, $tmp)) and $args[$field]=$this->_safe_field($tmp);
				( ($tmp=$_POST[$field] or $tmp=$_GET[$field]) and (empty($regex) or preg_match($regex, $tmp)) ) ? $args[$field]=$this->_safe_field($tmp) : $args[$field]=$default;
			}
				//$a='a';
				//$b='b';
				//($tmp=$a or $tmp=$b) and true and $abc=$tmp;
				//echo $abc;
			return $args;
	}
	
	function _safe_field($field) {
		$dangers = array("'", '"', '$', ';', '\\');
		$safes = array('’', '”', '-USD-', '；', '\\\\');
		return str_replace($dangers, $safes, $field);
	}
	
	function get_id(){
		global $status;
		$id = $this->_get_id();
		if($id==-1){
			$status='no_id';
			return;
		}else{
			$status = 'id_found';
			return array('id'=>$id);
		}
	}
	
	function get_specific_fields($args=array()){
		global $status;
		if(empty($args)){$status = 'args_blank'; return false;}
		if(!is_array($args)){$args = array($args);}
		$retn = array();
		foreach($args as $k){
			$retn[$k] = $_REQUEST[$k];
		}
		$status = 'got_value';
		return $retn;
	}
	
	function _get_id($idname='id') {
			$id = -1;
			($tmp=$_POST[$idname] or $tmp=$_GET[$idname]) and preg_match('/^\d+$/', $tmp) and $id=$tmp;
			return $id;
	}
	
	function _remove_null_field(&$info) {
		foreach($info as $k=>$v) {
			if(!isset($v)) unset($info[$k]);
		}
	}

	protected function _check_required_field(&$args) {
		foreach($this->required_fields as $field) {
			if(!isset($args[$field]) or $args[$field]==$this->fields[$field][1]) return false;
		}
		return true;
	}
	
	function check_post($args){
		global $status;
		//$status = (strtoupper($_SERVER['REQUEST_METHOD']) == 'POST') ? 'post' : 'not_post';
		if(strtoupper($_SERVER['REQUEST_METHOD']) == 'POST'){
			$status = 'post';
			return array_merge($_POST, $_GET);
		}else{
			$status = 'not_post';
		}
	}
	
	function check_ajax($args){
		global $status;
		if(isset($_REQUEST['ajax'])){
			$status = 'ajax';
			return $_REQUEST;
		}else{
			$status = 'not_ajax';
		}
	}
}
?>