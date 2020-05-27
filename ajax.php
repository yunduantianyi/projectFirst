<?php
/*
	Author: [ChinaWin]
*/
require 'common.inc.php';

// if($action != 'mobile') {
// 	check_referer() or exit;
// }
require DT_ROOT.'/include/post.func.php';


if(empty($ac) || empty($func)){
	$res['msg'] = '请求来路错误!';
}else{

if( $ac == 'Index' ){
	if( $func == 'ajax_my_goods' ){    //my goods

		$str = file_get_contents(DT_PATH."page.php?ac=index&func=ajax_my_goods&class=".$class."&userid=".$userid);//将整个文件内容读入到一个字符串中
		$str = str_replace("\r\n"," ",$str);
		$res['html'] = $str;
		$res['class'] = $class;

	}else if( $func == 'ajax_set_opinion' ){  //日志
		if( trim($opinion) ){
			$db->query("INSERT INTO {$DT_PRE}opinion (`userid`,`name`,`opinion`,`time`) VALUES('".$_userid."','".$USER['name']."','".$opinion."','".timetodate(time(),5)."') ");

			$res['code'] = 1;
			$res['msg'] = "已提交反馈,谢谢";
		}else{
			$res['code'] = 2;
		}

	}else if( $func == 'ajax_about_my' ){  //actions

		if( $act ){
			if( $USER['isAdmin']  || ($type == 8)){  //管理员查看所有
				$condition = '1';

			}else{
				$condition = "(userid = '".$_userid."' OR need_userid = '".$_userid."')";
			}
			
			// dump($_POST);
			if( $type ==  2){
				$id = '2,21,22,20';
			}else if( $type == 4 ){
				$id = '4,41,42,40';
			}else if( $type == 6 ){
				$id = '6,61,60,7';
			}else if( $type == 1 ){
				$id = '1,13';
			}else if( $type == 8 ){
				$id = '8,81';
			}else if( $type == 9 ){
				$id = '91';
			}
			if( $act == 'more' ){
				$min = intval($ten * 10);
				if( $type == 0 ){  //0  最初的加载
					$tmp = $db->query("SELECT * FROM {$DT_PRE}log_goods WHERE $condition ORDER BY `time` DESC LIMIT $min,10 ");
				}else{
					
					$tmp = $db->query("SELECT * FROM {$DT_PRE}log_goods WHERE $condition AND `status` in($id) ORDER BY `time` DESC LIMIT $min,10 ");
				}
				
			}else{  //apply
				$tmp = $db->query("SELECT * FROM {$DT_PRE}log_goods WHERE $condition AND `status` in($id) ORDER BY `time` DESC LIMIT 10 ");
				// dump("SELECT * FROM {$DT_PRE}log_goods WHERE $condition AND `status` in($id) ORDER BY `time` DESC LIMIT 10 ");
			}
			$count = 0;

			while( $v = $db->fetch_array($tmp) ){
				$sb[] = $v;
				if( get_user($v['userid'],'avatar') ){    //$avatar  头像
					$avatar = "<img src=".get_user($v['userid'],'avatar')." class='my_avatar'>";
				}else{
					$avatar = getNickName(get_user($v['userid'],'name'));
				}
				if($v['status'] == 1){    //XXX干了啥
					$action = get_user($v['userid'],'name').' 入库物品';
				}else if($v['status'] == 13){
					$action = get_user($v['userid'],'name').' 入库分派给 '.get_user($v['need_userid'],'name').'使用';
				}else if($v['status'] == 2){
					$action = get_user($v['userid'],'name').' 申请领用';
				}else if( $v['status'] == 22 ){
					$action = get_user($v['userid'],'name').' 撤回领用申请';
				}else if( $v['status'] == 21 ){
					$action = get_user($v['userid'],'name').' 同意了 '.get_user($v['need_userid'],'name').'的申请';
				}else if( $v['status'] == 20 ){
					$action = get_user($v['userid'],'name').' 驳回了 '.get_user($v['need_userid'],'name').'的申请';
				}else if( $v['status'] == 4 ){
					$action = get_user($v['userid'],'name').' 转让给 '.get_user($v['need_userid'],'name');
				}else if( $v['status'] == 42 ){
					$action = get_user($v['userid'],'name').' 撤销转让给 '.get_user($v['need_userid'],'name');
				}else if( $v['status'] == 41 ){
					$action = get_user($v['need_userid'],'name').' 确认转让并接收';
				}else if( $v['status'] == 40 ){
					$action = get_user($v['need_userid'],'name').' 拒绝接收转让';
				}else if( $v['status'] == 6 ){
					$action = get_user($v['userid'],'name').' 归还了物品';
				}else if( $v['status'] == 61 ){
					$action = get_user($v['userid'],'name').' 将物品重新放回物品库';
				}else if( $v['status'] == 60 ){
					$action = get_user($v['userid'],'name').' 将物品设置为<b style="color:#ccc">已报废</b>';
				}else if( $v['status'] == 7 ){
					$action = get_user($v['userid'],'name').' 将物品设置为<b style="color:#ccc">归还客户</b>';
				}else if( $v['status'] == 8 ){
					$action = get_user($v['userid'],'name').' 将物品设为<b style="color:#f30">维修中</b>';
				}else if( $v['status'] == 81 ){
					$action = get_user($v['userid'],'name').' <b style="color:#f30">完成维修</b>,重新放回物品库';
				}else if( $v['status'] == 9 ){
					$action = get_user($v['userid'],'name').' 将物品设为<b style="color:#888">已报废</b>';
				}else if( $v['status'] == 91 ){
					$action = get_user($v['userid'],'name').' <b style="color:#F60">请求借阅</b>图书';
				}

				if( get_goods_belong($v['bid'],'bianhao') ){
					$bianhao = '( '.get_goods_belong($v['bid'],'bianhao').' )';
				}
				if( $v['note'] ){
					$note = '<div class="log_note">'.$v['note'].'</div><div class="log_line"></div>';
				}else{
					$note = '<div class="log_line"></div>';
				}

		
				$h .= '<div class="log"><span>'.$avatar.'</span><span style="width:'.$width.'px;">'.$action.'</span><span>'.timetodate($v['time'],5).'</span></div><div class="log_note">'.get_goods(get_goods_belong($v['bid'],'gid'),'title').$bianhao.'</div>'.$note;
				$count +=1;
			}
			// dump($sb);

			$res['html'] = $h ? $h : "";
			$res['count'] = $count;
			$res['code'] = 1;
		}else{
			$use = isMobile() ? 'pe' : 'pc';  //pc pe 判断

			$str = file_get_contents(DT_PATH."page.php?ac=index&func=ajax_about_my&width=".$width."&height=".$height."&use=".$use."&userid=".$_userid);//
			$str = str_replace("\r\n"," ",$str);
			$res['html'] = $str;

		}
		
	}else if( $func == 'daochu_edit' ){  //导出页面   编辑删除操作
		$g = $db->get_one("SELECT * FROM {$DT_PRE}goods_belong WHERE id=$id");
		if( $g ){
			if( $type == 'del' ){   //删除  
				// dump($_POST);
				$note = "删除了 ".get_goods(get_goods_belong($id,'gid'),'title')."(".get_goods_belong($id,'bianhao').")";
				if( $db->query("DELETE FROM {$DT_PRE}goods_belong WHERE id = $id") ){
					$res['code'] = 1;
					$res['msg'] = "删除成功";

					$db->query("INSERT INTO {$DT_PRE}log_goods (`bid`,`note`,`userid`,`time`,`status`) VALUES('".$id."', '".$note."', '".$_userid."', '".time()."', '0' ) ");
				}else{
					$res['code'] = 3;
					$res['msg'] = "删除失败,请将 <b style='color:#red'>".$id."</b> 提供给开发者查看！";
				}
				$res['code'] = 1;
				$res['msg'] = "删除成功";
			}else if( $type == 'edit' ){ //编辑  页面

			}
		}else{
			$res['code'] = 2;
			$res['msg'] = "物品已不存在";
		}

	}else if( $func == 'ajax_daochu_edit' ){
		if( $type == 'html' ){
			$str = file_get_contents(DT_PATH."page.php?ac=index&func=ajax_daochu_edit&id=".$id);//
			$str = str_replace("\r\n"," ",$str);
			$res['html'] = $str;
		}else if( $type == 'edit' ){
			// dump($_POST);
			if( $USER['isAdmin'] ){

				$g = $db->get_one("SELECT * FROM {$DT_PRE}goods_belong WHERE id=$id");

				if( $g ){
					if( $g['gid'] != $gid ){
						$t .= get_goods($g['gid'],'title')."->".get_goods($gid,'title').", ";
					}
					if( ($g['userid'] != $userid) ){
						$t .= get_user($g['userid'],'name')."->".get_user($userid,'name').", ";
					}
					if( $g['status']  != $status){
					 	$t .="物品状态改变, ";
					}
					if( $g['bianhao'] != trim($bianhao) ){
						$t .= "编号 ".$g['bianhao']."->".$bianhao.", ";
					}
					if( $g['xinghao'] != trim($xinghao) ){
						$t .= "型号 ".$g['xinghao']."->".$xinghao.", ";
					}
					if( $g['neicun'] != trim($neicun) ){
						$t .= "内存 ".$g['neicun']."->".$neicun.", ";
					}
					if( $g['banben'] != trim($banben) ){
						$t .= "版本 ".$g['banben']."->".$banben.", ";
					}
					if( $g['xitong'] != trim($xitong) ){
						$t .= "系统 ".$g['xitong']."->".$xitong.", ";
					}
					if( $g['note'] != trim($note) ){
						$t .= "备注 ".$g['note']."->".$note.", ";
					}
					if( $g['note_use'] != trim($note_use) ){
						$t .= "领用备注 ".$g['note_use']."->".$note_use.", ";
					}
					
					if( $t ){  //有修改
						$t = $USER['name']."编辑 ".substr($t,0,-1);
						if( ($status==1) || ($status==7) || ($status==8) || ($status==9) ){
							$userid = "";
							
							$note_use = "";
							$starttime = "";
							$res['code'] = 1;
						}else{
							if( $userid == "" ){
								$res['code'] = 2;
							}else{
								$res['code'] = 1;
							}
							$starttime = $t['starttime'];

						}

						if( $res['code'] == 1 ){

							$db->query("UPDATE {$DT_PRE}goods_belong SET `gid`='".$gid."',`userid`='".$userid."',`status`='".$status."',`bianhao`='".$bianhao."',`xinghao`='".$xinghao."',`neicun`='".$neicun."',`banben`='".$banben."',`xitong`='".$xitong."',`note`='".$note."',`note_use`='".$note_use."',`starttime`='".$starttime."' WHERE id=$id ");
							$db->query("INSERT INTO {$DT_PRE}log_goods (`bid`,`note`,`userid`,`time`,`status`) VALUES('".$id."','".$t."','".$_userid."','".time()."','0') ");

							$res['return']['gid'] = get_goods($gid,'title');
							$res['return']['userid'] = get_user($userid,'name');
							$res['return']['bianhao'] = trim($bianhao);
							$res['return']['xinghao'] = trim($xinghao);
							$res['return']['neicun'] = trim($neicun);
							$res['return']['banben'] = trim($banben);
							$res['return']['xitong'] = trim($xitong);
							$res['return']['note'] = trim($note);
							$res['return']['note_use'] = trim($note_use);

							$res['msg'] = "修改成功";
						}else{
							$res['msg'] = "请选择使用人或修改状态";
						}
					}else{
						$res['code'] = 2;
						$res['msg'] = "未作出修改！";

					}
				}else{
					$res['code'] = 2;
					$res['msg'] = "该物品已不存在,请重新搜索";
				}
			}else{
				$res['code'] = 3;
				$res['msg'] = "";
			}

		}
	}else if( $func == 'ajax_my_action' ){  //actions

		$str = file_get_contents(DT_PATH."page.php?ac=index&func=ajax_my_action&class=".$class."&userid=".$userid);//
		$str = str_replace("\r\n"," ",$str);
		$res['html'] = $str;
	}else if( $func == 'check_more_return' ){
		$r = $db->get_one("SELECT * FROM {$DT_PRE}goods_belong WHERE id=$id");
		$more_return =  strtotime($more_return);   //日期转时间戳
		if( $type == 'check' ){  //检查 more_return 是否合适
			if( $r['more_return']  ){   //已经申请中并且  比我的大
				$res['code'] = 1;
				$res['msg'] = "已经申请延期至<span style='color:#f60'>".timetodate($r['more_return'],0)."</span><br/>以下时间将覆盖之前申请<span style='color:#f60'>".timetodate($more_return,0)."</span>";
			}else{
				$res['code'] = 1;
				$res['msg'] = "是否申请延期至<span style='color:#f60'>".timetodate($more_return,0)."</span>";
			}
		}else{   //正式 updaAte
			$db->query("UPDATE {$DT_PRE}goods_belong SET `more_return`='".$more_return."' WHERE id=$id ");
			$db->query("INSERT INTO {$DT_PRE}log_goods (`bid`,`userid`,`time`,`status`) VALUES('".$id."','".$_userid."', '".time()."', '6' ) ");
			$res['code'] = 1;
			$res['msg'] ='已提交延期申请！';
		}
	}else if( $func == 'ajax_show_log' ){  //
		
			$use = isMobile() ? 'pe' : 'pc';  //pc pe 判断
			$str = file_get_contents(DT_PATH."page.php?ac=index&func=ajax_show_log&width=".$width."&height=".$height."&userid=".$userid."&id=".$id."&type=".$type."&use=".$use);//将整个文件内容读入到一个字符串中
			$str = str_replace("\r\n"," ",$str);
			$res['html'] = $str;
		
		
	}else if( $func == 'ajax_find_info' ){  //ajax 查询条件
		$condition = "1";
		// dump($keywords);
		if( $category ){
			$category =  substr($category, 0, -1);
			$condition .= " AND gid in($category) ";
		}
		if( $userid ){
			$u_arr = explode(",",$userid);
			foreach ($u_arr as $k => $v) {
				if( $v ){
					$user_tmp .= "'".$v."',";
				}
				
			}
			$userid =  substr($user_tmp, 0, -1);

			$condition .= " AND userid in($userid) ";
		}
		if( $status ){
			$status =  substr($status, 0, -1);   //1领用  2申请中  3 使用中(4 6)
			$condition .= " AND status in($status) ";
		}

		// dump($status);
		if( $starttime ){  //领用时间
			$start_end = explode(" - ",$starttime);
			$start = datetotime($start_end['0']);
			$end = datetotime($start_end['1']);
			$condition .= " AND starttime BETWEEN $start AND $end";
		}
		if( $usingtime ){  //启用时间
			$start_end = explode(" - ",$usingtime);
			$start = datetotime($start_end['0']);
			$end = datetotime($start_end['1']);
			$condition .= " AND usingtime BETWEEN $start AND $end";

		}
		if( $edittime ){
			$start_end = explode(" - ",$edittime);
			$start = datetotime($start_end['0']);
			$end = datetotime($start_end['1']);
			$condition .= " AND edittime BETWEEN $start AND $end";
		}
		if( trim($keywords) ){
			$condition .= " AND (`id` like '%$keywords%' OR `bianhao` like '%$keywords%' OR `xinghao` like '%$keywords%' OR `neicun` like '%$keywords%' OR `banben` like '%$keywords%' OR `xitong` like '%$keywords%' OR `note` like '%$keywords%' OR `note_use` like '%$keywords%' )";
		}
		$min = ($page-1) * $limit;
		$max = $page * $limit;
		$count = $db->count($DT_PRE."goods_belong",$condition);
		$tmp = $db->query("SELECT * FROM {$DT_PRE}goods_belong WHERE $condition LIMIT $min,$max");

		while( $r = $db->fetch_array($tmp) ){

			$r['userid'] = get_user($r['userid'], 'name');
			$r['gid'] = get_goods($r['gid'], 'title');
			$r['starttime'] = $r['starttime'] ? timetodate($r['starttime'],5) : "";  //领用时间
			$r['edittime'] = $r['edittime'] ? timetodate($r['edittime'],5) : "";   //入库时间
			$r['usingtime'] = $r['usingtime'];  //启用时间
			if( $r['status']== '1' ){
				$r['status'] = '<span style="color:#f90">可领用</span>';
			}else if( $r['status'] == '2' ){
				$r['status'] = '<span style="color:red">申请中</span>';
			}else if( $r['status'] == '3' ){
				$r['status'] = '<span style="color:red">使用中</span>';
			}else if( $r['status'] == '4' ){
				$r['status'] = '<span style="color:#38ADFF">转让中</span>';
			}else if( $r['status'] == '6' ){
				$r['status'] = '<span style="color:#38ADFF">归还中</span>';
			}else if( $r['status'] == '7' ){
				$r['status'] = '<span style="color:#ccc">已归还客户</span>';
			}else if( $r['status'] == '8' ){
				$r['status'] = '<span style="color:#f30">维修中</span>';
			}else if( $r['status'] == '9' ){
				$r['status'] = '<span style="color:#ccc">已报废</span>';
			}

			$arr[] = $r;
		}
		$res['code'] = 0;
		$res['count'] = $count;
		$res['msg'] = "";
		$res['data'] = $arr;
	}else if( $func == 'ajax_find_goods_info' ){
		$tmp = $db->query("SELECT `id`,`title` FROM {$DT_PRE}goods WHERE cid = $gid AND status=1 ORDER BY edittime ASC ");
		while( $r = $db->fetch_array($tmp) ){
			$goods[] = $r;
		}
		foreach( $goods as $k=>$v ){
			$option .= "<option value='".$v['id']."'>".$v['title']."</option>";
		}

		$res['html'] = $option;
	}
}else if( $ac == 'Edit' ){   //编辑   ajax 处理
	if( $func == 'class_clear_asc' ){   // 编辑——清空排序
		$condition = "";
		$r = $db->query("UPDATE {$DT_PRE}class SET `asc`='' WHERE `fromtype`=$fromtype ");
		$res['code'] = 1;
		// $res['msg'] = "aaaa";
		// dump($res);
	}else if( $func == 'class_del' ){
		// dump($_POST);
		$id_arr = explode(',', $id);  //分解 id
		if( $type == 0 ){   //查询  class
			// dump($id_arr);
			$msg = "";
			foreach( $id_arr as $v ){
				// dump($v);
				$tmp[$v] = $db->get_one("SELECT COUNT('id') as num FROM {$DT_PRE}goods WHERE cid='".$v."' AND status =1 ");
				$t = $db->get_one("SELECT `title` FROM {$DT_PRE}class WHERE id='".$v."' ");
				if( $tmp[$v]['num'] == 0 ){  //数量为0  通知删除项
					$msg_nexist .= "<span style='color:#f60'>".$t['title']."</span> 、";
					unset($tmp[$v]);
				}else{  //数量部位0  通知不能删
					$msg_exist .= "<span style='color:#f60'>".$t['title']."</span>分类有（<span style='color:#f60'>".$tmp[$v]['num']."</span>个物品） 、";
				}
			}
			if( $tmp ){  //数量存在
				$res['code'] = 1;
				$res['msg'] = rtrim($msg_exist, "、")."无法删除分类!";
			}else{  //空的

				$res['code'] = 2;

				$res['msg'] = rtrim($msg_nexist, "、")."分类下已无物品，请确认是否删除？";
			}
			
		}else if( $type == 1 ){  //开始删除
			foreach ($id_arr as $v) {
				// $db->query("UPDATE {$DT_PRE}class SET `status` = 0,`edittime` = '".time()."' WHERE id='".$v."' ");
				$db->query("DELETE FROM  {$DT_PRE}class WHERE id='".$v."' ");
			}
			$res['code'] = 1;
			$res['msg'] = "删除成功!";
			

		}
	}else if( $func == 'class_confirm' ){  //code 1 成功   2 无数据  3 数据不通过        fromtype  1 公司  2外部
		// dump($arr);
		$tmp = array();  //存放处理数组
		$tips = "";
		if( $arr ){
			foreach( $arr as $k => $v ){
				if( $v['name'] == 'id' ){
					$tmp[$k]['id'] = $arr[$k]['value'];
					$tmp[$k]['title'] = trim($arr[$k+1]['value']);  //名称
					$tmp[$k]['asc'] = intval($arr[$k+2]['value']);  //排序
					$tmp[$k]['manager'] = $arr[$k+3]['value'];  //负责人

					$title[] = trim($arr[$k+1]['value']);  //判定重复
					$desc[] = intval($arr[$k+2]['value']);  //排序判定
				}
			}

			foreach (array_count_values($title) as $tk => $tb) {
				if( $tk == "" ){  //传入了空分类
					$t .= "分类不能为空!<br/>";
				}else{  //判断是否有同名
					if( $tb  > 1 ){
						$t .= "<span style='color:#f60'>".$tk."</span>重复!<br/>"  ;
					}
				}
			}

			if( !$t ){   //没有错误log
				// dump($tmp);
				if( $type == 0 ){  //筛选修改添加  替换负责人
					$msg = "";
					foreach ($tmp as $tk => $tv) {
						if( $tv['id'] ){   //有id 判断是否更新
							$r = $db->get_one("SELECT * FROM {$DT_PRE}class WHERE id='".$tv['id']."' ");
							if( ($tv['title'] != $r['title'] ) || ($tv['asc'] != $r['asc'] ) || ($tv['manager'] != $r['manager']) ){
								if( $tv['title'] != $r['title'] ){
									$msg .= "修改<span style='color:#f60'>".$r['title']."</span> 名称：<span style='color:#f60'>".$tv['title']."</span>";
								}else{
									$msg .="修改<span style='color:#f60'>".$r['title']."</span>";
								}
								if( $tv['manager'] != $r['manager'] ){
									$msg .= " 负责人:<span style='color:#f60'>".get_user($r['manager'],'name')." => ".get_user($tv['manager'],'name')."</span>";
								}

								if( $tv['asc'] != $r['asc'] ){
									$msg .= " 排序：<span style='color:#f60'>".$r['asc']." => ".$tv['asc']."</span><br/>";
								}else{
									$msg .= "<br>";
								}

							}
						}else{  //无id  插表
							$msg .="<span style='color:red'>添加</span>&nbsp;&nbsp;<span style='color:#f60'>".$tv['title']."</span> 排序 ".$tv['asc']."<br/>";
						}
					}
					if( $msg ){
						$res['code'] = 1;
						$res['msg'] = $msg;
					}else{
						$res['code'] = 2;
						$res['msg'] = "未对分类作出修改!";
					}

				}else{  //正式添加
					// dump($tmp);
					foreach ($tmp as $tk => $tv) {
						if( $tv['id'] ){   //有id 判断是否更新
							$r = $db->get_one("SELECT * FROM {$DT_PRE}class WHERE id='".$tv['id']."' ");
							if( ($tv['title'] != $r['title'] ) || ($tv['asc'] != $r['asc'] ) || ($tv['manager'] !=$r['manager']) ){
								$db->query("UPDATE {$DT_PRE}class SET `title`='".$tv['title']."',`asc`='".$tv['asc']."',`manager`='".$tv['manager']."',`edittime`='".time()."' WHERE id = '".$tv['id']."' ");

							}
						}else{  //无id  插表
							$db->query("INSERT INTO {$DT_PRE}class (`title`,`asc`,`edittime`,`fromtype`,`manager`) VALUES('".$tv['title']."', '".$tv['asc']."' ,'".time()."','".$fromtype."','".$tv['manager']."') ");
						}
					}
					$res['code'] = 1;
				
				}
			}else{
				$res['msg'] = $t;
				$res['code'] = 2;
			}
		}else{
			$res['msg'] = "无分类数据，请添加新分类!";
			$res['code'] = 2;
		}
	}else if( $func == 'wp_del' ){
		$id_arr = explode(',', $id);  //分解 id
		if( $type == 0 ){   //查询  class
			// dump($id_arr);
			$msg = "";
			foreach( $id_arr as $v ){
				// dump($v);
				$tmp[$v] = $db->get_one("SELECT COUNT('id') as num FROM {$DT_PRE}goods_belong WHERE gid='".$v."' AND userid != '' ");
				$t = $db->get_one("SELECT `title` FROM {$DT_PRE}goods WHERE id='".$v."' ");
				if( $tmp[$v]['num'] == 0 ){  //数量为0  已经无人使用
					$msg_nexist .= "<span style='color:#f60'>".$t['title']."</span> 、";
					unset($tmp[$v]);
				}else{  //数量部位0  通知不能删
					$msg_exist .= "<span style='color:#f60'>".$t['title']."</span>有（<span style='color:#f60'>".$tmp[$v]['num']."</span>人使用） 、";
				}
			}
			if( $tmp ){  //数量存在
				$res['code'] = 1;
				$res['msg'] = rtrim($msg_exist, "、")."无法删除物品!";
			}else{  //空的

				$res['code'] = 2;

				$res['msg'] = rtrim($msg_nexist, "、")."物品已无人使用，请确认是否删除？";
			}
			
		}else if( $type == 1 ){  //开始删除
			foreach ($id_arr as $v) {

				// $db->query("UPDATE {$DT_PRE}goods SET `status` = 0,`edittime` = '".time()."' WHERE id='".$v."' ");
				$db->query("DELETE FROM {$DT_PRE}goods WHERE id='".$v."' ");
			}
			$res['code'] = 1;
			$res['msg'] = "删除成功!";
			

		}
	}else if( $func == 'wp_confirm' ){
		$tmp = array();  //存放处理数组
		// dump($_POST);
		$tips = "";
		if( $arr ){	
			foreach( $arr as $k => $v ){
				if( $v['name'] == 'id' ){
					$tmp[$k]['id'] = $arr[$k]['value'];
					$tmp[$k]['title'] = trim($arr[$k+1]['value']);
					$tmp[$k]['is_con'] = trim($arr[$k+2]['value']);
					$tmp[$k]['note'] = trim($arr[$k+3]['value']);

					$title[] = trim($arr[$k+1]['value']);  //判定重复  物品title
				}
			}

			foreach (array_count_values($title) as $tk => $tb) {
				if( $tk == "" ){  //传入了空分类
					$t .= "物品名不能为空!<br/>";
				}else{  //判断是否有同名
					$other_rp = $db->get_one("SELECT * FROM {$DT_PRE}goods WHERE `title`= '".$tk."' AND `cid` <> '".$cid."' ");
					if( $tb  > 1 ){
						$t .= "<span style='color:#f60'>".$tk."</span>重复!<br/>"  ;
					}
					if( $other_rp ){
						$t .= "<span style='color:#f60'>".$tk."</span>与其他分类下物品名重复!<br/>"  ;
					}
					

				}
			}
			
			// exit;
			if( !$t ){   //没有错误log
				// dump($tmp);
				if( $type == 0 ){  //筛选修改添加
					$msg = "";
					foreach ($tmp as $tk => $tv) {
						if( $tv['id'] ){   //有id 判断是否更新
							$r = $db->get_one("SELECT * FROM {$DT_PRE}goods WHERE id='".$tv['id']."' AND cid='".$cid."' ");
							if( ($tv['title'] != $r['title'] ) || ($tv['note'] != $r['note'] ) || ($tv['is_con'] != $r['is_con'] ) ){
								if( trim($tv['title']) != trim($r['title']) ){
									$msg .= "修改<span style='color:#f60'>".$r['title']."</span> 名称：<span style='color:#f60'>".$tv['title']."</span>";
								}else{
									$msg .="修改<span style='color:#f60'>".$r['title']."</span>";
								}
								if( $tv['is_con'] != $r['is_con'] ){
									if( $tv['is_con'] == 0 ){  //修改为  非消耗品
										$msg .= " 为<b>非消耗品</b>";

									}else{   //修改为消耗品
										$msg .= " 为<b>消耗品</b>";
									}
								}


								if( trim($tv['note']) != trim($r['note']) ){
									$msg .= "，备注被修改<br>";
								}else{
									$msg .= "<br>";
								}

							}
						}else{  //无id  插表
							$msg .="<span style='color:red'>添加</span>&nbsp;&nbsp;<span style='color:#f60'>".$tv['title']."</span><br/>";
						}
					}
					if( $msg ){
						$res['code'] = 1;
						$res['msg'] = $msg;
					}else{
						$res['code'] = 2;
						$res['msg'] = "未对物品作出修改!";
					}

				}else{  //正式添加  type = 1
					// dump($tmp);
					$time = time();
					foreach ($tmp as $tk => $tv) {
						if( $tv['id'] ){   //有id 判断是否更新
							$r = $db->get_one("SELECT * FROM {$DT_PRE}goods WHERE id='".$tv['id']."' AND cid ='".$cid."' ");
							if( ($tv['title'] != $r['title'] ) || ($tv['note'] != $r['note'] || ($tv['is_con'] != $r['is_con'] ) ) ){
								$db->query("UPDATE {$DT_PRE}goods SET `title`='".$tv['title']."',`note`='".$tv['note']."',`is_con`='".$tv['is_con']."',`edittime`='".time()."' WHERE id = '".$tv['id']."' AND cid = '".$cid."' ");

							}
						}else{  //无id  插表
							$db->query("INSERT INTO {$DT_PRE}goods (`cid`,`title`,`note`,`is_con`,`fromtype`,`edittime`) VALUES('".$cid."','".$tv['title']."', '".$tv['note']."' , '".$tv['is_con']."','".$fromtype."','".$time."') ");
						}
					}
					$res['code'] = 1;
				
				}
			}else{
				$res['msg'] = $t;
				$res['code'] = 2;
			}
		}else{
			$res['msg'] = "未检测到新增物品!";
			$res['code'] = 2;
		}
	}else if( $func == 'goods_action' ){ //apply 领用转让
		$goods = $db->get_one("SELECT * FROM {$DT_PRE}goods WHERE id = $gid");  //获取goods 信息
		if( $arr ){
			$tmp = array();  //存放处理数组

			if( $goods['is_con'] == 1 ){   //消耗品另作处理  只要gid 数量和备注
				$ub = $db->get_one("SELECT * FROM {$DT_PRE}goods_belong WHERE gid=$gid AND status=1");

				$count = intval($ub['num']);  //还剩多少的消耗品
				$num = intval($arr[2]['value']);   //申请的数量
				$note_use = $arr[3]['value'];
				$minus = intval($count - $num);  //要减去的数量

				if( ($num <= $count) || ($num >= 1) ){  //    1<数量<  $count
					$my_belong = $db->get_one("SELECT * FROM {$DT_PRE}goods_belong WHERE gid=$gid AND userid='".$_userid."' AND status = 2 ");  //本人是否有物品申请中 ？ 存在记录？
				
					if( $my_belong['num'] && intval($my_belong['num']) >= 0 ){   //记录和数量存在    这人已有就add  没有insert
						$add = intval($my_belong['num'])+$num;
						$db->query("UPDATE {$DT_PRE}goods_belong SET `num`=$add WHERE gid=$gid AND userid='".$_userid."' AND status = 2  ");
						$insert_id = $my_belong['id'];
				
					}else{  //数量不存在 直接insert一条记录
						$db->query("INSERT INTO {$DT_PRE}goods_belong (`gid`,`bianhao`,`xinghao`,`neicun`,`banben`,`xitong`,`note_use`,`userid`,`starttime`,`num`,`status`) VALUES('".$gid."','".$ub['bianhao']."','".$ub['xinghao']."','".$ub['neicun']."','".$ub['banben']."','".$ub['xitong']."','".$note_use."','".$_userid."','".time()."','".$num."','2') ");
						$insert_id = $db->insert_id();
				
					}
					$note = get_user($_userid,'name')."申请领用  ".$num."  个该物品";
					$db->query("UPDATE {$DT_PRE}goods_belong SET `num`=$minus WHERE gid=$gid AND status=1 ");  //减少物品
					$db->query("INSERT INTO {$DT_PRE}log_goods (`bid`,`note`,`userid`,`need_userid`,`time`,`status`) VALUES('".$insert_id."','".$note."','".$_userid."','','".time()."','0') ");

					$res['cid'] = $goods['cid'];
					$res['content'] = get_user($_userid,'name')."申请领用  ".$num."  个".$goods['title'];

					$res['msg'] = "申请成功,请等待批复!";
					$res['code'] = 1;



				}else{
					$res['code'] = 3;
					$res['msg'] = "领取数量异常！";
				}
			}else{
				foreach( $arr as $k => $v ){
					if( $v['name'] == 'id' ){
						$tmp[$k]['id'] = $arr[$k]['value'];
						$tmp[$k]['status'] = trim($arr[$k+1]['value']);
						$tmp[$k]['note_use'] = trim($arr[$k+2]['value']);

						$err = $db->get_one("SELECT `status` FROM {$DT_PRE}goods_belong WHERE id='".$tmp[$k]['id']."' ");
						
						if( ($tmp[$k]['status'] == 1) && ($err['status'] != 1 ) ){  //领用状态  但发生改变了
							$err_html .= "物品(".intval( intval($k / 3)+1 )."),";
							$errid[] = $tmp[$k]['id'];
						}
					}
				}
				if( $err_html ){  //存在处理的 优先级
					$err_html = substr($err_html, 0, -1);
					$res['code'] = 2;
					$res['msg'] = "<span style='color:#2e6da4'>".$err_html."</span> 状态已发生改变，请重新申请";
					$res['errid'] = $errid;
				}else{  // ok的啦  爽快申请吧
					$time_tmp = time();  //当前时间   遍历添加可能会延迟不准确
					foreach ($tmp as $k => $v) {

						if( $v['status'] == 1 ){  //领用   除了1 需要注意
							$db->query("UPDATE {$DT_PRE}goods_belong SET `userid`='".$_userid."' ,`note_use`='".$v['note_use']."',`starttime`='".$time_tmp."',`status`='2' WHERE id='".$v['id']."' ");
							$db->query("INSERT INTO {$DT_PRE}log_goods (`bid`,`note`,`userid`,`time`,`status`) VALUES('".$v['id']."', '".$v['note_use']."', '".$_userid."', '".$time_tmp."', '2' ) ");
							
							if( get_goods(get_goods_belong($v['id'],'gid'),'cid') == 1 ){  //图书特殊对待
								//图书查看是否有借用情况
								$need = $db->get_one("SELECT * FROM {$DT_PRE}need_book WHERE `gid`='".$v['id']."' AND `userid`='".$_userid."' AND `status` =1 ");
								if( $need ){
									$db->query("UPDATE {$DT_PRE}need_book SET `status`=0,`time`='".timetodate(time(),6)."'  WHERE id='".$need['id']."' ");
								}


								$content .= get_goods(get_goods_belong($v['id'],'gid'),'title')."(".get_goods_belong($v['id'],'note').")、";
							}else{
								$content .= get_goods(get_goods_belong($v['id'],'gid'),'title')."(".get_goods_belong($v['id'],'bianhao').")、";
							}

						

						}
						
					}
					$res['msg'] = "申请成功,请等待批复!";
					$res['code'] = 1;

					$res['cid'] = $goods['cid'];
					$res['content'] = $USER['name']." 申请领用 ".$content." 请及时处理";
				}	
			}
		}else{
			$res['code'] = 3;
			$res['msg'] = "未检测到添加数据!";

		}		
	}else if( $func == 'ajax_go_where' ){
		
		$str = file_get_contents(DT_PATH."page.php?ac=edit&func=ajax_go_where&type=".$type."&count=".$count);//将整个文件内容读入到一个字符串中
		$str = str_replace("\r\n"," ",$str);
		$res['html'] = $str;
	}else if( $func == 'ajax_confirm_log' ){  //重写
		if( $id ){   //处理操作
			// dump($_POST);
			$r = $db->get_one("SELECT A.*,B.is_con FROM {$DT_PRE}goods_belong AS A,{$DT_PRE}goods AS B WHERE A.id=$id AND A.gid=B.id");

			if( $type == 'apply' ){  
				if( $r['status'] == 2 ){
					if( $r['is_con'] == 0 ){  //非消耗品
						if( $action == 'confirm' ){
							$db->query("UPDATE {$DT_PRE}goods_belong SET `status`=3 WHERE id=$id ");
							//同意领用 21
							$db->query("INSERT INTO {$DT_PRE}log_goods (`bid`,`note`,`userid`,`need_userid`,`time`,`status`) VALUES('".$id."', '".$note."', '".$_userid."', '".$r['userid']."', '".time()."', '21' ) ");

							$res['msg'] = '已同意领用申请';
							$res['time'] = $r['starttime'];

						}else if( $action == 'cancel' ){  
							
							$db->query("UPDATE {$DT_PRE}goods_belong SET `note_use`='',userid='',starttime='',status=1 WHERE id=$id ");
							//拒绝领用  20
							
							$db->query("INSERT INTO {$DT_PRE}log_goods (`bid`,`note`,`userid`,`need_userid`,`time`,`status`) VALUES('".$id."', '".$note."', '".$_userid."', '".$r['userid']."', '".time()."', '20' ) ");
							
							$res['msg'] = '已驳回领用申请';
							$res['time'] = $r['starttime'];
						}
					}elseif($r['is_con'] == 1 ){ //消耗品
						$tade = $db->get_one("SELECT * FROM {$DT_PRE}goods_belong WHERE gid='".$r['gid']."' AND `userid`='".$r['userid']."' AND `status` = 3 ");
						// dump($tade);
						if( $action == 'confirm' ){
							if( $tade ){  //存在领用的，则加上去
								$add_num = intval($tade['num'])+intval($r['num']);
								$db->query("UPDATE {$DT_PRE}goods_belong SET `num`=$add_num WHERE id='".$tade['id']."' ");
								//同意领用 21
								
								$db->query("DELETE FROM {$DT_PRE}goods_belong WHERE id=$id ");
								$db->query("UPDATE {$DT_PRE}log_goods SET `bid`='".$tade['id']."' WHERE bid=$id ");
								$id = $tade['id'];

							}else{  //不存在的 ，这个变成3
								$db->query("UPDATE {$DT_PRE}goods_belong SET `status`=3 WHERE id=$id ");
								//同意领用 21
							}
							$db->query("INSERT INTO {$DT_PRE}log_goods (`bid`,`note`,`userid`,`need_userid`,`time`,`status`) VALUES('".$id."', '".$note."', '".$_userid."', '".$r['userid']."', '".time()."', '21' ) ");

							$res['msg'] = '已同意消耗品领用';
							$res['time'] = $r['starttime'];
						}else if( $action == 'cancel' ){ //消耗品cancel 删除并加回去

							$total = $db->get_one("SELECT * FROM {$DT_PRE}goods_belong WHERE gid='".$r['gid']."' AND `status`=1 ");
							$total_num = intval($total['num'])+intval($r['num']);
							$db->query("UPDATE {$DT_PRE}goods_belong SET `num`=$total_num WHERE id='".$total['id']."' ");
							$db->query("DELETE FROM {$DT_PRE}goods_belong WHERE id='".$r['id']."' ");

							$res['msg'] = '已驳回消耗品申请';
							$res['time'] = $r['starttime'];
						}
					}
					$res['code'] = 1;
				}else{
					$res['code'] = 2;
					$res['msg'] = "申请状态已发生改变，正在刷新数据";
				}
				
			}else if( $type == 'give' ){   //   4 主动转让   被转者操作   
				if( $r && ($r['status'] == 4) ){
					if( $action == 'confirm' ){
						$db->query("UPDATE {$DT_PRE}goods_belong SET `userid`='".$r['need_userid']."',`need_userid`='',`starttime`='".time()."',`needtime`='',`note_need`='',`status`=3 WHERE id=$id ");
						$db->query("INSERT INTO {$DT_PRE}log_goods (`bid`,`note`,`userid`,`need_userid`,`time`,`status`) VALUES('".$id."', '".$note."', '".$r['userid']."', '".$r['need_userid']."', '".time()."', '41' ) ");

						$res['code'] =1;
						$res['msg'] = '接收了 '.get_user($r['userid'],'name').'转让的'.get_goods($r['gid'],'title');
						
					}else if( $action == 'cancel' ){
						
						$db->query("UPDATE {$DT_PRE}goods_belong SET `need_userid`='',`needtime`='',`note_need`='',`status`=3 WHERE id=$id ");
						$db->query("INSERT INTO {$DT_PRE}log_goods (`bid`,`note`,`userid`,`need_userid`,`time`,`status`) VALUES('".$id."', '".$note."', '".$r['userid']."', '".$r['need_userid']."', '".time()."', '40' ) ");

						$res['code'] =1;
						$res['msg'] = '拒绝领用 '.get_user($r['userid'],'name').' 转让的'.get_goods($r['gid'],'title');
						
					}
				}else{
					$res['code'] = 2;
					$res['msg']='操作失败，物品状态已发生改变！';
				}
			}else if( ($type == 'return') || ($type == 'repair') ){  //报废或者继续入库
				if( ($r['status'] == 6) || ($r['status'] == 8) ){
					$res['code'] = 1;

					if( $action == 'confirm' ){  //继续入库
						$db->query("UPDATE {$DT_PRE}goods_belong SET `userid`='',`need_userid`='',`note_use`='',`note_need`='',`starttime`='',`note_return`='',`needtime`='',`status`=1 WHERE id=$id ");
						$db->query("INSERT INTO {$DT_PRE}log_goods (`bid`,`note`,`userid`,`need_userid`,`time`,`status`) VALUES('".$id."', '".$note."', '".$_userid."', '".$r['userid']."', '".time()."', '61' ) ");
						$res['msg'] = "物品已设为可继续申请领用";
					}else if( $action == 'cancel' ){
						$db->query("UPDATE {$DT_PRE}goods_belong SET `userid`='',`need_userid`='',`note_use`='',`note_need`='',`starttime`='',`note_return`='',`needtime`='',`status`=9 WHERE id=$id ");
						$db->query("INSERT INTO {$DT_PRE}log_goods (`bid`,`note`,`userid`,`need_userid`,`time`,`status`) VALUES('".$id."', '".$note."', '".$_userid."', '".$r['userid']."', '".time()."', '60' ) ");
						$res['msg'] = "物品已设置为报废状态";
					}else if( $action == 'back' ){  //外部物品独有  归还外部厂商客户
						$db->query("UPDATE {$DT_PRE}goods_belong SET `userid`='',`need_userid`='',`note_use`='',`note_need`='',`starttime`='',`note_return`='',`needtime`='',`status`=7 WHERE id=$id ");
						$db->query("INSERT INTO {$DT_PRE}log_goods (`bid`,`note`,`userid`,`need_userid`,`time`,`status`) VALUES('".$id."', '".$note."', '".$_userid."', '".$r['userid']."', '".time()."', '7' ) ");
						$res['msg'] = "物品已归还给厂家客户";

					}else if( $action == 'repair' ){
						$db->query("UPDATE {$DT_PRE}goods_belong SET `userid`='',`need_userid`='',`note_use`='',`note_need`='',`starttime`='',`note_return`='',`needtime`='',`status`=8 WHERE id=$id ");
						$db->query("INSERT INTO {$DT_PRE}log_goods (`bid`,`note`,`userid`,`need_userid`,`time`,`status`) VALUES('".$id."', '".$note."', '".$_userid."', '".$r['userid']."', '".time()."', '8' ) ");
						$res['msg'] = "物品已设置为维修中";
					}else if( $action == 'repaired' ){
						$db->query("UPDATE {$DT_PRE}goods_belong SET `userid`='',`need_userid`='',`note_use`='',`note_need`='',`starttime`='',`note_return`='',`needtime`='',`status`=1 WHERE id=$id ");
						$db->query("INSERT INTO {$DT_PRE}log_goods (`bid`,`note`,`userid`,`need_userid`,`time`,`status`) VALUES('".$id."', '".$note."', '".$_userid."', '', '".time()."', '81' ) ");
						$res['msg'] = "物品维修完成,已重新加入物品库中";
					}
				}else{
					$r['code'] = 2;
					$res['msg']='操作失败，物品状态已发生改变！';

				}
				
			}
			
		}else{
			$str = file_get_contents(DT_PATH."page.php?ac=edit&func=ajax_confirm_log&time=".$time."&height=".$height."&type=".$type);//将整个文件内容读入到一个字符串中
			$str = str_replace("\r\n"," ",$str);
			// dump($str);
			$res['html'] = $str;

		}
	}else if( $func == 'ajax_file_ruku' ){

		$str = file_get_contents(DT_PATH."page.php?ac=edit&func=ajax_file_ruku&fromtype=".$fromtype."&height=".$height);//将整个文件内容读入到一个字符串中
		$str = str_replace("\r\n"," ",$str);
		// dump($str);
		$res['html'] = $str;		
	}else if( $func == 'ajax_check_file' ){  //处理导入的问题
		if( pathinfo($_FILES['upfile']['name'], PATHINFO_EXTENSION) == "xls" ){
			if( move_uploaded_file($_FILES['upfile']['tmp_name'], './uploads/'.$_userid.'.xls') ){
				$res['msg'] = "上传成功！";
				$res['code'] = 1;
			}else{
				$res['msg'] = "上传失败！";
				$res['code'] = 2;
			}
		}else{
			$res['code'] = 2;
			$res['msg'] = "请上传xls后缀文件！";
		}

	}else if( $func == 'ajax_ruku_add' ){   //入库post提交
	
		if( $arr ){
			$tmp = array();  //存放处理数组
			foreach( $arr as $k => $v ){
				if( $v['name'] == 'title' ){
					$tmp[$k]['title'] 	= get_user_goods($arr[$k]['value']);     //标题  ->gid
					$tmp[$k]['bianhao'] = trim($arr[$k+1]['value']);  //编号
					$tmp[$k]['xinghao'] = trim($arr[$k+2]['value']);  //型号
					$tmp[$k]['neicun'] 	= trim($arr[$k+3]['value']);  //内存
					$tmp[$k]['banben'] 	= trim($arr[$k+4]['value']);  //版本
					$tmp[$k]['xitong'] 	= trim($arr[$k+5]['value']);  //系统
					$tmp[$k]['userid'] 	= get_user_name($arr[$k+6]['value']);  //用户
					$tmp[$k]['num'] 	= trim($arr[$k+7]['value']);     //数量
					$tmp[$k]['status'] 	= get_user_status($arr[$k+8]['value']);  //状态
					$tmp[$k]['usingtime'] 	= trim($arr[$k+9]['value']);  //备注
					$tmp[$k]['note'] 	= trim($arr[$k+10]['value']);  //备注
				}
			}
			$count = 0;
			$time_tmp = time();  //当前时间   遍历添加可能会延迟不准确
			foreach ($tmp as $k => $v) {
				$con = get_goods($v['title'],'is_con');
				if( $con == 0 ){  //不是消耗品  数量直接insert
					for($i=0; $i<$v['num']; $i++ ){
						$db->query("INSERT INTO {$DT_PRE}goods_belong (`gid`,`bianhao`,`xinghao`,`neicun`,`banben`,`xitong`,`userid`,`status`,`note`,`usingtime`,`edittime`) VALUES('".$v['title']."', '".$v['bianhao']."', '".$v['xinghao']."', '".$v['neicun']."', '".$v['banben']."', '".$v['xitong']."', '".$v['userid']."', '".$v['status']."','".$v['note']."', '".$v['usingtime']."','".$time_tmp."')");
						$count += 1;
						$insert_id = $db->insert_id();  //最後添加數據
						if($v['status']){  //log 添加
							if( $v['status'] == 3 ){
								$status = "1".$v['status'];
							}else{
								$status = $v['status'];
							}
							$db->query("INSERT INTO {$DT_PRE}log_goods (`bid`,`note`,`userid`,`need_userid`,`time`,`status`) VALUES('".$insert_id."','".$v['note']."','".$_userid."','".$v['userid']."','".$time_tmp."','".$status."') ");
						}
					}
				}else{   //消耗品  累计或添加
					$has = $db->get_one("SELECT * FROM {$DT_PRE}goods_belong WHERE gid='".$v['title']."' AND status=1 ");
					
					if( $has && $v['status'] == 1 ){ //存在只需update
						$has_num = intval($has['num']) + intval($v['num']);
						$db->query("UPDATE {$DT_PRE}goods_belong SET `num`=$has_num WHERE gid='".$v['title']."' AND status=1 ");
					}else{
						$db->query("INSERT INTO {$DT_PRE}goods_belong (`gid`,`bianhao`,`xinghao`,`neicun`,`banben`,`xitong`,`userid`,`status`,`note`,`num`,`usingtime`,`edittime`) VALUES('".$v['title']."', '".$v['bianhao']."', '".$v['xinghao']."', '".$v['neicun']."', '".$v['banben']."', '".$v['xitong']."', '".$v['userid']."', '".$v['status']."','".$v['note']."','".$v['num']."', '".$v['usingtime']."','".time()."')");
					}
					$count += 1;
				}
			}
			$res['code'] = 1;
			$res['msg'] = "处理了".$count."条数据!";
		}else{
			$res['code'] = 2;
			$res['msg'] = "未检测到添加数据!";
		}	
	}else if( $func == 'ajax_daochu_category' ){
		//导出物品类别报表
		require_once DT_ROOT.'/api/Classes/PHPExcel.php';
		// require_once DT_ROOT.'/api/Classes/PHPExcel.php';

	
		// require_once DT_ROOT.'/api/Classes/PHPExcel/IOFactory.php';

		$objPHPExcel = new PHPExcel();

		// 重命名工作sheet
		$objPHPExcel->getActiveSheet()->setTitle('物品分类报表');
		//根据excel坐标，添加数据
		$objPHPExcel->setActiveSheetIndex(0)
		    ->setCellValue('A1', '所属分类')
		    ->setCellValue('B1', '物品来源')
		    ->setCellValue('C1', '物品种类')
		    ->setCellValue('D1', '物品备注')
		    ->setCellValue('E1', '是否消耗品')
		    ->setCellValue('F1', '编辑时间');
		$objPHPExcel->getActiveSheet()->getStyle('A1:F1')->getFont()->setBold(true);  //设置第一行加粗
		$objPHPExcel->getActiveSheet()->getDefaultColumnDimension('A:F')->setWidth(20);//设置宽度都为20
		
		$tmp1 = $db->query("SELECT * FROM {$DT_PRE}goods WHERE `status` = 1 ORDER BY cid ASC  ");
		while( $r = $db->fetch_array($tmp1) ){
			$goods[] = $r;
		}
		foreach ($goods as $k => $v) {
			$l = $k+2;  //插入行

			if( $goods[$k]['cid'] !=  $goods[$k-1]['cid'] ){
				$objPHPExcel->getActiveSheet()->setCellValue('A'.$l,get_wp_class($v['cid'], 'title'));
				$objPHPExcel->getActiveSheet()->setCellValue('B'.$l,$v['fromtype']==1?'内部':'外部');
			}
			$objPHPExcel->getActiveSheet()->setCellValue('C'.$l,$v['title']);
			$objPHPExcel->getActiveSheet()->setCellValue('D'.$l,$v['note']);
			$objPHPExcel->getActiveSheet()->setCellValue('E'.$l,$v['is_con']==1?'消耗':'');
			$objPHPExcel->getActiveSheet()->setCellValue('F'.$l,timetodate($v['edittime'],5));
		}
		// 设置第一个sheet为工作的sheet
		$objPHPExcel->setActiveSheetIndex(0);
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		if( file_exists('./uploads/category.xls') ){
			unlink('./uploads/category.xls');
		}
		$objWriter->save('./uploads/category.xls');
		
		$res['code'] = 1;
	}else if( $func == 'ajax_daochu_goods' ){
		$condition = "1";
		// dump($keywords);
		if( $category ){
			$category =  substr($category, 0, -1);
			$condition .= " AND gid in($category) ";
		}
		if( $userid ){
			$u_arr = explode(",",$userid);
			foreach ($u_arr as $k => $v) {
				if( $v ){
					$user_tmp .= "'".$v."',";
				}
			}
			$userid =  substr($user_tmp, 0, -1);

			$condition .= " AND userid in($userid) ";
		}
		if( $status ){
			$status =  substr($status, 0, -1);   //1领用  2申请中  3 使用中(4 6)
			$condition .= " AND status in($status) ";
		}

		if( $starttime ){  //领用时间
			$start_end = explode(" - ",$starttime);
			$start = datetotime($start_end['0']);
			$end = datetotime($start_end['1']);
			$condition .= " AND starttime BETWEEN $start AND $end";
		}
		if( $usingtime ){  //启用时间
			$start_end = explode(" - ",$usingtime);
			$start = datetotime($start_end['0']);
			$end = datetotime($start_end['1']);
			$condition .= " AND usingtime BETWEEN $start AND $end";

		}
		if( $edittime ){
			$start_end = explode(" - ",$edittime);
			$start = datetotime($start_end['0']);
			$end = datetotime($start_end['1']);
			$condition .= " AND edittime BETWEEN $start AND $end";
		}
		if( trim($keywords) ){
			$condition .= " AND `bianhao` like '%$keywords%' OR `xinghao` like '%$keywords%' OR `neicun` like '%$keywords%' OR `banben` like '%$keywords%' OR `xitong` like '%$keywords%' OR `note` like '%$keywords%' OR `note_use` like '%$keywords%' ";
		}
		$count = $db->count($DT_PRE."goods_belong",$condition);
		// $tmp = $db->query("SELECT * FROM {$DT_PRE}goods_belong WHERE $condition LIMIT 0,500");
		// 数据量太大搞不出来，分批次！！！
		// $tmp = $db->query("SELECT * FROM {$DT_PRE}goods_belong WHERE $condition LIMIT 0,500");
		$tmp = $db->query("SELECT * FROM {$DT_PRE}goods_belong WHERE $condition LIMIT 2000,500");
		// $tmp = $db->query("SELECT * FROM {$DT_PRE}goods_belong LIMIT 0,500");
		// dump($condition);
		

		while( $r = $db->fetch_array($tmp) ){

			$r['userid'] = get_user($r['userid'], 'name');
			$r['gid'] = get_goods($r['gid'], 'title');
			$r['starttime'] = $r['starttime'] ? timetodate($r['starttime'],5) : "";  //领用时间
			$r['edittime'] = $r['edittime'] ? timetodate($r['edittime'],5) : "";   //入库时间
			$r['usingtime'] = $r['usingtime'] ? timetodate($r['usingtime'],5) : "";  //启用时间
			if( $r['status']== '1' ){
				$r['status'] = '可领用';
			}else if( $r['status'] == '2' ){
				$r['status'] = '申请中';
			}else if( $r['status'] == '3' ){
				$r['status'] = '使用中';
			}else if( $r['status'] == '4' ){
				$r['status'] = '转让中';
			}else if( $r['status'] == '6' ){
				$r['status'] = '归还中';
			}else if( $r['status'] == '7' ){
				$r['status'] = '已归还客户';
			}else if( $r['status'] == '8' ){
				$r['status'] = '维修中';
			}else if( $r['status'] == '9' ){
				$r['status'] = '已报废';
			}

			$arr[] = $r;
		}


		if( $arr ){   //$arr 存在则可以导出


			$field = substr($field,0,-1);
			$field_arr = explode(",",$field);
		
			//导出物品类别报表
			// require_once DT_ROOT.'/api/Classes/PHPExcel/IOFactory.php';
			require_once DT_ROOT.'/api/Classes/PHPExcel.php';

			$objPHPExcel = new PHPExcel();
			

			// 重命名工作sheet
			$objPHPExcel->getActiveSheet()->setTitle('物品明细报表');
			//根据excel坐标，添加数据
			
			$field_tmp = array(
				'id'  		=> '序号',
				'gid'  		=> '物品名称',
				'bianhao'  	=> '编号',
				'xinghao'  	=> '型号',
				'neicun'  	=> '内存',
				'banben'  	=> '版本',
				'xitong'  	=> '系统',
				'userid'  	=> '使用者',
				'number'  	=> '物品数量',
				'status'  	=> '物品状态',
				'note'  	=> '备注',
				'note_use'  => '领用备注',
				'starttime' => '领用时间',
				'usingtime' => '启用时间',
				'edittime'  => '入库时间'
				);

			
			for( $i=0; $i < count($field_arr); $i++){
				$li = $field_arr[$i];  //英文字段名

				$objPHPExcel->setActiveSheetIndex(0)->setCellValue(chr(ord(A)+$i)."1", $field_tmp[$li]);
			   	
			    for($j=0; $j < count($arr); $j++ ){
			    	$lj = $j+2;

			    	$objPHPExcel->getActiveSheet()->setCellValue(chr(ord(A)+$i).$lj, $arr[$j][$li]);
			    	
			    }
			}

			$objPHPExcel->getActiveSheet()->getStyle('A1:Z1')->getFont()->setBold(true);  //设置第一行加粗
			
			$objPHPExcel->getActiveSheet()->getStyle("A2:Z1000")->getAlignment()->setWrapText(TRUE); //自动换行

			$objPHPExcel->getActiveSheet()->getDefaultColumnDimension('A:Z')->setWidth(20);//设置宽度都为20
			$objPHPExcel->getActiveSheet()->freezePaneByColumnAndRow(1,2);  //设置冻结表 行列


				// 设置第一个sheet为工作的sheet
			$objPHPExcel->setActiveSheetIndex(0);
			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
			if( file_exists('./uploads/goods/'.$_userid.'.xls') ){
				unlink('./uploads/goods/'.$_userid.'.xls');
			}
			$objWriter->save('./uploads/goods/'.$_userid.'.xls');
			$res['code'] =1;
			$res['href'] = DT_PATH."uploads/goods/".$_userid.".xls";

		}else{
			$res['code'] = 2;
			$res['msg'] = "没有导出数据";
		}
	}else if( $func == 'my_goods' ){
		$r = $db->get_one("SELECT * FROM {$DT_PRE}goods_belong WHERE id=$id AND status = 3 AND userid ='".$_userid."' ");

		if( $r ){
			if( $type == 'return' ){  //设为归还
				$db->query("UPDATE {$DT_PRE}goods_belong SET `note_return`='".$note."',`status` = 6 WHERE id=$id ");
				$db->query("INSERT INTO {$DT_PRE}log_goods (`bid`,`note`,`userid`,`time`,`status`) VALUES('".$id."', '".$note."', '".$_userid."', '".time()."', '6' ) ");

				$res['code'] = 1;
				$res['msg'] = '已归还物品,等待管理员审核';

			
		

				if( get_goods($r['gid'],'cid') == 1 ){  //图书特殊对待
					$res['content'] = get_user($_userid,'name')." 归还 ".get_goods($r['gid'],'title')."(".$r['note'].")"." 请及时处理（".timetodate(time(),5)."）";
				}else{
					$res['content'] = get_user($_userid,'name')." 归还 ".get_goods($r['gid'],'title')." 请及时处理（".timetodate(time(),5)."）";
				}
				
				$res['cid'] = get_goods($r['gid'],'cid');



			}else if( $type == 'give' ){  //设为转让
				if( $r['userid'] != $need_userid ){
					$db->query("UPDATE {$DT_PRE}goods_belong SET `need_userid`='".$need_userid."',`note_need`='".$note."',`needtime` = '".time()."' , `status` = 4 WHERE id=$id ");
					

					$db->query("INSERT INTO {$DT_PRE}log_goods (`bid`,`note`,`userid`,`need_userid`,`time`,`status`) VALUES('".$id."', '".$note."', '".$_userid."', '".$need_userid."', '".time()."', '4' ) ");
					$res['code'] = 1;
					$res['msg'] = '已将物品转让，请等待对方处理!';



					
					if( get_goods($r['gid'],'cid') == 1 ){  //图书特殊对待
						$res['content'] = get_user($_userid,'name')."将 ".get_goods($r['gid'],'title')."(".$r['note'].")"." 转让给你,请及时处理（".timetodate(time(),5)."）";
					}else{
						$res['content'] = get_user($_userid,'name')."将 ".get_goods($r['gid'],'title')." 转让给你,请及时处理（".timetodate(time(),5)."）";
					}
					
					$res['uid'] = $need_userid;


				}else{
					$res['code'] = 2;
					$res['msg'] = "不能选择转让给自己!";
				}
			}
		}else{
			$res['code'] = 2;
			$res['msg'] = "物品状态已发生改变,请刷新重试!";

		}
	}else if( $func == 'edit_cancel' ){  //edit  和 cancel
		// dump($_POST);
		// exit;
		if( $action == 'apply' ){ //申请apply
			$r = $db->get_one("SELECT A.*,B.is_con FROM {$DT_PRE}goods_belong AS A,{$DT_PRE}goods AS B WHERE A.id=$id AND A.status = 2 AND A.userid ='".$_userid."' AND A.gid=B.id ");
			if( $r ){
				if( $type == 'cancel' ){  //撤回
					if( $r['is_con'] == 1 ){  //消耗品撤回
						$total = $db->get_one("SELECT * FROM {$DT_PRE}goods_belong WHERE gid='".$r['gid']."' AND status=1 ");
						$total_num = intval($total['num'])+ intval($r['num']);

						$db->query("UPDATE {$DT_PRE}goods_belong SET `num`=$total_num WHERE id='".$total['id']."' ");
						$db->query("DELETE FROM {$DT_PRE}goods_belong WHERE id=$id ");
					}else{
						$db->query("UPDATE {$DT_PRE}goods_belong SET `note_use`='',`userid`='',`status`=1 WHERE id=$id ");
						$db->query("INSERT INTO {$DT_PRE}log_goods (`bid`,`note`,`userid`,`need_userid`,`time`,`status`) VALUES('".$id."', '".$note."', '".$_userid."', '', '".time()."', '22' ) ");
					}
					$res['code'] = 1;
					$res['msg'] = '已撤回物品申请';
				}else if($type == 'edit'){  //修改note
					if( $r ){
						$db->query("UPDATE {$DT_PRE}goods_belong SET `note_use`= '".$note."' WHERE id=$id ");
						$res['code'] = 1;
						$res['msg'] = '备注已修改';
						$res['note'] = $note;
					}else{
						$res['code'] = 2;
						$res['msg'] = '物品状态已发生改变,无法操作';
					}
				}
			}else{
				$res['code'] = 2;
				$res['msg'] = '管理员已对该申请作出审批,无法完成此操作!';
			}
		}else if( $action == 'give' ){
			$r = $db->get_one("SELECT * FROM {$DT_PRE}goods_belong WHERE `id`=$id AND `status` = 4 AND userid ='".$_userid."' ");
			if( $r ){
				if( $type == 'cancel' ){  //撤回
					$db->query("UPDATE {$DT_PRE}goods_belong SET `need_userid`='',`note_need`='',`status`=3 WHERE id=$id ");
					$db->query("INSERT INTO {$DT_PRE}log_goods (`bid`,`note`,`userid`,`need_userid`,`time`,`status`) VALUES('".$id."', '".$note."', '".$_userid."', '".$r['need_userid']."', '".time()."', '42' ) ");
					$res['code'] = 1;
					$res['msg'] = '已撤回物品申请';
				}else if($type == 'edit'){  //修改note
					if( $r ){
						$db->query("UPDATE {$DT_PRE}goods_belong SET `note_need`= '".$note."' WHERE id=$id ");
						$res['code'] = 1;
						$res['msg'] = '备注已修改';
						$res['note'] = $note;
					}else{
						$res['code'] = 2;
						$res['msg'] = '物品状态已发生改变,无法操作';
					}
				}

			}else{
				$res['code'] = 2;
				$res['msg'] = '管理员已完成审批,无法完成此操作!';
			}

		} else if( $action == 'return' ){
			$r = $db->get_one("SELECT * FROM {$DT_PRE}goods_belong WHERE `id`=$id AND `status` = 6 AND userid ='".$_userid."' ");
			if( $r ){
				if( $type == 'cancel' ){  //撤回
					$db->query("UPDATE {$DT_PRE}goods_belong SET `note_return`='',`status`=3 WHERE id=$id ");
					$db->query("INSERT INTO {$DT_PRE}log_goods (`bid`,`note`,`userid`,`need_userid`,`time`,`status`) VALUES('".$id."', '".$note."', '".$_userid."', '', '".time()."', '62' ) ");
					$res['code'] = 1;
					$res['msg'] = '已撤回物品申请';
				}else if($type == 'edit'){  //修改note
					if( $r ){
						$db->query("UPDATE {$DT_PRE}goods_belong SET `note_return`= '".$note."' WHERE id=$id ");
						$res['code'] = 1;
						$res['msg'] = '备注已修改';
						$res['note'] = $note;
					}else{
						$res['code'] = 2;
						$res['msg'] = '物品状态已发生改变,无法操作';
					}
				}

			}else{
				$res['code'] = 2;
				$res['msg'] = '管理员已完成审批,无法完成此操作!';
			}
		}
	}else if( $func == 'ajax_check_log' ){
		$str = file_get_contents(DT_PATH."page.php?from=".$from."&ac=edit&func=ajax_check_log&gid=".$gid."&height=".$height."&userid=".$userid."&id_total=".$id_total);
		$str = str_replace("\r\n"," ",$str);
		$res['html'] = $str;
	}else if( $func == 'ajax_need_book' ){
		if( $gid ){
			$r = $db->get_one("SELECT * FROM {$DT_PRE}need_book WHERE `userid`='".$_userid."' AND `gid`=$gid");
			
			$gb = $db->get_one("SELECT * FROM {$DT_PRE}goods_belong WHERE `id`=$gid ");

			if( $r || ( $gb['userid'] == $_userid ) ){
				$res['code'] = 2;
				$res['msg'] = '您已请求借阅过该图书!';

				
			}else{
				$r = $db->count("{$DT_PRE}need_book","`gid`=$gid AND `status`=1");
				if( $r >=3 ){
					$res['code'] = 2;
					$res['msg'] = '请求借阅人数过多，请借阅其他图书!';
				}else{
					$r = $db->query("INSERT INTO {$DT_PRE}need_book (`gid`,`userid`,`time`,`status`) VALUES('".$gid."','".$_userid."','".timetodate(time(),6)."','1') ");

					$db->query("INSERT INTO {$DT_PRE}log_goods (`bid`,`userid`,`time`,`status`) VALUES('".$gid."', '".$_userid."', '".time()."', '91' ) ");
					$res['code'] = 1;
					$res['msg'] = '请求成功!';
					$res['html'] = "<div class='need_man'><div>".$USER['name']."</div><div>请求借阅</div><div>".timetodate(time(),6)."</div></div>";
				}
			}
		}else{
			$res['code'] = 2;
			$res['msg'] = '目标失效，请稍候重试!';	
		}
	}else if( $func == 'ajax_check_log_id' ){
		// dump($id);
		$r = $db->get_one("SELECT * FROM {$DT_PRE}goods_belong WHERE `status`=1 AND `id`=$id");
		if( $r ){
			$r['bianhao'] = trim($r['bianhao']) ? trim($r['bianhao']) : "—";
			$r['xinghao'] = trim($r['xinghao']) ? trim($r['xinghao']) : "—";
			$r['neicun'] = trim($r['neicun']) ? trim($r['neicun']) : "—";
			$r['banben'] = trim($r['banben']) ? trim($r['banben']) : "—";
			$r['xitong'] = trim($r['xitong']) ? trim($r['xitong']) : "—";

			
			$r['caozuo'] = '<em style="color:#f60">申请领用</em>';
			
			$res['code'] = 1;
			$res['html'] = $r;
		}else{
			$res['code'] = 2;
		}
	}else if( $func == 'ajax_get_image' ){

		$str = file_get_contents(DT_PATH."page.php?ac=edit&func=ajax_get_image&gid=".$gid);
		$str = str_replace("\r\n"," ",$str);
		$res['html'] = $str;



	}else if( $func == 'ajax_set_image' ){  //设置图片上传

		if( ($_FILES['upfile']['name']) && in_array(pathinfo($_FILES['upfile']['name'], PATHINFO_EXTENSION),array("jpg","png","jpeg")) ){
			if( file_exists("./uploads/goods/".$gid.".png") ){
				unlink("./uploads/goods/".$gid.".png");
			}
			$path = $gid.".png";
			$cache = time().".png";
			if( move_uploaded_file($_FILES['upfile']['tmp_name'], './uploads/goods/'.$path) ){
				$db->query("UPDATE {$DT_PRE}goods SET `image`='".$path."' WHERE id=$gid");

				// move_uploaded_file('./uploads/goods/'.$path, './uploads/cache/'.$cache);
				copy('./uploads/goods/'.$path, './uploads/cache/'.$cache);
				$res['code'] = 1;
				$res['msg'] = DT_PATH."uploads/cache/".$cache;
			}else{
				$res['msg'] = "上传失败！";
				$res['code'] = 2;
			}
		}else{
			$res['code'] = 2;
			$res['msg'] = "请上传图片格式文件！";
		}
	}

	else if( $func == 'ajax_clean_img' ){
		$goods = $db->get_one("SELECT * FROM {$DT_PRE}goods WHERE id=$gid ");
		if( file_exists("./uploads/goods/".$gid.".png") ){
			unlink("./uploads/goods/".$gid.".png");
		}
		if( $goods['image'] ){
			$db->query("UPDATE {$DT_PRE}goods SET `image`='' WHERE id=$gid ");
			$res['code'] = 1;
		}else{
			$res['code'] = 2;
		}
	}





}


}
echo json_encode($res);
// exit($res);

// @include DT_ROOT.'/api/ajax/'.$action.'.inc.php';
?>