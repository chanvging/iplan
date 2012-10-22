<?php
//require MODROOT.'lib/PHPPowerpoint.php';
set_include_path(get_include_path() . PATH_SEPARATOR . MODROOT.'lib/');
echo 'sdfsdf';
var_dump(get_include_path());
var_dump(file_exists('PHPPowerpoint.php'));
include 'PHPPowerpoint.php';
include 'PHPPowerPoint/IOFactory.php';
echo 'includeed';
$tmp = new PHPPowerPoint();
var_dump($tmp);