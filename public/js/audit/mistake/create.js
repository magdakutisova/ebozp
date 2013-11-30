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

		// skryti vsech skupin
		subContext.children("optgroup").hide();
		
		// zobrazeni skupiny odpovidajici kategorii
		subContext.children("optgroup[label='" + context.val() + "']").show();
	}
	
	function checkSubcategory() {
		var context = $(this);
		
		if (context.val() == "") {
			context.replaceWith($("<input type='text' />").attr("id", context.attr("id")).attr("name", context.attr("name")));
		}
	}
    
    function validate() {
        // kontrola datumu
        var dateStr = $(this).find("input[name='mistake[will_be_removed_at]']").val();
        
        if (dateStr.length == "") return true;
        
        // prevod na cislo
        var dateArr = dateStr.split(". ");
        var date = Number(dateArr[2] + dateArr[1] + dateArr[0]);
        
        var dateObj = new Date();
        var today = dateObj.getFullYear() * 10000 + (dateObj.getUTCMonth() + 1) * 100 + dateObj.getUTCDate();
        
        if (date < today) {
            var row = $(this).find("input[name='mistake[will_be_removed_at]']").parents("tr:first");
            row.addClass("error");
            
            row.find("ul").remove();
            row.find("td:last").append($("<ul>").append("<li>").text("Navrhované datum odstranění nesmí být dřív než dnešní datum"))
            
            return false;
        }
        
        return true;
    }
	
	$("#mistake-will_be_removed_at").datepicker({
		"dateFormat" : "dd. mm. yy",
		"dayNamesMin" : ["Ne", "Po", "Út", "St", "Čt", "Pá", "So"],
		"monthNames" : ["Leden", "Únor", "Březen", "Duben", "Květen", "Červen", "Červenec", "Srpen", "Září", "Říjen", "Listopad", "Prosinec"],
		"firstDay" : 1
	});
    
    $("#mistakepost").submit(validate);
	
	$("#mistake-category").change(checkCategory).change();
	//$("#mistake-subcategory").change(checkSubcategory);
});