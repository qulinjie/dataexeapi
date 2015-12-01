$(document).ready(function(){




$(document).on('click', '#export-castTender-page', function(event){
	var page = $('#castTender-current-page').val();
	var content = $('#content').val();
	var status = $('#castTender-search-status').val();
	
	$("#export-castTender-page").attr('disabled', 'disabled');
	location.href = BASE_PATH + "castTender/export?scope=page&page=" + page + "&content=" + content+ "&status=" + status + '&1=1';
	$("#export-castTender-page").removeAttr('disabled');
});

$(document).on('click', '#export-castTender-all', function(event){
	var page = $('#castTender-current-all').val();
	var content = $('#content').val();
	var status = $('#castTender-search-status').val();
	
	$("#export-castTender-all").attr('disabled', 'disabled');
	location.href = BASE_PATH + "castTender/export?scope=all&page=" + page + "&content=" + content+ "&status=" + status + '&1=1';
	$("#export-castTender-all").removeAttr('disabled');
});




prettyPrint();
});
