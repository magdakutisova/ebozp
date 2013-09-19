$(function () {
	
	var oldType = null;
	
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
		// nacteni pobocky
		var subsidiaryId = Number($("#mistake-subsidiary_id").val());
		var data = {
				clientId : CLIENTID
		};
		
		
		if (subsidiaryId)
			data["mistake[subsidiary_id]"] = subsidiaryId;
		
		// nacteni dat a otevreni dialogu
		$.get("/audit/mistake/create.html", data, function (response) {
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
	
	function filterMistakes() {
		// zobrazeni vsech radku
		var tbodies = $("#mistake-list tbody");
		
		tbodies.show();
		
		// zapis filtru
		var filters = {};
		
		$("#mistake-category,#mistake-subcategory,#mistake-workplace,#mistake-weight").each(function () {
			// zjisteni hodnoty
			var val = $(this).find("option:checked").text();
			
			// pokud neni vybrana hodnota, pak se nic nedeje
			if (val == "---") return;
			
			// zapis filtracni hodnoty
			var name = $(this).attr("id").split("-")[1];
			filters[name] = val;
		});
		
		// zapis filtru pobocky
		var subsidiaryId = Number($("#mistake-subsidiary_id").val());
		
		if (subsidiaryId) {
			filters["subsidiary_id"] = subsidiaryId;
		}
		
		// skryti polozek, ktere nevyhovuji filtracni podmince
		tbodies.each(function () {
			// vyhodnoceni zda radek vyhovuje podmince
			var isOk = true;
			
			for (var n in filters) {
				// kotrnola hodnoty
				if ($(this).find(":hidden[name='" + n + "']").val() != filters[n]) {
					isOk = false;
				}
			}
			
			if (!isOk) {
				$(this).hide();
			}
		});
		
		// vyhodnoceni mnoziny neshod
		if (oldType == $("#mistake-type").val()) {
			return false;
		} else {
			return true;
		}
	}
	
	// nastaveni puvodniho typu filtrovani
	oldType = $("#mistake-filter").val();
	
	$("#mistake-list button").click(openMistake);
	$("#toggle-filter").click(toggleFilter);
	$("#new-mistake").click(createMistake);
	$("#mistake-subsidiary_id").change(loadWorkplaces);
	$("#mistakefilter").submit(filterMistakes);
	
	filterMistakes();
});