<?php
/*
	Author: [ChinaWin]
	Development Time: 2017-03-28
*/
require 'common.inc.php';

$module = $_POST['mod'] ? trim($_POST['mod']) : '';
$mod_arr = array('member');
if(empty($module) || !in_array($module,$mod_arr)){
	echo json_encode($res);
	exit();
}
$ac = $_POST['ac'] ? trim($_POST['ac']) : '';
$ac_arr = array(
	'member'=>array('ajax_login', 'forgetpassword', 'bind_bank', 'MessageCenterShow', 'Index_deposit', 'forget_password'),
);
if(empty($ac) || !in_array($ac,$ac_arr[$module])){
	echo json_encode($res);
	exit();
}
$moduleid = intval($moduleid);
if($moduleid > 0){
	$MOD = cache_read('module-'.$moduleid.'.php');
	if(file_exists(DT_ROOT.'/lang/'.DT_LANG.'/'.$module.'.inc.php')){
		include DT_ROOT.'/lang/'.DT_LANG.'/'.$module.'.inc.php';
	}
	include(DT_ROOT.'/module/'.$module.'/ajax.inc.php');
}else{
	echo json_encode($res);
	exit();
}
?>