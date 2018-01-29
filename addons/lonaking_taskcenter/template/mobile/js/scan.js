var scan = {
	init : function(){
		
	},
	event : function(){
		$('#bind-gift').on('click',function(){
			var btn = $(this);
			btn.attr('disabled','disabled');
			var url = $('html').attr('data-bind-gift-url');
			var post_data = {
				password :  $('input[name=password]').val(),
				openid : $('html').attr('data-openid')
			};
			if('' == post_data.password || '' == post_data.openid ){
				alert('密码不能为空');
				btn.removeAttr('disabled');
				return ;
			}
				
			$.post(url,post_data,function(result){
				var json = eval('(' + result + ')');
				if(json.status == 200){
					alert(json.message);
					$("#scan").show();
					$("#bind-info").remove();
				}else{
					alert(json.message);
					btn.removeAttr('disabled');
				}
			})
		});

		$("#input-check").on('click',function(){
			var url = $('html').attr('data-use-url');
			var postData = {
				consume_code :$('input[name=consume_code]').val(),
				openid : $('html').attr('data-openid')
			};
			$.post(url, postData,function(result){
				var json = eval('(' + result + ')');
				alert(json.message);
			})
		})
	},
	functions : {
		
	}
};
$(function(){
	scan.event();
})