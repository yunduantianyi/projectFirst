<?php

if(empty($func) || !in_array($func,array('Send_Mobile_Code' ,'Register','Login'))){
	$res['msg'] = '请求来路错误';
}else{
	if($func == 'Send_Mobile_Code'){ //注册发送短信验证码
		$mobile = $_POST['username'];
		$r = $db->get_one("SELECT * FROM {$DT_PRE}member WHERE username='".$mobile."' OR mobile='".$mobile."'");
		if( $r ){
			$res['code'] = 2;
			$res['msg'] = '该手机号已被注册!';
		}else{
			$t = $db->get_one("SELECT * FROM {$DT_PRE}sms WHERE mobile='".$mobile."' ORDER BY sendtime DESC");
			// dump($t);
			if( $t['type'] == 1 && (time()-$t['sendtime']) < 1800 ){
				$res['msg'] = '30分钟内无法重复发送!';
				$res['code'] = 3;
				$res['aaa'] = $t;
			}else{
				$rand = rand(100000,999999);
				$arr = array('mobile'=>$mobile,'code'=>$rand,'type'=>1);
				//短信通道,开通自己的后开启
				$res_ali = send_ali($arr);

				if( $res_ali == 'ok' ){
					$res['msg'] = '成功发送短信!';
					$res['code'] = 1;
				}else{
					//这里可以做个微信提示 或者备用短信接口
					$res['msg'] = '发送失败,请稍后重试!';
					$res['code'] = 3;
				}
			}
		}
		
	}else if( $func == 'Register' ){ //主页面注册
		$username = $_POST['username'];
		$password = $_POST['password'];
		$code = $_POST['code'];
		$a = $db->get_one("SELECT userid FROM {$DT_PRE}member WHERE username='".$username."' OR mobile='".$username."'");
		$b = $db->get_one("SELECT * FROM {$DT_PRE}sms WHERE mobile = '".$username."' AND type=1 ORDER BY sendtime DESC");
		if( $a ){
			$res['msg'] = '该手机号已注册!';
			$res['code'] = 2;	
		}else{
			if( (time() - $b['sendtime']) < 1800 ){
				if( $b['code'] == $code ){
					// $password = encrypt($password);
					$password = md5($password);
					//注册成功,插入一条进入member表
					$db->query("INSERT INTO {$DT_PRE}member (`username`,`password`,`online`,`mobile`,`regtime`,`loginip`,`logtime`,`logtimes`) VALUES('".$username."','".$password."','1','".$username."','".time()."','".$DT_IP."','".time()."','1')");
					$userid = $db->insert_id();
					set_cookie('userid',$userid); //cookie ->userid
					$res['code'] = 1;
				}else{
					$res['code'] = 2;
					$res['msg'] = '验证码不匹配!';
				}
			}else{
				$res['code'] = 2;
				$res['msg'] = '验证码超时,请重新发送!';
			}
		}
	}else if( $func == 'Login' ){ //主页面登陆
		$username = $_POST['username'];
		$password = md5($_POST['password']);
		$r = $db->get_one("SELECT * FROM {$DT_PRE}member WHERE username='".$username."' OR mobile='".$username."'");
		if( $r ){
			if( $r['password'] == $password ){
				set_cookie('userid',$r['userid']); //cookie ->userid
				$db->query("UPDATE {$DT_PRE}member SET logtime='".time()."' WHERE userid=".$r['userid']);
				$res['code'] = 1;
				// $res['url'] = $format;
				$res['msg'] = '登录成功...';
			}else{
				$res['code'] = 2;
				$res['msg'] = '密码错误,请重试!';
			}
		}else{
			$res['code'] = 2;
			$res['msg'] = '该用户尚未注册!';
		}
	}


}