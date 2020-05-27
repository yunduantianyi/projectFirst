$(function(){
	var $height = $(window).height();


	$(window).resize(function () {          //当浏览器大小变化时
		var height1 = $(window).height(); //浏览器时下窗口文档的高度
		var height2 = $(document).height(); //浏览器时下窗口可视区域高度
		if( $height > height1 ){   //原本比当前大  键盘顶上去
			$(".tab-bar").hide();
			$(".doaction").hide();
			$(".doactions_3,.doactions_4").hide();

			$(".do_apply").hide();
		}else{
			$(".tab-bar").show();
			$(".doaction").show();

			$(".doactions_3,.doactions_4").show();

			$(".do_apply").show();

		}

	});

	$.Change_checked = function(obj){

		if( $(obj).find("span").attr("class") == 'class_none' ){
			$(obj).find("span").removeClass("class_none");
			$(obj).find("span").addClass("class_checked");
		}else{
			$(obj).find("span").removeClass("class_checked");
			$(obj).find("span").addClass("class_none");

		}
	};
	$.From = function(from){
		console.log(from);
		var href = DTPath+"page.php?from="+from+"&ac=edit";
		layer.open({
			 title: [
		      '请选择物品来源'
		    ]
		    ,content: '<div style="width:100%;height:60px;line-height:60px;"><a style="width:35%;font-size:14px;margin:0 3px" href='+href+'&fromtype=1 class="btn btn-info">企业内部物品</a><a style="width:35%;margin:0 3px;font-size:14px;" href='+href+'&fromtype=2 class="btn btn-info">外部物品领用</a></div>'
		    
		});	
	}

	$.FILES = function(){
		var use = $("input[name='use']").val();
		if( use == 'pc' ){  //请在pc操作
			var fromtype = $("input[name='fromtype']").val();
			console.log(fromtype);
			 	
	    	var query = new Object();
			query.ac = 'Edit';
			query.func = 'ajax_file_ruku';
			query.fromtype = fromtype;
			query.height = parseInt($height * 0.7);
			$.ajax({
				data: query,
				type: "POST",
				dataType: "json",
				url: DTPath+'ajax.php',
				success: function(data){
					layer.open({
						 title: [
					      "物品录入操作"
					    ]
					    ,content: data.html
					    ,btn: ['关闭']
					    ,yes: function(index){
					      // location.reload();
					      layer.close(index);
					    }
					});	
				}
			});
		}else{
			layer.open({
                content: '录入请在<b style="color:#5bc0de">客户端</b>操作'
                ,btn: ['我知道了']
                ,skin: 'footer'
                ,yes: function(index){
                    layer.closeAll();
                }
            });
		}
	}

	$.Daochu_Category = function(){
		// var use = $("input[name='use']").val();
		var query = new Object();
		query.ac = 'Edit';
		query.func = 'ajax_daochu_category';
		
		$.ajax({
			data: query,
			type: "POST",
			dataType: "json",
			url: DTPath+'ajax.php',
			success: function(data){
				// console.log(data.code);
				if( data.code == 1 ){
					window.location.href = DTPath+'uploads/category.xls';
				}else{

				}
			}
		});
	}
	$.No_Field = function(obj){
		if( $(obj).hasClass("no_add") ){
			$(obj).removeClass("no_add");
		}else{
			$(obj).addClass("no_add");
		}
		
	}
	$.Daochu_Goods = function(){
		layer.open({
	        type: 1
	        ,offset: 'rt' //具体配置参考：http://www.layui.com/doc/modules/layer.html#offset
	        ,title:'导出字段选择'
	        ,content: '<div class="daochu_js_class"><i attr-field="id" onclick="$.No_Field(this)">序号</i><i attr-field="gid" onclick="$.No_Field(this)">物品名称</i><i attr-field="bianhao" onclick="$.No_Field(this)">编号</i><i attr-field="xinghao" onclick="$.No_Field(this)">型号</i><i attr-field="neicun" onclick="$.No_Field(this)">内存</i><i attr-field="banben" onclick="$.No_Field(this)">版本</i><i attr-field="xitong" onclick="$.No_Field(this)">系统</i><i attr-field="userid" onclick="$.No_Field(this)">当前使用者</i><i attr-field="number" onclick="$.No_Field(this)">物品数量</i><i attr-field="status" onclick="$.No_Field(this)">物品状态</i><i attr-field="note" onclick="$.No_Field(this)">备注</i><i attr-field="note_use" onclick="$.No_Field(this)">领用备注</i><i attr-field="starttime" onclick="$.No_Field(this)">领用时间</i><i attr-field="usingtime" onclick="$.No_Field(this)">启用时间</i><i attr-field="edittime" onclick="$.No_Field(this)">入库时间</i></div>'
	        ,btn: '确定导出'
	        ,btnAlign: 'c' //按钮居中
	        ,shade: 0 //不显示遮罩
	        ,yes: function(){
	          set_goods_daochu();
	          // layer.closeAll();
	        }
	      });
	}
	function set_goods_daochu(){
		var field = "",category="",userid="",status="";
		$(".daochu_js_class i").each(function(){
			if( ($(this).attr("class") == undefined) || ($(this).attr("class") == "")  ){
				field += $(this).attr("attr-field")+",";
			}
		})

		if( field ){
			var query = new Object();
			query.ac = 'Edit';
			query.func = 'ajax_daochu_goods';
		  	$(".search_category").find("input[name='category[]']").each(function(){
		  		if( $(this).is(":checked") ){
		  			category += $(this).val()+",";
		  		}
		  	})

		  	$(".search_userid em").each(function(){
		  		if( $(this).is(":visible") ){
		  			userid += $(this).attr("attr-userid")+",";
		  			userid += $(this).attr("attr-name")+",";
		  		}
		  	})

		  	$(".status").find("input[name='status[]']").each(function(){
		  		if( $(this).is(":checked") ){
		  			status += $(this).val()+",";
		  		}
		  	})
		  	query.category = category;
		  	query.userid = userid;
		  	query.status = status;

		  	query.starttime = $("input[name='starttime']").val();
	    	query.usingtime = $("input[name='usingtime']").val();
	    	query.edittime = $("input[name='edittime']").val();
	    	query.keywords = $("input[name='keywords']").val();


	    	query.field = field;  //传入field

	    	layer.load(2);

			$.ajax({
				data: query,
				type: "POST",
				dataType: "json",
				url: DTPath+'ajax.php',
				success: function(data){
					if( data.code == 1 ){
						layer.closeAll();
						window.location.href = data.href;
					}else{
						layer.msg(data.msg);
						layer.closeAll('loading');
					}
				}
			});

		}else{
			layer.tips('请至少选择一个字段导出', '.daochu_js_class', {
			 	tips: [4, '#F60']
			})


		}
	}

	$.Upload = function(type){
		if( type == 'uploads' ){
			var fd = new FormData();  //实例化
			var xls = $(".xls_upload").get(0).files[0];   //上传资源
			if (xls == undefined) {  //空不能过去
				$(".xls_upload").val("");
				$(".add_html").html("上传内容为空！");
				return false;
			}
			fd.append("upfile", xls);  //上传screenshot
			fd.append("ac","Edit");
			fd.append("func","ajax_check_file");
			fd.append("fromtype",$("input[name='fromtype']").val());

			$.ajax({
				url: DTPath+"ajax.php",
				type: "POST",
				processData: false,
				contentType: false,
				data: fd,
				dataType:"json",
				success: function (data) {
					if( data.code == 1 ){
						$(".add_html").html(data.msg);
						window.setTimeout(function () {
	                   		window.location.href = DTPath+"page.php?ac=edit&func=check_uploads&from=uploads";
	               		}, 1000);
					}else if( data.code == 2 ){
						$(".add_html").html(data.msg);
					}
				}
			});
		}else{
			window.location.href = DTPath+"page.php?ac=edit&func=check_uploads";

		}	
	}
	$.Upload_img = function(obj,gid){
		var fd = new FormData();  //实例化
		var img = $(obj).get(0).files[0];   //上传资源
		
		fd.append("upfile", img);  //上传screenshot
		fd.append("ac","Edit");
		fd.append("func","ajax_set_image");
		fd.append("gid",gid);

		$.ajax({
			url: DTPath+"ajax.php",
			type: "POST",
			processData: false,
			contentType: false,
			data: fd,
			dataType:"json",
			success: function (data) {
				if( data.code == 1 ){
					$(obj).closest('.ajax_get_image').find("img").attr("src","");
					// window.setTimeout(function () {
                   		$(obj).closest('.ajax_get_image').find("img").attr("src",data.msg);
               		// }, 500);
					

				}else{
					$(obj).parent().next().next().html(data.msg);
					window.setTimeout(function () {
                   		$(obj).parent().next().next().html("");
               		}, 3000);
				}
			}
		});
	}



	var itime = 0;


	$.Index_Click = function(obj){

		
		var id = $(obj).attr("id");
		var fromtype = $("input[name='fromtype']").val();
		var isAdmin = $("input[name='isAdmin']").val();
		var isMobile = $("input[name='isMobile']").val();

		// itime++;
		// setTimeout(function () {
  //           itime = 0;
  //       }, 200);
		// if (itime > 1) {  //这是双击666
		        // }
		    
		if( isMobile == 1 ){  //手机
			var click = $(obj).attr("attr-click");
			if( click == 0 ){
				$(obj).attr("attr-click","1");
			}else{
				layer.open({
				    content: '手机端<span style="color:#f60">长按</span>选择物品'
				    ,skin: 'msg'
				    ,time: 2 //2秒后自动关闭
				  });
				$(obj).attr("attr-click","0");
			}

		}else{
			window.location.href = DTPath+"page.php?ac=edit&func=apply&fromtype="+fromtype+"&id="+id;
		}

          	
		 

	}


	$.Add_Note = function(obj,gid = ''){  //带gid   请求借阅
		if( gid ){
			var $lt = $(obj).closest('.check_add_note');

			console.log(gid);
			var query = new Object();
			query.ac = 'Edit';
			query.func = 'ajax_need_book';
			query.gid  = gid;
			$.ajax({
				data: query,
				type: "POST",
				dataType: "json",
				url: DTPath+'ajax.php',
				success: function(data){
					

					if( data.code == 1 ){

						$lt.find(".need_tips").removeClass("red green");
						$lt.find(".need_tips").addClass("green");
						$lt.find(".need_tips").html(data.msg);
						$lt.find(".need_tips").fadeIn();
						$lt.find(".add_need_book").append(data.html);
					}else{
			
						$lt.find(".need_tips").removeClass("red green");
						$lt.find(".need_tips").addClass("red");
						$lt.find(".need_tips").html(data.msg);
						$lt.find(".need_tips").fadeIn();

					}

					window.setTimeout(function () {
                   		$lt.find(".need_tips").fadeOut();
               		}, 3000);
				}
			});



		}else{
			var $tr = $(obj).closest('tr');
			if( $(obj).html() == '收起' ){
				//去除其他边框
				$(obj).html("查看详情");
				// $tr.removeClass("border-no-bottom");
				$tr.next().fadeOut();
				$tr.next().removeClass("border-no-top");
			}else{
				$tr.next("tr").fadeIn();
				// $tr.addClass("border-no-bottom");
				$tr.next().addClass("border-no-top");
				$tr.find("a").html("收起");

				//去除其他边框
				$tr.siblings().find("a").html("查看详情");
				// $tr.siblings().removeClass("border-no-bottom");
				$tr.next().siblings(".apply-desc").fadeOut();
				$tr.next().siblings().removeClass("border-no-top");
			}	
		}
		
	}





$.Log = function(obj){
	// console.log(parseInt($(window).width() * 0.8)-130);
	$(obj).find(".session-content").addClass("session-current");
	$(obj).siblings().find(".session-content").removeClass("session-current");

	var id = $(obj).attr("id");
	var userid = $("input[name='userid']").val();
	var title = $(obj).find(".content-top .left").html();
	var type = $("input[name='type']").val();
	

	if( (type != 'apply') && (type != 'give') ){
		type = '';
	}

	console.log(type+"$.Log");

	var query = new Object();
	query.ac = 'Index';
	query.func = 'ajax_show_log';
	query.userid = userid;
	query.id = id;
	query.width =parseInt($(window).width() * 0.8)-130;
	query.height = parseInt($height * 0.7);
	query.type = type;
	$.ajax({
		data: query,
		type: "POST",
		dataType: "json",
		url: DTPath+'ajax.php',
		success: function(data){
			layer.open({
				title: [
					title
				]
				,content: data.html
				,btn: ['关闭']
				,yes: function(index){
					// location.reload();
					layer.close(index);
				}
			}); 
		}
	});
	
}




var time = 0;//初始化起始时间  


$(".longClick").on('touchstart', function(e){  
    e.stopPropagation();  
    time = setTimeout(function(){  

    	var id = e.currentTarget.id;
    
		var fromtype = $("input[name='fromtype']").val();
		var isAdmin = $("input[name='isAdmin']").val();

		var top = $(".content").scrollTop();  //记录滚动位置
		sessionStorage.contentTop = top;
		// console.log(sessionStorage.contentTop);
 		window.location.href = DTPath+"page.php?ac=edit&func=apply&fromtype="+fromtype+"&id="+id;


    }, 1000);//这里设置长按响应时间  
});  
  
$(".longClick").on('touchend', function(e){  
    e.stopPropagation();  
    clearTimeout(time);    
});


if( ($("input[name='ac_top']").val()== 'edit')  && ($("input[name='func_top']").val()== 'index')  ){
	$(".content").scrollTop(sessionStorage.contentTop);
}

if( ($("input[name='ac_top']").val()== 'edit')  && ($("input[name='func_top']").val()== 'index') || ($("input[name='ac_top']").val()== 'edit')  && ($("input[name='func_top']").val()== 'apply') ){

}else{
	sessionStorage.contentTop = 0;
}





	$.Set_Log = function(type,act,obj){   //申请  转让  归还  入库  维修
		var title = "",bottom="";
		var ten = parseInt($(".middle_bottom .log").length/10);   //方便10 位数查询
		if( act == 'apply' ){
			$(obj).html("<span style='color:#f60'>加载中...</span>");

			var width = $(".width").val();
			if( type == 2 ){   //   2   21  22  20
				bottom = '申请';
				title = "查看日志(申请)";
			}else if( type == 4 ){   //   4  41  42  40
				bottom = '转让';
				title = "查看日志(转让)";
			}else if( type == 6 ){   //归还还包括  归还给厂商     //6  61  60 7
				bottom = '归还';
				title = "查看日志(归还处理)";
			}else if( type == 1 ){
				bottom = '入库';
				title = "查看日志(管理员入库)";  //1   13
			}else if( type == 8 ){
				bottom = '维修';
				title = "查看日志(维修)";   //8  81
			}else if( type == 9 ){
				bottom = '借阅';
				title = "图书请求借阅";   //8  81
			}
			$(".layui-m-layermain h3").html(title);
			console.log(type);
			$(".log_type").val(type);   //type  为了加载更多

		}else if( act == 'more' ){
			type = $(".log_type").val();
			if( $(obj).html() != '加载更多' ){   //还没好  或者没了
				return false;
			}
		
			$(obj).html("<span style='color:#f60'>正在加载更多日志...</span>");

		}
			
		var query = new Object();
		query.ac = 'Index';
		query.func = 'ajax_about_my';
		query.type = type;
		query.act = act;
		query.width = width;
		query.ten = ten;
		$.ajax({
			data: query,
			type: "POST",
			dataType: "json",
			url: DTPath+'ajax.php',
			success: function(data){
				if( data.code==1 ){
					if( act == 'apply' ){
						$(".ajax_about_my .middle_bottom").html(data.html);
						$(".do_status_6 i").css({"color":"#5bc0de"});
						$(".do_status_6 i span").css({"color":"#5bc0de"});
						$(obj).html("<span style='color:#f60'>"+bottom+"</span>");

						
						if( data.count < 10 ){  //数量不足
							$(".more_log").html("<span style='color:#ccc'>没有更多了...</span>");
						}else{
							$(".more_log").html("加载更多");
						}
					}else{
						$(".ajax_about_my .middle_bottom").append(data.html);
						if( data.count < 10 ){  //数量不足
							$(".more_log").html("<span style='color:#ccc'>没有更多了...</span>");
						}else{
							$(".more_log").html("加载更多");
						}



					}
				}
			}
		});

	}






})