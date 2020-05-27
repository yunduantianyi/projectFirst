$(document).ready(function(e) {
	$.Company_List = function(id,title){
		layer.open({
		  type: 2,
		  area: ['500px', '300px'],
		  offset: ['100px'],
		  fix: true,
		  maxmin: false,
		  shade: 0.6,
		  title: title,
		  content:"ajax_template.php?mod=company&ac=list&id="+id,
		  // success: function(){
		  // 	$(".layui-layer-title").html(title);
		  // }
		});
	}

	$.Add_Ad = function(id){
		layer.open({
		  type: 2,
		  area: ['500px', '500px'],
		  offset: ['100px'],
		  fix: true,
		  maxmin: false,
		  shade: 0.6,
		  title: '添加广告位',
		  content:"ajax_template.php?mod=advert&ac=add&id="+id,
		  // success: function(){
		  // 	$(".layui-layer-title").html(title);
		  // }
		});
	}


	$.Add_ads = function(id,title){  //添加广告
		layer.open({
		  type: 2,
		  area: ['700px', '500px'],
		  offset: ['100px'],
		  fix: true,
		  maxmin: false,
		  shade: 0.6,
		  title:title,
		  content:"ajax_template.php?mod=advert&ac=ads&id="+id,
		 
		});

		
	}




	$.Custom_genjin = function(id){
		layer.open({
		type: 2,
		area: ['100%', '100%'],
		offset: ['10px'],
		fix: true,
		maxmin: false,
		shade: 0.6,
		title: '跟进历史',
		content:"ajax_template.php?mod=custom&ac=genjin&id="+id
		});
	}



	$.User_Login = function(obj){  //后台登陆判断
		var username = $("input[name='username']").val();
		var password = $("input[name='password']").val();

		if(username == ''){
			$('#username').focus();	
			return false;
		}
		
		if(password == ''){
			$('#password').focus();	
			return false;
		}
		var query = new Object();
		query.username = username;
		query.password = password;
		query.ac = 'Member';
		query.func = 'login';
	
		// console.log(query);
		// return false;
		$.ajax({
			data:query,
			type:"POST",
			dataType:"json",
			url:DTPath+"ajax_admin.php",
			success: function(data){
				if( data.code == 1 ){
					$(".submit_btn").val('登录成功,正在跳转!');
					
					setTimeout(function(){
						window.location.reload();
					},2500)
					
				}else{
					$(".a-login-tips").html(data.msg);
					$(".a-login-tips").show();
					setTimeout(function(){
						$(".a-login-tips").fadeOut();
					},2500)
				}
				
			}
		});
	}

	$.User_LoginOut = function(){  // 通用 退出判断
		var query = new Object();
		query.ac = 'Member';
		query.func = 'loginout';
		// console.log(DTPath);
		$.ajax({
			data:query,
			type:"POST",
			dataType:"json",
			url:DTPath+"ajax_admin.php",
			success: function(data){
				if( data.code == 1 ){
					layer.load();
					setTimeout(function(){
						window.location.reload();
					},2000)
					
				}else{
					
				}
				
			}
		});
	}


	$('button.J_tabRight_Reflush').click(function(){
		$('div.J_mainContent').find('iframe.J_iframe:visible').attr('src',$('div.J_mainContent').find('iframe.J_iframe:visible').attr('data-id'));
	});



});
