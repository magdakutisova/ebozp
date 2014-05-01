function PLANNING() {
	function newTask(first_argument) {
		// nacteni informaci
		var context = $(this);
		var userId = context.attr("g7:user");
		var dt = context.attr("g7:date");

		// sestaveni url
		var url = "/planning/task/post.html?planning[user_id]=" + userId + "&planning[planned_on]=" + dt;
		
		// otevreni frame
		jQuery.iframeDialog(url, 800, 500, "Nový úkol", "refresh");
	}

	$(".planning-calendar td button").click(newTask);
}

jQuery(PLANNING);