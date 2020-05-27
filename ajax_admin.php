<?php
require 'common.inc.php';
$res = array('code' => 0, 'msg' => '');

$ac = $ac ? $ac : '';
$ac_arr = array('Member','News','Extends');


if(empty($ac) || !in_array($ac,$ac_arr)){
	$res['msg'] = '请求来路错误！';
}else{	
	
	@include DT_ROOT.'/api/ajax/Admin/'.$ac.'.inc.php';
}

echo json_encode($res);
exit();
?>
