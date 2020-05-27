<?php
require 'common.inc.php';
$ac_list = array('index','edit','service','my');

$func_list = array(
	'index' => array('index', 'my_goods', 'my_action','ajax_my_goods','ajax_my_action','ajax_show_log','log','daochu_manage','ajax_about_my','ajax_daochu_edit','kanban'),//首页
	'edit' => array('index', 'class_manage', 'wp_manage', 'goods_stock', 'ajax_check_log', 'ajax_confirm_log','ajax_file_ruku', 'check_uploads', 'apply','ajax_get_image'),
	'service' => array('index'),
	'my' => array('index'),
	
);
// dump(isMobile());
if(empty($func)){
	$func = 'index';	
}

if(empty($ac) || !in_array($ac,$ac_list)){
	$ac = $func = 'error';	
}

if(!in_array($func,$func_list[$ac])){
	$ac = $func = 'error';
}
$title = '物品领用';


if( !$USER ){
	if( ($ac=='index') && ($func =='index') ){

	}else{

		header("Refresh:2;url=".DT_PATH."page.php?from=index&ac=index");
		
	}
}

if( $ac == 'index' ){
	if($func == 'index'){
		$apply = array();
		$more_return = array();
		$give = array();
		if( $USER['isAdmin'] == 1 ){  //管理员操作内容
			//领用 处理
			$res1 = $db->query("SELECT * FROM {$DT_PRE}goods_belong WHERE `status` = 2 ORDER BY starttime DESC ");
			while($r1 = $db->fetch_array($res1)){
				$apply[$r1['starttime']][] = $r1;  //管理员权限  status = 2
			}

			//延期处理
			$res2 = $db->query("SELECT * FROM {$DT_PRE}goods_belong WHERE `status` =3 AND more_return <>'' ");
			while($r2 = $db->fetch_array($res2)){
				$more_return[] = $r2;  //管理员权限  status = 2
			}
		}

		$res3 = $db->query("SELECT * FROM {$DT_PRE}goods_belong WHERE ((`userid`='".$_userid."') AND (`status`=5) ) OR ((`need_userid`='".$_userid."') AND (`status`=4)) ");
		while($r3 = $db->fetch_array($res3)){
			$give[] = $r3;  //管理员权限  status = 2
		}

		$count = count($apply) + count($more_return) + count($give);

		$use = isMobile() ? 'pe' : 'pc';  //pc pe 判断

		// dump(count($apply));
		// dump(count($more_return));
		// dump(count($give));

	}else if( $func == 'kanban' ){

			$arr = array();
			$a =  timetodate(strtotime('-4week'),0);
			$b =  timetodate(strtotime('-3 week'),0);
			$c =  timetodate(strtotime('-2 week'),0);
			$d =  timetodate(strtotime('-1 week'),0);
			$e =  timetodate(time(),0);
			$arr = array_merge( getDays($a), getDays($b), getDays($c), getDays($d), getDays($e));

		
			dump($arr);


	}else if( $func == 'my_goods' ){
		// $res = $db->query("SELECT * FROM {$DT_PRE}goods_belong WHERE status in(2,3,5) AND (userid='".$_userid."' OR need_userid = '".$_userid."' ) ORDER BY starttime DESC ");
		// while($r = $db->fetch_array($res)){
		// 	$my_goods[] = $r;

		// }


	}else if( $func == 'my_action' ){  //添加了  权限分配  那就是class有名字的可以操作 自己名下的
		$apply = array();
		$more_return = array();
		$give = array();

		$manager_arr = get_goods_arr($_userid);

		if( $manager_arr ){   //存在处理分类区间
			$res1 = $db->query("SELECT * FROM {$DT_PRE}goods_belong WHERE `gid` in ($manager_arr) AND (`status` = 2 OR `status`=6) ORDER BY starttime DESC ");
			while($r1 = $db->fetch_array($res1)){
				$apply[] = $r1;  //管理员权限  status = 2
			}

			$apply_count = count($apply);

			$repaire_count = $db->count($DT_PRE.'goods_belong', "status=8 AND gid in($manager_arr)");


			// dump($apply_count);
			// dump($repaire_count);

		}

		// if( $USER['isAdmin'] == 1 ){  //管理员操作内容
		// 	//领用 处理
		// 	$res1 = $db->query("SELECT * FROM {$DT_PRE}goods_belong WHERE (`status` = 2 OR `status`=6) ORDER BY starttime DESC ");
		// 	while($r1 = $db->fetch_array($res1)){
		// 		$apply[] = $r1;  //管理员权限  status = 2
		// 	}
		// 	$apply_count = count($apply);
		// 	$repaire_count = $db->count($DT_PRE.'goods_belong', "status=8");
		// }

		$res3 = $db->query("SELECT * FROM {$DT_PRE}goods_belong WHERE ((`userid`='".$_userid."') AND (`status`=5) ) OR ((`need_userid`='".$_userid."') AND (`status`=4)) ");
		while($r3 = $db->fetch_array($res3)){
			$give[] = $r3;  //管理员权限  status = 2
		}
		$give_count = count($give);
			
	}else if( $func == 'ajax_my_goods' ){
		$_userid = $_userid ? $_userid : $userid;
		$t = intval(preg_replace('/nav/i','',$class));
		$t = $t== 0 ? 1: $t;
		if( $t == 1 ){   //借用的物品   要归还
			$sql = "SELECT A.*,B.title,B.is_con FROM {$DT_PRE}goods_belong AS A,{$DT_PRE}goods AS B WHERE A.gid = B.id AND A.userid='".$_userid."' AND (A.status='3' OR A.status='8') AND B.is_con='0' ORDER BY A.starttime DESC ";
		}else if( $t == 2 ){  //领用的物品  无需归还
			$sql = "SELECT A.*,B.title,B.is_con FROM {$DT_PRE}goods_belong AS A,{$DT_PRE}goods AS B WHERE A.gid = B.id AND A.userid='".$_userid."' AND A.status='3' AND B.is_con='1' ORDER BY A.starttime DESC ";
		}else if( $t == 3 ){  //申请的物品  在审核
			$sql = "SELECT A.*,B.title,B.is_con FROM {$DT_PRE}goods_belong AS A,{$DT_PRE}goods AS B WHERE A.gid = B.id AND A.userid='".$_userid."' AND (A.status='2' OR A.status='4' OR A.status='6') ORDER BY A.starttime DESC ";
		}
		$res = $db->query($sql);
		while($r = $db->fetch_array($res)){
			$r['manager'] = get_wp_class(get_goods($r['gid'],'cid'), 'manager');  //查询物品的审核人
			$my_goods[] = $r;


		}
		// dump($sql);
		
		// dump($my_goods);

	}else if( $func == 'ajax_about_my' ){
		if( $use == 'pc'  && $width >400 ){
			$width = 400;
		}

		$res = $db->query("SELECT * FROM {$DT_PRE}log_goods WHERE (`userid`='".$userid."' OR `need_userid`='".$userid."') ORDER BY `time` DESC LIMIT 0,10 ");
		while( $r = $db->fetch_array($res) ){
			$log[] = $r;
		}
	}else if( $func == 'ajax_my_action' ){
		$_userid = $_userid ? $_userid : $userid;
		if( $_userid ){
			$USER = get_user($_userid);
		}
		$t = intval(preg_replace('/nav/i','',$class));
		$manager_arr = get_goods_arr($_userid);

		// dump($manager_arr);
		
		if( $manager_arr ){  //有处理权限
			$t = $t== 0 ? 1: $t;
		}else{
			$t = 10;    //只有3 普通用户可以查看
		}
		// if( $USER['isAdmin'] == 1 ){
		// 	$t = $t== 0 ? 1: $t;
		// }else{
		// 	$t = 10;    //只有3 普通用户可以查看
		// }
		
		if( $t == 1 ){  //申请领用
			$type = 'apply';
			$sql = "SELECT A.*,B.title,B.is_con,B.fromtype FROM {$DT_PRE}goods_belong AS A,{$DT_PRE}goods AS B WHERE A.gid = B.id AND A.gid in($manager_arr) AND (A.status='2' OR A.status='6') ORDER BY A.starttime DESC ";

		}else if( $t == 2 ){  //维修中
			$type = 'repaire';
			$sql = "SELECT A.*,B.title,B.is_con,B.fromtype FROM {$DT_PRE}goods_belong AS A,{$DT_PRE}goods AS B WHERE A.gid = B.id AND A.gid in($manager_arr) AND (A.status='8') ORDER BY A.starttime DESC ";

		}else if( $t == 10 ){   //转让相关   userid  5  别人请求我 我操作        need_userid   4  别人给我  我操作
			$type = 'give';
			$sql = "SELECT A.*,B.title,B.is_con FROM {$DT_PRE}goods_belong AS A,{$DT_PRE}goods AS B WHERE A.gid = B.id AND ((A.userid='".$_userid."' AND A.status =5) OR ( A.need_userid='".$_userid."' AND A.status =4 ))
			ORDER BY A.needtime DESC ";
		}
		$res = $db->query($sql);
		while($r = $db->fetch_array($res)){
			
			$my_action[] = $r;
			
		}
		// dump($my_action);

	}else if( $func == 'ajax_show_log' ){
		$user = get_user($userid);

		if( $use == 'pc'  && $width >400 ){
			$width = 400;
		}
		// $width = $use == 'pe' ? $width : '400';

		$my = $db->get_one("SELECT A.*,B.title,B.is_con FROM {$DT_PRE}goods_belong AS A,{$DT_PRE}goods AS B WHERE A.gid = B.id AND A.id=$id ");
		
		// $log_tmp = $db->query("SELECT * FROM {$DT_PRE}log_goods WHERE bid = $id ORDER BY id DESC LIMIT 5");
		$log_tmp = $db->query("SELECT * FROM {$DT_PRE}log_goods WHERE bid = $id ORDER BY id DESC");
		while( $r = $db->fetch_array($log_tmp) ){
			$log[] = $r;
		}
		// dump($my);
		// dump($log);
	}else if( $func == 'log' ){

	}else if( $func == 'daochu_manage' ){
		$res = $db->query("SELECT `id`,`title`,`fromtype` FROM {$DT_PRE}class WHERE status=1 ORDER BY `fromtype` ASC ");
		while($r =  $db->fetch_array($res) ){
			$r_goods_tmp =   $db->query("SELECT `id`,`title` FROM {$DT_PRE}goods WHERE cid='".$r['id']."' AND status=1 ");
			while($r_goods = $db->fetch_array($r_goods_tmp)){
				$r['goods'][$r_goods['id']] = $r_goods;
				
			}
			$class[] = $r;
		}
		// dump($class); //分类
		



	}else if( $func == 'ajax_daochu_edit' ){
		$g = $db->get_one("SELECT * FROM {$DT_PRE}goods_belong WHERE id = $id ");
		$class_tmp = $db->query("SELECT `id`,`title` FROM {$DT_PRE}class WHERE status=1 ORDER BY  `asc` is null,`asc` asc  ");
		while( $r = $db->fetch_array($class_tmp) ){
			$class[] = $r;
		}

		$goods_tmp = $db->query("SELECT `id`,`cid`,`title` FROM {$DT_PRE}goods WHERE status =1 AND `cid`='".get_goods($g['gid'],'cid')."' ORDER BY id ASC ");
		while( $r = $db->fetch_array($goods_tmp) ){
			$goods[] = $r;
		}

		$log_tmp = $db->query("SELECT * FROM {$DT_PRE}log_goods WHERE bid = $id ORDER BY id DESC LIMIT 10");
		while( $r = $db->fetch_array($log_tmp) ){
			$log[] = $r;
		}

	}
}else if( $ac == 'edit' ){
	if( $func == 'index' ){  //编辑首页

		/********************************************************************************/

		$res = $db->query("SELECT A.*,B.is_con FROM {$DT_PRE}goods_belong AS A,{$DT_PRE}goods AS B WHERE A.gid=B.id AND B.is_con=1 ");

		while( $r = $db->fetch_array($res) ){
			// dump($r);
			$arr[$r['gid']][] = $r;
		}
		// dump($arr);
		foreach( $arr as $k => $v ){
			foreach( $v as $gk=>$gv ){
				// dump($gv);
				$sb[$k][$gv['userid']][] = $gv;
			}
		}
		// dump($sb);

		foreach( $sb as $k=>$v ){
			foreach( $v as $sk=>$sv ){
				$num = count($sv);
				// dump($k);
				

				
				// if( $sk == "" ){   // 那就是没人领的   $sv[0]   解决剩余
				// 	// dump($sv[0]);
					
				// 	$db->query("UPDATE {$DT_PRE}goods_belong SET `num`='".$num."' WHERE id='".$sv[0]['id']."' ");
				// 	$db->query("DELETE FROM {$DT_PRE}goods_belong WHERE gid='".$k."' AND `status`=1 AND id != '".$sv[0]['id']."'  ");
				// }else{  //有人领， 那么这个人名下的物品累加  ，其他删除
				// 	$db->query("UPDATE {$DT_PRE}goods_belong SET `num`='".$num."' WHERE id='".$sv[0]['id']."' ");
				// 	$db->query("DELETE FROM {$DT_PRE}goods_belong WHERE gid='".$k."' AND `status` !=1 AND id != '".$sv[0]['id']."' AND userid='".$sk."'  ");
					
				// }
			}
		}





		/*****************************************************************************************/

		$isMobile = isMobile() ? 1 : 0;
		$title = $fromtype == 1 ? "企业内部物品领用" : "外部物品领用";
		if( $from == 'edit' && $USER['isAdmin'] == 1 ){
			$title = $fromtype == 1 ? "企业内部编辑" : "外部物品编辑";
		}
		$res = $db->query("SELECT * FROM {$DT_PRE}class WHERE status=1 AND `fromtype`='".$fromtype."' ORDER BY  `asc` is null,`asc` asc  ");
		while($r =  $db->fetch_array($res) ){
			$r_goods_tmp =   $db->query("SELECT * FROM {$DT_PRE}goods WHERE status=1 AND cid='".$r['id']."' ");
			while($r_goods = $db->fetch_array($r_goods_tmp)){
				$r['goods'][$r_goods['id']] = $r_goods;
				if( $r_goods['is_con'] == 1 ){
					$tmp_num = $db->get_one("SELECT `num` FROM {$DT_PRE}goods_belong WHERE gid='".$r_goods['id']."' AND status = 1 ");
				}else{
					$tmp_num = $db->get_one("SELECT COUNT('id') as num FROM {$DT_PRE}goods_belong WHERE gid='".$r_goods['id']."' AND userid='' AND status = 1 ");
				}
				// dump($tmp_num);
				
			
				$r['goods'][$r_goods['id']]['num'] = intval($tmp_num['num']);
			}
			// $r['sql'] = "SELECT * FROM {$DT_PRE}goods WHERE cid='".$r['id']."' ";
			$class[] = $r;
			// dump($r);
		}
		
		$tmp_time_arr = explode("_",$USER['returnDate']);
		if( $tmp_time_arr['0'] && $tmp_time_arr['1'] ){
			$returnTime =  date("Y-m-d", strtotime("+".$tmp_time_arr['0']." ".$tmp_time_arr['1']));
		}else{
			$returnTime =  date("Y-m-d", strtotime("+7 days"));
		}
		// dump($returnTime);

	}else if( $func == 'class_manage' ){
		$title = $fromtype == 1 ? '分类管理(内部物品)' : '分类管理(外部物品)';

		if( $fromtype ){
			$res = $db->query("SELECT * FROM {$DT_PRE}class WHERE status = 1 AND fromtype='".$fromtype."' ORDER BY  `asc` is null,`asc` asc ");
			while($r =  $db->fetch_array($res) ){
				$class[] = $r;
			}
		}
		// dump($class);

	}else if( $func == 'wp_manage' ){
		$title = '物品管理';
		if( $USER['isAdmin'] == 1 ){
			$Condition = "";
		}else{ // 非管理员  那就凭manager
			$Condition = "AND `manager`='".$_userid."' ";
		}

		if( $id ){  //传值  id
			$class = $db->get_one("SELECT * FROM {$DT_PRE}class WHERE `id`=$id");
		}else{   //第一次进入  第一个id
			$class = $db->get_one("SELECT * FROM {$DT_PRE}class WHERE `status`=1 $Condition  ORDER BY `asc` ASC ");

		}
		//所有class列表   $class_total
		$r1_class = $db->query("SELECT `id`,`title`,`fromtype` FROM {$DT_PRE}class WHERE `status` = 1 AND `fromtype` = 1 $Condition ORDER BY  `asc` is null,`asc` asc ");
		$r2_class = $db->query("SELECT `id`,`title`,`fromtype` FROM {$DT_PRE}class WHERE `status` = 1 AND `fromtype` = 2 $Condition ORDER BY  `asc` is null,`asc` asc ");
		while($r1 =  $db->fetch_array($r1_class) ){
			$class1[] = $r1;
		}
		while($r2 =  $db->fetch_array($r2_class) ){
			$class2[] = $r2;
		}


		// dump($class_total);
		//当前class   下id的物品   $goods
		$r_goods = $db->query("SELECT * FROM {$DT_PRE}goods WHERE cid= '".$class['id']."' AND status =1 ");
		while($goods_tmp =  $db->fetch_array($r_goods) ){
			$goods[] = $goods_tmp;
		}
		// dump($goods);

	}else if( $func == 'wp_stock' ){
		// dump("goods_stock");

	}else if( $func == 'ajax_check_log' ){
	
		$id_arr = explode(",", $id_total);  //已选集合
		$goods = $db->get_one("SELECT * FROM {$DT_PRE}goods WHERE id =$gid");
		$res = $db->query("SELECT * FROM {$DT_PRE}goods_belong WHERE gid=$gid ORDER BY id DESC ");
		

		while($r = $db->fetch_array($res)){
			$my[] = $r;
		}
		
        // $_POST['end'] = date('Y-m-d', strtotime(date('Y-m-d')." +6 month"));
		
		// date('Y-m-d', strtotime(date('Y-m-d')." +6 month"))

		foreach( $my as $k => $v ){
		// 	$arr = getNeedBook($v['id']);
			$need = $db->query("SELECT * FROM {$DT_PRE}need_book WHERE gid='".$v['id']."' ORDER BY `time` ASC ");
				
			$i = 0;
			while($rn = $db->fetch_array($need)){
				$my[$k]['needbook'][$i] = $rn;
				$i++;
			}
		}


	}else if( $func == 'ajax_confirm_log' ){  //通过时间获取转让  弃用
		if( $type == 'apply' ){  //领用
			$sql = "SELECT A.*,B.title,B.is_con FROM {$DT_PRE}goods_belong AS A,{$DT_PRE}goods AS B WHERE A.gid = B.id AND A.status=2 AND starttime='".$time."' ORDER BY A.starttime DESC ";

			$res = $db->query($sql);
			
			while($r = $db->fetch_array($res)){
				$delHandler[] = $r;
			}
			// dump($delHandler);
		}

	}else if( $func == 'ajax_file_ruku' ){  //入库  导入
		
	}else if( $func == 'check_uploads' ){
		if( $from == 'uploads' ){
			$title = "入库物品编辑";
			// include DT_ROOT.'/api/Classes/PHPExcel/IOFactory.php';  //导入excel 功能
			include DT_ROOT.'/PHPExcel/PHPExcel/IOFactory.php';  //导入excel 功能


			$inputFileName = './uploads/'.$_userid.'.xls';
			date_default_timezone_set('PRC');
			$inputFileType = PHPExcel_IOFactory::identify($inputFileName);
			$objReader = PHPExcel_IOFactory::createReader($inputFileType);
			$objPHPExcel = $objReader->load($inputFileName);
			
			$sheet = $objPHPExcel->getSheet(0);
			$highestRow = $sheet->getHighestRow();
			$highestColumn = $sheet->getHighestColumn();
			for ($row = 1; $row <= $highestRow; $row++){  //2 从第二行开始
				if( $row == 1 ){
					$r1 = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, NULL, TRUE, FALSE);
				

				}else{
					$r = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, NULL,TRUE, FALSE);
					if( $r['0']['1']  ){  //有记录
						$rowData[] = $r['0'];
					}
				}

				
				
			}

				// dump($r1);
			
	
			// dump($rowData);
		
		}else{



		}
		
		

	}else if( $func == 'apply' ){  //申请领用
		$goods = $db->get_one("SELECT * FROM {$DT_PRE}goods WHERE id=$id");
		
		if( $goods['is_con'] == 1 ){
			$goods_belong = $db->get_one("SELECT * FROM {$DT_PRE}goods_belong WHERE gid=$id AND status=1 ");
			$count = intval($goods_belong['num']);
		}else{
			$count = $db->count($DT_PRE.'goods_belong', "gid=$id AND status=1");  //ke领用
		}
		

		


		
		


	}else if( $func == 'ajax_get_image' ){
		// dump($_GET);
		$goods = $db->get_one("SELECT * FROM {$DT_PRE}goods WHERE id=$gid");
		if( $goods['image'] ){
			$image = DT_PATH."uploads/goods/".$goods['image'];
			
		}else{
			$image = "";
		}

	}
}else if( $ac == 'service' ){


}else if( $ac == 'my' ){
	


}


include template($func,$ac);

?>
