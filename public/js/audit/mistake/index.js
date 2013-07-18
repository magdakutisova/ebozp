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
	
	function createMistake() {
		// nacteni dat a otevreni dialogu
		$.get("/audit/mistake/create.html", { clientId : CLIENTID}, function (response) {
			response = $(response);
			response.find("#mistake-subsidiary_id").change(loadWorkplaces);
			
			response.dialog({
				modal : true,
				resizable : false,
				draggable : false,
				width : 700
			});
		}, "html");
	}
	
	function loadWorkplaces() {
		var context = $(this);
		var subsidiaryId = context.val();
		
		if (subsidiaryId == "0") {
			// deaktivace dat
			context.parents("form:first").find("#mistake-workplace_id").attr("disabled", "disabled");
		} else {
			// nacteni dat ze serveru
			$.get("/audit/workplace/list.json", { subsidiaryId : subsidiaryId}, function (response) {
				// odstraneni vsech pracovist ze seznamu krome prvni polozky (obecna neshoda)
				var workplaces = context.parents("form:first").find("#mistake-workplace_id");
				workplaces.children().filter(":gt(0)").remove();
				
				for (var i in response) {
					var workplace = response[i];
					
					$("<option></option>").attr("value", workplace.id_workplace).text(workplace.name).appendTo(workplaces);
				}
				
				workplaces.removeAttr("disabled");
			}, "json");
		}
	}
	
	$("#mistake-list button").click(openMistake);
	$("#toggle-filter").click(toggleFilter);
	$("#new-mistake").click(createMistake);
	$("#mistake-subsidiary_id").change(loadWorkplaces);
});