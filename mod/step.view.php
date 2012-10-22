<?php
	class stepview extends view {
		
		function addstep() {
			echo 'add_ok';
		}
		
		function editstep() {
			echo 'edit_ok';
		}
		
		function delstep() {
			echo 'del_ok';
		}
		
		function show_form() {
			$this->smarty->display('step.form.html');
		}
		
		function show_todo_list($args){
			global $status;
			$status = 'tpl_ready';
			return array('tpl'=>'todo_list.html', 'todos'=>$args);
		}
	}
?>
