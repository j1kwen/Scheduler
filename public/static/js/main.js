/**
 * Author	: Shannon
 * Date		: 2016-12-05 ‏‎10:52:03
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
	$(window).on('scroll',function(){
		var st = $(document).scrollTop();
		if(st > 0){
			$('.btn-return-top').fadeIn(function(){
				$(this).removeClass('dn');
			});
		}else{
			$('.btn-return-top').fadeOut(function(){
				$(this).addClass('dn');
			});
		}	
	});
	$("button.btn-form-submit").click(function() {
		var nec = $(this).parents("form").find(".has-feedback");
		var isok = true;
		nec.each(function() {
			var ipt = $(this).find("input,select");
			if(ipt.val() == null || ipt.val() == '') {
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
				_data = _data + $(this).attr("name").trim() + "=" + $(this).val().trim();
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
						loadItem();
					} else {
						showErrorAlert('错误', data.msg);
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

	$("#aboutModal").on("shown.bs.modal", function() {
		var g_url = $("#about-modal").attr('data-url');
		$("#about-modal").html('<div class="loading-icon"></div>');
		$.ajax({
			url: g_url,
			success: function(data) {
				if(data.code == 0) {
					$("#about-modal").html('<div class="list-empty"><span class="glyphicon glyphicon-remove-circle"></span>&nbsp;登陆失效，请刷新页面</div>');
				} else {
					$("#about-modal").html(data);					
				}
			},
			error: function() {
				$("#about-modal").html('<div class="list-empty"><span class="glyphicon glyphicon-remove-circle"></span>&nbsp;加载失败，请检查网络</div>');
			}
		});
	});
	$("#aboutModal").on("hidden.bs.modal", function() {
		$("#about-modal").empty();
	});
	$("#tb-logout").click(function() {
		var _name = $(this).parent().siblings(".usr-info").find("a").text();
		var _url = $(this).attr('data-url');
		$.confirm({
			type: 'blue',
			icon: 'glyphicon glyphicon-log-out',
			title: '退出',
			content: '<h4>当前用户：' + _name + '</h4><p>确实要退出系统吗？</p>',
			buttons: {
				取消: {
					btnClass: "btn btn-default",
				},
				确定: {
					btnClass: "btn btn-info",
					action: function() {
						window.location.href = _url;
					},
				}
			}
		});
			
	});
	$("#tb-mod-passwd").click(function() {
		var _user = $(this).parent().parent().prev().attr("data-user");
		var _name = $(this).parent().siblings(".usr-info").find("a").text();
		var _url = $(this).attr('data-url');
		$.confirm({
			type: 'orange',
			icon: 'glyphicon glyphicon-lock',
			title: '修改密码',
			content: '<h4>当前用户：' + _name + '</h4><div class="input-group"><span class="input-group-addon">原密码</span><input type="password" id="m-old" class="form-control" placeholder="请输入原密码" value=""></div><br/><div class="input-group"><span class="input-group-addon">新密码</span><input type="password" id="m-pwd" class="form-control" placeholder="新密码" value=""></div><br/><div class="input-group"><span class="input-group-addon">请确认</span><input type="password" id="m-pwd2" class="form-control" placeholder="确认新密码" value=""></div>',
			buttons: {
				取消: {
					btnClass: "btn btn-default",
				},
				确定: {
					btnClass: "btn btn-warning",
					action: function() {
						var _old = $("#m-old").val();
						var _pwd = $("#m-pwd").val();
						var _pwd2 = $("#m-pwd2").val();
						if(_old == '' || _pwd == '' || _pwd2 == '') {
							showErrorAlert('错误', '请输入完整！');
							return false;
						}
						if(_pwd != _pwd2) {
							showErrorAlert('错误', '两次输入的密码不一致');
							return false;
						}
						$.ajax({
							url: _url,
							type: "POST",
							data: "user=" + _user.trim() + "&old=" + _old + "&pwd=" + _pwd + "&pwd2=" + _pwd2,
							success: function(data) {
								if(data.success) {
									$.confirm({
										keyboardEnabled: false,
										backgroundDismiss: false,
										icon: 'glyphicon glyphicon-ok',
										title: '成功',
										content: data.msg,
										type: 'green',
										buttons: {
											确定: {
												btnClass: "btn btn-success",
												action: function() {
													window.location.href = data.url;
												},
											},
										},
									});
								} else {
									if(data.redir) {
										$.confirm({
											keyboardEnabled: false,
											backgroundDismiss: false,
											icon: 'glyphicon glyphicon-remove-circle',
											title: '错误',
											content: data.msg,
											type: 'red',
											buttons: {
												确定: {
													btnClass: "btn btn-danger",
													action: function() {
														window.location.href = data.url;
													},
												},
											},
										});
									} else {
										showErrorAlert('错误', data.msg);
									}
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
	$(".btn-refresh").click(function() {
		$(this).find("span.glyphicon").addClass("refreshing");
		loadItem();
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
	$(".btn-return-top").click(function () {
        var speed=300;//滑动的速度
        $('body,html').animate({ scrollTop: 0 }, speed);
        return false;
	});
	function loadItem() {
		$("[data-area]").html('<div class="loading-icon"></div>');
		$.ajax({
			url: get_item,
			success: function(data) {
				if(data.code == 0) {
					$(".refreshing").removeClass("refreshing");
					$("[data-area]").html('<div class="list-empty"><span class="glyphicon glyphicon-remove-circle"></span>&nbsp;登陆失效，请刷新页面</div>');
				} else {					
					$(".refreshing").removeClass("refreshing");
					$("[data-area]").html(data);
				}
			}, 
			error: function() {
				$(".refreshing").removeClass("refreshing");
				$("[data-area]").html('<div class="list-empty"><span class="glyphicon glyphicon-remove-circle"></span>&nbsp;发生错误</div>');
			}
		});
	}
	loadItem();
});