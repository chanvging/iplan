<?php
class user extends model{
	
	function __construct(){
		parent::__construct();
		$this->table = 'user';
		$this->fields = array(
			'id'=>INT_ID,
			'user'=>WORD,
			'passwd'=>NOT_BLANK,
		);
		$this->not_blank_fields = array('user', 'passwd');
		$this->authority = array('read', 'write');
		$this->auth_type = array('GLOBAL','MEMBER', 'FRIENDS', 'ALL');
	}
	
	function register($args){
		global $status;
		$data = $this->_get_add_field_data($args);
		if(! $this->_check_required_field($data)){die('lack fields required');}
		//var_dump($data);die;
		$count = toolkit('db')->get_count($this->table, array('user'=>$data['user']));
		if($count != 0) {
			$status = 'user_exist';
			return $data;
		}
		$id = toolkit('db')->add($this->table, $data);
		if($id>0){
			$status = 'user_add_ok';
			$data['id'] = $id;
			return $data;
		}else{
			$status = 'user_add_error';
			return;
		}
	}
	
	function is_login(&$args){
		global $status;
		session_start();
//		echo '<br/>session'; var_dump($_SESSION);
		$status = $_SESSION['login']===true ? 'has_login' : 'not_login';
		if($status=='has_login' and !empty($_REQUEST['xn_sig_user']) and $_REQUEST['xn_sig_user']!=$_SESSION['user']){
			$status = 'not_login';
		}
		if(!empty($_SESSION['id'])){$args['login_userid'] = $_SESSION['id'];}
		return;
	}
	
	function login($args){
		global $status;
		$data = $this->_get_add_field_data($args);
		$user = $data['user'];
		$passwd = $data['passwd'];
		if(empty($user) or empty($passwd)){
			$status = 'lack_field';
			return;
		}
		$user = toolkit('db')->get1row($this->table, array('user'=>$user));
		if(empty($user)){
			$status = 'no_user';
			return;
		}else if($user['passwd']==$passwd){
			$status = 'check_ok';
			return $user;
		}else{
			$status = 'check_failed';
			return;
		}
	}
	
	function do_login($args){
		global $status;
//var_dump($args);
		session_start();
		$_SESSION['id'] = $args['id'];
		$_SESSION['user'] = $args['user'];
		$_SESSION['login'] = true;
		$status = 'ok';
//echo '<br/>session:';
//var_dump($_SESSION);
		return;
	}
	
	function logout($args){
		global $status;
		session_start();
		$_SESSION = array();
		session_destroy();
		$status = 'ok';
		return;
	}
	
	function get_authority($args){
		global $status;
		
		$planid = $args['check_planid'];
		$check_auth = $args['check_auth'];
		
		if(!in_array($check_auth, $this->authority)){
			$status = 'no_this_auth';
			return false;
		}

		session_start();
		
		$userid = $_SESSION['id'];
		if(!preg_match('/^\d+ \d+$/', $planid.' '.$userid) or empty($check_auth) or !in_array($check_auth, $this->authority)){
			$status = 'invalid_id';
			return false;
		}else{
			$plan = toolkit('db')->get1row('plan', array('id'=>$planid));
			
			if(empty($plan)){
				$rtn = false;
				$parid = 0;
			}else{
				if($plan['owner']==$userid){
					$rtn = true;
				}else{
					$parid = $plan['par_plan'];
					$auth = toolkit('db')->getrows('authority', array('planid'=>$planid));
					
					if(empty($auth)){
						$rtn = 'inheri';
					}else{
						//$str_auth = $auth['authority'];
						$k = array_search($check_auth, $this->authority);
						
						if($k===false){
							$rtn = false;//?
						}else{
							//$rtn = $str_auth{$k}=='1'?true:false;
							$auth_idxed = array();
							foreach($auth as $tmp){
								$auth_idxed[$tmp['type']][] = $tmp;
							}
							foreach($this->auth_type as $type){
								if(!empty($auth_idxed[$type])){
									foreach($auth_idxed[$type] as $rule){
										$rtn = $this->match_auth_rule($rule, $userid, $k);
										if($rtn==true or $rtn==false){
											break;
										}
									}
								}
							}
						}
					}
				}
			}
			
			if($rtn=='inheri' and $parid){
				$par_auth = $this->get_authority(array('check_planid'=>$parid, 'check_auth'=>$check_auth));
				$rtn = $par_auth['authority'];
			}
			$args['authority'] = $rtn;
			$status = 'auth_got';
			return $args;
		}
	}
	
	function match_auth_rule($rule, $userid, $k=0){
		global $status;
		switch($rule['type']){
			case 'MEMBER':
				$membs = unserialize($rule['data']);
				$match = in_array($userid, $membs);
				break;
			case 'FRIENDS':
				//return is_friends();
				$match = false;
				break;
			case 'ALL':
			case 'GLOBAL':
				$match = true;
		}
		if(!$match){
			return 'not_match';
		}
		switch($rule['auth']{$k}){
			case '0':
				$rtn = false;
				break;
			case '1':
				$rtn = true;
				break;
			case 'x':
				$rtn = 'inheri';
				break;
		}
		$status = 'matched';
		return $rtn;
	}
	
	function check_read_auth($args){
		global $status;
		$args['check_planid'] = $this->get_check_id($args);
		if($args['check_planid']===false){
			$status = 'no';
			return;
		}
		$args['check_auth'] = 'read';
		$rst = $this->get_authority($args);
		$status = $rst['authority']===true?'yes':'no';
	}
	
	function check_write_auth($args){
		global $status;
		$args['check_planid'] = $this->get_check_id($args);
		if($args['check_planid']===false){
			$status = 'no';
			return;
		}
		$args['check_auth'] = 'write';
		$rst = $this->get_authority($args);
		//var_dump($rst);die;
		$status = $rst['authority']===true?'yes':'no';
	}
	
	private function get_check_id($args){
		$maybe = array('planid', 'parid', 'id');
		foreach($maybe as $k){
			if(preg_match('/^\d+$/', $args[$k])){
				return $args[$k];
			}
		}
		return false;
	}
	
	function get_user_info_by_name($args){
		global $status;
		$name = $_REQUEST['user'];
		//var_dump($name);
		if(!empty($name)){
			$info = toolkit('db')->get1row('user', array('user'=>$name));
			//var_dump($info);
			if(!empty($info)){
				$status = 'got_user';
				return $info;
			}
		}
		$status = 'blank';
		return;
	}
	
	function create_session($args){
		global $status;
		$userid = $args['id'];
		$user = $args['user'];
		if(empty($userid) or empty($user)){
			$status = 'invalid_args';
			return false;
		}
		$info = toolkit('db')->get1row('session', array('userid'=>$userid));
		if(!empty($info)){
			$status = 'succ';
			return array('key'=>$info['skey']);
		}
		$key = md5(time());
		toolkit('db')->add('session', array('skey'=>$key, 'userid'=>$userid, 'user'=>$user));
		$status = 'succ';
		return array('key'=>$key);
	}
	
	function get_db_session($args){
		global $status;
		$key = $args['key'];
		if(empty($key)){
			$status = 'no';
			return;
		}
		$info = toolkit('db')->get1row('session', array('skey'=>$key));
		if(empty($info)){
			$status = 'no';
			return;
		}
		$status = 'got';
		return array('id'=>$info['userid'], 'user'=>$info['user']);
	}
	
	function visitor_info(){
		session_start();
		var_dump($_SESSION);
		die;
	}
}
?>