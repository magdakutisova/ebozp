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
		
	}
	
	$("#deadline-is_period").click(togglePeriodic);
	$("#deadline-resp_type").change(toggleGuard);
	$("#deadline-subsidiary_id,#deadline-deadline_type").change(submitDeadlineForm);
	$("#deadlinetable tbody tr td button").filter("[name='edit']").click(openEdit).end().filter("[name='get']").click(openGet);
});