$(document).ready(function(e) {
  
	$.Set_Area = function(obj){
       
		var query = new Object();
		var s = $(obj).attr('class');
		s = s.replace('form-control ','');
		var id = $(obj).val();
		
		query.ac = 'Extend';
		query.func = 'get_area';
		query.s = s;
		query.id = id;
		
		if(s == 'city'){
			query.pid = $(obj).parent().parent().find('select.province').val();
		}else if(s == 'area'){
			query.cid = $(obj).parent().parent().find('select.city').val();
		}else if(s == 'street'){
			query.aid = $(obj).parent().parent().find('select.area').val();
		}
		
		$.ajax({
			type: "POST",
			url: "ajax.php",
			data: query,
			dataType: "json",
			success: function(data){
				if(s == 'province'){
					if(data.html){
						$(obj).parent().parent().find('select.city').html(data.html).show();
					}
					$(obj).parent().parent().find('select.area').html('<option value="0">请选择</option>').hide();
					$(obj).parent().parent().find('select.street').html('<option value="0">请选择</option>').hide();
				}else if(s == 'city'){
					if(data.html){
						$(obj).parent().parent().find('select.area').html(data.html).show();
						$(obj).parent().parent().find('select.street').html('<option value="0">请选择</option>').hide();
					}
				}else if(s == 'area'){
					if(data.html){
						$(obj).parent().parent().find('select.street').html(data.html).show();
					}
				}
				return false;
			}
		});
	}
	
});
