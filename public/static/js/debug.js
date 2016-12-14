/**
 * 
 */
$('#submit-file').click(function() {
	var formData = new FormData();
	formData.append("file",$("input[type='file']")[0].files[0]);
	$.ajax({ 
		url : '/resource/upload.html', 
		type : 'POST', 
		data : formData, 
		processData : false, 
		contentType : false,
		beforeSend:function(){
			console.log("正在进行，请稍候");
		},
		success : function(data) { 
//			$('#echo-area').html(data);
			$.confirm({
				closeIcon: true,
				closeIconClass: 'glyphicon glyphicon-remove',
				columnClass: "xlarge",
				backgroundDismiss: false,
				title: "预览",
				content: data,
			});
		}, 
		error : function(responseStr) { 
			console.log("error");
		} 
	});
});