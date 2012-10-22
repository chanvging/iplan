<?php
class userctl extends control{
	function get_xiaonei_user_data(){

/*	
	$file = fopen('user_data.txt', 'a');
	foreach($_REQUEST as $k=>$v){
		fwrite($file, "$k => $v\n");
	}
	fwrite($file, "\n");
	$sig = $this->get_sig($_REQUEST);
	fwrite($file, $sig);
	fclose($file);
*/	
		global $status;
		if($this->get_sig($_REQUEST)!=$_REQUEST['xn_sig']){die('error');}
		if(!empty($_REQUEST['xn_sig_user'])){
			$status = 'got_data';
			$_REQUEST['user'] = $_REQUEST['xn_sig_user'];
			$_REQUEST['passwd'] = 'xn_'.time();
			return;
		}
	}
	
	function check_xn_app_add(){
		global $status;
		$status = $_REQUEST['xn_sig_added'] ? 'yes' : 'no';
		//die($status);
	}
	
	private function get_sig($parms){
		$tmp = '';
		ksort($parms);
		foreach($parms as $k=>$v){
			if(preg_match('/^xn_sig_(\w+)$/', $k, $match)){
				$tmp .= "{$match[1]}=$v";
			}
		}
		$tmp .= '0cfc608d57aa4ce48d0481f3eec5d424';
		return md5($tmp);
	}
	
	function go_to_login($args){
		global $status;
		$key = $args['key'];
		if(empty($key)){
			$status = 'no_key';
			return;
		}else{
			$status = 'url_ready';
			return array('url'=>'index.php?cmd=login&ajax&key='.$key);
		}
	}
	
	function using_demo_user($args){
		global $status;
		$status = 'demo_user';
		return array('user'=>'demo', 'passwd'=>'test');
	}
	
}