$(function () {
	
	function openMistake() {
		var mistakeId = $(this).parent().find(":hidden[name='mistakeId']").val();
		
		// sestaveni routy
		var url = "/klient/" + CLIENTID + "/mistake/" + mistakeId + "/html";
		
		$.iframeDialog(url, 730, 400, "Neshoda");
		
		return 
		
		// sestaveni dialogu
		var iframe = $("<iframe width='700px' height='400px'>").attr("src", url);
		
		$("<div>").append(iframe).dialog({
			modal: true,
			width: "730px",
			draggable: false,
			title : "Neshoda"
		});
	}
	
	$("#mistake-list button").click(openMistake);
});