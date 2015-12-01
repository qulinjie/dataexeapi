$(document).ready(function(){

$('#f-form-placeholder').unbind("keydown");
$('#f-form-placeholder').on('keydown',function(event){
    if(event.which == 13){
    	return false;
    }
});

$(document).on('click', '#tender-bid-btn', function(event){
	var id =  $(this).parent().parent().children().first().text();
	var obj =  $(this).parent();
	
	$("#btn-confirm-tender").removeAttr('disabled');
	$('#tender-bid-comment').val('');
	$("#tender-bid-hint").html('').hide();
	$('#tender-bid-modal').modal('show');
	
	$('#btn-confirm-tender').unbind("click");
	$('#btn-confirm-tender').on('click', {'id':id},function(event){
		$("#btn-confirm-tender").attr('disabled', 'disabled');
		
		var comment = $('#tender-bid-comment').val();
		
		$.post(BASE_PATH + 'castTender/add', {'id':event.data.id,'comment':comment},
		        function(result){
		            if(result.code != 0) {
		                $("#tender-bid-hint").html(result.msg + '(' + result.code + ')').fadeIn();
		            }else {
		            	$("#tender-bid-hint").html(result.msg + ', 关闭...').fadeIn();
		            	setTimeout(function(){
		            		$('#tender-bid-modal').modal('hide');
		            		obj.html('已投标');
		            	},800);
		            }
		        },
		        'json'
		    );
	});
});

$(document).on('click', '#export-trender-page', function(event){
	var page = $('#tender-current-page').val();
	var content = $('#content').val();
	
	$("#export-trender-page").attr('disabled', 'disabled');
	location.href = BASE_PATH + "tender/export?scope=page&page=" + page + "&content=" + content;
	$("#export-trender-page").removeAttr('disabled');
});

$(document).on('click', '#export-trender-all', function(event){
	var page = $('#tender-current-all').val();
	var content = $('#content').val();
	
	$("#export-trender-all").attr('disabled', 'disabled');
	location.href = BASE_PATH + "tender/export?scope=all&page=" + page + "&content=" + content;
	$("#export-trender-all").removeAttr('disabled');
});




prettyPrint();
});
