$(document).ready(function(){
/**************************************************************************************/
/*************************** global settings start ************************************/
$(function(){
  $.scrollUp({/*animation: 'fade',activeOverlay: '#00FFFF', */scrollText: ''});
});

Messenger.options = {
    extraClasses: 'messenger-fixed messenger-on-bottom messenger-on-right',
    theme: 'flat'
};

$("a[data-toggle=popover]")
  .popover()
  .click(function(e) {
    e.preventDefault();
});

function csrf_empty(){
    location.href = BASE_PATH + '404.php';
}

/*************************** global setting end ***************************************/
/**************************************************************************************/

/**************************************************************************************/
/*************************** register/login/logout start ******************************/
function check_mail(mail_str){
    var reyx=/^([a-zA-Z0-9_\.-])+@([a-zA-Z0-9_\.-])+\.([a-zA-Z0-9_-])+/;
    if(! reyx.exec(mail_str)) return false;
    return true;
}

function do_encrypt( pwd ) {
      var public_key="00c10bebb447db4fa916e4bb3d3e3a05322a4023b78538c6a5676205bdd71f9d912938a27ac0bcd835c42bf50268f797e59ebdc95ffe70a49be3ac35fddbdaf7c52ed55996dbd5ca19b4076491a20c8dbc30383e55c5c6a7c15be938c9a50775918bcaa4ec687763034609ea910e80e0e9c8c33bd927e25e0cbf987d90941314f7";
      var public_length="10001";
      var rsa = new RSAKey();
      rsa.setPublic(public_key, public_length);
      var res = rsa.encrypt(pwd);
      if(res) {
          return res;
      }else {
          return '';
      }
}

function login(){
	$("#login").addClass("disabled");
    $("#login-hint").fadeOut();
    var csrf = $("#login-csrf").val();
    if(csrf == ''){
        $("#login").removeClass("disabled");
        csrf_empty();
    }
    var hint_html = '';
    var email = $("#login-email").val();
    if(email == '') {
        hint_html += (hint_html == '' ? '请填写邮箱！' : '<BR>请填写邮箱！');
    }else {
        var ret_check = check_mail(email);
        if(! ret_check){
            hint_html += (hint_html == '' ? '邮箱格式错误！' : '<BR>邮箱格式错误！');
        }
    }
    var pwd = $("#login-password").val();
    if(pwd == '') {
        hint_html += (hint_html == '' ? '请填写密码！' : '<BR>请填写密码！');
    }
    var pincode = $("#login-pincode").val();
    if(pincode == '') {
        hint_html += (hint_html == '' ? '请填写验证码！' : '<BR>请填写验证码！');
    }
    if(hint_html != ''){
        $("#login-hint").html(hint_html).fadeIn();
        $("#login").removeClass("disabled");
        return 0;
    }
    var remember_me = 1;
    if($("#login-remember").is(':checked')){
        var remember_me = 2;
    }
    //加密密码
    pwd = do_encrypt(pwd);
    pwd = hex2b64(pwd);
    $("#login").html("登录中...");
    $.post(BASE_PATH + 'user/login', {'login_token':csrf,
        'email': email,
        'pwd': pwd,
        'vcode': pincode,
        'remember_me':remember_me},
        function(result){
            if(result.code != 200){
                $("#login-hint").html(result.msg + '(' + result.code + ')').fadeIn();
                $("#login").removeClass("disabled");
                $("#login").html("登录");
            }else {
                $("#login-hint").html(result.msg + ', 进入...').fadeIn();
                setTimeout(function(){
                    $("#login-modal").modal('hide');
                    //update_head();
                    //update_main();
                    $("#login").removeClass("disabled");
                    $("#login").html("登录");
                    location.href = BASE_PATH;
                }, 1000);
            }
            $("#login-pincode-img").click();
        },
        'json');
}

//login
$(document).on('click', '#login', function(event){
    login();
});

$(document).on('keydown', '#login-pincode', function(event){
    if(event.which == 13){
       login();
    }
});
$(document).on('keydown', '#login-email', function(event){
    if(event.which == 13){
       login();
    }
});
$(document).on('keydown', '#login-password', function(event){
    if(event.which == 13){
       login();
    }
});

$(document).on('keydown', '#login-remember', function(event){
    if(event.which == 13){
       login();
    }
});

function register(){
	$("#register").attr('disabled', 'disabled');
    $("#login-hint").html('').fadeOut();
    var csrf = $("#register-csrf").val();
    if(csrf == ''){
        $("#register").removeAttr('disabled');
        csrf_empty();
    }
    
    var first_name = $("#register-first-name").val();
    var second_name = $("#register-second-name").val();
    var hint_html = '';
    if(first_name == '') {
        hint_html += '请填写姓！';
    }
    if(second_name == ''){
        hint_html += (hint_html == '' ? '请填写名！' : '<BR>请填写名！' );
    }
    
    var email = $("#register-email").val();
    if(email == '') {
        hint_html += (hint_html == '' ? '请填写邮箱！' : '<BR>请填写邮箱！');
    }else {
        var ret_check = check_mail(email);
        if(! ret_check){
            hint_html += (hint_html == '' ? '邮箱格式错误！' : '<BR>邮箱格式错误！');
        }
    }
    
    var pwd = $("#register-password").val();
    var pwd_check = $("#register-password-check").val();
    if(pwd == '') {
        hint_html += (hint_html == '' ? '请填写密码！' : '<BR>请填写密码！');
    }else if(pwd != pwd_check){
        hint_html += (hint_html == '' ? '两次密码不一致！' : '<BR>两次密码不一致！');
    }
    var pincode = $("#register-pincode").val();
    if(pincode == '') {
        hint_html += (hint_html == '' ? '请填写验证码！' : '<BR>请填写验证码！');
    }
    if(hint_html != ''){
        $("#register-hint").html(hint_html).fadeIn();
        $("#register").removeAttr('disabled');
        return 0;
    }
    //加密密码
    pwd = do_encrypt(pwd);
    pwd = hex2b64(pwd);
    //注册
    $("#register").html("注册中...");
    $.post(BASE_PATH + 'user/register', {'register_token':csrf, 
        'first_name':first_name, 
        'second_name':second_name,
        'email':email,
        'pwd':pwd,
        'vcode':pincode
        },
        function(result){
            if(result.code != 100) {
                $("#register-hint").html(result.msg + '(' + result.code + ')').fadeIn();
                $("#register").removeAttr('disabled');
                $("#register").html("注册");
            }else {
                $("#register-hint").html(result.msg + ', 关闭...').fadeIn();
                setTimeout(function(){
                    $("#register-modal").modal('hide');
                    $("#register").removeAttr('disabled');
                    $("#register").html("注册");
                    location.href = BASE_PATH;
                }, 1000);
            }
            $("#register-pincode-img").click();
        },
        'json'
    );
}
//register
$(document).on('click', "#register", function(){
    register();
});

$(document).on('keydown', '#register-first-name', function(event){
    if(event.which == 13){
       register();
    }
});
$(document).on('keydown', '#register-second-name', function(event){
    if(event.which == 13){
       register();
    }
});
$(document).on('keydown', '#register-email', function(event){
    if(event.which == 13){
       register();
    }
});
$(document).on('keydown', '#register-password', function(event){
    if(event.which == 13){
       register();
    }
});
$(document).on('keydown', '#register-password-check', function(event){
    if(event.which == 13){
       register();
    }
});
$(document).on('keydown', '#register-pincode', function(event){
    if(event.which == 13){
       register();
    }
});

//logout
$(document).on('click', '#logout', function(){
    $.post(BASE_PATH + 'user/logout', {'other_token' : $("#other-csrf").val()},function(result){
        if(result.code != 300){
            //TODO
        }else {
            location.href = BASE_PATH;
        }
        
    }, 'json');
});

$('#login-modal').on('show.bs.modal', function (e) {
      $("#login-pincode-img").click();
});

$('#register-modal').on('show.bs.modal', function (e) {
      $("#register-pincode-img").click();
});
/*************************** register/login/logout end *********************************/
/**************************************************************************************/

/**************************************************************************************/
/*************************** main page start ******************************************/
$(document).on('click', '#check-more-new', function(e){
    var id_str = $("#main_new > div").last().attr("id");
    var start_id = id_str.substr(4);
    start_id ++;
    $.post(BASE_PATH + 'article/new_more', {'other_token' : $("#other-csrf").val(), 'start_id':start_id},
            function(result){
        if(result.code != 0){
            Messenger().post('加载最新文章失败(' + result.code + '):' + result.msg);
        }else {
            if(result.data == ''){
                Messenger().post('没有更多..');
            }
            else {
                $("#main_new").append(result.data);
            }
        }
    }, 'json');
});

$(document).on('click', '#check-more-recommend', function(e){
    var id_str = $("#main_recommend > div").last().attr("id");
    var start_id = id_str.substr(10);
    start_id ++;
    $.post(BASE_PATH + 'article/recommend_more', {'other_token' : $("#other-csrf").val(), 'start_id':start_id},
            function(result){
        if(result.code != 0){
            Messenger().post('加载推荐文章失败(' + result.code + '):' + result.msg);
        }else {
            if(result.data == ''){
                Messenger().post('没有更多..');
            }
            else {
                $("#main_recommend").append(result.data);
            }
        }
    }, 'json');
});
/*************************** main page end ******************************************/
/**************************************************************************************/

/**************************************************************************************/
/*************************** search page start ****************************************/
var last_q_a = "";
var last_q_u = "";

var last_search_type = "article";

function search(type, start){
    var q = $("#search-query-string").val();
    if(q == ''){
        Messenger().post("请输入要搜索的内容");
        return;
    }
    $.post(BASE_PATH + 'search/search', {'other_token' : $("#other-csrf").val(), 'q' : q, 'type':type, 'start': start},
            function(result){
        if(result.code != 1100){
             Messenger().post('搜索失败(' + result.code + '):' + result.msg);
        }else{
            if(start == 1){
                if(type == 'article'){
                    $("#search-result-article-tab-real").html(result.data.html);
                    $("#search-result-panel").show();
                    if(result.data.more == 1) $("#check-more-sra").fadeIn();
                    else $("#check-more-sra").hide();
                }
                else if(type == 'user'){
                    $("#search-result-user-tab-real").html(result.data.html);
                    if(result.data.more == 1) $("#check-more-sru").fadeIn();
                    else $("#check-more-sru").hide();
                }
            }else {
                if(type == 'article'){
                    $("#search-result-article-tab-real").append(result.data.html);
                    if(result.data.more == 1) $("#check-more-sra").fadeIn();
                    else $("#check-more-sra").hide();
                }
                else if(type == 'user'){
                    $("#search-result-user-tab-real").append(result.data.html);
                    if(result.data.more == 1) $("#check-more-sru").fadeIn();
                    else $("#check-more-sru").hide();
                }
            }
        }
    }, 'json');
    if(type == 'user'){
        last_q_u = q;
    }else if(type == 'article'){
        last_q_a = q;
    }
}
$(document).on('click', '#button-search', function(){
    if(last_search_type == "article"){
        search('article', 1);
    }else {
        search('user', 1);
    }
        
});

$(document).on('keydown', '#search-query-string', function(event){
    if(event.which == 13){
        if(last_search_type == "article"){
            search('article', 1);
        }else {
            search('user', 1);
        }
    }
});

$('a[href="#search-result-user-tab"]').on('shown.bs.tab',function(e){
    if($("#search-result-user-tab-real").html() == false || last_q_u != $("#search-query-string").val()){
        search('user', 1);
    }
    last_search_type = "user";
});

$('a[href="#search-result-article-tab"]').on('shown.bs.tab',function(e){
    if($("#search-result-article-tab-real").html() == false || last_q_a != $("#search-query-string").val()){
        search('article', 1);
    }
    last_search_type = "article";
});

$(document).on('click', "#check-more-sra", function(event){
    $id_str = $(".search-result-item-article").last().attr("id");
    $start_id = $id_str.substr(4);
    $start_id ++;
    search('article', $start_id);
});

$(document).on('click', "#check-more-sru", function(event){
    $id_str = $(".search-result-item-user").last().attr("id");
    $start_id = $id_str.substr(4);
    $start_id ++;
    search('user', $start_id);
});
/***************************  search page end *****************************************/
/**************************************************************************************/

/**************************************************************************************/
/*************************** my article list page start ********************************/
function update_my_article_list(){
    $("#my-article-list").html('刷新中...');
    $.post(BASE_PATH + 'article/my_article_list', {'other_token' : $("#other-csrf").val()}, function(result){
        if(result.code != 0){
            $("#my-article-list").html('加载文章列表失败(' + result.code + '):' + result.msg);
        }else {
            $("#my-article-list").html(result.data);
        }
    }, 'json');
}

/***************************  my article list page end ********************************/
/**************************************************************************************/

/**************************************************************************************/
/*************************** notify page start ****************************************/

function update_notify(){
    $("#notify-list").html("刷新中...");
    $.post(BASE_PATH + 'notify/get_notify_html',{'other_token' : $("#other-csrf").val()},
            function(result){
        if(result.code != 0){
            $("#notify-list").html('加载失败(' + result.code + '):' + result.msg);
        }else {
            $("#notify-list").html(result.data);
        }
    }, 'json');
}

function mark_all_notify_read(){
    $.post(BASE_PATH + 'notify/mark_all_read',{'other_token' : $("#other-csrf").val()},
            function(result){
        if(result.code != 0){
			Messenger().post("错误（" + result.code + "）：" + result.msg);
        }else {
			update_notify();
        }
    }, 'json');
}

function mark_notify_read(id){
    $.post(BASE_PATH + 'notify/mark_read',{'other_token' : $("#other-csrf").val(), 'id':id},
            function(result){
        if(result.code != 0){
            Messenger().post("错误（" + result.code + "）：" + result.msg);
        }else {
            update_notify();
        }
    }, 'json');
}

$(document).on('click', "#clear-all-notify", function(event){
    mark_all_notify_read();
});

$(document).on('click', ".clear-notify", function(event){
    var id_str = $(this).attr("id");
    var id = id_str.substr(12);
	mark_notify_read(id);
});

/***************************  notify page end *****************************************/
/**************************************************************************************/

/**************************************************************************************/
/*************************** independent search page start *****************************/
function search_article(start){
    var q = $("#search-query-string-article").val();
    if(q == ''){
        Messenger().post("请输入要搜索的内容");
        return;
    }
    $.post(BASE_PATH + 'search/search_article', {'q' : q, 'start': start},
            function(result){
        if(result.code != 1100){
             Messenger().post('搜索失败(' + result.code + '):' + result.msg);
        }else{
            if(start == 1){
                $("#search-result").html(result.data.html);
                $("#search-result-panel-article").show();
               
            }else {
                $("#search-result").append(result.data.html);
            }
			if(result.data.more == 1) $("#check-more-sr").fadeIn();
            else $("#check-more-sr").hide();
        }
    }, 'json');
}

$(document).on('click', '#button-search-article', function(){
    search_article(1);
});

$(document).on('keydown', '#search-query-string-article', function(event){
    if(event.which == 13){
        search_article(1);
    }
});
$(document).on('click', "#check-more-sr", function(event){
    $id_str = $(".search-result-item-article-a").last().attr("id");
    $start_id = $id_str.substr(3);
    $start_id ++;
    search_article($start_id);
});
/***************************  independent search page end *****************************/
/**************************************************************************************/

/**************************************************************************************/
/*************************** independent tag page start*************************************/
function search_article_by_tag(start){
    var q = $("#tag_name_input").val();
    if(q == ''){
        Messenger().post("请输入要搜索的内容");
        return;
    }
    $.post(BASE_PATH + 'search/search_article_by_tag', {'q' : q, 'start': start},
            function(result){
        if(result.code != 1100){
             Messenger().post('搜索失败(' + result.code + '):' + result.msg);
        }else{
            if(start == 1){
                $("#search-result-tag").html(result.data.html);
                $("#search-result-panel-tag").show();
               
            }else {
                $("#search-result-tag").append(result.data.html);
            }
            if(result.data.more == 1) $("#check-more-srt").fadeIn();
            else $("#check-more-srt").hide();
        }
    }, 'json');
}

$(document).on('click', "#check-more-srt", function(event){
    $id_str = $(".search-result-item-article-t").last().attr("id");
    $start_id = $id_str.substr(4);
    $start_id ++;
    search_article_by_tag($start_id);
});
/***************************  independent tag page end ********************************/
/**************************************************************************************/


/**************************************************************************************/
/***************************  home start **********************************************/
$(document).on('click', '.button-follow', function(){
	var id_str = $(this).attr("id");
	var id = id_str.substr(14);
	$.post(BASE_PATH + 'follow/follow',{'other_token' : $("#other-csrf").val(), 'user_id' : id},
			function(result){
		if(result.code != 1000){
			if(result.code == 3) {
                $("#register-modal").modal();
            }else Messenger().post('操作失败(' + result.code + '):' + result.msg);
		}else {
			if(result.data == 'follow'){
			    Messenger().post("订阅成功!");
				$("#" + id_str).addClass('btn-default');
				$("#" + id_str).removeClass('btn-success');
				$("#" + id_str).html('已订阅');
			}else {
			    Messenger().post("取消订阅成功!");
				$("#" + id_str).removeClass('btn-default');
				$("#" + id_str).addClass('btn-success');
				$("#" + id_str).html('订阅');
			}
		}
	},'json');
	setTimeout(function(){
		$("#follow-hint-" + id).fadeOut();
	}, 2000);
});


$(document).on('click', '.home-title', function(){
	var id_str = $(this).attr("id");
	var id = id_str.substr(11);
	
	$.post(BASE_PATH + 'follow/get_follow_cnt', {'other_token' : $("#other-csrf").val(), 'user_id' : id},
			function(result){
		if(result.code != 1000){
			Messenger().post("错误（" + result.code + "）：" + result.msg);
		}else{
			Messenger().post("订阅指数：" + result.data);
		}
	}, 'json');
});

$(document).on('click', '#check-more-my-article', function(e){
    var id_str = $("#home_article > div").last().attr("id");
    var start_id = id_str.substr(3);
    start_id ++;
    $.post(BASE_PATH + 'u/get_more_article', {'user_id':$("#user_id").html(), 'other_token' : $("#other-csrf").val(), 'start_id':start_id},
            function(result){
        if(result.code != 900){
            Messenger().post('加载最新文章失败(' + result.code + '):' + result.msg);
        }else {
            if(result.data == ''){
                Messenger().post('没有更多..');
            }
            else {
                $("#home_article").append(result.data);
            }
        }
    }, 'json');
});
/***************************  home end   **********************************************/
/**************************************************************************************/

/**************************************************************************************/
/***************************  show user info start*************************************/
function update_user_info_html(){
    $.post(BASE_PATH + 'user/user_info_html', {'other_token' : $("#other-csrf").val()}, function(result){
        if(result.code != 0){
            $("#user-info-tab").html('<BR><div class="alert alert-danger" role="alert"><p class="text-center">(' + result.code + '):' + result.msg + '</p></div>');
        }else {
            $("#user-info-tab").html(result.data);
        }
    }, 'json');
}

$('a[href="#user-info-tab"]').on('shown.bs.tab',function(e){
    update_user_info_html();
});

$('#user-modal').on('show.bs.modal',function(e){
    var tab_href = $(".user-center-tabs > li.active > a").attr('href');
	if(tab_href == '#user-info-tab'){
		update_user_info_html();
	}else if(tab_href == '#user-info-alter-tab'){
		update_alter_user_info_html();
	}else if(tab_href == '#user-info-wp-tab'){
		update_wp_setting_html();
	}
});
/***************************  show user info end **************************************/
/**************************************************************************************/

/**************************************************************************************/
/***************************  upload avatar start*************************************/
$(document).on('click', '#upload-avatar-browser', function(event){
	$("#upload-avatar-file").click();
});

var jcrop_api;

function init_jcrop(){
    	$('#crop-avatar-img').Jcrop({aspectRatio:1, boxWidth: 550, boxHeight: 800, onChange: jcrop_change}, function(){
			jcrop_api = this;
			var widget_size = jcrop_api.getBounds();
			console.log('w:' + widget_size[0] + ' h:' + widget_size[1]);
			if(widget_size[0] > widget_size[1]){
				jcrop_api.setSelect([ (widget_size[0] - widget_size[1]) / 2, 0, (widget_size[0] + widget_size[1]) / 2, widget_size[1] ]);
			}else {
				jcrop_api.setSelect([0, (widget_size[1] - widget_size[0]) / 2, widget_size[0], (widget_size[1] + widget_size[0]) / 2] );
			}
		});
}

$(document).on('hidden.bs.modal', '#upload-avatar-modal', function(event){
	jcrop_api.destroy();
	$('#user-modal').modal();
});

function jcrop_change(c){
	$("#upload-avatar-x").val(Math.round(c.x));
	$("#upload-avatar-y").val(Math.round(c.y));
	$("#upload-avatar-w").val(Math.round(c.w));
	$("#upload-avatar-h").val(Math.round(c.h));
}

$(document).on('click', '#set-avatar', function(){
	$("#set-avatar").attr("disabled", "disabled");
	$("#set-avatar").html("更新头像中...");
	
	$.post(BASE_PATH + 'user/set_avatar', {'other_token' : $("#other-csrf").val(), 'x':$("#upload-avatar-x").val(),
	           'y':$("#upload-avatar-y").val(),'w':$("#upload-avatar-w").val(),'h':$("#upload-avatar-h").val(),}, function(result){
        if(result.code != 1800){
			Messenger().post('设置头像失败(' + result.code + '):' + result.msg);
        }else {
			Messenger().post('设置头像成 功!');
			$('#upload-avatar-modal').modal('hide');
        }
		$("#set-avatar").removeAttr("disabled");
        $("#set-avatar").html("设置选中区域为新头像");
    }, 'json');
});

$(document).on('change', '#upload-avatar-file', function(){
	
    $('#upload-avatar-browser').html('上传中...');
    $('#upload-avatar-browser').addClass("disabled");
    $.ajaxFileUpload
    (
        {
            url: BASE_PATH + 'user/upload_avatar',
            secureuri:false,
            fileElementId:'upload-avatar-file',
            dataType: 'json',
            data:{'elementId':'upload-avatar-file', 'other_token' : $("#other-csrf").val()},
            success: function (data, status)
            {
                if(data.code != 1800)
                {
                    $('#upload-avatar-hint').html('上传失败：' + data.msg + '(' + data.code + ')');
                }else {
                    $('#upload-avatar-hint').html('上传成功');
					$('#user-modal').modal('hide');
					$('#upload-avatar-modal').modal();
					$('#crop-avatar-img').attr('src', BASE_PATH + 'image/avatar/' + data.data.id + '?xl=' + data.data.time);
					init_jcrop();
                }
                $('#upload-avatar-browser').html('上传头像');
                $('#upload-avatar-browser').removeClass("disabled");
            },
            error: function (data, status, e)
            {
                alert(e);
            }
        }
    );
    
    return false;
});



/***************************  upload avatar end **************************************/
/**************************************************************************************/




/**************************************************************************************/
/***************************  alter user info start ************************************/
function update_alter_user_info_html(){
    $.post(BASE_PATH + 'user/user_alter_info_html', {'other_token' : $("#other-csrf").val()}, function(result){
        if(result.code != 0){
            $("#user-info-alter-tab").html('<BR><div class="alert alert-danger" role="alert"><p class="text-center">(' + result.code + '):' + result.msg + '</p></div>');
        }else {
            $("#user-info-alter-tab").html(result.data);
        }
    }, 'json');
}

$(document).on('click', '#alter-info-button', function(event){
	$('#alter-info-button').addClass("disabled");
	$("#alter-hint").html('').fadeOut();
	var csrf = $("#other-csrf").val();
	if(csrf == ''){
		$('#alter-info-button').removeClass("disabled");
		csrf_empty();
	}
	
	var first_name = $("#alter-first-name").val();
	var second_name = $("#alter-second-name").val();
	var hint_html = '';
	if(first_name == '') {
		hint_html += '请填写姓！';
	}
	if(second_name == ''){
		hint_html += (hint_html == '' ? '请填写名！' : '<BR>请填写名！' );
	}
	
	var email = $("#alter-email").val();
	if(email == '') {
		hint_html += (hint_html == '' ? '请填写邮箱！' : '<BR>请填写邮箱！');
	}else {
		var ret_check = check_mail(email);
		if(! ret_check){
			hint_html += (hint_html == '' ? '邮箱格式错误！' : '<BR>邮箱格式错误！');
		}
	}
	
	var info = $("#alter-info").val();
	if(info == '') {
		hint_html += (hint_html == '' ? '请填写简介！' : '<BR>请填写简介！');
	}
	
	var pincode = $("#alter-info-pincode").val();
	if(pincode == '') {
		hint_html += (hint_html == '' ? '请填写验证码！' : '<BR>请填写验证码！');
	}
	if(hint_html != ''){
		$("#alter-hint").html(hint_html).fadeIn();
		$('#alter-info-button').removeClass("disabled");
		return 0;
	}
	
	//修改用户信息
	$('#alter-info-button').html("修改中...");
	$.post(BASE_PATH + 'user/update_user_info', {'other_token':csrf, 
		'first_name':first_name, 
		'second_name':second_name,
		'email':email,
		'pincode':pincode,
		'info':info
		},
		function(result){
			if(result.code != 1200) {
				$("#alter-hint").html(result.msg + '(' + result.code + ')').fadeIn();
				$('#alter-info-button').removeClass("disabled");
				$('#alter-info-button').html("提交");
			}else {
				$("#alter-hint").html(result.msg + ', 关闭...').fadeIn();
				setTimeout(function(){
					$("#user-modal").modal('hide');
					$('#alter-info-button').removeClass("disabled");
					$('#alter-info-button').html("提交");
					$("#alter-hint").html('').fadeOut();
					location.href = BASE_PATH;
				}, 1000);
			}
			$("#alter-info-pincode-img").click();
		},
		'json'
	);

});
$('a[href="#user-info-alter-tab"]').on('shown.bs.tab',function(e){
    update_alter_user_info_html();
});
/***************************  alter user info end **************************************/
/**************************************************************************************/

/**************************************************************************************/
/***************************  alter password start ************************************/
$(document).on('click', '#alter-pwd-button', function(event){
	$("#alter-pwd-button").addClass("disabled");
	$("#alter-pwd-hint").html('').fadeOut();
	var csrf = $("#other-csrf").val();
	if(csrf == ''){
		$("#alter-pwd-button").removeClass("disabled");
		csrf_empty();
	}
	
	var cur_pwd = $("#alter-pwd-current-pwd").val();
	var pwd = $("#alter-pwd-new-pwd").val();
	var pwd_check = $("#alter-pwd-new-pwd-check").val();
	hint_html = '';
	if(cur_pwd == '') {
		hint_html += (hint_html == '' ? '请填写当前密码！' : '<BR>请填写当前密码！');
	}
	if(pwd == '') {
		hint_html += (hint_html == '' ? '请填写新密码！' : '<BR>请填写新密码！');
	}else if(pwd != pwd_check){
		hint_html += (hint_html == '' ? '两次新密码不一致！' : '<BR>两次新密码不一致！');
	}
	
	if(cur_pwd == pwd){
		hint_html += (hint_html == '' ? '旧密码和新密码一样！' : '<BR>旧密码和新密码一样！');
	}
	
	var pincode = $("#alter-pwd-pincode").val();
	if(pincode == '') {
		hint_html += (hint_html == '' ? '请填写验证码！' : '<BR>请填写验证码！');
	}
	
	if(hint_html != ''){
		$("#alter-pwd-hint").html(hint_html).fadeIn();
		$("#alter-pwd-button").removeClass("disabled");
		return 0;
	}
	//加密密码
	pwd = do_encrypt(pwd);
	pwd = hex2b64(pwd);
	cur_pwd = do_encrypt(cur_pwd);
	cur_pwd = hex2b64(cur_pwd);
	//修改用户密码
	$("#alter-pwd-button").html("修改中...");
	$.post(BASE_PATH + 'user/update_pwd', {'other_token':csrf, 
		'cur_pwd':cur_pwd, 
		'new_pwd':pwd,
		'pincode':pincode,
		},
		function(result){
			if(result.code != 1300) {
				$("#alter-pwd-hint").html(result.msg + '(' + result.code + ')').fadeIn();
				$("#alter-pwd-button").removeClass("disabled");
				$("#alter-pwd-button").html("提交");
			}else {
				$("#alter-pwd-hint").html(result.msg + ', 关闭...').fadeIn();
				setTimeout(function(){
					$("#user-modal").modal('hide');
					$("#alter-pwd-button").removeClass("disabled");
					$("#alter-pwd-button").html("提交");
					$("#alter-pwd-hint").html('').fadeOut();
					location.href = BASE_PATH;
				}, 1000);
			}
			$("#alter-pwd-pincode-img").click();
		},
		'json'
	);
});
/***************************  alter password end **************************************/
/**************************************************************************************/

/**************************************************************************************/
/***************************  wp setting start ****************************************/
function update_wp_setting_html(){
    $.post(BASE_PATH + 'wp/setting_html', {'other_token' : $("#other-csrf").val()}, function(result){
        if(result.code != 1600){
            $("#user-info-wp-tab").html('<BR><div class="alert alert-danger" role="alert"><p class="text-center">(' + result.code + '):' + result.msg + '</p></div>');
        }else {
            $("#user-info-wp-tab").html(result.data);
        }
    }, 'json');
}

function enable_sync(){
    $("#wp-setting-hint").fadeOut();
    $.post(BASE_PATH + 'wp/enable_sync', {'other_token' : $("#other-csrf").val()}, function(result){
        if(result.code != 1600){
            $("#wp-setting-hint").html('(' + result.code + '):' + result.msg ).fadeIn();
            $("#wp-switch").removeAttr("checked");
        }else {
            $("#wp-setting-hint").html('已成功开启wordpress同步功能！' ).fadeIn();
            $("#wp-api-key").html(result.data.wp_code);
		    $("#wp-refresh-key").fadeIn();
        }
    }, 'json');
}

function disable_sync(){
    $("#wp-setting-hint").fadeOut();
    $.post(BASE_PATH + 'wp/disable_sync', {'other_token' : $("#other-csrf").val()}, function(result){
        if(result.code != 1600){
            $("#wp-setting-hint").html('(' + result.code + '):' + result.msg ).fadeIn();
            $("#wp-switch").attr("checked");
        }else {
            $("#wp-setting-hint").html('已关闭wordpress同步功能！' ).fadeIn();
        }
    }, 'json');
}

function refresh_key(){
    $("#wp-setting-hint").fadeOut();
    $.post(BASE_PATH + 'wp/update_wp_code', {'other_token' : $("#other-csrf").val()}, function(result){
        if(result.code != 1600){
            if(result.code == 1602){
                $("#wp-setting-hint").html('请先打开开关！').fadeIn();
            }else{
                $("#wp-setting-hint").html('(' + result.code + '):' + result.msg ).fadeIn();
            }
        }else {
            $("#wp-setting-hint").html('更新api key成功！' ).fadeIn();
            $("#wp-api-key").html(result.data.wp_code);
        }
    }, 'json');
};

$(document).on('switchChange.bootstrapSwitch', '#wp-switch', function(event, state){
    if(state){
        enable_sync();
    }else {
        disable_sync();
    }
});

$(document).on('click', '#wp-refresh-key', function(){
    refresh_key();
});

$(document).on('init.bootstrapSwitch', '#wp-switch', function(){
    
});
$('a[href="#user-info-wp-tab"]').on('shown.bs.tab',function(e){
    update_wp_setting_html();
});
/***************************  wp setting end **************************************/
/**************************************************************************************/


/**************************************************************************************/
/***************************  new article start ****************************************/
$('#new-article-browser').click(function(){
    $('#new_article_file').click();
});

function upload_article_new(){
	$('#new-article-hint').removeClass('alert-warning');
	$('#new-article-hint').removeClass('alert-success');
	$('#new-article-hint').addClass('alert-info');
    $('#new-article-hint').html('上传中...').fadeIn();
    $.ajaxFileUpload
    (
        {
            url: BASE_PATH + 'article/upload',
            secureuri:false,
            fileElementId:'new_article_file',
            dataType: 'json',
            data:{'elementId':'new_article_file', 'other_token' : $("#other-csrf").val()},
            success: function (data, status)
            {
                if(data.code != 400)
                {
					$('#new-article-hint').removeClass('alert-success');
					$('#new-article-hint').removeClass('alert-info');
					$('#new-article-hint').addClass('alert-warning');
                    $('#new-article-hint').html('上传失败：' + data.msg + '(' + data.code + ')').fadeIn();
                    $("#new-article-preview").addClass('disabled');
                    $("#new-article-publish").attr('disabled', 'disabled');
                }else {
					$('#new-article-hint').removeClass('alert-warning');
					$('#new-article-hint').removeClass('alert-info');
                    $('#new-article-hint').addClass('alert-success');
                    $('#new-article-hint').html('上传成功!<BR>标题:' + data.data['title'] + '<BR>标签:' + (data.data['tags']).join(',')).fadeIn();
                    $("#new-article-preview").removeClass('disabled');
                    $("#new-article-publish").removeAttr('disabled');
                }
            },
            error: function (data, status, e)
            {
                alert(e);
            }
        }
    );
    return false;
}

$(document).on('change', '#new_article_file', function(){
    upload_article_new();
});

$(document).on('hidden.bs.modal', '#write-article-modal', function(event){
	$('#new-article-hint').removeClass('alert-warning');
    $('#new-article-hint').removeClass('alert-success');
    $('#new-article-hint').addClass('alert-info');
    $('#new-article-hint').html('请点击此按钮选择Markdown的zip包。').show();
});

$(document).on('click', '#new-article-publish', function(){
	$("#new-article-browser").addClass("disabled");
    $("#new-article-publish").addClass("disabled");
    $("#new-article-preview").addClass("disabled");
    $("#new-article-publish").html("发布中...");
    $('#new-article-hint').fadeOut();
    $.post(BASE_PATH + 'article/publish',{'other_token' : $("#other-csrf").val()},
            function(result){
                if(result.code != 500){
                    $('#new-article-hint').html('发布失败：' + result.msg + '(' + result.code + ')').fadeIn();
                    $("#new-article-publish").removeClass("disabled");
                    $("#new-article-preview").removeClass("disabled");
                    $("#new-article-publish").html("发布");
                }else {
                    //$('#new-article-hint').html('发布成功，跳转到文章页...').fadeIn();
                    $('#new-article-hint').html('发布成功！').fadeIn();
                    setTimeout(function(){
                        $("#write-article-modal").modal('hide');
                        update_my_article_list();
                        $("#new-article-publish").removeClass("disabled");
                        $("#new-article-preview").removeClass("disabled");
                        $("#new-article-publish").html("发布");
                    }, 1000);
                }
				$("#new-article-browser").removeClass("disabled");
    },'json');
});

$(document).on('click', "#preview-publish-top, #preview-publish-bottom", function(){
	$("#preview-publish-top").addClass('disabled');
	$("#preview-publish-bottom").addClass('disabled');
	$("#preview-cancel-top").addClass('disabled');
    $("#preview-cancel-bottom").addClass('disabled');
	var article_id = $('#preview-article-id').html();
	if (article_id == '0') {
		$("#preview-hint-top, #preview-hint-bottom").html("发布中...");
		$.post(BASE_PATH + 'article/publish', {
			'other_token': $("#other-csrf").val()
		}, function(result){
			if (result.code != 500) {
				$("#preview-hint-top, #preview-hint-bottom").html('发布失败：' + result.msg + '(' + result.code + ')').fadeIn();
				$("#preview-publish-top").removeClass("disabled");
				$("#preview-publish-bottom").removeClass("disabled");
				$("#preview-cancel-top").removeClass("disabled");
				$("#preview-cancel-bottom").removeClass("disabled");
			}
			else {
				$("#preview-hint-top, #preview-hint-bottom").html('发布成功，跳转到到文章列表页...');
				setTimeout(function(){
					location.href = BASE_PATH + 'article';
				}, 1000);
			}
		}, 'json');
	}else {
		$("#preview-hint-top, #preview-hint-bottom").html("更新中...");
        $.post(BASE_PATH + 'article/publish_update', {
            'other_token': $("#other-csrf").val(), 'article_id':article_id
        }, function(result){
            if (result.code != 500) {
                $("#preview-hint-top, #preview-hint-bottom").html('更新失败：' + result.msg + '(' + result.code + ')').fadeIn();
                $("#preview-publish-top").removeClass("disabled");
                $("#preview-publish-bottom").removeClass("disabled");
                $("#preview-cancel-top").removeClass("disabled");
                $("#preview-cancel-bottom").removeClass("disabled");
            }
            else {
                $("#preview-hint-top, #preview-hint-bottom").html('更新成功，跳转到到文章列表页...');
                setTimeout(function(){
                    location.href = BASE_PATH + 'article';
                }, 1000);
            }
        }, 'json');
	}
});

$(document).on('click', "#preview-cancel-top, #preview-cancel-bottom", function(){
	location.href = BASE_PATH + 'article';
});
/***************************  new article end **************************************/
/**************************************************************************************/

/**************************************************************************************/
/***************************  view article start ****************************************/
/*$(document).on('click', '.view-full-article', function(e){
    var href = $(this).attr("href");
    var nonce = href.substr(1, 4);
    var id = href.substr(19);
    $.post(BASE_PATH + 'article/view_full',{'other_token' : $("#other-csrf").val(), 'article_id' : id},
            function(result){
        if(result.code != 0){
            $("#" + nonce + "-article-body-" + id).append('<p>获取全文失败：' + result.msg + '(' + result.code + ')</p>');
        }else {
            $("#" + nonce + "-article-body-" + id).html(result.data);
        }
    },'json');
    e.preventDefault();
});
*/
$(document).on('click', '.button-keep', function(){
    var id_str = $(this).attr("id");
    var id = id_str.substr(12);
    $.post(BASE_PATH + 'keep/keep',{'other_token' : $("#other-csrf").val(), 'article_id' : id},
            function(result){
        if(result.code != 800){
			if(result.code == 3) {
				$("#register-modal").modal();
			}else Messenger().post('操作失败(' + result.code + '):' + result.msg);
        }else {
            button_keep_clicked = 2;
            if(result.data == 'keep'){
                Messenger().post("收藏成功!");
                $("#" + id_str).addClass('btn-default');
                $("#" + id_str).removeClass('btn-success');
                $("#" + id_str).html('已收藏');
            }else {
                Messenger().post("取消收藏成功!");
                $("#" + id_str).removeClass('btn-default');
                $("#" + id_str).addClass('btn-success');
                $("#" + id_str).html('收藏');
            }
        }
    },'json');
    setTimeout(function(){
        $("#keep-hint-" + id).fadeOut();
    }, 2000);
});

$(document).on('click', '.article-title', function(){
    var id_str = $(this).attr("id");
    var id = id_str.substr(19);
    $.post(BASE_PATH + 'keep/get_keep_cnt', {'other_token' : $("#other-csrf").val(), 'article_id' : id},
            function(result){
        if(result.code != 0){
            Messenger().post("错误（" + result.code + "）:" + result.msg);
        }else{
            Messenger().post("收藏指数：" + result.data);
        }
    }, 'json');
});
/***************************  view article end **************************************/
/**************************************************************************************/

/**************************************************************************************/
/***************************  update article start ****************************************/

$('#update-article-browser').click(function(){
    $('#update_article_file').click();
});

$('#update-article-modal').on('show.bs.modal', function (e) {
	var id_str = $(e.relatedTarget).attr("id");
	var article_id = id_str.substr(18);
	var article_title = $(e.relatedTarget).parent().prev().prev().html();
	$("#update-article-title").html(article_title);
	$("#update-article-id").html(article_id);
});

function upload_article_update(){
	$('#update-article-hint').removeClass('alert-warning');
    $('#update-article-hint').removeClass('alert-success');
    $('#update-article-hint').addClass('alert-info');
    $('#update-article-hint').html('上传中...').fadeIn();
	$.ajaxFileUpload
	(
		{
			url: BASE_PATH + 'article/upload_update',
			secureuri:false,
			fileElementId:'update_article_file',
			dataType: 'json',
			data:{'elementId':'update_article_file', 'other_token' : $("#other-csrf").val(), 'article_id': $("#update-article-id").html()},
			success: function (data, status)
			{
				if(data.code != 400)
				{
					$('#update-article-hint').removeClass('alert-info');
                    $('#update-article-hint').removeClass('alert-success');
                    $('#update-article-hint').addClass('alert-warning');
					$('#update-article-hint').html('上传失败：' + data.msg + '(' + data.code + ')').fadeIn();
					$("#update-article-preview").addClass('disabled');
					$("#update-article-publish").attr('disabled', 'disabled');
				}else {
					$('#update-article-hint').removeClass('alert-info');
                    $('#update-article-hint').removeClass('alert-warning');
                    $('#update-article-hint').addClass('alert-success');
					$('#update-article-hint').html('上传成功!<BR>标题:' + data.data['title'] + '<BR>标签:' + (data.data['tags']).join(',')).fadeIn();
					$("#update-article-preview").removeClass('disabled');
					$("#update-article-publish").removeAttr('disabled');
				}
			},
			error: function (data, status, e)
			{
				alert(e);
			}
		}
	);
	return false;
}

$(document).on('hidden.bs.modal', '#update-article-modal', function(event){
    $('#update-article-hint').removeClass('alert-warning');
    $('#update-article-hint').removeClass('alert-success');
    $('#update-article-hint').addClass('alert-info');
    $('#update-article-hint').html('请点击此按钮选择Markdown的zip包。').show();
});

$(document).on('change', '#update_article_file', function(){
    upload_article_update();
});


$("#update-article-publish").click(function(){
	$("#update-article-browser").addClass("disabled");
	$("#update-article-publish").addClass("disabled");
	$("#update-article-preview").addClass("disabled");
	$("#update-article-publish").html("更新中...");
	$('#update-article-hint').fadeOut();
	$.post(BASE_PATH + 'article/publish_update',{'other_token' : $("#other-csrf").val(), 'article_id':$("#update-article-id").html()},
			function(result){
				if(result.code != 500){
					$('#update-article-hint').html('更新失败：' + result.msg + '(' + result.code + ')').fadeIn();
					$("#update-article-publish").removeClass("disabled");
					$("#update-article-preview").removeClass("disabled");
					$("#update-article-publish").html("提交更新");
				}else {
					$('#update-article-hint').html('更新成功！').fadeIn();
					setTimeout(function(){
						$("#update-article-modal").modal('hide');
						update_my_article_list();
						$("#update-article-publish").removeClass("disabled");
						$("#update-article-preview").removeClass("disabled");
						$("#update-article-publish").html("提交更新");
					}, 1000);
				}
				$("#update-article-browser").removeClass("disabled");
	},'json');
});


/***************************  update article end **************************************/
/**************************************************************************************/

/**************************************************************************************/
/***************************  delete article start ****************************************/
$('#delete-article-modal').on('show.bs.modal', function (e) {
    var id_str = $(e.relatedTarget).attr("id");
    var article_id = id_str.substr(18);
    var article_title = $(e.relatedTarget).parent().prev().prev().html();
    $("#delete-article-title").html(article_title);
    $("#delete-article-id").html(article_id);
});

$("#delete-article-confirm").click(function(){
	$("#delete-article-cancel").addClass("disabled");
	$("#delete-article-confirm").addClass("disabled");
    $('#delete-article-hint').fadeOut();
	$("#delete-article-confirm").html("删除中...");
    $.post(BASE_PATH + 'article/delete',{'other_token' : $("#other-csrf").val(), 'article_id':$("#delete-article-id").html()},
            function(result){
                if(result.code != 1400){
                    $("#delete-article-hint").html('删除失败：' + result.msg + '(' + result.code + ')').fadeIn();
					$("#delete-article-cancel").removeClass("disabled");
                    $("#delete-article-confirm").removeClass("disabled");
					$("#delete-article-confirm").html("确定");
                }else {
                    $("#delete-article-hint").html('删除成功...').fadeIn();
                    setTimeout(function(){
						$("#delete-article-cancel").removeClass("disabled");
                        $("#delete-article-confirm").removeClass("disabled");
						$("#delete-article-confirm").html("确定");
                        $("#delete-article-modal").modal('hide');
                        update_my_article_list();
                        $('#delete-article-hint').fadeOut();
                    }, 1000);
                }
    },'json');
});
/***************************  delete article end **************************************/
/**************************************************************************************/

/**************************************************************************************/
/***************************  verify email start ****************************************/
$(document).on('click', '#send_verify_email', function(e){
	$.post(BASE_PATH + 'user/send_verify_email', {'other_token' : $("#other-csrf").val()}, function(result){
		if(result.code != 1500){
			$("#try_send_mail_later_hint").html('发生错误(' + result.code + '):' + result.msg + ' 稍后重试！').show();
		}else {
			$("#try_send_mail_later_hint").html('发送验证邮件成功，如果没有收到，稍后重试！').show();
		}
		$("#send_verify_email").hide();
		setTimeout(function(){
			$("#send_verify_email").show();
			$("#try_send_mail_later_hint").hide();
		}, 60000);
	}, 'json');
});

$(document).on('click', '#send_verify_email_1', function(e){
	$.post(BASE_PATH + 'user/send_verify_email', {'other_token' : $("#other-csrf").val()}, function(result){
		if(result.code != 1500){
			$("#try_send_mail_later_hint_1").html('发生错误(' + result.code + '):' + result.msg + ' 稍后重试！').show();
		}else {
			$("#try_send_mail_later_hint_1").html('发送验证邮件成功，如果没有收到，稍后重试！').show();
		}
		$("#send_verify_email_1").hide();
		setTimeout(function(){
			$("#send_verify_email_1").show();
			$("#try_send_mail_later_hint_1").hide();
		}, 60000);
	}, 'json');
});
/***************************  verify email end **************************************/
/**************************************************************************************/

/**************************************************************************************/
/***************************  find password start ****************************************/

function find_password(){
    $("#find-password-button").addClass("disabled");
    $("#find-password-hint").html('').hide();
    var csrf = $("#other-csrf").val();
    if(csrf == ''){
        $("#find-password-button").removeClass("disabled");
        csrf_empty();
    }
    var hint_html = '';
    var email = $("#find-password-email").val();
    if(email == '') {
        hint_html += (hint_html == '' ? '请填写邮箱！' : '<BR>请填写邮箱！');
    }else {
        var ret_check = check_mail(email);
        if(! ret_check){
            hint_html += (hint_html == '' ? '邮箱格式错误！' : '<BR>邮箱格式错误！');
        }
    }
    var pincode = $("#find-password-pincode").val();
    if(pincode == ''){
        hint_html += (hint_html == '' ? '请填写验证码！' : '<BR>请填写验证码！');
    }
    if(hint_html != ''){
        $("#find-password-hint").html(hint_html).fadeIn();
        $("#find-password-button").removeClass("disabled");
        return 0;
    }
    $("#find-password-button").html("处理中...");
    $.post(BASE_PATH + 'user/send_find_password_email', {'other_token': $("#other-csrf").val(), 
       'email':email, 'pincode': pincode}, function(result){
           if(result.code != 1700){
              $("#find-password-hint").html('发生错误(' + result.code + '):' + result.msg + ' 稍后重试！').fadeIn();
              $("#find-password-button").removeClass("disabled");
              $("#find-password-pincode-img").click();
           }else {
              $("#find-password-form").hide();
              $("#find-password-hint").removeClass("alert-danger");
              $("#find-password-hint").addClass("alert-success");
              $("#find-password-hint").html('<p><span class="glyphicon glyphicon-ok"></span><span>&nbsp;已向' + email + '发送密码重设邮件，请访问邮件中给出的网页链接地址，根据页面提示完成密码重设。</span></p>').fadeIn();
              $(".panel-footer-find-password").hide();
           }
           $("#find-password-button").html("提交");
       }, 'json');
}

$(document).on('click', '#find-password-button', function(e){
	find_password();
});

$(document).on('keydown', '#find-password-email', function(event){
    if(event.which == 13){
       find_password();
    }
});

$(document).on('keydown', '#find-password-pincode', function(event){
    if(event.which == 13){
       find_password();
    }
});


function reset_password(){
	$("#reset-password-button").addClass("disabled");
    $("#reset-password-hint").html('').hide();
    var csrf = $("#other-csrf").val();
    if(csrf == ''){
        $("#alter-pwd-button").removeClass("disabled");
        csrf_empty();
    }
    
    var pwd = $("#reset-pwd-new-pwd").val();
    var pwd_check = $("#reset-pwd-new-pwd-check").val();
    hint_html = '';
    if(pwd == '') {
        hint_html += (hint_html == '' ? '请填写新密码！' : '<BR>请填写新密码！');
    }else if(pwd != pwd_check){
        hint_html += (hint_html == '' ? '两次新密码不一致！' : '<BR>两次新密码不一致！');
    }
    
    var pincode = $("#reset-password-pincode").val();
    if(pincode == '') {
        hint_html += (hint_html == '' ? '请填写验证码！' : '<BR>请填写验证码！');
    }
    
    var code = $("#reset-pwd-code").val();
    if(code == '') {
        hint_html += (hint_html == '' ? '网页错误（缺失code）！' : '<BR>网页错误（缺失code）！');
    }
    
    if(hint_html != ''){
        $("#reset-password-hint").html(hint_html).fadeIn();
        $("#reset-password-button").removeClass("disabled");
        return 0;
    }
    //加密密码
    pwd = do_encrypt(pwd);
    pwd = hex2b64(pwd);
    //重置用户密码
    $("#reset-password-button").html("重置中...");
    $.post(BASE_PATH + 'user/do_reset_pwd', {'other_token':csrf, 
        'new_pwd':pwd,
        'pincode':pincode,
        'code':code
        },
        function(result){
            if(result.code != 1700) {
                $("#reset-password-hint").html(result.msg + '(' + result.code + ')').fadeIn();
                $("#reset-password-button").removeClass("disabled");
                $("#reset-password-pincode-img").click();
            }else {
                $("#reset-password-form").hide();
                $("#reset-password-hint").removeClass("alert-danger");
                $("#reset-password-hint").addClass("alert-success");
                $("#reset-password-hint").html('<p><span class="glyphicon glyphicon-ok"></span><span>&nbsp;密码重置成功，请使用新密码登录xplusplus.cn。</span></p>').fadeIn();
				$(".panel-footer-reset-password").hide();
            }
            $("#reset-password-button").html("提交");
        },
        'json'
    );
}

$(document).on('click', '#reset-password-button', function(event){
    reset_password();
});

$(document).on('keydown', '#reset-pwd-new-pwd', function(event){
    if(event.which == 13){
       reset_password();
    }
});

$(document).on('keydown', '#reset-pwd-new-pwd-check', function(event){
    if(event.which == 13){
       reset_password();
    }
});

$(document).on('keydown', '#reset-password-pincode', function(event){
    if(event.which == 13){
       reset_password();
    }
});

/***************************  find password end **************************************/
/**************************************************************************************/

/**************************************************************************************/
/***************************  recommend user start ****************************************/
$(document).on('click', '#check-more-recommend-user', function(e){
    var id_str = $("#recommend-user > div").last().attr("id");
    var start_id = id_str.substr(15);
    start_id ++;
    $.post(BASE_PATH + 'user/more_recommend_user_html', {'other_token' : $("#other-csrf").val(), 'start_id':start_id},
            function(result){
        if(result.code != 0){
            Messenger().post('获取推荐用户失败(' + result.code + '):' + result.msg);
        }else {
            if(result.data == ''){
                Messenger().post('没有更多..');
            }
            else {
                $("#recommend-user").append(result.data);
            }
        }
    }, 'json');
});
/***************************  recommend user end **************************************/
/**************************************************************************************/


$(document).on('click', '#seller-chg-pwd-btn', function(event){
	$('#upd-seller-pwd-modal').modal('show');
	$('#upd-seller-pwd-modal').modal({keyboard: false});
	$("#upd-seller-hint").html('').hide();
	
	$("#seller-old-pwd").val('');
	$("#seller-new-pwd").val('');
	$("#seller-new-pwd2").val('');
	
	$('#btn-seller-upd-pwd').on('click',function(event){
		updateSellerPwd();
	});
});

function updateSellerPwd(){
	$("#btn-seller-upd-pwd").attr('disabled', 'disabled');
	$("#upd-seller-hint").html(hint_html).fadeOut();
	
	var old_pwd = $("#seller-old-pwd").val();
    var new_pwd = $("#seller-new-pwd").val();
    var new_pwd2 = $("#seller-new-pwd2").val();
    
    var hint_html = '';
    if(old_pwd == '') {
    	hint_html += (hint_html == '' ? '' : '<BR>');
        hint_html +=  '请输入旧密码！';
    }
    if(new_pwd == ''){
    	hint_html += (hint_html == '' ? '' : '<BR>');
        hint_html +=  '请输入新密码！';
    }
    if(new_pwd2 == ''){
    	hint_html += (hint_html == '' ? '' : '<BR>');
        hint_html +=  '请输入确认密码！';
    }
    if(new_pwd != new_pwd2){
    	hint_html += (hint_html == '' ? '' : '<BR>');
        hint_html +=  '新密码与确认密码输入不一致！';
    }
    if(6 > $.trim(new_pwd).length){
    	hint_html += (hint_html == '' ? '' : '<BR>');
        hint_html +=  '新密码必须大于6位！';
    }
    
    if(hint_html != ''){
        $("#upd-seller-hint").html(hint_html).fadeIn();
        $("#btn-seller-upd-pwd").removeAttr('disabled');
        return 0;
    }
    
    $("#btn-seller-upd-pwd").html("提交中...");
    $.post(BASE_PATH + 'passport/changePwd', {
    	'old_pwd':old_pwd, 
        'new_pwd':new_pwd
        },
        function(result){
        	$("#add-seller-hint").html('');
            if(result.code != 0) {
                $("#upd-seller-hint").html(result.msg + '(' + result.code + ')').fadeIn();
                $("#btn-seller-upd-pwd").removeAttr('disabled');
                $("#btn-seller-upd-pwd").html("确定");
            }else {
                $("#upd-seller-hint").html(result.msg + ', 关闭...').fadeIn();
                setTimeout(function(){
                    $("#upd-seller-pwd-modal").modal('hide');
                    $("#btn-seller-upd-pwd").removeAttr('disabled');
                    $("#btn-seller-upd-pwd").html("确定");
                    
                    $("#seller-old-pwd").val('');
                	$("#seller-new-pwd").val('');
                	$("#seller-new-pwd2").val('');
                }, 500);


				$.post(BASE_PATH + 'passport/logout', {},
					function(result){
						if ( result.code == 0 ) {
							setTimeout(function(){
								location.href = BASE_PATH;
							},800);
						}
					},
					'json'
				);
				
            }
        },
        'json'
    );
}

prettyPrint();
});

