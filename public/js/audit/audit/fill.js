$(function () {
	var questionary = new QUESTIONARY.Questionary();
	
	questionary.setFromArray(QDATA);
	
	function render() {
		var target = $("#questionary");
		
		target.children().remove();
		questionary.render().appendTo(target);
	}
	
	function submitForm() {
		var qContent = window.JSON.stringify(questionary.toArray());
		
		// nastaveni inputu
		$("#audit-content").val(qContent);
		
		return true;
	}
	
	$("#auditfill").submit(submitForm);
	render();
});