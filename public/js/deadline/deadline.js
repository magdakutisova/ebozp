$(function () {
	var clientId = $("#CLIENT_ID").val();
	
	// inicializace formulare lhuty
	
	// prepinani periodicke a neperiodicke lhuty
	function togglePeriodic() {
		var field = $("#deadline-period");
		
		if ($(this).filter(":checked").length) {
			field.removeAttr("disabled");
		} else {
			field.attr("disabled", "disabled");
		}
	}
	
	// prepinani zodpovedne osoby guard/neguard
	function toggleGuard() {
		submitDeadlineForm();
	}
	
	// odesle formular pro aktualizaci nekterych hodnot
	function submitDeadlineForm() {
		$("#deadlineform").removeAttr("action").submit();
	}
	
	function openEdit() {
		// nacteni id lhuty a sestaveni adresy
		var deadId = $(this).parent().find(":hidden").val();
		var url = "/deadline/deadline/edit?clientId=" + clientId + "&deadlineId=" + deadId;
		
		// otevreni iframe dialogu s editaci lhuty
		$.iframeDialog(url, 800, 400, "Úprava lhůty");
	}
	
	function openGet() {
		// nacteni id lhuty a sestaveni adresy
		var deadId = $(this).parent().find(":hidden").val();
		var url = "/deadline/deadline/get?clientId=" + clientId + "&deadlineId=" + deadId;
		
		// otevreni iframe dialogu s editaci lhuty
		$.iframeDialog(url, 800, 400, "Úprava lhůty");
	}
	
	function toggleFilter() {
		$("#deadlinefilter").toggle();
	}
	
	function filter() {
		// zobrazeni vsech zaznamu lhut
		var table = $("#deadlinetable");
		table.find("tbody").show();
		
		// nacteni filtracnich podminek
		var filterConds = {};
		
		$(this).find("select").each(function () {
			var context = $(this);
			var val = context.find("option:checked").text();
			
			if (val == "---") return;
			
			filterConds[context.attr("name")] = val;
		});
		
		// skryti tech lhut, ktere nevyhovuji podminkam
		table.find("tbody").each(function () {
			// nalezeni hodnot
			var isOk = true;
			var context = $(this);
			
			for (var item in filterConds) {
				var val = context.find(":hidden[name='" + item + "']").val();
				
				if (val != filterConds[item]) isOk = false;
			}
			
			if (!isOk) context.hide();
		});
		
		return false;
	}
	
	$("#deadline-is_period").click(togglePeriodic);
	$("#deadline-resp_type").change(toggleGuard);
	$("#deadline-subsidiary_id,#deadline-deadline_type").change(submitDeadlineForm);
	$("#deadlinetable tbody tr td button").filter("[name='edit']").click(openEdit).end().filter("[name='get']").click(openGet);
	$("#deadline-filter-toggle").click(toggleFilter);
	$("#deadlinefilter").submit(filter);
	
	$("#deadline-done_at").datepicker({
		"dateFormat" : "yy-mm-dd",
		"dayNamesMin" : ["Po", "Út", "St", "Čt", "Pá", "So", "Ne"],
		"monthNames" : ["Leden", "Únor", "Březen", "Duben", "Květen", "Červen", "Červenec", "Srpen", "Září", "Říjen", "Listopad", "Prosinec"]
	});
	
});