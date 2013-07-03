$(function () {
	
	function openMistake() {
		var mistakeId = $(this).parent().find(":hidden[name='mistakeId']").val();
		
		// sestaveni routy
		var url = "/klient/" + CLIENTID + "/mistake/" + mistakeId + "/html";
		
		$.iframeDialog(url, 745, 500, "Neshoda");
	}
	
	function toggleFilter() {
		$("#mistakefilter").toggle();
	}
	
	$("#mistake-list button").click(openMistake);
	$("#toggle-filter").click(toggleFilter);
});