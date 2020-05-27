<?php


if(empty($func) || !in_array($func,array('add','status','add_project','add_company'))){
	$res['msg'] = '请求来路错误';
}else{

	if( $func == 'screenshot' ){
		
		
	
	}else if( $func == 'add' ){
		if( $nid ){
			// dump($_POST);
			$r = $db->query("UPDATE {$DT_PRE}news SET `title`= '".$title."',`keywords`='".$keywords."',`screenshot`='".$screenshot."',`content`='".$content."',`edittime`='".time()."' WHERE nid='".$nid."' ");
			$res['code'] = 3;
			$res['msg'] = "修改成功!";

		}else{
			$r = $db->query("INSERT INTO {$DT_PRE}news (`title`,`keywords`,`screenshot`,`content`,`edittime`) values('".$title."','".$keywords."','".$screenshot."','".$content."','".time()."')");
		
			if( $r ){
				$res['code'] = 1;
				$res['msg'] = "添加成功!";
			}else{
				$res['code'] = 2;
				$res['msg'] = "失败,请稍候重试!";
			}
		}
		
	}else if( $func == 'status' ){
		if( $nid ){
			$s1 = $db->get_one("SELECT status FROM {$DT_PRE}news WHERE nid ='".$nid."' ");
			$status = $s1['status'] == 1 ? 0:1;
			$s2 = $db->query("UPDATE {$DT_PRE}news SET `status`='".$status."' WHERE nid='".$nid."' ");
			if( $s2 ){
				$res['code'] = 1;
			}else{
				$res['code'] = 2;
			}
		}else{
			$res['code'] = 2;
		}

	}else if( $func == 'add_project' ){
		if( $pid ){
			$r = $db->query("UPDATE {$DT_PRE}project SET `title`= '".$title."',`desc`='".$desc."',`keywords`='".$keywords."',`nav`='".$nav."',`screenshot`='".$screenshot."',`content`='".$content."',`edittime`='".time()."' WHERE pid='".$pid."' ");
			$res['code'] = 3;
			$res['msg'] = "修改成功!";
		}else{
			$r = $db->query("INSERT INTO {$DT_PRE}project (`title`,`desc`,`keywords`,`nav`,`screenshot`,`content`,`edittime`) values('".$title."','".$desc."','".$keywords."','".$nav."','".$screenshot."','".$content."','".time()."')");
		
			if( $r ){
				$res['code'] = 1;
				$res['msg'] = "添加成功!";
			}else{
				$res['code'] = 2;
				$res['msg'] = "失败,请稍候重试!";
			}
		}

		


	}else if( $func == 'add_company' ){
		
		if( $cid ){
			$r = $db->query("UPDATE {$DT_PRE}company SET `title`= '".$title."',`desc`='".$desc."',`nav`='".$nav."',`content`='".$content."',`edittime`='".time()."' WHERE cid='".$cid."' ");
			$res['code'] = 3;
			$res['msg'] = "修改成功!";
		}else{

			$r = $db->query("INSERT INTO {$DT_PRE}company (`title`,`desc`,`nav`,`content`,`edittime`) values('".$title."','".$desc."','".$nav."','".$content."','".time()."')");
		
			if( $r ){
				$res['code'] = 1;
				$res['msg'] = "添加成功!";
			}else{
				$res['code'] = 2;
				$res['msg'] = "失败,请稍候重试!";
			}
		}

		


	}


}