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
	$(".btn-mod-course").click(function() {
		var _tr = $(this).parent().siblings().toArray();
		var _name = $(_tr[1]).find("span").text();
		var _mac = $(_tr[3]).text();
		var _type = $(_tr[4]).text();
		var item = $(this).parent().parent();
		var _id = $(item).attr('data-id').trim();
		var _tp_lst = new String();
		for(_t in type_list) {
			var _i = type_list[_t];
			_tp_lst = _tp_lst + '<option value="' + _i.id + '" title="' + _i.des + '"';
			if(_i.name == _type) {
				_tp_lst = _tp_lst + ' selected="selected"';
			}
			_tp_lst = _tp_lst + '>' + _i.name + '</option>';
		}
		$.confirm({
			type: 'orange',
			icon: 'glyphicon glyphicon-pencil',
			title: '修改机房信息',
			content: '<h4>机房号：' + _name + '</h4><div class="input-group"><span class="input-group-addon">机器数量</span><input type="number" id="m-mac" class="form-control" placeholder="请输入机器数量" value="' + _mac + '"></div><br/><div class="input-group"><span class="input-group-addon">课程类型</span><select class="form-control" id="m-type">' + _tp_lst + '</select></div>',
			buttons: {
				取消: {
					btnClass: "btn btn-default",
				},
				确定: {
					btnClass: "btn btn-warning",
					action: function() {
						var m_mac = $("#m-mac").val().trim();
						var m_type = $("#m-type").val().trim();
						var m_tp_name = "";
						for(_t in type_list) {
							var _i = type_list[_t];
							if(_i.id == m_type) {
								m_tp_name = _i.name;
								break;
							}
						}
						if(m_mac == _mac && m_tp_name == _type) {
							return true;
						}
						$.ajax({
							url: mod_machine,
							type: "POST",
							data: "id=" + _id + "&mac=" + m_mac + "&type=" + m_type,
							success: function(data) {
								if(data.success) {
									$(_tr[3]).text(m_mac);
									$(_tr[4]).text(m_tp_name);
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