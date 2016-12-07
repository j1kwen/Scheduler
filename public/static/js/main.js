/**
 *  Main javascript file
 */

$(document).ready(function() {
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
	function setWaiting(btn, wat) {
		$(btn).addClass('disabled');
		$(btn).text(wat);
	}
	function setReset(btn, ret) {
		$(btn).removeClass('disabled');
		$(btn).text(ret);
	}
	$("button.btn-form-submit").click(function() {
		var nec = $(this).parents("form").find(".has-feedback");
		var isok = true;
		nec.each(function() {
			var ipt = $(this).find("input");
			if(ipt.val() == '') {
				ipt.after('<span class="glyphicon glyphicon-remove form-control-feedback"></span>');
				$(this).addClass("has-error");
				if(isok) {
					ipt.focus();
				}
				isok = false;
			}
		});
		if(isok) {
			var fields = $(this).parents("form").find("[name]");
			var _url = $(this).parents("form").attr('action');
			var _data = "";
			var btn = $(this);
			fields.each(function() {
				if(_data != "") {
					_data = _data + "&"
				}
				_data = _data + $(this).attr("name") + "=" + $(this).val();
			});
			setWaiting($(this), '保存中…');
			$.ajax({
				url: _url,
				type: "POST",
				data: _data,
				success: function(data) {
					if(data.success) {
						setReset(btn, '确定');
						$(btn).parents(".modal[role='dialog']").modal('hide');
						location.reload();
					} else {
						showErrorAlert('错误', '服务器异常，请重试！');
						setReset(btn, '确定');
					}
				},
				error: function() {
					showErrorAlert('错误', '请求失败，请重试！');
					setReset(btn, '确定');
				}
			});
		}
	});
	$("input").blur(function() {
		$(this).val($(this).val().trim());
		if($(this).val() != '') {
			$(this).parents(".has-error").removeClass("has-error")
				.find("span.form-control-feedback").remove();
		}
	});

	$(".btn-set-term").click(function() {
		var year = $(this).parent().siblings("h4").text();
		var s_term = $(this).parent().siblings("h5").text();
		var s_id = $(this).attr('data-id');
		var str = $(this).text();
		var str_d = "已设置";
		var btn = $(this);
		$.confirm({
			keyboardEnabled: true,
			backgroundDismiss: true,
			type: 'blue',
			icon: 'glyphicon glyphicon-transfer',
			title: '切换学期',
			content: '<h4>确实要切换到以下学期吗？</h4><p><strong>' + year + '</strong></p><p><strong>' + s_term + '</strong></p>',
			buttons: {
				取消: {
					btnClass: 'btn btn-default',
				},
				确定: {
					btnClass: 'btn btn-primary',
					action: function() {
						setWaiting(btn, '请稍候…');
						$.ajax({
							url: set_cur_term,
							type: "POST",
							data: "id=" + s_id,
							success: function(data) {
								if(data.success) {
									setReset(btn, str);
									$(".cur-term").parent().find("button.disabled")
									.toggleClass('disabled').text(str);
									if($(".cur-term").size() == 0) {
										$(btn).parents(".thumbnail").prepend('<div class="cur-term">\n'
												+ '<img src="/public/static/img/cur-term.png" alt="...">\n'
												+ '</div>');
									} else {					
										$(".cur-term").prependTo($(btn).parents(".thumbnail"));
									}
									$(btn).toggleClass('disabled').text(str_d);
								} else {
									showErrorAlert('错误', '服务器异常，请重试！');
									setReset(btn, str);
								}
							},
							error: function() {
								showErrorAlert('错误', '请求失败，请重试！');
								setReset(btn, str);
							}
						});
					},
				}
			},
		});
	});
	$(".btn-del-term").click(function() {
		var year = $(this).parent().siblings("h4").text();
		var s_term = $(this).parent().siblings("h5").text();
		var btn = $(this);
		var s_id = $(this).siblings("[data-id]").attr('data-id');
		var item = $(this).parents(".thumbnail").parent();
		$.confirm({
			keyboardEnabled: true,
			backgroundDismiss: true,
			type: 'red',
			icon: 'glyphicon glyphicon-trash',
			title: '确认删除',
			content: '<h4>确实要删除该学期吗？</h4><p><strong>' + year + '</strong></p><p><strong>' + s_term + '</strong></p><p>删除后，该学期的课表将一并删除！</p>',
			buttons: {
				取消: {
					btnClass: 'btn btn-default',
				},
				确定: {
					btnClass: 'btn btn-danger',
					action: function() {	
						$.ajax({
							url: del_cur_term,
							type: "POST",
							data: "id=" + s_id,
							success: function(data) {
								if(data.success) {
									$(item).fadeOut("slow",function() {
										$(this).remove();
									});
								} else {
									showErrorAlert('错误', '服务器异常，请重试！');
								}
							},
							error: function() {
								showErrorAlert('错误', '请求失败，请重试！');
							}
						});
					},
				},
			},
		});
	});
	$('.btn-mod-term').click(function() {
		var _id = $(this).siblings("[data-id]").attr('data-id');
		var _term = new String($(this).parent().siblings("h5").text());
		var _start = _term.substr(-10);
		var _mod = $(this).parent().siblings("h5");
		$.confirm({
			keyboardEnabled: true,
			backgroundDismiss: true,
			type: 'blue',
			icon: 'glyphicon glyphicon-pencil',
			title: '修改开学日',
			content: '<div class="input-group date" id="picker-dialog" data-date="' + _start + '" data-date-format="yyyy-mm-dd" data-link-format="yyyy-mm-dd"><input class="form-control" name="date" size="16" type="text" value="' + _start +'" readonly=""><span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span></div>'
					+ '<script>$("#picker-dialog").datetimepicker({language:  "zh-CN", weekStart: 1,todayBtn:  1,autoclose: 1,todayHighlight: 1,startView: 2,minView: 2,forceParse: 0})</script>',
			buttons: {
				取消: {
					btnClass: 'btn btn-default',
				},
				确定: {
					btnClass: 'btn btn-primary',
					action: function() {
						var sel_date = $("#picker-dialog>input").val();
						if(sel_date == _start) {
							return true;
						}
						$.ajax({
							url: mod_cur_term,
							type: "POST",
							data: "id=" + _id + "&date=" + sel_date,
							success: function(data) {
								if(data.success) {
									var _prefix = $(_mod).text().slice(0, -10);
									$(_mod).text(_prefix + sel_date);
								} else {
									showErrorAlert('错误', '服务器异常，请重试！');
								}
							},
							error: function() {
								showErrorAlert('错误', '请求失败，请重试！');
							}
						});
					},
				},
			},
		});
	});
	$("#aboutModal").on("shown.bs.modal", function() {
		var g_url = $("#about-modal").attr('data-url');
		$("#about-modal").html('<div class="loading-icon"></div>');
		$.ajax({
			url: g_url,
			success: function(data, status) {
				if(status == "success") {					
					$("#about-modal").html(data);
				} else {
					$("#about-modal").html('<div class="alert alert-danger" role="alert"><strong>错误！</strong>未知错误，请重试！</div>');
				}
			},
			error: function() {
				$("#about-modal").html('<div class="alert alert-danger" role="alert"><strong>错误！</strong>加载失败，请检查网络！</div>');
			}
		});
	});
	$("#aboutModal").on("hidden.bs.modal", function() {
		$("#about-modal").empty();
	});
	$(".btn-refresh").click(function() {
		location.reload();
	});
	$('.form_date').datetimepicker({
	    language:  'zh-CN',
	    weekStart: 1,
	    todayBtn:  1,
		autoclose: 1,
		todayHighlight: 1,
		startView: 2,
		minView: 2,
		forceParse: 0
	});
});