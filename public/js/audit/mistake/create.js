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
	function loadCategories() {
		var category = $(this).val();
		
		$.get("/audit/category/children.json", {"name" : category}, writeCategories, "json")
	}
	
	$("#mistake-will_be_removed_at").datepicker({
		"dateFormat" : "dd. mm. yy",
		"dayNamesMin" : ["Po", "Út", "St", "Čt", "Pá", "So", "Ne"],
		"monthNames" : ["Leden", "Únor", "Březen", "Duben", "Květen", "Červen", "Červenec", "Srpen", "Září", "Říjen", "Listopad", "Prosinec"]
	});
	
	$("#mistake-category").change(loadCategories);
});