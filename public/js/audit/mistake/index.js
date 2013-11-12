$(function() {

	var oldType = null;
	var oldSubsidiary = null;

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
	
	var sheet = $("<style></style>").appendTo("head")[0];
	var styleSheet = sheet.sheet == undefined ? sheet.styleSheet : sheet.sheet;
	var styles = {};
	
	function changeVis(rule, vis) {
		var index = rule.index;
		var cssRule = rule.rule;
		
		if (vis) {
			if (cssRule.style.setProperty == undefined) {
				cssRule.style.setAttribute("display", "table-row-group");
			} else {
				cssRule.style.setProperty("display", "table-row-group");
			}
		} else {
			// odebrani stareho stylu
			var className = cssRule.selectorText.substr(1);
			
			if (styleSheet.deleteRule == undefined) {
				styleSheet.removeRule(index);
			} else {
				styleSheet.deleteRule(index);
			}
			
			addClassToSheet(styleSheet, className, "display: none !important;", styles, index);
		}
	}

	function filterMistakes() {
		// vyhodnoceni mnoziny neshod
		if (oldType != $("#mistake-filter").val()) {
			return true;
		}

		// vyhodnoceni zmeny pobocky
		if (oldSubsidiary != $("#mistake-subsidiary_id").val()) {
			return true;
		}

		// nacteni filtracnich hodnot
		var workplaceElement = $("#mistake-workplace_id")[0];
		var workplace = workplaceElement[workplaceElement.selectedIndex].label;
		
		var categoryElement = $("#mistake-category")[0];
		var category = categoryElement[categoryElement.selectedIndex].label;
		
		var subcategoryElement = $("#mistake-subcategory")[0];
		var subcategory = subcategoryElement[subcategoryElement.selectedIndex].label;
		var subcategoryContainer = subcategoryElement[subcategoryElement.selectedIndex].parentNode.label;
		
		var weight = $("#mistake-weight").val();
		
		// vyhodnoceni zavaznosti
		changeVis(styles["filter-weight-1"], false);
		changeVis(styles["filter-weight-2"], false);
		changeVis(styles["filter-weight-3"], false);
		
		if (weight == "0") {
			changeVis(styles["filter-weight-1"], true);
			changeVis(styles["filter-weight-2"], true);
			changeVis(styles["filter-weight-3"], true);
		} else {
			changeVis(styles["filter-weight-" + weight], true);
		}
		
		// vyhodnoceni kategorie
		if (category == "---") {
			// zobrazeni kategorii
			for (var cat in HASH_TABLE.categories) {
				changeVis(styles[HASH_TABLE.categories[cat]], true);
			}
		} else {
			// skryti vsech kategorii
			for (var cat in HASH_TABLE.categories) {
				changeVis(styles[HASH_TABLE.categories[cat]], false);
			}
			
			// zobrazeni vybrane kategorie
			changeVis(styles[HASH_TABLE.categories[category]], true);
		}
		
		// vyhodnoceni podkategorie
		if (subcategory == "---") {
			for (var subGroup in HASH_TABLE.subcategories) {
				for (var subCat in HASH_TABLE.subcategories[subGroup]) {
					var style = styles[HASH_TABLE.subcategories[subGroup][subCat]];

					changeVis(style, true);
				}
			}
		} else {
			for (var subGroup in HASH_TABLE.subcategories) {
				for (var subCat in HASH_TABLE.subcategories[subGroup]) {
					var style = styles[HASH_TABLE.subcategories[subGroup][subCat]];
					if (subGroup == subcategoryContainer && subCat == subcategory) {
						changeVis(style, true);
					} else {
						changeVis(style, false);
					}
				}
			}
		}
		
		// vyhodnoceni pracoviste
		if (workplace == "---") {
			// vsechny pracoviste se zobrazi
			for (var work in HASH_TABLE.workplaces) {
				changeVis(styles[HASH_TABLE.workplaces[work]], true);
			}
		} else {
			for (var work in HASH_TABLE.workplaces) {
				if (work == workplace) {
					changeVis(styles[HASH_TABLE.workplaces[work]], true);
				} else {
					changeVis(styles[HASH_TABLE.workplaces[work]], false);
				}
			}
		}
		
		return false;
	}
	
	function addClassToSheet(sheet, className, definition, list, index) {
		// index dalsiho pravidla
		var ruleList;
		
		if (sheet["cssRules"] == undefined) {
			ruleList = sheet.rules;
		} else {
			ruleList = sheet.cssRules;
		}
		
		if (index == undefined) {
			index = ruleList.length;
			
		}
		
		var css = "." + className + " {" + definition + "}";
		
		if (sheet.insertRule == undefined) {
			sheet.addRule("." + className, definition, index);
		} else {
			sheet.insertRule(css, index);
		}
		
		list[className] = {
				index : index,
				rule : ruleList[index]
		};
	}
	
	// inicializace trid CSS
	function writeClasses(table) {
		for (index in table) {
			var item = table[index];
			
			if (item.constructor != String) {
				writeClasses(item);
			} else {	
				addClassToSheet(styleSheet, item, "display: table-row-group;", styles);			
			}
		}
	}
	
	writeClasses(HASH_TABLE);
	
	// zapis trid zavaznosti
	addClassToSheet(styleSheet, "filter-weight-1", "display: table-row-group", styles);
	addClassToSheet(styleSheet, "filter-weight-2", "display: table-row-group", styles);
	addClassToSheet(styleSheet, "filter-weight-3", "display: table-row-group", styles);

	// nastaveni puvodniho typu filtrovani
	oldType = $("#mistake-filter").val();
	oldSubsidiary = $("#mistake-subsidiary_id").val();

	$("#mistake-list button").click(openMistake);
	$("#toggle-filter").click(toggleFilter);
	$("#new-mistake").click(createMistake);
	$("#mistake-subsidiary_id").change(loadWorkplaces);
	$("#mistakefilter").submit(filterMistakes);

	filterMistakes();
});