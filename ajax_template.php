<?php
/*
	Author: [ChinaWin]
	Development Time: 2017-04-10
*/
require 'common.inc.php';
// if($DT_BOT){
// 	dhttp(403);
// }

$mod = $_GET['mod'] ? trim($_GET['mod']) : '';
$mod_arr = array('edit');

if(empty($mod) || !in_array($mod,$mod_arr)){
	exit();
}

$ac = $_GET['ac']?trim($_GET['ac']):'';
$ac_arr = array(
	'edit' => array('class'),
);
if(empty($ac) || !in_array($ac,$ac_arr[$mod])){
	exit();
}
// include(DT_ROOT.'/'.$mod.'/config.inc.php');


if( $mod == 'edit' ){
	if( $ac == 'class' ){

		// dump("asd");
	}




}





@include template("ajax_".$ac, $mod);
