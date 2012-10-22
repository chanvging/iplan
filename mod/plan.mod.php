<?php

class plan extends model {

	protected $kv;
	protected $login_user;
	protected $key_path_tree = 'path_tree';
	protected $key_plan_changed = 'plan_changed';
	
	function __construct() {
		parent::__construct();
		$this->fields = array(
			'id'=>INT_ID,
			'name'=>NOT_BLANK,
			'actor'=>'/./',
			'description'=>'/./',
			'target'=>'/./',
			'start'=>TIME,
			'deadline'=>TIME,
			'depend_plan'=>INT_ID,
			'sub_plan'=>INT_ID,
			'owner'=>INT_ID,
			'archive'=>'/^[01]$/',
			'status'=>'/^RUNNING|WAITING|HALT|PAUSE$/',
		);
		
		$this->not_blank_fields = array('name', 'owner');
		
		$this->login_user = empty($_SESSION['id']) ? -1 : $_SESSION['id'];
		
		$this->key_path_tree = 'path_tree:' . $this->login_user;
		$this->key_plan_changed = 'plan_changed:' . $this->login_user;
		
		//$this->kv = new SaeKV();
		//$this->kv->init();
		
	}

	function add($args) {
		global $status;

		$data = $this->_get_add_field_data($args);

		if(! $this->_check_required_field($data) ) {die('some required fields lacked');};
		
		$now = date('Y-m-d');
		if(empty($data['start'])){
			$data['start'] = $now;
		}
		if(empty($data['deadline'])){
			$data['deadline'] = $now;
		}
		
		if(! $this->date_later($data['start'], $data['deadline'])) {die('the date is invalid');}

		//$tmp = toolkit('db')->get1row('plan', array('name'=>$data['name']));
		//if(!empty($tmp)) die($data['name'].' already exist');
		
		$this->_remove_some_field($data, array('id'));
		
		$data['status'] = $this->time2status($data['start'], $data['deadline']);
		
		if($data['status']=='COMPLETE'){die('the date is invalid');}
		
		$id = toolkit('db')->add('plan', $data);
		if($id>0) {
			$status = 'add_ok';
		} else {
			$status = 'add_error';
		}
		
		if($status=='add_ok'){
			toolkit('db_cache')->set($this->key_plan_changed, '1');
		}
		
		return array('id'=>$id, 'is_post'=>true);
	}
	
	function edit($args) {
		global $status;

		$data = $this->_get_edit_field_data($args);

		empty($data['id']) and die('no id proved');
		
		$id = intval($data['id']);
		$this->_remove_some_field($data, array('id'));
		toolkit('db')->edit('plan', $data, array('id'=>$id));
		if(!empty($data['name'])){
			toolkit('db')->edit('step', array('action'=>$data['name']), array('subplanid'=>$id));
			toolkit('db_cache')->set($this->key_plan_changed, '1');
		}
		$status = 'edit_over';
		
		if(isset($_REQUEST['editable'])){
			$status = 'editable';
			return array('msg'=>array_shift($data));
		}
		return array('id'=>$id);
	}
	
	function drop($args) {
		global $status;
//		var_dump($args);die;
		$id = intval($args['id']);
		if($id<=0){
			die('invalid id');
		}
		toolkit('step')->drop_steps(array('id'=>$id));
		$subplans = toolkit('db')->getrows('plan', array('par_plan'=>$id));
		foreach($subplans as $tmp){
			$this->drop(array('id'=>$tmp['id']));
		}
		toolkit('db')->del('plan', array('id'=>$id));
		toolkit('db')->edit('step', array('subplanid'=>0), array('subplanid'=>$id));
		toolkit('db')->del('summary_log', array('planid'=>$id));
		$status = 'delete_ok';
	}
	
	function get_plan_info($args) {
		global $status;
		
		$id = $args['id'];
		$this->info = toolkit('db')->get1row('plan', array('id'=>$id));
		if(empty($this->info) ) { $status = 'no_plan_found'; return; }
		
		$timer_info = toolkit('db')->get1row('tomato_timer', array('uid'=>$_SESSION['id']));
		if(!empty($timer_info)){
			if($timer_info['stepid'] && time()-strtotime($timer_info['startime'])>=1500){
				toolkit('step')->tomato_timer(array('stepid'=>$timer_info['stepid'], 'act'=>'stop'));
				$timer_info = toolkit('db')->get1row('tomato_timer', array('uid'=>$_SESSION['id']));
			}
			$timer_info['startime'] = $this->convert_to_localtime($timer_info['startime']);
			if(array_shift(explode(' ', $timer_info['startime']))!=date('Y-m-d')) $timer_info['counts']=0;
		}
		$this->info['tomato_timer'] = $timer_info;

		$steps = toolkit('step')->get_steps(array('id'=>$id));
		$this->info['steps'] = $steps;
		
		$alerts = toolkit('alert')->get_alerts(array('userid'=>$_SESSION['id']));
		$this->info['alerts'] = $alerts;

		//toolkit('user')->visitor_info();
		
		$this->info['plan_path'] = $this->get_plan_path($id);
		
		$status = 'plan_found';
		return $this->info;
	}
	
/*	function get_all_plans($userid) {
		$plan_ids = toolkit('db')->getrows('plan', array('userid'=>$userid), array('id'));
		$plans = array();
		foreach($plan_ids as $plan_id) {
			$plans[] = $this->get_plan_info($plan_id);
		}
		return $plans;
	}*/
	
	function get_running_plans($args) {
		global $status;
		$plans = $this->get_plans(array('status'=>'RUNNING'));
//		$plans = toolkit('db')->getrows('plan', array('status'=>'RUNNING'));
		if(empty($plans)){
			$status = 'no_running_plan';
			return;
		}else{
			$status = 'plans_found';
			return $plans;
		}
	}
	
	function get_complete_plans($args){
		global $status;
		$userid = intval($args['login_userid']);
		$plans = $this->get_plans(' par_plan=0 and owner='.$userid." and status='COMPLETE'", array('id', 'name', 'start'));
		if(empty($plans)){
			$status = 'no_complete_plan';
			return;
		}else{
			$status = 'plans_found';
			return $plans;
		}
	}
	
	function handle_complete_plan($args){
		global $status;
		$planid = intval($args['planid']);
		$summary = empty($args['summary'])? 'over' : $args['summary'];
		if($planid<=0) {
			$status = 'invalid_id';
			return;
		}
		$info = toolkit('db')->get1row('plan', array('id'=>$planid));
		if(empty($info)){
			$status = 'no_plan';
			return;
		}
		if($info['status']!='COMPLETE'){
			toolkit('db')->edit('plan', array('status'=>'COMPLETE', 'summary'=>$summary), array('id'=>$planid));
			if(empty($info['par_plan'])){
				$status = 'top_plan';
			}else{
				$status = 'sub_plan';
				$now = date('Y-m-d H:i:s');
				toolkit('db')->edit('step', array('status'=>'FINISH', 'do_it'=>'NO', 'summary'=>$summary, 'summary_time'=>$now), array('subplanid'=>$planid));
			}
		}else{
			$status = 'no_chg';
		}
		return;
	}
	
	function redo_plan($args){
		global $status;
		$id = intval($args['id']);
		if($id<=0){
			$status = 'no_id';
			return;
		}
		$info = toolkit('db')->get1row('plan', array('id'=>$id, 'status'=>'COMPLETE'));
		if(empty($info)){
			$status = 'no_plan';
			return;
		}
		$setdata = array(
			'planid'=>$info['id'],
			'target'=>$info['target'],
			'description'=>$info['description'],
			'summary'=>$info['summary'],
		);
		toolkit('db')->add('summary_log', $setdata);
		toolkit('db')->edit('plan', array('status'=>'RUNNING'), array('id'=>$info['id']));
		if($info['par_plan']){
			toolkit('db')->edit('step', array('status'=>'DOING'), array('subplanid'=>$info['id']));
		}
		$status = 'succ';
		return;
	}
	
	function get_sub_plans($args){
		$parid = $args['id'];
		return $this->get_plans(array('par_plan'=>$parid));//status from get_plans:no_condition,no_plan,plans_found
	}
	
	function get_top_plan($args){
		global $status;
		$userid = intval($args['login_userid']);
		if($userid<=0){
			$status = 'no_login';
			return;
		}
		return $rtn = $this->get_plans(' par_plan=0 and owner='.$userid, array('id', 'name', 'status', 'start'), 'status, start DESC');
	}
	
	function filter_archive($args){
		global $status;
		$ary_tmp = array();
		foreach($args as $tmp){
			if($tmp['archive']) continue;
			$ary_tmp[] = $tmp;
		}
		if(empty($ary_tmp)){
			$status = 'empty';
			return;
		}else{
			$status = 'filtered';
			return $ary_tmp;
		}
	}
	
	function get_plans($condi='', $fields=array(), $orderby=''){
		global $status;
		$plans = toolkit('db')->getrows('plan', $condi, $fields, '', $orderby);
		if(empty($plans)){
			$status = 'no_plan';
			return;
		}
		$status = 'plans_found';
		return $plans;
	}
	
	function date_later($a, $b){
		$stamp_a = preg_match('/^\d+$/', $a) ? $a : strtotime($a);
		$stamp_b = preg_match('/^\d+$/', $b) ? $b : strtotime($b);
		return $stamp_a <= $stamp_b;
	}
	
	function update($args) {
		die('fuck');
		global $status;
		$cond_stat = empty($args['condition']) ? '' : ' and '.$args['condition'];
		$cond_stat = " (status='RUNNING' or status='WAITING') $cond_stat";
		$plans = toolkit('db')->getrows('plan', $cond_stat);

		if(empty($plans)) {
			$status = 'no_plans';
			return;
		}
		foreach($plans as $plan){
			$sta = $this->time2status($plan['start'], $plan['deadline']);
			if($sta != $plan['status']) {
				toolkit('db')->edit('plan', array('status'=>$sta), array('id'=>$plan['id']));
			}
		}
		$status = 'update_over';
	}
	
	function time2status($a, $b) {
		$now = time();
		if($this->date_later($now, $a)){
			return 'WAITING';
		}
		elseif($this->date_later($a,$now) and $this->date_later($now-86400, $b)) {
			return 'RUNNING';
		}
		elseif($this->date_later($b,$now-86400)){
			return 'COMPLETE';
		}
	}
	
	function subplan($args){
		global $status;
		$planid = $args['id'];
		toolkit('db')->edit('plan', array('par_plan'=>$args['parid']), array('id'=>$planid));
		toolkit('db')->edit('step', array('subplanid'=>$planid), array('id'=>$args['stepid']));
		$status = 'ok';
		echo $planid;
	}
	
	function archive_plan($args){
		global $status;
		$data = $this->_get_edit_field_data($args);
		empty($data['id']) and die('no id proved');
		$archive = intval($data['archive']);
		if($archive==1 || $archive==0){
			toolkit('db')->edit('plan', array('archive'=>$archive), array('id'=>intval($data['id'])));
			if(toolkit('db')->affected_rows){
				$status = 'updated';
			}else{
				$status = 'update_error';
			}
		}else{
			$status = 'invalid';
		}
	}
	
	function export_ppt($args){
	}
	
	function export_html($args){
		global $status;
		$planid = $args['id'];
		if(empty($planid)){
			$status = 'no_plan';
			return $args;
		}
		$html_head = '<!DOCTYPE html><html><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8" /><title>EXPORTED PLAN</title></head><body>';
		$html_foot = '</body></html>';
		$html_cont = $this->go_through($planid);
		
		echo $html_head.$html_cont.$html_foot;
	}
	
	protected function go_through($planid){
		$html = '';
		$plan_row = toolkit('db')->get1row('plan', array('id'=>$planid));
		if(empty($plan_row)){
			return '';
		}
		$html .= "{$plan_row['name']}<ol>";
		$stepid = $plan_row['stepid'];
		while(!empty($stepid)){
			$step_row = toolkit('db')->get1row('step', array('id'=>$stepid));
			if(empty($step_row)) break;
			$html .= '<li>';
			if(!empty($step_row['subplanid'])){
				$html .= $this->go_through($step_row['subplanid']);
			}else{
				$html .= $step_row['action'];
			}
			$html .= '</li>';
			$stepid = $step_row['next'];
		}
		$html .= '</ol>';
		return $html;
	}
	
	function export_doc($args){
		global $status;
		$planid = $args['id'];
		if(empty($planid)){
			$status = 'no_plan';
			return $args;
		}
		
		$html_head = '<!DOCTYPE html><html><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8" /><title>EXPORTED PLAN</title></head><body>';
		$html_foot = '</body></html>';
		
		echo $html_head . $this->go_through_as_doc($planid) . $html_foot;
	}
	
	function go_through_as_doc($planid, $level=0, $index_prefix=''){
		$html = '';
		$plan_row = toolkit('db')->get1row('plan', array('id'=>$planid));
		if(empty($plan_row)){
			return '';
		}
		
		if($level>=1 && $level<=6){
			$html .= "<h$level>$index_prefix {$plan_row['name']}</h$level>";
		}elseif($level==0){
			$html .= '<div class="title" align="center">'.$plan_row['name'].'</div>';
		}else{
			$html .= '<div><b>'.$plan_row['name'].'</b></div>';
		}
		
		if(!empty($plan_row['description'])){
			$html .= "<p>{$plan_row['description']}</p>";
		}
		
		$stepid = $plan_row['stepid'];
		$index_num = 1;
		while(!empty($stepid)){
			$step_row = toolkit('db')->get1row('step', array('id'=>$stepid));
			if(empty($step_row)) break;
			$sub_level = $level+1;
			$index = $index_prefix=='' ? $index_num : "$index_prefix.$index_num";
			if(!empty($step_row['subplanid'])){
				$html .= $this->go_through_as_doc($step_row['subplanid'], $sub_level, $index);
			}else{
				$html .= "<h$sub_level>$index {$step_row['action']}</h$sub_level>";
				if(!empty($step_row['memo'])){
					$html .= '<p>' . $step_row['memo'] . '</p>';
				}
			}
			$stepid = $step_row['next'];
			$index_num++;
		}
		return $html;
	}
	
	public function get_plan_path($planid){
		if(empty($planid)) return array();
		
		//$path_tree = $this->flush_path_tree();
		$plan_changed = toolkit('db_cache')->get($this->key_plan_changed);
		if($plan_changed===false){
			toolkit('db_cache')->set($this->key_plan_changed, '1');
			$plan_changed = '1';
		}
		if($plan_changed){
			$path_tree = $this->flush_path_tree();
		}else{
			$path_tree = toolkit('db_cache')->get($this->key_path_tree);
		}
		
		$path = array();
		$par_plan = $path_tree[$planid][1];
		while(!empty($par_plan)){
			array_unshift($path, array($par_plan, $path_tree[$par_plan][0]));
			$par_plan = $path_tree[$par_plan][1];
		}
		
		return $path;
	}
	
	public function flush_path_tree(){//echo '<p>flush paln tree</p>';
		$path_tree = array();
		$this->get_tree($path_tree);
		
		toolkit('db_cache')->set($this->key_path_tree, $path_tree);
		toolkit('db_cache')->set($this->key_plan_changed, '0');
		
		return $path_tree;
	}
	
	private function get_tree(&$path_tree, $par_planid=0){
		$plans = toolkit('db')->getrows('plan', array('par_plan'=>$par_planid, 'owner'=>$this->login_user), array('id', 'name'));
		foreach($plans as $plan){
			$path_tree[$plan['id']] = array($plan['name'], $par_planid);
			$this->get_tree($path_tree, $plan['id']);
		}
	}
	
	function tongji($args){
		global $status;
		$from_date = $args['from_date'];
		$to_date = $args['to_date'];
		$userid = $args['userid'];
		
		if(empty($userid)){
			$status = 'not_login';
			return;
		}
		
		if(empty($from_date) || empty($to_date)){
			$status = 'date_error';
			return $args;
		}
		$sql = "select planid, count(*) as ct from tomatos where userid=$userid and time>'$from_date' and time<'$to_date' group by planid";
		$tomatos = toolkit('db')->get_data($sql);
		
		$tongji_data = array();
		$sum = 0;
		foreach($tomatos as $tomato){
			$path = $this->get_plan_path($tomato['planid']);
			if(empty($path)){
				$plan_info = toolkit('db')->get1row('plan', array('id'=>$tomato['planid']), array('name'));
				$root_plan = array($tomato['planid'], $plan_info['name']);
			}else{
				$root_plan = array_shift($path);
			}
			if(empty($tongji_data[$root_plan[0]])){
				$tongji_data[$root_plan[0]] = array($root_plan[1], $tomato['ct']);
			}else{
				$tongji_data[$root_plan[0]][1] += $tomato['ct'];
			}
			$sum += $tomato['ct'];
		}
		
		$formated_data = array();
		foreach($tongji_data as $planid=>$data){
			$percent = round($data[1] * 100 / $sum) . '%';
			array_push($formated_data, array('id'=>$planid, 'name'=>$data[0], 'count'=>$data[1], 'percent'=>$percent));
		}
		
		$tmp['tongji'] = $formated_data;

		$status = 'succ';
		return $tmp;
	}
	
	function add_schedule(){
		global $status;
		
		$planid = intval($_POST['id']);
		$unit = intval($_POST['unit']);
		$tomatos = intval($_POST['tomatos']);
		
		if($planid<=0 || $tomatos<=0){
			$status = 'error';
			return false;
		}
		
		$scheduled_plans = toolkit('db_cache')->get('scheduled_plans-' . $_SESSION['id']);
		
		if(!is_array($scheduled_plans)){
			$scheduled_plans = array();
		}
		
		if(in_array($planid, $scheduled_plans)){
			$status = 'exist';
			return false;
		}
		
		array_push($scheduled_plans, $planid);
		toolkit('db_cache')->set('scheduled_plans-' . $_SESSION['id'], $scheduled_plans);
		
		$info = array(
			'unit'=>$unit,
			'tomatos'=>$tomatos,
			'eaten'=>0,
            		'time'=>time(),
		);
		
		toolkit('db_cache')->set('scheduled_plan-'.$planid, $info);
		
		$status = 'succ';
	}
    
    function schedule($args){
        global $status;
        $scheduled_plan_ids = toolkit('db_cache')->get('scheduled_plans-' . $_SESSION['id']);

        if(empty($scheduled_plan_ids)){
            $status = 'empty';
            return $args;
        }
        
        $scheduled_plans = array();
        
        foreach($scheduled_plan_ids as $id){
            $plan = toolkit('db')->get1row('plan', array('id'=>$id), array('id', 'name'));
            $sche_info = toolkit('db_cache')->get('scheduled_plan-' . $id);
            if(!in_same_unit($sche_info['time'], $sche_info['unit'])){
                $sche_info['eaten'] = 0;
            }
            
            if($sche_info['unit']=='0'){
                $sche_info['left_days'] = 7 - date('w');
            }else{
                $sche_info['left_days'] = 0;
            }
            
            $plan['sche_info'] = $sche_info;
            $scheduled_plans[] = $plan;
        }
        
        $status = 'succ';
        return $scheduled_plans;
        
    }
    
    function del_schedule($args){
        global $status;
        $planid = $args['id'];
        
        $scheduled_plans = toolkit('db_cache')->get('scheduled_plans-' . $_SESSION['id']);
        
        $pos = array_search($planid, $scheduled_plans);
        
        if($pos!==FALSE){
            toolkit('db_cache')->del('scheduled_plan-' . $planid);
            unset($scheduled_plans[$pos]);
            if(empty($scheduled_plans)){
                toolkit('db_cache')->del('scheduled_plans-' . $_SESSION['id']);
            }else{
                toolkit('db_cache')->set('scheduled_plans-' . $_SESSION['id'], $scheduled_plans);
            }
            $status = 'succ';
        }else{
            $status = 'not_scheduled';
            return;
        }
    }
    
}

