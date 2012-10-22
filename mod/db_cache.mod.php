<?php
	class db_cache extends model {

		function __construct() {
			parent::__construct();
		}
		
		function get($key) {
			if(is_null($key)) return false;
			$data = toolkit('db')->get1row('kv', array('k'=>$key));
			if(empty($data)) return false;
			if($data['type']=='array' || $data['type']=='object'){
				$data['v'] = unserialize($data['v']);
			}elseif($data['type']=='integer'){
				$data['v'] = intval($data['v']);
			}
			return $data['v'];
		}
		
		function set($key, $value){
			if(is_null($key) || is_null($value)) return false;
			$type = gettype($value);
			if($type=="NULL" || $type=="unknown type") return false;
			if($type=='array' || $type=='object'){
				$value = serialize($value);
			}
			toolkit('db')->add('kv', array('k'=>$key, 'v'=>$value, 'type'=>$type), true);
		}
        
        function del($key){
            if(is_null($key)) return FALSE;
            toolkit('db')->del('kv', array('k'=>$key));
        }
		
	}//end of class

//end of php file
