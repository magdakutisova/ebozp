$(function () {
	
	// otevreni dialogu s editaci neshody
	function openMistake() {
		
		var mistakeId = $(this).parent().find("input[name='mistakeId']").val();
		
		var url = "/klient/" + CLIENT_ID + "/pobocka/" + SUBSIDIARY_ID + "/check/" + CHECK_ID + "/mistake/ " + mistakeId + "/edit";
		
		$.iframeDialog(url, 800, 600, "Neshoda");
	}
	
	$("#tabs").tabs();
	$("#mistake-table button").click(openMistake);
});