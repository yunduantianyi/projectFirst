<?php

if(empty($func) || !in_array($func,array('list','get'))){
	$res['msg'] = '请求来路错误';
}else{
	//客户列表
	if($func == 'list'){
		$id = intval($id);
		$action = intval($_POST['action']);
		$level = intval($_POST['level']);
		if( $action ){ //取消
			$db->query("UPDATE {$DT_PRE}company SET level=0 WHERE id=".$id);
			$db->query("DELETE FROM {$DT_PRE}nav WHERE cid=".$id);
			$res['msg'] ='已取消推荐';
			$res['code'] = 1;
		}else{ //修改   需要进行判定
			if( $level ){
				$c = $db->get_one("SELECT * FROM {$DT_PRE}company WHERE id=".$id);
				$count = $db->count($DT_PRE."nav" ,'cid='.$id);
				$db->query("UPDATE {$DT_PRE}company SET level=$level WHERE id=".$id); //修改company表level

				if( $count ){
					$db->query("UPDATE {$DT_PRE}nav SET level=$level WHERE cid=".$id);
				}else{
					$a=$db->query("INSERT INTO {$DT_PRE}nav (`cid`,`company`,`homepage`, `edittime`, `editor`,`level`) VALUES (".$id.",'".$c['company']."','".$c['homepage']."',".time().",'',".$level.") ");
				}
				$res['code'] = 1;
				$res['msg'] = '已修改推荐区';
				$res['sss'] = $a;
			}else{
				$res['msg'] = '请选择推荐区';
				$res['code'] = 2;
			}
		}
		// $res['post'] = $_POST;
	}else if($func == 'get'){
		$level = $_POST['level'];
		if( $level =='' ){

		}elseif( $level=='top' ){  //置顶推荐内容
			// $res = $db->query("SELECT * FROM {$DT_PRE}nav WHERE level=1");
			// while( $r = $db->fetch_array( $res ) ){
			// 	$top[] = $r;
			// }
			// $res['html'] = '';
			// foreach( $top as $k=>$v ){
			
			// }

			dump($res['html']);
			$res['code'] = 1;
		}

	}



}