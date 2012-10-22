<?php

class db {
	public $last_id = 0;
	protected $db;
	function __construct() {
		$this->db();
	}
	
	function db() {
		$this->db_host = 'localhost';
		$this->db_user = 'root';
		$this->db_passwd = '';
		$this->db_name = 'iplan';
		
		$this->db = $this->connect_db();
		if(! $this->db) die('error on connect database');
		
		$this->db->query('set names utf8');
		
	}
	
	function connect_db(){
		return new mysqli($this->db_host, $this->db_user, $this->db_passwd, $this->db_name);
	}
	
	function add($table, $data, $replace=false) {
		$insert_stat = $this->_get_insert_info($data);
		$insert = $replace ? 'REPLACE' : 'INSERT';
		$stat = "$insert INTO $table $insert_stat";
		$this->db->query($stat) or die("error check:$stat");
		$this->affected_rows = $this->db->affected_rows;
		$this->last_id = $this->db->insert_id;
		return $this->last_id;
	}
	
	function edit($table, $data, $condi) {
		if(is_array($data)){
			$update_stat = $this->_get_insert_info($data);
		}else{
			$update_stat = 'SET '.$data;
		}
		$condi_stat = $this->_get_condition_info($condi);
		$stat = "UPDATE $table $update_stat $condi_stat";
		$this->db->query($stat) or die("error check:$stat");
		$this->affected_rows = $this->db->affected_rows;
	}
	
	function del($table, $condi) {
		$condi_stat = $this->_get_condition_info($condi);
		$stat = "delete from $table $condi_stat";
		$this->db->query($stat) or die("error check:$stat");
		$this->affected_rows = $this->db->affected_rows;
	}
	
	function getrows($table, $condi='', $fields=array(), $extra='', $orderby='') {
		$field_stat = $this->_get_fields($fields);
		if(!empty($condi)){
			if(is_array($condi)) $condi_stat = $this->_get_condition_info($condi);
			else $condi_stat = 'where '.$condi;
		}
		if($extra != '') {
			if($condi_stat != '') {$condi_stat .= ' and '.$extra;}
			else {$condi_stat = "where $extra";}
		}
		if($orderby!=''){
			$orderby = ' ORDER BY '.$orderby;
		}
		$stat = "SELECT $field_stat FROM $table $condi_stat $orderby";
		$rst = $this->db->query($stat) or die("error check:$stat");
		if($rst->num_rows==0) return array();
		$this->rows = array();
		while($row = $rst->fetch_array(MYSQLI_ASSOC)) {
			$this->rows[] = $row;
		}
		$rst->close();
		return $this->rows;
	}
	
	function get1row($table, $condi=array(), $fields=array(), $extra='') {
		$field_stat = $this->_get_fields($fields);
		$condi_stat = $this->_get_condition_info($condi);
		if($extra != '') {
			if($condi_stat != '') {$condi_stat .= ' and '.$extra;}
			else {$condi_stat = "where $extra";}
		}
		$stat = "SELECT $field_stat FROM $table $condi_stat limit 1";
		$rst = $this->db->query($stat) or die("error check:$stat");
		if($rst->num_rows==0) return array();
		$this->row = $rst->fetch_array(MYSQLI_ASSOC);
		return $this->row;
	}
	
	function get_count($table, $condi=''){
		if(empty($condi)) return 0;
		if(is_array($condi)){
			$condi_stat = $this->_get_condition_info($condi);
		}
		$stat = "select count(*) from $table $condi_stat";
		$rst = $this->db->query($stat) or die("error check:$stat");
		$row = $rst->fetch_array();
		return $row[0];
	}
	
	function _get_fields(&$fields) {
		if(empty($fields)) return '*';
		$fuhao=$stat='';
		foreach($fields as $field) {
			$stat .= $fuhao.$field;
			$fuhao = ',';
		}
		return $stat;
	}
	
	function _get_insert_info(&$data) {
		$fuhao = ' set ';
		$stat = '';
		foreach($data as $k=>$v) {
			$stat .= $fuhao." $k='$v' ";
			$fuhao = ',';
		}
		return $stat;
	}
	
	function _get_condition_info(&$data) {
		$fuhao = ' where ';
		$stat = '';
		foreach($data as $k=>$v) {
/*			if($v==''){
				$stat .= $fuhao." $k=null ";
				$fuhao = ' and ';
				continue;
			}*/
			$stat .= $fuhao." $k='$v' ";
			$fuhao = ' and ';
		}
		return $stat;
	}
	
	function get_data($sql){
		$rst = $this->db->query($sql) or die("error check:$sql");
		if($rst->num_rows==0) return array();
		$this->rows = array();
		while($row = $rst->fetch_array(MYSQLI_ASSOC)) {
			$this->rows[] = $row;
		}
		$rst->close();
		return $this->rows;
	}
	
	function close(){
		$this->db->close();
	}
}

?>
