<?php
$COOKIE_CLOSE = 1;

require 'common.inc.php';
require_once(__DIR__ . "/config.php");
require_once(__DIR__ . "/util/Log.php");
require_once(__DIR__ . "/util/Cache.php");
require_once(__DIR__ . "/api/Auth.php");
require_once(__DIR__ . "/api/User.php");
require_once(__DIR__ . "/api/Message.php");

$auth = new Auth();
$user = new User();
$message = new Message();

$event = $_REQUEST["event"];
switch($event){
    case '':
        echo json_encode(array("error_code"=>"4000"));
        break;
    case 'getuserid':
        $accessToken = $auth->getAccessToken();
        $code = $_POST["code"];
        $userInfo = $user->getUserInfo($accessToken, $code);
        Log::i("[USERINFO-getuserid]".json_encode($userInfo));
        echo json_encode($userInfo, true);
        break;

    case 'get_userinfo':
        $accessToken = $auth->getAccessToken();
        $userid = $_POST["userid"];
        $userInfo = $user->get($accessToken, $userid);
        $action = $_GET['action'];

        $uid = get_cookie('userid');
        $uInfo = get_user($userid);
        if( $action == 'check' ){   //检查

            if( !$uInfo ){
                $isAdmin  = $userInfo->isAdmin ? "1" : "0";
                $isBoss   = $userInfo->isBoss ? "1" : "0";

                $db->query("INSERT INTO {$DT_PRE}user (`userid`,`name`,`isAdmin`,`isBoss`,`mobile`,`avatar`) VALUES('".$userid."','".$userInfo->name."','".$isAdmin."','".$isBoss."','".$userInfo->mobile."','".$userInfo->avatar."') ");
            }else{
                // if( ($uInfo['name'] != $userInfo->name) || ($uInfo['isAdmin'] != $userInfo->isAdmin) || ($uInfo['isBoss'] != $userInfo->isBoss) || ($uInfo['mobile'] != $userInfo->mobile) || ($uInfo['avatar'] != $userInfo->avatar)){  //存在不同
                //     $isAdmin  = $userInfo->isAdmin ? "1" : "0";
                //     $isBoss   = $userInfo->isBoss ? "1" : "0";
                //     $db->query("UPDATE {$DT_PRE}user SET `name`='".$userInfo->name."',`isAdmin`='".$isAdmin."',`isBoss`='".$isBoss."',`mobile`='".$mobile."',`avatar`='".$userInfo->avatar."' WHERE `userid`='".$userid."' ");
                // }
            }
            $mid = $user->get($accessToken, $_GET['myid']);
            $mp = $mid->department;  //我部门
            $up = $userInfo->department;  //他部门
            if( $mp == $up ){
                $res['code'] = 1;
                 $res['msg'] = "相同";
            }else{
                 $res['code'] = 2;
                 $res['msg'] = "两个人的部门不相同";
            }

            echo json_encode($res, true);
        }else{
            if( ($uid != $userid) || !$uInfo ){
                set_cookie('userid', $userid);
                if( !$uInfo ){
                    $isAdmin  = $userInfo->isAdmin ? "1" : "0";
                    $isBoss   = $userInfo->isBoss ? "1" : "0";

                    $db->query("INSERT INTO {$DT_PRE}user (`userid`,`name`,`isAdmin`,`isBoss`,`mobile`,`avatar`) VALUES('".$userid."','".$userInfo->name."','".$isAdmin."','".$isBoss."','".$userInfo->mobile."','".$userInfo->avatar."') ");
                }
            }else{
                // if( ($uInfo['name'] != $userInfo->name) || ($uInfo['isAdmin'] != $userInfo->isAdmin) || ($uInfo['isBoss'] != $userInfo->isBoss) || ($uInfo['mobile'] != $userInfo->mobile) || ($uInfo['avatar'] != $userInfo->avatar)){  //存在不同
                //     $isAdmin  = $userInfo->isAdmin ? "1" : "0";
                //     $isBoss   = $userInfo->isBoss ? "1" : "0";
                   
                //     $db->query("UPDATE {$DT_PRE}user SET `name`='".$userInfo->name."',`isAdmin`='".$isAdmin."',`isBoss`='".$isBoss."',`mobile`='".$mobile."',`avatar`='".$userInfo->avatar."' WHERE `userid`='".$userid."' ");

                // }
            }


            //查找该成员是否为分类管理员
            $manager = $db->get_one("SELECT * FROM {$DT_PRE}class WHERE manager ='".$userid."' ");
            if( $manager ){
                $userInfo->manager = 1;
            }else{
                $userInfo->manager = 0;
            }


            echo json_encode($userInfo, true);
        }
        break;
    case 'jsapi-oauth':
        $href = $_GET["href"];
        $configs = $auth->getConfig($href);
        $configs['errcode'] = 0;
        echo json_encode($configs, JSON_UNESCAPED_SLASHES);
        break;

    case 'send_msg':    //type   uid   content   申请和归还需要注意
        $accessToken = $auth->getAccessToken();
        $type = $_POST['type'];
        $uid = $_POST['uid'];   //接受人   或者管理员  或者物品class分属

        $content = $_POST['content'];

        if( $type == 'apply' ){
            
            // $uid = "0225163314939996";   //管理员
            $a = $db->get_one("SELECT `manager` FROM {$DT_PRE}class WHERE id=$uid ");
            $uid = $a['manager'];
        }else if( $type == 'give' ){ //转让

            
            
        }else if( $type == 'get' ){  //接收反馈


        }else if( $type == 'return' ){  //归还
            // $uid = "0225163314939996";  
            $a = $db->get_one("SELECT `manager` FROM {$DT_PRE}class WHERE id=$uid ");
            $uid = $a['manager'];
        }

        $action_card = array(
            "title" => $content,
            "markdown" => $content,
            "single_title" => "查看详情",
            "single_url" => "http://goods.citos.cn/sg988/page.php?from=action&ac=index&func=my_action"

            );
        
        $arr=array("touser" => $uid,"agentid" => AGENTID, "msgtype" => "action_card", "action_card" => $action_card);
        $msg = $message->send($accessToken, $arr);
        echo json_encode($msg, true);
        break;


}
