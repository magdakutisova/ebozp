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

		$.iframeDialog(url, 745, 500, "Neshoda", "refresh");
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
        
        var url = "/audit/mistake/create.html?clientId=" + CLIENT_ID + "&mistake[subsidiary_id]=" + SUBSIDIARY_ID;
        
        $.iframeDialog(url, 800, 500, "Nová neshoda", "refresh");
        
        return;
	}
    
	$("#mistake-list button").click(openMistake);
	$("#toggle-filter").click(toggleFilter);
	$("#new-mistake").click(createMistake);
});