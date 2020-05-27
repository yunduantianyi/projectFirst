(function(){
    var OPENAPIHOST = 'http://goods.citos.cn/sg988';
    var isDingtalk = /DingTalk/.test(navigator.userAgent);
    var proper = {};
    var _userId = '';
    var _userInfo = {};
    Object.defineProperty(proper,'userId',{
        enumerable: true,
        get: function(){
            return _userId;
        },
        set: function(newValue){
            _userId = newValue;
            getUserInfo(proper.userId,'');
        }
    });
    Object.defineProperty(proper, 'userInfo',{
        enumerable: true,
        get: function(){
            return _userInfo;
        },
        set: function(newValue){
            _userInfo = newValue;
            updateUI();
        }
    });

    function parseCorpId(url, param) {
        var searchIndex = url.indexOf('?');
        var searchParams = url.slice(searchIndex + 1).split('&');
        for (var i = 0; i < searchParams.length; i++) {
            var items = searchParams[i].split('=');
            if (items[0].trim() == param) {
                return items[1].trim();
            }
        }
    }
    function openLink(url, corpId){
        if(corpId && typeof corpId === 'string'){
            if (url && url.indexOf('$CORPID$') !== -1) {
                url = url.replace(/\$CORPID\$/, corpId);
            }
        }
        if (isDingtalk) {
            dd.biz.util.openLink({
                url: url,
                onSuccess: function(){
                    if(typeof corpId === 'function'){
                        corpId();
                    }
                },
                onFail: function(){
                    if(typeof corpId === 'function'){
                        corpId();
                    }
                }
            });
        } else {
            window.open(url);
        }
    }

    function updateName(){
        var dateTime = new Date().getHours();
        var isAdmin = proper.userInfo.isAdmin;
        var isBoss =  proper.userInfo.isBoss;
        var name = proper.userInfo.name;
        var manager = proper.userInfo.manager;



        var nb = {};
        if(name){
            if (dateTime >= 5 && dateTime <= 12) {
                nb.wh = isAdmin ? '早上好，管理员，' + name : '早上好，' + name;
                nb.whImage = 'https://gw.alicdn.com/tps/TB1ubtjOFXXXXbzXpXXXXXXXXXX-36-36.jpg';
            } else if (dateTime > 12 && dateTime <= 18) {
                nb.wh = isAdmin ? '下午好，管理员，' + name : '下午好，' + name;
                nb.whImage = 'https://gw.alicdn.com/tps/TB1ubtjOFXXXXbzXpXXXXXXXXXX-36-36.jpg';
            } else {
                nb.wh = isAdmin ? '晚上好，管理员，' + name : '晚上好，' + name;
                nb.whImage = 'https://gw.alicdn.com/tps/TB15FNwOFXXXXbqXXXXXXXXXXXX-36-36.jpg';
            }
            nb.isAdmin = isAdmin ? 1 : 0;
            nb.isBoss = isBoss ? 1 : 0;
            nb.manager = manager;
        }
        return nb;
    }
    function updateUI(){
        var nb = updateName();
        var html = '<img src="'+ nb.whImage+'" class="admin-image">'
        + '<div class="admin-hello">'
        + nb.wh
        + '</div>';
        $('.admin-manager').html(html);

        if( (nb.isAdmin == 1) || (nb.isBoss == 1) || (nb.manager == 1) ){
            $(".admin_action").fadeIn();
        }

    }

    function getUserId(corpId){
        authCode(corpId).then(function(result){
            var code = result.code;
            var getUserIdRequest = {
                url: OPENAPIHOST + '/getOapiByName.php?event=getuserid',
                type: 'POST',
                data:{code:code},
                dataType: 'json',
                success: function(response){
                    if (response.errcode === 0){
                        proper.userId = response.userid;
                    } else {
                        alert(JSON.stringify(response) + 'getuserid');
                    }
                },
                error: function(err){
                    alert(JSON.stringify(err));
                }
            }
            $.ajax(getUserIdRequest);
        }).catch(function(error){
            alert(JSON.stringify(error));
        });
    }

    /*  userid    用户userid    可能是多个     manageraaaa|sb25000   | 隔开
        type      发起领用，转让  归还    用于区分发送给谁
        content      content内容
     */
    function send_msg(type,uid,content){ 
        
        var send_msg = {
            url: OPENAPIHOST + '/getOapiByName.php?event=send_msg',
            type: 'POST',
            data:{type:type,uid:uid,content:content},
            dataType: 'json',
            success: function(response){
                // alert(JSON.stringify(response)+"success");
            },
            error: function(err){
                // alert(JSON.stringify(error)+"error");
            }
        }
        $.ajax(send_msg);
       
    }


    function authCode(corpId){
        return new Promise(function(resolve, reject){
            dd.ready(function(){
                dd.runtime.permission.requestAuthCode({
                    corpId: corpId,
                    onSuccess: function(result) {
                        resolve(result);
                    },
                    onFail : function(err) {
                        reject(err);
                    }
                });
            });
        });
    }

    function toast(tips){
        dd.ready(function(){
            dd.device.notification.toast({
                icon: '', //icon样式，有success和error，默认为空 0.0.2
                text: tips, //提示信息
                duration: 1, //显示持续时间，单位秒，默认按系统规范[android只有两种(<=2s >2s)]
                delay: 1, //延迟显示，单位秒，默认0
                onSuccess : function(result) {
                    
                },
                onFail : function(err) {
                    alert(tips);
                }
            })
        });
    }



    function getUserInfo(userid,action){
        var getUserInfoRequest = {
            url: OPENAPIHOST + '/getOapiByName.php?event=get_userinfo&userid='+userid+'&action='+action,
            type: 'POST',
            data:{userid:userid},
            dataType: 'json',
            success: function(response){
                if( action == '' ){
                    if (response.errcode === 0){
                        proper.userInfo = response;
                    } else {
                        alert(JSON.stringify(response) + 'getUserInfo');
                    }
                }
            },
            error: function(err){

            }
        };
        $.ajax(getUserInfoRequest);
    }

    function getUserInfo(userid,action,myid){
        var result = 1;
        var getUserInfoRequest = {
            url: OPENAPIHOST + '/getOapiByName.php?event=get_userinfo&userid='+userid+'&action='+action+'&myid='+myid,
            type: 'POST',
            data:{userid:userid},
            dataType: 'json',
            async : false,
            success: function(response){
               if( action == '' ){
                    if (response.errcode === 0){
                        proper.userInfo = response;
                    } else {
                        alert(JSON.stringify(response) + 'getUserInfo');
                    }
                }
                if( response.code == 2 ){
                    result = 2;
                }
                
            },
            error: function(err){
                console.log(JSON.stringify(err)+"getuserinfo error3");
            }
        };
        $.ajax(getUserInfoRequest);

        return result;
    }

 
    $(function(){
        var originalUrl = location.href;
        var corpId = parseCorpId(originalUrl, 'corpId');
        var jsApiList = [
        'runtime.info',
        'device.notification.prompt',
        'biz.chat.pickConversation',
        'device.notification.confirm',
        'device.notification.alert',
        'device.notification.prompt',
        'biz.chat.open',
        'biz.util.open',
        'biz.user.get',
        'biz.contact.choose',
        'biz.telephone.call',
        'biz.ding.post'];


        //dd.ready(function(){
        //    dd.ui.pullToRefresh.enable({
        //        onSuccess: function() {
        //            window.location.reload();
        //        },
        //        onFail: function() {
        //        }
        //    })
        //});


        console.log(OPENAPIHOST + '/getOapiByName.php?event=jsapi-oauth&href='+encodeURIComponent(location.href));
        var signRequest = {
            url: OPENAPIHOST + '/getOapiByName.php?event=jsapi-oauth&href='+encodeURIComponent(location.href),
            type: 'GET',
            dataType: 'json',
            success: function(response){
                if (response.errcode === 0){
                    const config = {
                        agentId: response.agentId || '',
                        corpId: response.corpId || '',
                        timeStamp: response.timeStamp || '',
                        nonceStr: response.nonceStr || '',
                        signature: response.signature || '',
                        jsApiList: jsApiList || []
                    };
                    dd.config(config);
                    getUserId(response.corpId);
                }
            }
        };
        $.ajax(signRequest);


    $.Change_Manager = function(obj){  //改变当前所属负责人   
        console.log("Change_Manager");

        dd.ready(function(){
            dd.biz.contact.choose({
                startWithDepartmentId: 0, //-1表示打开的通讯录从自己所在部门开始展示, 0表示从企业最上层开始，(其他数字表示从该部门开始:暂时不支持)
                multiple: false, //是否多选： true多选 false单选； 默认true
                users: [], //默认选中的用户列表，userid；成功回调中应包含该信息
                disabledUsers:['0810106769992021','075215613926334431'],
                corpId: corpId, //企业id
                title : "请选择负责人",
                max: 1, //人数限制，当multiple为true才生效，可选范围1-1500
                onSuccess: function(data) {

                    if(data&&data.length>0){
                        var selectUserId = data[0].emplId;
                        var name = data[0].name;
                        if( (selectUserId != '0810106769992021') && (selectUserId != '075215613926334431') ){
                            $(obj).html(name);
                            $(obj).parent().find("input[name='manager']").val(selectUserId);
                        }else{
                            layer.open({
                                content: '该成员不能被设为负责人！'
                                ,skin: 'msg'
                                ,time: 3 //2秒后自动关闭
                            });
                        }
                    }
                },
                onFail : function(err) {
                    alert('dd error: ' + JSON.stringify(err));
                }
            });
        });
    }



    $.Show_Tips = function(){  // 几张提示
        dd.ready(function(){
            dd.device.notification.extendModal({
                cells: [
                   
                    {
                    "image":DTPath+"images/tips/tips1.png",
                    "title":"领用操作",
                    "content":"点击所选物品，点击右下角圆圈中的 选中领用,而后继续下部操作;"
                    },
                    {
                    "image":DTPath+"images/tips/tips2.png",
                    "title":"显示物品详情",
                    "content":"领用界面，'长按'或者'双点' 物品即可查看物品的具体所属,可直接申请所需编号,以及请求转让;"
                     },
                     {
                    "image":DTPath+"images/tips/tips3.png",
                    "title":"待我处理",
                    "content":"管理员可操作 '领用申请'，普通员工可操作 '他人转让物品' 与 '他人物品请求'; "
                     },
                     {
                    "image":DTPath+"images/tips/tips4.png",
                    "title":"我的物品",
                    "content":"显示所有与我相关物品,'借用物品'中对我领用物品进行'转让,延期,归还'等操作;"
                     }
                ],
                buttonLabels:["我知道了"],// 最多两个按钮，至少有一个按钮。
                onSuccess : function(result) {
                    //onSuccess将在点击button之后回调
                    /*{
                        buttonIndex: 0 //被点击按钮的索引值，Number，从0开始
                    }*/
                  
                },
                onFail : function(err) {}
            })
        });
    }
        

    $.ReturnTime = function(obj){   //领用归还时间
        var mydate = new Date();
        var em_time = $(obj).find("input[name='returntime']").val();
        // console.log(now);    
        dd.ready(function(){
            dd.biz.util.datepicker({
                format: 'yyyy-MM-dd',
                value: em_time, //默认显示日期
                onSuccess : function(result) {
                    var now_tamp = Date.parse(mydate);  //当天时间戳
                    var time_end = new Date(result.value).getTime(); //设定目标时间
                    if( now_tamp > time_end ){  //当天比选中的大  不符合吧
                        layer.open({
                            content: '归还日期不能小于当天！'
                            ,skin: 'msg'
                            ,time: 2 //2秒后自动关闭
                        });
                    }else{
                         // returnTime = result.value;
                        $(obj).find("em").html(result.value);
                        $(obj).find("input[name='returntime']").val(result.value);
                    }
                   
                },
                onFail : function(err) {}
            })
              
        });
    }


    $.Get_Userid = function(from,obj){  //搜索专用   pe-
        console.log("Get_Userid");
         var max = "";
        if( from == 'daochu' ){
            max = 1500;
        }else{
            max = 1;
        }
        dd.ready(function(){
            dd.biz.contact.choose({
                startWithDepartmentId: 0, //-1表示打开的通讯录从自己所在部门开始展示, 0表示从企业最上层开始，(其他数字表示从该部门开始:暂时不支持)
                multiple: true, //是否多选： true多选 false单选； 默认true
                users: [], //默认选中的用户列表，userid；成功回调中应包含该信息
                corpId: 'dinga668d7860e14a4ff35c2f4657eb6378f', //企业id
                disabledUsers:['0810106769992021','075215613926334431'],
                title : "请选择使用人",
                max: max, //人数限制，当multiple为true才生效，可选范围1-1500
                onSuccess: function(data) {
                   if( from == 'daochu' ){
                        if(data&&data.length>0){
                            $(".search_userid").html("");
                            for( var i=0; i < data.length; i++ ){
                                if( (data[i].emplId != "0810106769992021") || (data[i].emplId != "075215613926334431") ){
                                    if( data[i].avatar ){
                                        var ht = '<img src='+data[i].avatar+'>';
                                    }else{
                                        var name= data[i].name;
                                        // name = name.substring(0, name.length-6);
                                        var ht = '<span>'+name.substring(name.length-2,name.length+2)+'</span>';
                                    }
                                    var html = '<em onclick="$.U_Cancel(this)" attr-userid="'+data[i].emplId+'"  attr-name="'+data[i].name+'" ><div>'+ht+'</div><div>'+data[i].name;+'</div></em>';
                                    $(".search_userid").append(html);

                                }
                                

                            }
                            // getUserInfo(selectUserId,'check');   //检查是否存在这个损色
                        }else{
                            toast("未选择使用人",'information');
                        }
                    }else if( from == 'edit' ){  //编辑
                        if( (data[0].emplId == '0810106769992021') || (data[0].emplId == '075215613926334431') ){
                            $(".userid_content .userid_tips").html("无法选择该成员,请重新选择！");
                        }else{
                            if(data&&data.length>0){    //选了人
                                var avatar = data[0].avatar;
                                var name= data[0].name;
                                var userid = data[0].emplId;
                                var ht = "";
                                if( avatar ){
                                    ht += '<img src="'+avatar+'" alt="" class="userid_avatar">';
                                }else{
                                    ht += '<div class="userid_name1">'+name.substring(name.length-2,name.length+2)+'</div>';
                                }
                                ht += '<div class="userid_name2">'+name+'<i onclick="$.Cancel_Userid()" style="color:red">(清除使用者)</i> </div> <div class="userid_tips"></div>';

                                $(".userid_content").html(ht);
                                $("input[name='userid']").val(userid);

                            }
                        }
                    }
                },
                onFail : function(err) {
                    // toast('dd error: ' + JSON.stringify(err));
                }
            });
        });
    }


    $.Give = function(id,obj){   //转让骚操作    **************************
        
        var ht = $(obj).html();
        var status = $("input[name='status']").val();
        console.log(status);


        $(".select_tips").html("若转让无反应,右上角刷新 重新加载");
        $(".select_tips").show();
        // window.setTimeout(function () {
        //     $(".select_tips").fadeOut();       
        // }, 3000);

        if( status != 3 ){

            $(".select_tips").html("物品状态已改变,无法转让");
            $(".select_tips").show();
            return false;
        }
        if( ht == '转让' ){
            dd.ready(function(){
                dd.biz.contact.choose({
                    startWithDepartmentId: 0, //-1表示打开的通讯录从自己所在部门开始展示, 0表示从企业最上层开始，(其他数字表示从该部门开始:暂时不支持)
                    multiple: false, //是否多选： true多选 false单选； 默认true
                    users: [], //默认选中的用户列表，userid；成功回调中应包含该信息
                    disabledUsers:['0810106769992021','075215613926334431'],
                    corpId: corpId, //企业id
                    title : "请选择转让人",
                    max: 1, //人数限制，当multiple为true才生效，可选范围1-1500
                    onSuccess: function(data) {

                        if(data&&data.length>0){
                            var selectUserId = data[0].emplId;
                            var name = data[0].name;

                            
                            var res = getUserInfo(selectUserId,'check',proper.userId);   //
                            if( proper.userId == selectUserId ){  //不能给自己
                                $(".select_tips").html("不能转让给自己！");
                                $(".select_tips").show();
                            }else if( (selectUserId == "0810106769992021") || (selectUserId == "075215613926334431") ){
                                $(".select_tips").html("不能转让给该成员！");
                                $(".select_tips").show();
                                toast("不能转让给该成员！",'warning');

                            }else{
                                $(".ajax_show_log .note").attr("placeholder","请填写转让备注");
                                $(".ajax_show_log .note").fadeIn();
                                $(".selectUserId").val(selectUserId);
                                $(".select_tips").html("转让给 "+name);
                                $(".select_tips").show();
                                $(obj).html("确认转让");
                                $(obj).css({"color":"#f30"});
                                
                            }
                        }
                    },
                    onFail : function(err) {
                        alert('dd error: ' + JSON.stringify(err));
                    }
                });
            });
        }else{  //成功转让
            var nid = $(".selectUserId").val();
            var note = $(".ajax_show_log .note").val();
            $.post(DTPath+'ajax.php', {"ac": "Edit", "func": "my_goods","id":id,"type":"give","note":note,"need_userid":nid}, function( data ){
                var o = eval("("+data+")");
                if( o.code == 1 ){

                    layer.open({
                        content: o.msg
                        ,skin: 'msg'
                        ,time: 1.5  //1.5秒后自动关闭
                    });
                    send_msg('give',o.uid,o.content);
                    $("#"+id).fadeOut();
                    $("#"+id).find(".session-content").removeClass("session-current");
                }else if( o.code == 2){  //未知错误
                    $(".select_tips").html(o.msg);
                    $(".select_tips").show();
                }
            });

        }    
    }

  

    $.Action_Confirm = function(id,action,type){  //105  处理申请   confirm   apply    *******************  
       
        var note = $(".ajax_show_log .note").val();   //操作备注
        // console.log(id+"||"+action+"||"+type+"||"+note);
        // return false;
        $.post(DTPath+'ajax.php', {"ac": "Edit", "func": "ajax_confirm_log","id":id,"action":action,"type":type,"note":note}, function( data ){
            var o = eval("("+data+")");
            if( o.code == 1 ){
                layer.open({
                    content: o.msg
                    ,skin: 'msg'
                    ,time: 2  //1秒后自动关闭
                });
                $("#"+id).fadeOut();
                $("#"+id).find(".session-content").removeClass("session-current");
                console.log(type);
                if( type == 'give' ){  //give 数量变更
                    var a = $.trim($(".give_count").html());
                    a = a.substring(1,a.length-1)-1;
                    if( a <=0 ){
                        $(".give_count").html("");
                    }else{
                        $(".give_count").html(" ( "+a+" ) ");
                    }
                }else if( (type == 'apply') || (type == 'return')  ){  //apply 数量变更
                    var a = $.trim($(".apply_count").html());
                    a = a.substring(1,a.length-1)-1;
                    if( a <=0 ){
                        $(".apply_count").html("");
                    }else{
                        $(".apply_count").html(" ( "+a+" ) ");
                    }
                }else if( type == 'repair' ){  //维修
                    var a = $.trim($(".apply_count").html());
                    var b = $.trim($(".repaire_count").html());
                    a = a.substring(1,a.length-1);
                    b = b.substring(1,b.length-1);

                    if( action == 'repair' ){  //维修中
                        var c = a-1;
                        if( c <= 0 ){
                            $(".apply_count").html("");
                        }else{
                            $(".apply_count").html(" ( "+c+" ) ");
                        }
                        console.log(b);
                        if( b <=0 ){
                            $(".repaire_count").html(" ( 1 ) ");
                        }else{
                            var d = parseInt(b)+1;
                            $(".repaire_count").html(" ( "+d+" ) ");
                        }
                       

                    }else if( action == 'repaired' ){  //维修完成
                        var a = $.trim($(".repaire_count").html());
                        a = a.substring(1,a.length-1)-1;
                        if( a <=0 ){
                            $(".repaire_count").html("");
                        }else{
                            $(".repaire_count").html(" ( "+a+" ) ");
                        }
                    }
                }
            }else if( o.code == 2){  //未知错误
                layer.open({
                    content: o.msg
                    ,skin: 'msg'
                    ,time: 2  //1.5秒后自动关闭
                });
                window.setTimeout(function () {
                        window.location.reload();
                }, 1500);
            }
        }); 
       
    }

    $.Cancel_Edit = function(id,obj,type,action){  //修改备注和撤回*********************action=== apply,give,return
        var ht = $(obj).html();
        var note = $(".ajax_show_log .note").val();

        console.log(ht+"||"+action);
        // return false;

        if( action == 'apply' ){
            if( ht == '撤销申请' ){
                $(".ajax_show_log .note").attr("placeholder","请填写撤销申请备注");
                $(".ajax_show_log .note").fadeIn();
                $(obj).html("确认撤销申请");   //-----------
                $(obj).css({"color":"#f30"}); 

            }else if( ht == '修改申请备注' ){
                $(".ajax_show_log .note").attr("placeholder","请填写申请备注");
                $(".ajax_show_log .note").fadeIn();
                $(obj).html("确认修改申请备注");  //-----------
                $(obj).css({"color":"#f30"}); 
            }
        }else if( action == 'give' ){  //转让修改
            if( ht == '撤销转让' ){
                $(".ajax_show_log .note").attr("placeholder","请填写撤销转让备注");
                $(".ajax_show_log .note").fadeIn();
                $(obj).html("确认撤销转让");   //-----------
                $(obj).css({"color":"#f30"}); 

            }else if( ht == '修改转让备注' ){
                $(".ajax_show_log .note").attr("placeholder","请填写转让备注");
                $(".ajax_show_log .note").fadeIn();
                $(obj).html("确认修改转让备注");  //-----------
                $(obj).css({"color":"#f30"}); 
            }
        }else if( action == 'return' ){ //归还修改
            if( ht == '撤销归还' ){
                $(".ajax_show_log .note").attr("placeholder","请填写撤销归还备注");
                $(".ajax_show_log .note").fadeIn();
                $(obj).html("确认撤销归还");   //-----------
                $(obj).css({"color":"#f30"}); 

            }else if( ht == '修改归还备注' ){
                $(".ajax_show_log .note").attr("placeholder","请填写归还备注");
                $(".ajax_show_log .note").fadeIn();
                $(obj).html("确认修改归还备注");  //-----------
                $(obj).css({"color":"#f30"}); 
            }
        }
        if( (ht == '确认撤销申请' || ht == '确认修改申请备注') || (ht == '确认撤销转让' || ht == '确认修改转让备注') || (ht == '确认撤销归还' || ht == '确认修改归还备注') ){
            $.post(DTPath+'ajax.php', {"ac": "Edit", "func": "edit_cancel","id":id,"note":note,"type":type,"action":action}, function( data ){
                var o = eval("("+data+")");
                if( o.code == 1 ){
                    if( type == 'cancel' ){
                        layer.open({
                            content: o.msg
                            ,skin: 'msg'
                            ,time: 2  //1秒后自动关闭
                        });
                        $("#"+id).fadeOut();
                        $("#"+id).find(".session-content").removeClass("session-current");
                    }else{
                        console.log(o.note);
                        $("#"+id).find(".edit_note").html(o.note);
                        layer.open({
                            content: o.msg
                            ,skin: 'msg'
                            ,time: 2  //1秒后自动关闭
                        });
                    }
                }else if( o.code == 2){  //未知错误
                    $(".select_tips").html(o.msg);
                    $(".select_tips").show();

                }
            }); 
        }
    }

    $.Return = function(id,obj){  //归还 ************************
        var ht = $(obj).html();
        var status = $("input[name='status']").val();
      
        if( ht == '归还' ){
            $(".ajax_show_log .note").attr("placeholder","请填写归还备注");
            $(".ajax_show_log .note").fadeIn();
            $(obj).html("确认归还");
            $(obj).css({"color":"#f30"});
        }else{
            var note = $(".ajax_show_log .note").val();
            console.log(note);
            if( id ){
                
                $.post(DTPath+'ajax.php', {"ac": "Edit", "func": "my_goods","id":id,"note":note,"type":"return"}, function( data ){
                    var o = eval("("+data+")");
                    if( o.code == 1 ){
                        layer.open({
                            content: o.msg
                            ,skin: 'msg'
                            ,time: 2  //1秒后自动关闭
                        });
                        send_msg('return',o.cid, o.content);
                        $("#"+id).fadeOut();
                        $("#"+id).find(".session-content").removeClass("session-current");
                    }else if( o.code == 2){  //未知错误
                        $(".select_tips").html(o.msg);
                        $(".select_tips").show();

                    }
                }); 

            }else{
                layer.open({
                    content: '没有匹配物品'
                    ,skin: 'msg'
                    ,time: 1
                });
            }
        }
        
    };

    $.Goods_Action = function(){   //apply.htm 领用和  已经木有请转操作啦   ***********************
        var $arr = $(".apply_form #form").serializeArray();
        var is_con = $("input[name='is_con']").val();
        var gid = $("input[name='gid']").val();
        console.log(""+$arr);
        if( $arr.length ){
            layer.open({
                content: '是否确认申请物品'
                ,btn: ['确认申请','取消']
                ,skin: 'footer'
                ,yes: function(index){
                layer.open({
                    type: 2
                    ,shadeClose: false
                    ,content: '提交申请中...'
                });
                $.post(DTPath+'ajax.php', {"ac": "Edit", "func": "goods_action","gid":gid,"arr":$arr}, function( data ){
                        var o = eval("("+data+")");
                        if( o.code == 1 ){
                            layer.open({
                                content: o.msg
                                ,skin: 'msg'
                                ,time: 2  //1.5秒后自动关闭
                            });
                            send_msg('apply',o.cid,o.content);
                            window.setTimeout(function () {
                                var fromtype = $(".apply_form input[name='fromtype']").val();
                                window.location.href = DTPath+"page.php?from=edit&ac=edit&fromtype="+fromtype;
                            }, 2000);
                        }else if( o.code == 2){  //优先级错误
                            // console.log(o.errid);
                            layer.open({
                                content: o.msg
                                ,shadeClose: false
                                ,btn: ['我知道了']
                                ,skin: 'footer'
                                ,yes: function(index){
                                    for(var i = 0; i < o.errid.length; i++){  
                                        // console.log("--"+o.errid[i]);  
                                        $(".apply_content #id_"+o.errid[i]).remove();
                                    } 
                                    layer.closeAll();
                                }
                            });
                        }else if( o.code == 3 ){
                            layer.open({
                                content: o.msg
                                ,skin: 'msg'
                                ,time: 2  //2秒后自动关闭
                            });
                            window.setTimeout(function () {
                                    // layer.closeAll();
                                    window.location.reload();
                            }, 2000);
                        }
                }); 
                  layer.close(index);
                }
            });
        }else{
            layer.open({
                content: '请增加物品明细'
                ,skin: 'msg'
                ,time: 1.5  //1.5秒后自动关闭
              });
            return false;
        }
    }







    });
})();
