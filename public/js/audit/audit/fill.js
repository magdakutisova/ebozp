$(function () {
	var questionary = new QUESTIONARY.Questionary();
	
	questionary.setFromArray(QDATA);
	
	function render() {
		var target = $("#questionary");
		
		target.children().remove();
		questionary.render().appendTo(target);
		
		// nastaveni skryvaci funkce
		target.find(":radio").each(function () {
			$(this).click(solveMistake);
		}).filter(":checked").click();
		
		$("#questionary").find(":text[name$='removed']").datepicker({
			"dateFormat" : "dd. mm. yy",
			"dayNamesMin" : ["Po", "Út", "St", "Čt", "Pá", "So", "Ne"],
			"monthNames" : ["Leden", "Únor", "Březen", "Duben", "Květen", "Červen", "Červenec", "Srpen", "Září", "Říjen", "Listopad", "Prosinec"]
		});
	}
	
	function solveMistake() {
		var mistake = $(this).parents(".questionary-item:first").next();
		
		if ($(this).val() == "N") {
			mistake.show();
		} else {
			mistake.hide();
		}
	}
	
	function submitForm() {
		var qContent = window.JSON.stringify(questionary.getValues());
		
		// nastaveni inputu
		$("#audit-content").val(qContent);
		
		return true;
	}
	
	$("#auditfill").submit(submitForm);
	render();
});