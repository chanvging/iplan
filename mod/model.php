<?php
	class model {
		function __construct() {
			define(INT_ID, '/^\d+$/');
			define(WORD, '/^\w+$/');
			define(NOT_BLANK, '/\S/');
			define(TIME, '#^(\d+[-/.]?)+$#');
		}
		
		protected function _remove_some_field(&$args, $no_fields) {
			foreach($no_fields as $field) {
				if(isset($args[$field])) unset($args[$field]);
			}
		}
		
	function _safe_field($field) {
		$dangers = array("'", '"', '$', ';', '\\');
		$safes = array('’', '”', '-USD-', '；', '\\\\');
		return trim(str_replace($dangers, $safes, $field));
	}

	protected function _check_required_field(&$args) {
		foreach($this->not_blank_fields as $field) {
			if(!isset($args[$field]) or $args[$field]==$this->fields[$field][1]) return false;
		}
		return true;
	}
		
		protected function _get_add_field_data(&$data) {
			$args = array();
			foreach($this->fields as $field=>$regex) {
				($tmp=$data[$field]) and (empty($regex) or preg_match($regex, $tmp)) and $args[$field]=$this->_safe_field($tmp);
			}
			return $args;
		}
		
		protected function _get_edit_field_data(&$data) {
			$args = array();
			$keys = array_keys($data);
			foreach($this->fields as $field=>$regex) {
				if(!in_array($field, $keys)) continue;
				$tmp = $data[$field];
				if((empty($tmp) and !in_array($field, $this->not_blank_fields)) or (!empty($tmp) and preg_match($regex, $tmp))) $args[$field] = $this->_safe_field($tmp);
			}
			return $args;
		}
		
		function convert_to_localtime($time, $format='Y-m-d H:i:s', $timezone='Asia/Shanghai'){
			$dt_obj = new DateTime($time, new DateTimeZone('UTC'));
			$dt_obj->setTimezone(new DateTimeZone($timezone));
			return $dt_obj->format($format);
		}
	}
