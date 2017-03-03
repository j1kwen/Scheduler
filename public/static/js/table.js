/**
 * 
 */
$(document).ready(function() {
	
	$(".start-working").click(function() {
		$.confirm({
			type: 'blue',
			icon: 'glyphicon glyphicon-time',
			title: '执行排课进程',
			content: 'url:' + url_work,
			columnClass: 'large',
			backgroundDismiss: false,
			buttons: {
				关闭: {
					btnClass: "btn btn-default btn-cfm-close",
					action: function() {
						location.reload();
					},
				},
			}
		});
	});
	
	$("#week-prev").click(function() {
		if(week_idx > 1) {
			week_idx--;
		} else {
			week_idx = 1;
		}
		get_item = base_url + "?week=" + week_idx;
		$(".btn-refresh").click();
		$("#span-week").text("第" + week_idx + "周");
	});
	
	$("#week-next").click(function() {
		if(week_idx < 30) {
			week_idx++;
		} else {
			week_idx = 30;
		}
		get_item = base_url + "?week=" + week_idx;
		$(".btn-refresh").click();
		$("#span-week").text("第" + week_idx + "周");
	});
});