<?php
	if( $func == 'news' || $func == 'project' || $func == 'company'){// news
			$suf = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
			if( in_array($suf, array('jpg','jpeg','bmp','png')) ){
				$path = 'uploads/'.$func;
				if( !file_exists($path) ){
					mkdir($path, 0777, true);
				}
				$img = 'uploads/'.$func.'/'.timetodate(time(),7).'.'.$suf;
				$a = move_uploaded_file($_FILES['file']['tmp_name'],$img);
				


				$res['path'] = $a;

				$res['code'] = 0;
				$res['msg'] = "请上传图片格式文件!";

				
				$res['data'] = array("src"=>DT_PATH.'uploads/'.$func.'/'.timetodate(time(),7).'.'.$suf);
			}else{
				$res['code'] = 1;
				$res['msg'] = "请上传图片格式文件!";
			}
			
	}else if( $func == 'screenshot' ){

		$suf = pathinfo($_FILES['upfile']['name'], PATHINFO_EXTENSION);
		if( in_array($suf, array('jpg','jpeg','bmp','png')) ){
			$path = 'uploads/screenshot';
			if( !file_exists($path) ){
				mkdir($path, 0777, true);
			}
			$screenshot = 'uploads/screenshot/'.timetodate(time(),7).'.'.$suf;
			$a = move_uploaded_file($_FILES['upfile']['tmp_name'],$screenshot);
			$res['code'] = 1;
			$res['type'] = $type;
			$res['screenshot'] = DT_PATH.'uploads/screenshot/'.timetodate(time(),7).'.'.$suf;
		}else{
			$res['code'] = 2;
			$res['msg'] = "请上传图片格式文件!";
		}
	}

return json_encode($res);