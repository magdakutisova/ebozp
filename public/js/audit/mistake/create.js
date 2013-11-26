$(function () {
	
	// zapise kategorie
	function writeCategories(response) {
		var target = $("#subcategories");
		
		target.children().remove();
		
		for (var i in response) {
			$("<option>").attr("value", response[i]).appendTo(target);
		}
	}
	
	// metoda dynamickeho nacitani kategorii
	function checkCategory() {
		var context = $(this);
		var subContext = $("select#mistake-subcategory");
		/*
		// kontrola "jineho"
		if (context.val() == "") {
			context.replaceWith($("<input type='text' />").attr("id", context.attr("id")).attr("name", context.attr("name")));
			
			subContext.replaceWith($("<input type='text' />").attr("id", subContext.attr("id")).attr("name", subContext.attr("name")));
		} else {*/
			// skryti vsech skupin
			subContext.children("optgroup").hide();
			
			// zobrazeni skupiny odpovidajici kategorii
			subContext.children("optgroup[label='" + context.val() + "']").show();
		//}
	}
	
	function checkSubcategory() {
		var context = $(this);
		
		if (context.val() == "") {
			context.replaceWith($("<input type='text' />").attr("id", context.attr("id")).attr("name", context.attr("name")));
		}
	}
	
	$("#mistake-will_be_removed_at").datepicker({
		"dateFormat" : "dd. mm. yy",
		"dayNamesMin" : ["Ne", "Po", "Út", "St", "Čt", "Pá", "So"],
		"monthNames" : ["Leden", "Únor", "Březen", "Duben", "Květen", "Červen", "Červenec", "Srpen", "Září", "Říjen", "Listopad", "Prosinec"],
		"firstDay" : 1
	});
	
	$("#mistake-category").change(checkCategory).change();
	//$("#mistake-subcategory").change(checkSubcategory);
});