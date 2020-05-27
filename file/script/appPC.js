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
            getUserInfo(proper.userId,'','');
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
            DingTalkPC.biz.util.openLink({
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
                    console.log(JSON.stringify(response)+"getuserid response");
                    if (response.errcode === 0){
                        proper.userId = response.userid;
                    } 
                },
                error: function(err){
                    console.log(JSON.stringify(err)+"getuserid error");
                }
            }
            $.ajax(getUserIdRequest);
        }).catch(function(error){
            console.log(JSON.stringify(error)+"getuserid catch");
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
            DingTalkPC.ready(function(){
                DingTalkPC.runtime.permission.requestAuthCode({
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

    function toast(tips,type){
        if( !type ){
            type = 'alert';
        }
        DingTalkPC.device.notification.toast({
            type: type, //toast的类型 alert, success, error, warning, information, confirm
            text: tips, //提示信息
            duration: 2, //显示持续时间，单位秒，最短2秒，最长5秒
            delay: 0, //延迟显示，单位秒，默认0, 最大限制为10
            onSuccess : function(result) {
                /*{}*/
            },
            onFail : function(err) {}
        })
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
        'device.notification.alert',
        'device.notification.toast',
        'biz.util.openSlidePanel',
        'biz.navigation.quit',
        'device.notification.confirm',
        'biz.util.uploadImage',
        'biz.contact.choose',
        'biz.util.downloadFile',
        'biz.chat.chooseConversation',
        'biz.util.previewImage',
        'biz.util.ut',
        'biz.util.open',
        'biz.user.get',
        'runtime.permission.requestAuthCode',
        'biz.contact.externalComplexPicker',
        'biz.contact.pickCustomer',
        'biz.util.uploadAttachment',
        'biz.cspace.preview',
        'biz.util.openLink',

    ];

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

                    DingTalkPC.config(config);
                    getUserId(response.corpId);
                  

                
                    
                }
            }
        };
        $.ajax(signRequest);



    $.Change_Manager = function(obj){  //改变当前所属负责人   
        console.log("Change_Manager");

        DingTalkPC.ready(function(){
            DingTalkPC.biz.contact.choose({
                startWithDepartmentId: 0, //-1表示打开的通讯录从自己所在部门开始展示, 0表示从企业最上层开始，(其他数字表示从该部门开始:暂时不支持)
                multiple: false, //是否多选： true多选 false单选； 默认true
                users: [], //默认选中的用户列表，userid；成功回调中应包含该信息
                disabledUsers:['0810106769992021','075215613926334431'],
                corpId: 'dinga668d7860e14a4ff35c2f4657eb6378f', //企业id
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
                            // layer.open({
                            //     content: '该成员不能被设为负责人！'
                            //     ,skin: 'msg'
                            //     ,time: 3 //2秒后自动关闭
                            // });
                            toast("该成员不能被设为负责人！",'warning');
                        }
                    }
                },
                onFail : function(err) {
                    // alert('dd error: ' + JSON.stringify(err));
                }
            });
        });
    }

    $.Get_Userid = function(from,obj){  //搜索 导出专用====================
        console.log(from+"   Get_Userid");
        var max = "";
        if( from == 'daochu' ){
            max = 1500;
        }else{
            max = 1;
        }
        DingTalkPC.ready(function(){
            DingTalkPC.biz.contact.choose({
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
        if( status != 3 ){

            $(".select_tips").html("物品状态已改变,无法转让");
            $(".select_tips").show();
            toast("物品状态已改变,无法转让","warning");
            return false;
        }
        if( ht == '转让' ){

            DingTalkPC.ready(function(){
                DingTalkPC.biz.contact.choose({
                    startWithDepartmentId: 0, //-1表示打开的通讯录从自己所在部门开始展示, 0表示从企业最上层开始，(其他数字表示从该部门开始:暂时不支持)
                    multiple: false, //是否多选： true多选 false单选； 默认true
                    users: [], //默认选中的用户列表，userid；成功回调中应包含该信息
                    corpId: 'dinga668d7860e14a4ff35c2f4657eb6378f', //企业id
                    disabledUsers:['0810106769992021','075215613926334431'],
                    title : "请选择转让人",
                    max: 1, //人数限制，当multiple为true才生效，可选范围1-1500
                    onSuccess: function(data) {
                        console.log(data);
                        if(data&&data.length>0){
                            var selectUserId = data[0].emplId;
                            var name = data[0].name;

                            var res = getUserInfo(selectUserId,'check',proper.userId);   //

                            console.log(res+"res");
                            if( proper.userId == selectUserId ){  //不能给自己
                                $(".select_tips").html("不能转让给自己！");
                                $(".select_tips").show();
                                toast("不能转让给自己！",'warning');
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
        }else{
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
                    toast("物品已转让",'success');
                    $("#"+id).fadeOut();
                    $("#"+id).find(".session-content").removeClass("session-current");
                }else if( o.code == 2){  //未知错误
                    $(".select_tips").html(o.msg);
                    $(".select_tips").show();
                }
            });

        }

         
    }

     $.Action_Confirm = function(id,action,type){  //105   confirm   apply    *******************  
       
        var note = $(".ajax_show_log .note").val();   //操作备注
       
        // console.log(id+"||"+action+"||"+type+"||"+note);
        // return false;
        
        $.post(DTPath+'ajax.php', {"ac": "Edit", "func": "ajax_confirm_log","id":id,"action":action,"type":type,"note":note}, function( data ){
            var o = eval("("+data+")");
            if( o.code == 1 ){
                toast("操作成功！",'success');
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
                        toast(o.msg,"success");
                        layer.open({
                            content: o.msg
                            ,skin: 'msg'
                            ,time: 2  //1秒后自动关闭
                        });
                        $("#"+id).fadeOut();
                        $("#"+id).find(".session-content").removeClass("session-current");
                    }else{
                        toast(o.msg,"error");
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
                        toast("操作成功！","success");
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
