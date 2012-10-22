<?php

error_reporting(0);

define(APPROOT, dirname(__file__));
define(MODROOT, APPROOT.'/mod/');
define(THEMEROOT, APPROOT.'/themes');
define(COMS, APPROOT.'/coms/');

//var_dump($_REQUEST);die;
require APPROOT."/mod/spyc.php"; //parse yaml files

require_once APPROOT."/global.func.php";

$cmd = $_GET['cmd'] ? $_GET['cmd'] : 'index';

//require "coms/$cmd.com.php";
//require "items.php";
$items = Spyc::YAMLLoad('items.yaml');

$yamlfile = COMS."$cmd.yaml";
file_exists($yamlfile) or die('not found yaml file: '.$yamlfile);

$coms = Spyc::YAMLLoad($yamlfile);

$keys = array_keys($coms);
//var_dump($keys);
foreach($coms as $ak=>$av) {
	if(empty($av['pin'])) continue;
	$values = array_values($av['pin']);
	if(empty($values)) continue;
	$keys = array_diff($keys, $values);//var_dump($keys);echo '<br/>';
	if(count($keys)==1) break;
	if(count($keys)==0) die('error for your coms files');
}
//var_dump($keys);
if(count($keys)!=1) die("check your com file: $yamlfile");

$com = array_pop($keys);
$args = array();

$status = '';//该变量时必需的，其他模块会使用该变量

while(!empty($com)) { //echo $com.'-->';
	$item_name = $coms[$com]['item'];
	empty($coms[$com]['args']) or $args = array_merge($args, $coms[$com]['args']);
	$item = $items[$item_name];
	$retn = toolkit($item['model'])->{$item['action']}($args);
	empty($retn) or $args=$retn;
	if($status=='') break;
	list($com, $file) = explode('<', $coms[$com]['pin'][$status]);
	if(!empty($file)) {
		$yamlfile = COMS.$file;
		file_exists($yamlfile) or die('not found yaml file: '.$yamlfile);
		$coms = Spyc::YAMLLoad($yamlfile);
	}
	$status = '';
}

?>
