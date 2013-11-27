$(function() {

	function hashStr(str) {
		var retVal = 0;
		
		str = stringToBytes(str);
		
		for ( var i = 0; i < str.length; i++) {
			retVal += str[i];
		}

		return retVal;
	}

    // metoda dynamickeho nacitani kategorii
	function checkCategory() {
		var context = $(this);
		var subContext = $("select#mistake-subcategory");

		// skryti vsech skupin
		subContext.children("optgroup").hide();
		
		// zobrazeni skupiny odpovidajici kategorii
		subContext.children("optgroup[label='" + context.val() + "']").show();
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
    
    function loadWorkplaces() {
		var context = $(this);
		var subsidiaryId = context.val();

		if (subsidiaryId == "0") {
			// deaktivace dat
			context.parents("form:first").find("#mistake-workplace_id").attr(
					"disabled", "disabled");
		} else {
			// nacteni dat ze serveru
			$.get("/audit/workplace/list.json", {
				subsidiaryId : subsidiaryId
			}, function(response) {
				// odstraneni vsech pracovist ze seznamu krome prvni polozky
				// (obecna neshoda)
				var workplaces = context.parents("form:first").find(
						"#mistake-workplace_id");
				workplaces.children().filter(":gt(0)").remove();

				for ( var i in response) {
					var workplace = response[i];

					$("<option></option>")
							.attr("value", workplace.id_workplace).text(
									workplace.name).appendTo(workplaces);
				}

				workplaces.removeAttr("disabled");
			}, "json");
		}
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
            response.find("#mistake-category").change(checkCategory).change();
			response.find("#mistake-will_be_removed_at,#mistake-notified_at")
					.datepicker(
							{
								"dateFormat" : "dd. mm. yy",
								"dayNamesMin" : ["Ne", "Po", "Út", "St", "Čt", "Pá", "So"],
								"monthNames" : ["Leden", "Únor", "Březen", "Duben", "Květen", "Červen", "Červenec", "Srpen", "Září", "Říjen", "Listopad", "Prosinec"],
								"firstDay" : 1
							});

			$("<div />").append(response).dialog({
				modal : true,
				resizable : false,
				draggable : false,
				width : 700
			});
            
            response.find("#mistake-category").change();
		}, "html");
	}
    
	$("#mistake-list button").click(openMistake);
	$("#toggle-filter").click(toggleFilter);
	$("#new-mistake").click(createMistake);
});