$(function() {
	
	var removeButtonSet = {
			icons: {
				primary : "ui-icon-close"
			},
			
			"text" : false
	};
	
	function createItem(name) {
		var li = $("<li></li>");
		
		$("<textarea cols='4' rows='50'></textarea>").attr("name", name).appendTo(li);
		$("<button>Smazat</button>").click(removeDiscuss).appendTo(li).button(removeButtonSet);
		
		return li;
	}
	
	function addDiscuss() {
		// vytvoreni polozky
		li = createItem("discussed[content][]");
		
		var target = $("#discuss-list");
		li.appendTo(target);
		target.sortable("refresh");
		
	}
	
	function addChange() {
		// vytvoreni polozky
		li = createItem("change[content][]");
		
		var target = $("#change-list");
		li.appendTo(target);
		target.sortable("refresh");
	}
	
	function addOrder() {
		// vytvoreni polozky
		li = createItem("order[content][]");
		
		var target = $("#order-list");
		li.appendTo(target);
		target.sortable("refresh");
	}
	
	function addOutput() {
		// vytvoreni polozky
		li = createItem("output[content][]");
		
		var target = $("#output-list");
		li.appendTo(target);
		target.sortable("refresh");
	}
	
	function removeDiscuss() {
		if (confirm("Skutečně odebrat položku?")) $(this).parents("li:first").remove();
	}
	
	$("#watch-tabs").tabs();
	$("#discuss-list,#change-list,#order-list,#output-list").sortable();
	$("#discuss-list button,#change-list button,#order-list button,#output-list button").click(removeDiscuss).button(removeButtonSet);
	$("#add-discuss").click(addDiscuss);
	$("#add-change").click(addChange);
	$("#add-order").click(addOrder);
	$("#add-output").click(addOutput);
	$("#mistake-will_be_removed_at,#watch-watched_at").datepicker({
		"dateFormat" : "dd. mm. yy",
		"dayNamesMin" : ["Po", "Út", "St", "Čt", "Pá", "So", "Ne"],
		"monthNames" : ["Leden", "Únor", "Březen", "Duben", "Květen", "Červen", "Červenec", "Srpen", "Září", "Říjen", "Listopad", "Prosinec"]
	});
});