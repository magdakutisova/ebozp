$(function() {

	function hashStr(str) {
		var retVal = 0;
		
		str = stringToBytes(str);
		
		for ( var i = 0; i < str.length; i++) {
			retVal += str[i];
		}

		return retVal;
	}

	function openMistake() {
		var mistakeId = $(this).parent().find(":hidden[name='mistakeId']")
				.val();

		// sestaveni routy
		var url = "";
		
		if ($(this).attr("name") == "show-mistake")
			url = "/klient/" + CLIENTID + "/mistake/" + mistakeId + "/html";
		else
			url = "/klient/" + CLIENTID + "/mistake/" + mistakeId + "/edit-alone/html";

		$.iframeDialog(url, 745, 500, "Neshoda");
	}

	function toggleFilter() {
		$("#mistakefilter").toggle();
	}

	function createMistake() {
		// nacteni pobocky
		var subsidiaryId = Number($("#mistake-subsidiary_id").val());
		var data = {
			clientId : CLIENTID
		};

		if (subsidiaryId)
			data["mistake[subsidiary_id]"] = subsidiaryId;

		// nacteni dat a otevreni dialogu
		$.get("/audit/mistake/create.html", data, function(response) {
			response = $(response);
			response.find("#mistake-subsidiary_id").change(loadWorkplaces);
			response.find("#mistake-will_be_removed_at,#mistake-notified_at")
					.datepicker(
							{
								"dateFormat" : "dd. mm. yy",
								"dayNamesMin" : ["Ne", "Po", "Út", "St", "Čt", "Pá", "So"],
								"monthNames" : ["Leden", "Únor", "Březen", "Duben", "Květen", "Červen", "Červenec", "Srpen", "Září", "Říjen", "Listopad", "Prosinec"],
								"firstDay" : 1
							});

			response.dialog({
				modal : true,
				resizable : false,
				draggable : false,
				width : 700
			});
		}, "html");
	}
    
	$("#mistake-list button").click(openMistake);
	$("#toggle-filter").click(toggleFilter);
	$("#new-mistake").click(createMistake);
});