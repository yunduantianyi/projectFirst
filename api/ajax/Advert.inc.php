<?php

if(empty($func) || !in_array($func,array('add','edit','ads','eads'))){
	$res['msg'] = '请求来路错误';
}else{
	
	if($func == 'add'){//添加广告位  

		$sql = "INSERT INTO {$DT_PRE}ad (`title`,`introduce`,`width`,`height`,`editor`,`addtime`,`image_url`,`status`,`price`) VALUES('".$title."','".$introduce."','".$width."','".$height."','','".time()."','','".$status."','".$price."')";
		$res['asd'] = $sql;
		if( $db->query($sql) ){
			$res['msg'] = '添加成功!';
			$res['code'] = 1;
		}

	}else if( $func == 'edit' ){
		$id = intval($_POST['id']);
		$sql = "UPDATE {$DT_PRE}ad SET `title`='".$title."',`introduce`='".$introduce."',`width`='".$width."',`height`='".$height."',`edittime`='".time()."',`status`='".$status."',`price`='".$price."' WHERE id =$id ";
		if( $db->query($sql) ){
			$res['msg'] = '修改成功!';
			$res['code'] = 1;
		}
	}else if( $func == 'ads' ){
		$id = intval($_POST['id']);
		$start = strtotime($_POST['start']);
		$end = strtotime($_POST['end']);
		$sql = "INSERT INTO {$DT_PRE}ads (`aid`,`title`,`thumb`,`introduce`,`status`,`start`,`end`,`addtime`) VALUES('".$id."','".$title."','','".$introduce."','".$status."','".$start."','".$end."','".time()."')";
		if( $db->query( $sql ) ){
			$db->query("UPDATE {$DT_PRE}ad SET amount=amount+1 WHERE id=$id");
			$res['msg'] = '添加成功!';
			$res['code'] = 1;
		}
	}else if( $func == 'eads' ){
		// dump($_POST);
		$pid = intval($_POST['pid']);
		$start = strtotime($_POST['start']);
		$end = strtotime($_POST['end']);
		$sql = "UPDATE {$DT_PRE}ads SET `title`='".$title."',`introduce`='".$introduce."',`status`='".$status."',`start`='".$start."',`end`='".$end."',`edittime`='".time()."' WHERE pid=$pid";
		if( $db->query($sql) ){
			$res['msg'] = '修改成功!';
			$res['code'] = 1;
		}
	}


}