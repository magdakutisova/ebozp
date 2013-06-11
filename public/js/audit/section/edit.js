$(function () {
	function toggleFieldset() {
		$("#toggler").parent().find("table").toggle();
	}
	
	function openQEdit() {
		// zjisteni id otazky
		var qId = $(this).next().val();
		
		// nacteni dat a otevreni dialogu
		$.get("/audit/question/get.html", { questionId : qId }, function (response) {
			$(response).dialog({
				title : "Editace otázky",
				modal : true,
				dragable : false,
				width : 550
			});
		}, "html");
	}
	
	$("#toggler").click(toggleFieldset).click();
	$("#question-list").sortable( { axis: "y"} );
	$("#question-list a").click(openQEdit);
});