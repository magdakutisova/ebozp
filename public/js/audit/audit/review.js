$(function () {
	var clientId = $("#clientId").val();
	var subsidiaryId = $("#subsidiaryId").val();
	var auditId = $("#auditId").val();
	var formId = $("#formId").val();
	
	$("#audit-done_at").datepicker({
		"dateFormat" : "dd. mm. yy",
		"dayNamesMin" : ["Po", "Út", "St", "Čt", "Pá", "So", "Ne"],
		"monthNames" : ["Leden", "Únor", "Březen", "Duben", "Květen", "Červen", "Červenec", "Srpen", "Září", "Říjen", "Listopad", "Prosinec"]
	});
	
	function openMistake() {
		var mistakeId = $(this).parent().find(":hidden[name='mistakeId']").val();
		
		// sestaveni routy
		var url = "/klient/" + clientId + "/audit/" + auditId + "/mistake/" + mistakeId + "/html";
		
		// sestaveni dialogu
		var iframe = $("<iframe width='700px' height='400px'>").attr("src", url);
		
		$("<div>").append(iframe).dialog({
			modal: true,
			width: "730px",
			draggable: false,
			title : "Neshoda"
		});
	}
	
	function toggleMistakeResponse(response) {
		// nacteni radku neshody
		var cell = $("input[name='mistakeId']").filter("[value='" + response.mistake.id + "']").parent();
		
		// nastaveni tlacitka
		var button = cell.find("button[name='mistake-submiter']");
		
		// vyhodnoceni submitu
		if (response.assoc.submit_status == "1") {
			button.text("Nepotvrzovat");
		} else {
			button.text("Potvrdit");
		}
		
		// nastaveni stavu
		cell.find(":hidden[name='submitStatus']").val(response.assoc.submit_status);
	}
	
	function toggleMistakeSubmit() {
		var context = $(this).parents("td:first");
		
		var status = context.find(":hidden[name='submitStatus']").val();
		status = Number(status);
		
		var mistakeId = context.find(":hidden[name='mistakeId']").val();
		
		// sestaveni zakladni adresy
		var url = "/klient/" + clientId + "/pobocka/" + subsidiaryId + "/audit/" + auditId + "/mistake/" + mistakeId;
		
		// vyhodnoceni stavu
		if (status == 1) {
			url += "/unsubmit.json";
		} else {
			url += "/submit.json";
		}
		
		// odslani dat
		$.post(url, null, toggleMistakeResponse, "json");
	}
	
	// kontrola odeslani
	function checkSubmit() {
		// nacteni dat
		var data = $("input[name='submitStatus'][value='0']");
		
		if (data.length) {
			return confirm("Některé neshody nebyly potvrzeny. Přesto chcete audit uzavřít?");
		}
		
		return true;
	}
	
	$("#mistakes-forms,#mistakes-others").find("button[name='edit-mistake']").click(openMistake);
	$("#mistakes-forms,#mistakes-others").find("button[name='mistake-submiter']").click(toggleMistakeSubmit);
	$("#auditcoordsubmit").submit(checkSubmit);
	$("#tabs").tabs();
});