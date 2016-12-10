/**
 * Author	: Shannon
 * Date		: 2016-12-09 17:37:46
 * Descri.	: Script of typeList
 */
function showErrorAlert(title, content) {
	$.alert({
		keyboardEnabled: true,
		backgroundDismiss: true,
		icon: 'glyphicon glyphicon-remove-circle',
		title: title,
		content: content,
		type: 'red',
	});
}
$(".btn-mod-type").click(function() {
	var _tr = $(this).parent().siblings().toArray();
	var _id = $(_tr[0]).text().trim();
	var _name = $(_tr[1]).find(".label").text();
	var _desc = $(_tr[2]).text();
	$.confirm({
		type: 'orange',
		icon: 'glyphicon glyphicon-pencil',
		title: '修改类型信息',
		content: '<h4>编号：' + _id + '</h4><div class="input-group"><span class="input-group-addon">名称</span><input type="text" id="m-name" class="form-control" placeholder="请输入类型名称" value="' + _name + '"></div><br/><div class="input-group"><span class="input-group-addon">描述</span><input type="text" id="m-desc" class="form-control" placeholder="描述该类型" value="' + _desc + '"></div>',
		buttons: {
			取消: {
				btnClass: "btn btn-default",
			},
			确定: {
				btnClass: "btn btn-warning",
				action: function() {
					var m_name = $("#m-name").val().trim();
					var m_desc = $("#m-desc").val().trim();
					if(m_name == _name && m_desc == _desc) {
						return true;
					}
					$.ajax({
						url: mod_type,
						type: "POST",
						data: "id=" + _id + "&name=" + m_name + "&description=" + m_desc,
						success: function(data) {
							if(data.success) {
								$(_tr[1]).find(".label").text(m_name);
								$(_tr[2]).text(m_desc);
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