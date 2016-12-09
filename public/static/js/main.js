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
				$("[data-area]").html(data);
				$(".refreshing").removeClass("refreshing");
			}, 
			error: function() {
				$("[data-area]").html('<div class="list-empty"><span class="glyphicon glyphicon-remove-circle"></span>&nbsp;发生错误</div>');
				$(".refreshing").removeClass("refreshing");
			}
		});
	}
	loadItem();
});