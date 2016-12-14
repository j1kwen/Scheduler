/**
 * 
 */

$(document).ready(function() {
	/**
	 * Author	: Shannon
	 * Date		: 2016-12-09 ‏‎13:08:45
	 */
	function showErrorAlert(title, content) {
		$.alert({
			keyboardEnabled: true,
			backgroundDismiss: true,
			icon: 'glyphicon glyphicon-remove-circle',
			title: title,
			content: content,
			type: 'red',
			buttons: {
				确定: {
					btnClass: 'btn btn-danger',
				},
			},
		});
	}
	$(".btn-del-course").click(function() {
		var _tr = $(this).parent().siblings().toArray();
		var _name = $(_tr[5]).text();
		var _mac = $(_tr[10]).text();
		var _type = $(_tr[6]).text();
		var item = $(this).parent().parent();
		var _id = $(item).attr('data-id');
		$.confirm({
			type: 'red',
			icon: 'glyphicon glyphicon-trash',
			title: '删除课程',
			content: '<h4>确实要删除该课程吗？</h4><p><strong><del>' + _name + ' （' + _mac + '） ' + _type + '</del></strong></p><p>删除后，请重新安排课表！</p>',
			buttons: {
				取消: {
					btnClass: "btn btn-default",
				},
				确定: {
					btnClass: "btn btn-danger",
					action: function() {
						$.ajax({
							url: del_course,
							type: "POST",
							data: "id=" + _id,
							success: function(data) {
								if(data.success) {
									$(item).fadeOut("slow",function() {
										$(this).remove();
									});
								} else {
									showErrorAlert('错误', data.msg);
								}
							},
							error: function() {
								showErrorAlert('错误', '请求失败，请重试！');
							}
						});
					},
				}
			}
		});
	});
});