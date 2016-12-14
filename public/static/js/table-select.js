/**
 * 
 */

$(document).ready(function() {
	$(".select-all").click(function() {
		toggleSelect($(this), $(this).get(0).checked);
	});
	$("th, td").click(function(event) {
		if(event.target == this) {			
			$(this).find("input[type='checkbox']").click();
		}
	});
	function toggleSelect(obj,checked) {
		$(obj).parents("table").find("tbody>tr>td>input[type='checkbox']").each(function() {
			$(this).get(0).checked = checked;
		});
	}
});