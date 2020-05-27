//打开字滑入效果
window.onload = function(){
	$(".connect p").eq(0).animate({"left":"0%"}, 600);
	$(".connect p").eq(1).animate({"left":"0%"}, 400);
};
$(function(){
	//注册页登陆提示
	$(".wrap-register").find('.username').focus(function(){
		layer.tips('输入手机号', '.username', {
		  tips: [4, '#3595CC'],
		  time: 2000
		});
	})
	
	$(".wrap-register").find('.password').focus(function(){
		layer.tips('请输入6位以上密码', '.password', {
		  tips: [4, '#3595CC'],
		  time: 2000
		});
	})

	//注册
	$("#reg").on('click',function(){
		var reg = /^1[3|4|5|7|8]{1}[0-9]{9}$/;
		var forward = $(".forward").val();
		if( !forward ){
			forward = DTPath;
		}
		var username = $(".wrap-register").find('.username').val();	
		var mobileCode = $(".mobileCode").val();
		var password = $(".wrap-register").find('.password').val();	
		var c_psd = $(".wrap-register").find('.c_password').val();	
		// console.log(username+'||'+psd+'||'+c_psd);
		if( username =='' || !reg.test(username) ){
			layer.tips('手机号格式不正确!', '.username', {
			  tips: [4, '#f60'],
			  time: 2000
			});
			return false;
		}
		if( mobileCode == '' ){
			layer.tips('请填写验证码!', '.mobileCode', {
			  tips: [4, '#f60'],
			  time: 2000
			});
			return false;
		}
		if( password.length <6 ){
			layer.tips('密码长度不足6位!', '.password', {
			  tips: [4, '#f60'],
			  time: 2000
			});
			return false;	
		}
		if( password != c_psd ){
			layer.tips('两次密码不一致!', '.c_password', {
			  tips: [4, '#f60'],
			  time: 2000
			});
			return false;	
		}
		$.post(DTPath+'ajax.php', {"ac": "Member", "func": "Register", "username": username,"code":mobileCode,"password":password}, function( data ){
			var o = eval("("+data+")"); //将返回的data转为json数组  飞舞战场的美少女,大活跃
			if( o.code == 1 ){
				$("#reg").css("background","#5cb85c")
				$("#reg").html('注册成功...');
				layer.load();
				window.setTimeout(function(){
					//type = 1 方便提示
					// location.href = DTPath+'page.php?ac=member&func=show&type=1&forward='+forward;
					if( forward ){
						location.href = forward;
					}else{
						location.href = DTPath;
					}
				},2000);

			}else if( o.code == 2 ){
				layer.msg('<span style="color:#000">'+o.msg+'<span>',{icon:5});
			}
		});
	
	})
	//登录
	$("#log").on('click',function(){
		var forward = $(".forward").val();
		var username = $(".wrap-login").find(".username").val();
		var password = $(".wrap-login").find(".password").val();
		$.post(DTPath+'ajax.php',{"ac":"Member","func":"Login","username":username,"password":password},function(data){
			var o = eval("("+data+")");
			if( o.code == 1 ){
				$("#log").css("background","#5cb85c")
				$("#log").html(o.msg);
				layer.load();
				setTimeout(function(){
					if( forward ){
						location.href = forward;
					}else{
						location.href = DTPath;	
					}
				},2000)
			}else{
				$(".login-tips").show();
				$(".login-tips").html(o.msg);
				setTimeout(function(){
					$(".login-tips").fadeOut();
				},2000)
			}

		});
	})

	$.Dtimer = function(t) {
		if( $('.get_code').hasClass('btn-inverse') ){
			return false;
		}
		var i = t ? t : 30;   //判断时间

		var reg = /^1[3|4|5|7|8]{1}[0-9]{9}$/;
		var username = $(".wrap-register").find('.username').val();	
		if( username =='' || !reg.test(username) ){
			layer.tips('手机号格式不正确!', '.username', {
			  tips: [4, '#f60'],
			  time: 2000
			});
			return false;
		}

		var query = new Object();
		query.ac = 'Member';
		query.func = 'Send_Mobile_Code';
		query.username = username;
		$.ajax({
			url: DTPath+'ajax.php',
			type: "POST",
			dataType: "json",
			data: query,
			success: function( data ){
				if( data.code == 1 ){
					layer.msg('<span style="color:#000">发送成功!<span>',{icon:1});
					$('.get_code').addClass("btn-inverse").html('重新发送('+i+')');
					var time_int=window.setInterval(
						function() {
							if(i == 1) {
								$('.get_code').removeClass("btn-inverse").html('获取验证码');
								clearInterval(time_int);
							} else {
								i--;
								$('.get_code').addClass("btn-inverse").html('重新发送('+i+')');
							}
						},
					1000);	
				}else if(data.code == 2){
					layer.tips(data.msg,'.username',{tips:[4,'#f60'],time:2000});
				}else if( data.code == 3 ){
					layer.msg('<span style="color:#000">'+data.msg+'<span>',{icon:5});
				}
			}
		});  //获取验证码
	}

	//回车键点击事件
	$(".wrap-register").find(".username,.c_password").on('keydown',function(){
		if( event.keyCode == 13 ){
        	$("#reg").click();
        }
	})

	$(".wrap-login").find(".username,.password").on('keydown',function(){
		if( event.keyCode == 13 ){
        	$("#log").click();
        }
	})








})