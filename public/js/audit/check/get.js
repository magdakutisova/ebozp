$(function () {
	
	/*
	 * zobrazeni detailu neshody
	 */
	function showDetails() {
		// nactnei id neshody
		var mistakeId = $(this).parent().find("input[name='mistakeId']").val();
		
		var url = "/klient/" + CLIENT_ID + "/mistake/" + mistakeId + "/html";
		
		$.iframeDialog(url, 800, 400);
	}
	
	$("#tabs").tabs();
	$("#mistake-list button").click(showDetails);
});