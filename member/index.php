<?php
/*
	Author: [ChinaWin]
	Development Time: 2017-03-21
*/
require 'config.inc.php';
require '../common.inc.php';
login();
require DT_ROOT.'/module/'.$module.'/common.inc.php';
require DT_ROOT.'/include/post.func.php';

$ac_arr = array('forgetpassword', 'gamehistory', 'fundin', 'fundout', 'deposit', 'withdraw', 'messagecenter', 'responsiblegambling');

if(isset($ac) && in_array($ac,$ac_arr)){
	require DT_ROOT.'/module/'.$module.'/'.$ac.'.inc.php';
}else{
	require DT_ROOT.'/module/'.$module.'/index.inc.php';
}
?>