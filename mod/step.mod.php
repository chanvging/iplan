<?php
	class step extends model {

		function __construct() {
			parent::__construct();
			$this->fields = array(
				'id'=>INT_ID,
				'action'=>NOT_BLANK,
				'planid'=>INT_ID,
				'status'=>'',
				'summary'=>'',
				'summary_time'=>'',
				'memo'=>'',
				'memo_time'=>'/^[\d :-]+$/',
				'done'=>'',
				'done_time'=>'',
				'todo'=>'',
				'todo_time'=>'',
			);
			
			$this->not_blank_fields = array('planid', 'action');
		}
		
		function add($args) {
			global $status;
			$preid = $args['preid'];
			//isset($preid) or die('args is not valid in step.add');
			$data = $this->_get_add_field_data($args);
			if(! $this->_check_required_field($data) ) {die('some required fields lacked');};
			$this->_remove_some_field($data, array('id'));
			$stepid = toolkit('db')->add('step', $data);
			
			$this->make_chain($data['planid'], $stepid, $preid);
			
			$status = 'add_step_ok';
			return array('stepid'=>$stepid);
		}
		
		function make_chain($planid, $stepid, $preid = 0){
			if(!($preid)) {
				$temp = toolkit('db')->get1row('plan', array('id'=>$planid));
				empty($temp) and die('no plan found');
				toolkit('db')->edit('step', array('next'=>$temp['stepid']), array('id'=>$stepid));
				toolkit('db')->edit('plan', array('stepid'=>$stepid), array('id'=>$planid));
			} else {
				$temp = toolkit('db')->get1row('step', array('id'=>$preid));
				if(empty($temp) or $temp['planid']!=$planid) die('no step or not belong to same plan');
				toolkit('db')->edit('step', array('next'=>$temp['next']), array('id'=>$stepid));
				toolkit('db')->edit('step', array('next'=>$stepid), array('id'=>$preid));
			}
		}
		
		function edit($args) {
			global $status;
			
			//$data = $this->_get_edit_field_data($args);//非AJAX调用使用该函数
			$data = $this->_get_add_field_data($args);//AJAX调用使用该函数
			
			empty($data['id']) and die('no id proved');
		
			$stepid = $data['id'];
			$this->_remove_some_field($data, array('id'));

			toolkit('db')->edit('step', $data, array('id'=>$stepid));
			
			$status = 'edit_step_ok';

			echo $args[$args['field']];
			
			return array('id'=>$stepid, 'field'=>$args['field']);
		}
		
		function del($args) {
			global $status;
			$id = $args['id'];
			isset($id) or die('args is not valid in step.del');
			$db = toolkit('db');
			$step_info = $db->get1row('step', array('id'=>$id));
			if(!empty($step_info)){
				$par_plan = $step_info['planid'];
				$sub_plan = $step_info['subplanid'];
				toolkit('user')->check_write_auth(array('planid'=>$par_plan));
				$can_edit = $status;
				//var_dump($can_edit);die;
				if($can_edit=='yes'){
					$this->break_chain($args);
					$db->del('step', array('id'=>$id));
					if(!empty($sub_plan)){
						toolkit('plan')->drop(array('id'=>$sub_plan));
					}
				}else{
					$status = 'no_auth';
					return;
				}
			}
			$status = 'del_step_ok';
		}
		
		function break_chain($args){
			global $status;
			$id = $args['id'];
			isset($id) or die('args is not valid in step.break_chain');
			$info = toolkit('db')->get1row('step', array('id'=>$id));
			if(empty($info)) {
				$status = 'not_found_record';
				return;
			}
			$db = toolkit('db');
			$db->edit('step',array('next'=>$info['next']), array('next'=>$info['id']));
			if($db->affected_rows != 1) {
				$db->edit('plan', array('stepid'=>$info['next']), array('id'=>$info['planid']));
			}
		}
		
		function drop_steps($args){//该函数根据args提供的planid删除属于某个plan的所有step
			global $status;
			$id = $args['id'];
			isset($id) or die('args is not valid in step.del');
			toolkit('db')->del('step', array('planid'=>$id));
			$status = 'delete_over';
		}
		
		function get_steps($args) {
			global $status;
			$planid = intval($args['id']);
			$temp = toolkit('db')->get1row('plan', array('id'=>$planid));
			if(empty($temp) or $temp['stepid']==0){
				$status = 'no_step';
				return;
			}
			$first = $temp['stepid'];
			while($first) {
				$temp = $this->get_step($first);
				if(empty($temp)) break;
				$steps[] = $temp;
				$first = $temp['next'];
			}
			
			$status = 'got_steps';
			return $steps;
		}
		
		function get_step($stepid){
			return toolkit('db')->get1row('step', array('id'=>intval($stepid)));
		}
		
		function summary($args){
			global $status;
			$stepid = intval($args['id']);
			if($stepid<=0){
				$status = 'invalid_id';
				return;
			}
			$step_info = toolkit('db')->get1row('step', array('id'=>$stepid));
			if(empty($step_info)){
				$status = 'no_step';
				return;
			}
			if($step_info['status']!='FINISH'){
				$planid = $step_info['subplanid'];
				$now = date('Y-m-d H:i:s');
				toolkit('db')->edit('step', array('status'=>'FINISH', 'do_it'=>'NO', 'summary'=>$args['summary'], 'summary_time'=>$now), array('id'=>$stepid));
				if(!empty($planid)){
					toolkit('db')->edit('plan', array('status'=>'COMPLETE', 'summary'=>$args['summary']), array('id'=>$planid));
				}
			}
			$status = 'ok';
		}
/*		
		function combine($args){
			global $status;
			$comb_id = $args['comb_id'];
			$to_id = $args['to_id'];
			if(empty($comb_id) or empty($to_id)){
				$status = 'lack_field';
				return false;
			}
			if($comb_id == $to_id){$status = 'same_step'; return false;}
			$info = toolkit('db')->get1row('step', array('id'=>$to_id));
			if(empty($info['subplanid'])){
				$status = 'no_sub_plan';
				return;
			}
			$this->break_chain(array('id'=>$comb_id));
			toolkit('db')->edit('step', array('planid'=>$info['subplanid']), array('id'=>$comb_id));
			$this->make_chain($info['subplanid'], $comb_id);
			$from_info = toolkit('db')->get1row('step', array('id'=>$comb_id));
			if($from_info['subplanid']>0){
				toolkit('db')->edit('plan', array('par_plan'=>$from_info['planid']), array('id'=>$from_info['subplanid']));
			}
			$status = 'combine_ok';
		}*/
		
		function combine($args){
            session_start();
			global $status;
			$comb_stepid = $args['comb_id'];
			$to_stepid = $args['to_id'];
			if($comb_stepid==$to_stepid){
				$status = 'no_change';
				return false;
			}
			if(empty($comb_stepid)){
				$status = 'lack_field';
				return false;
			}
			$from_info = toolkit('db')->get1row('step', array('id'=>$comb_stepid));
			if(empty($from_info)){
				$status = 'no_step_info';
				return false;
			}
			if(empty($to_stepid)){
				$par_info = toolkit('db')->get1row('plan', array('id'=>$from_info['planid']));
				if(empty($par_info['par_plan'])){
					$status = 'no_par_plan';
					return false;
				}
				$to_plan = $par_info['par_plan'];
			}else{
				$to_step_info = toolkit('db')->get1row('step', array('id'=>$to_stepid));
				if(empty($to_step_info)){
					$status = 'no_step_info';
					return false;
				}
				$to_plan = $to_step_info['subplanid'];
			}
                        
			if(empty($to_plan)){
				$new_plan = toolkit('plan')->add(array('name'=>$to_step_info['action'], 'owner'=>$_SESSION['id']));
				toolkit('plan')->subplan(array(
					'id'=>$new_plan['id'],
					'parid'=>$to_step_info['planid'],
					'stepid'=>$to_stepid,
				));
				$to_plan = $new_plan['id'];
			}

			$this->break_chain(array('id'=>$comb_stepid));
			toolkit('db')->edit('step', array('planid'=>$to_plan), array('id'=>$comb_stepid));
			$this->make_chain($to_plan, $comb_stepid);
			if($from_info['subplanid']>0){
				toolkit('db')->edit('plan', array('par_plan'=>$to_plan), array('id'=>$from_info['subplanid']));
				toolkit('db_cache')->set('plan_changed:' . $_SESSION['id'], '1');
			}
			$status = 'combine_ok';
		}
		
		function order($args){
			global $status;
			$step_id = $args['step_id'];
			$after_id = $args['after_id'];
			if($step_id==$after_id){
				$status = 'no_change';
				return false;
			}
			$step_info = toolkit('db')->get1row('step', array('id'=>$step_id));
			$after_info = toolkit('db')->get1row('step', array('id'=>$after_id));
			if($step_info['planid']!=$after_info['planid']){
				$status = 'not_same_plan';
				return false;
			}
			$this->break_chain(array('id'=>$step_id));
			toolkit('db')->edit('step', array('next'=>$after_info['next']), array('id'=>$step_id));
			toolkit('db')->edit('step', array('next'=>$step_id), array('id'=>$after_id));
			$status = 'ok';
		}
		
/*		function did_it($args){
			if($args['field']=='done'){
				$rst = toolkit('db')->get1row('step', array('id'=>$args['id']));
				if($rst['do_it'] == 'YES'){
					$this->do_it(array('id'=>$args['id'], 'do_it'=>'DID'));
				}
			}
		}
*/		
		function do_it($args){
			global $status;
			$stepid = $args['id'];
			$cur = $args['do_it'];
			if(empty($cur)){
				$rst = toolkit('db')->get1row('step', array('id'=>$stepid));
				$cur = $rst['do_it'];
				$doit=array(
					'YES'=>'NO',
					'DID'=>'NO',
					'NO'=>'YES',
				);
				$cur = $doit[$cur];
			}
			if(empty($cur) or !in_array($cur, array('YES', 'NO'))){
				$status = 'database_error';
				return;
			}
			toolkit('db')->edit('step', array('do_it'=>$cur), array('id'=>$stepid));
			$status = 'modi_ok';
			return array('msg'=>$cur);
		}
		
		function redo_step($args){
			global $status;
			$stepid = $args['id'];
			if(empty($stepid)){
				$status = 'no_id';
				return;
			}
			$step_info = toolkit('db')->get1row('step', array('id'=>$stepid, 'status'=>'FINISH'));
			if(empty($step_info)){
				$status = 'no_step';
				return;
			}
			toolkit('db')->edit('step', array('status'=>'DOING'), array('id'=>$stepid));
			if(!empty($step_info['subplanid'])){
				toolkit('plan')->redo_plan(array('id'=>$step_info['subplanid']));
			}
			$status = 'succ';
		}
		
		function todo_list($args){
			global $status;
			$userid = intval($args['login_userid']);
			if($userid<=0){
				$status = 'invalid_id';
				return;
			}
			$sql = 'select plan.id as planid, step.id as stepid, name, action, lastday from plan inner join step on plan.id=step.planid where do_it=\'YES\' and owner='.$userid.' order by plan.id ';
			$rt = toolkit('db')->get_data($sql);
			/*
			$rt = array('yes'=>array(), 'no'=>array());
			foreach($tmp as $record){
				if($record['do_it']=='YES'){
					$rt['yes'][] = $record;
				}else if($record['do_it']=='DID'){
					$rt['no'][] = $record;
				}
			}
			*/
			//var_dump($tmp);die;
			$status = 'ok';
			return $rt;
		}
		
/*		function update_do_it($args){
			global $status;
			$steps = toolkit('db')->getrows('step', array('do_it'=>'DID'));
			if(!empty($steps)){
				$today = date('Y-m-d');
				foreach($steps as $step){
					if(substr($step['done_time'],0, 10) != $today){
						toolkit('db')->edit('step', array('do_it'=>'YES'), array('id'=>$step['id']));
					}
				}
			}
			$status = 'update_ok';
			return;
		}
*/		
		function tomato($args){
			global $status;
			$stepid = $args['stepid'];
			$tomatos = $args['tomatos'];
			if($tomatos<1){
				$status ='tooless';
				return;
			}
			if($tomatos>9){
				$status = 'toomuch';
				return;
			}
			$step_info = toolkit('db')->get1row('step', array('id'=>$stepid));
			if(empty($step_info)){
				$status = 'nostep';
				return;
			}
			toolkit('user')->check_write_auth(array('planid'=>$step_info['planid']));
			$can_edit = $status;
			if(!$can_edit){
				$status = 'no auth';
				return;
			}
			if($step_info['red_tomato']==0){
				toolkit('db')->edit('step', array('red_tomato'=>$tomatos), array('id'=>$stepid));
				$status = 'red_ok';
			}elseif($step_info['red_tomato']<=$tomatos){
				$green_tomato = $tomatos - $step_info['red_tomato'];
				if($green_tomato!=$step_info['green_tomato']){
					toolkit('db')->edit('step', array('green_tomato'=>$green_tomato), array('id'=>$stepid));
				}
				$status = 'green_ok';
			}
		}
		
		function tomato_timer($args){
			session_start();
			global $status;
			$stepid = $args['stepid'];
			$act = $args['act'];
			$step_info = toolkit('db')->get1row('step', array('id'=>$stepid), array('planid','eaten'));
			if(empty($step_info)){
				$status = 'nostep';
				return;
			}
			toolkit('user')->check_write_auth(array('planid'=>$step_info['planid']));
			$can_edit = $status;
			if(!$can_edit){
				$status = 'no auth';
				return;
			}
			if($act=='start'){
				$timer_info = toolkit('db')->get1row('tomato_timer', array('uid'=>$_SESSION['id']));
			}else{
				$timer_info = toolkit('db')->get1row('tomato_timer', array('uid'=>$_SESSION['id'], 'stepid'=>$stepid));
			}
			if(!empty($timer_info) and !empty($timer_info['stepid'])){
				$intval = time() - strtotime($timer_info['startime']);
				$count_str = '';
				if($intval>=1500){
					$today = date('Y-m-d');
					toolkit('db')->edit('step', "eaten=eaten+1, do_it='YES', lastday='$today'", array('id'=>$timer_info['stepid']));
					toolkit('db')->add('tomatos', array('planid'=>$step_info['planid'], 'stepid'=>$timer_info['stepid'], 'userid'=>$_SESSION['id']));
					$count_str = 'counts=counts+1,';
					
					$this->update_scheduled_plan($step_info['planid']);
					
				}
				toolkit('db')->edit('tomato_timer', $count_str . 'planid=0, stepid=0', array('uid'=>$_SESSION['id']));
				$status = 'stoped';
			}
			if($act=='start'){
				if(empty($timer_info)){
					toolkit('db')->add('tomato_timer', array('uid'=>$_SESSION['id'], 'planid'=>$step_info['planid'], 'stepid'=>$stepid, 'startime'=>date('Y-m-d H:i:s')));
				}else{
					$data = array('planid'=>$step_info['planid'], 'stepid'=>$stepid, 'startime'=>date('Y-m-d H:i:s'));
					$lastday = array_shift(explode(' ', $timer_info['startime']));
					if($lastday!=date('Y-m-d')){
						$data['counts'] = 0;
					}
					toolkit('db')->edit('tomato_timer', $data, array('uid'=>$_SESSION['id']));
				}
				$status = 'started';
			}
		}
		
		function tomato_reset($args){
			global $status;
			$stepid = $args['id'];
			if(empty($stepid)){
				$status = 'no_id';
				return;
			}
			$step_info = toolkit('db')->get1row('step', array('id'=>$stepid));
			if(empty($step_info) or $step_info['status']=='FINISH'){
				$status = 'no_step';
				return;
			}
			$this->tomato_timer(array('stepid'=>$stepid));
			toolkit('db')->edit('step', array('red_tomato'=>0, 'green_tomato'=>0, 'eaten'=>0), array('id'=>$stepid));
			$status = 'succ';
		}
		
		function get_subplan($args){
			global $status;
			$stepid = intval($args['stepid']);
			if($stepid<=0){
				$status = 'no_step';
				return;
			}
			$step_info  = toolkit('db')->get1row('step', array('id'=>$stepid), array('subplanid'));
			if(empty($step_info)){
				$status = 'no_step';
				return;
			}
			if(empty($step_info['subplanid'])){
				$status = 'no_sub';
				return;
			}else{
				$status = 'has_sub';
				echo $step_info['subplanid'];
			}
		}
		
		function update_scheduled_plan($planid){
			$scheduled_plans = toolkit('db_cache')->get('scheduled_plans-' . $_SESSION['id']);
			if(empty($scheduled_plans)) return false;
			$path = toolkit('plan')->get_plan_path($planid);
			$plan_ids = array($planid);
			foreach($path as $tmp){
				$plan_ids[] = $tmp[0];
			}
			$affected_plans = array_intersect($scheduled_plans, $plan_ids);
			if(empty($affected_plans)) return false;
			foreach($affected_plans as $pid){
				$tmp_info = toolkit('db_cache')->get('scheduled_plan-'.$pid);
				if(!in_same_unit($tmp_info['time'], $tmp_info['unit'])){
					$tmp_info['eaten'] = 0;
				}
				$tmp_info['eaten']++;
				$tmp_info['time'] = time();
				toolkit('db_cache')->set('scheduled_plan-'.$pid, $tmp_info);
			}
		}
		
	}//end of class

//end of php file
