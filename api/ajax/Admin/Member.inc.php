<?php

if(empty($func) || !in_array($func,array('login' ,'loginout'))){
	$res['msg'] = '请求来路错误';
}else{
	if($func == 'login'){ //后台登陆验证
		// $res['msg'] = $_POST;
		$username = $_POST['username'];
		$password = $_POST['password'];
		$r = $db->get_one("SELECT * FROM  {$DT_PRE}member WHERE username='".$username."' ");

		if( $r ){
			if( $r['admin'] == 0 ){ //admin 0 无后台权限
				$res['code'] = 2;
				$res['msg'] = '您无权限登录后台!';
			}else if($r['password'] !== md5($password) ){
				$res['code'] = 2;
				$res['msg'] = '密码错误!';
			}else{
				set_cookie('userid',$r['userid']);
				$db->query("UPDATE {$DT_PRE}member SET logtime='".time()."' WHERE userid=".$r['userid']);
				$res['code'] = 1;
				$res['msg'] = '正在跳转后台!';
			}
			
		}else{
			$res['code'] = 2;
			$res['msg'] = '该用户尚未注册!';
		}

	}else if( $func == 'loginout' ){  //直接退出
		set_cookie('userid', '');
		$res['code'] = 1;
	}


}