$(document).ready(function(){

if($('#password') && $('#account')){
	$("#login-hint").hide();
	$('#login-btn').on('click',function(event){
		admin_login();
	});

	$('#password').on('keydown',function(event){
	    if(event.which == 13){
	    	$('#login-btn').click();
	    }
	});

	$('#account').on('keydown',function(event){
	    if(event.which == 13){
	    	$('#login-btn').click();
	    }
	});
}

function admin_login(){
	$("#login-btn").attr('disabled', 'disabled');
    $("#login-hint").html('').fadeOut();
    
    var account = $('#account').val();
    var pwd = $('#password').val();
    
    var hint_html = '';
    if(account == '') {
        hint_html += (hint_html == '' ? '' : '<BR>');
        hint_html +=  '请输入账号！';
    }
    if(pwd == '') {
    	 hint_html += (hint_html == '' ? '' : '<BR>');
         hint_html += '请输入密码！';
    }
    
    if(hint_html != ''){
        $("#login-hint").html(hint_html).fadeIn();
        $("#login-btn").removeAttr('disabled');
        return 0;
    }
    
	$("#login-btn").html("登录中...");
    $.post(BASE_PATH + 'passport/doLogin', {
    	'account':account,
    	'password':pwd
        },
        function(result){
            if(result.code != 0) {
                $("#login-hint").html(result.msg + '(' + result.code + ')').fadeIn();
                $("#login-btn").removeAttr('disabled');
                $("#login-btn").html("登录");
            }else {
                $("#login-hint").html(result.msg).fadeIn();
                setTimeout(function(){
                    $("#admin-login-modal").modal('hide');
                    location.href = BASE_PATH;
                }, 500);
            }
        },
        'json'
    );
}

$(document).on('click', '#amdin-loginOut-btn', function(event){
	var txt = $(this).text();
	
	$("#confirm-admin-hint").html('').hide();
	$('#confirm-admin-modal').modal('show');
	$('#confirm-admin-body').html('是否'+txt+'!');
	
	$('#confirm-admin-btn').unbind("click");
	$('#confirm-admin-btn').on('click', {},function(event){
		$.post(BASE_PATH + 'passport/logout', {},
		        function(result){
		            if(result.code != 0) {
		                $("#confirm-admin-hint").html(result.msg + '(' + result.code + ')').fadeIn();
		            }else {
		            	$("#confirm-admin-hint").html(result.msg + ', 关闭...').fadeIn();
		            	setTimeout(function(){
		            		$('#confirm-admin-modal').modal('hide');
		            	},500);
		            	setTimeout(function(){
		            		location.href = BASE_PATH;
		            	},800);
		            }
		        },
		        'json'
		    );
	});
});








prettyPrint();
});
