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
	
	function showMistake() {
		var mistakeId = $(this).parent().find(":hidden[name='mistakeId']").val();
		
		// sestaveni routy
		var url = "/klient/" + clientId + "/mistake/" + mistakeId + "/html";
		
		// sestaveni dialogu
		var iframe = $("<iframe width='700px' height='400px'>").attr("src", url);
		
		$("<div>").append(iframe).dialog({
			modal: true,
			width: "730px",
			draggable: false,
			title : "Neshoda"
		});
	}
	
	function sendMistakes() {
		/**
		 * vygenerovani seznamu zmen
		 */
		var changes = {};
		
		$(".semaphore").each(function () {
			var newState = $(this).semaphore("status");
			var oldState = $(this).next().val();
			
			if (Number(newState) != Number(oldState)) {
				// nacteni id mistake
				var mistakeId = $(this).parent().find(":hidden[name='mistakeId']").val();
				
				changes[mistakeId] = newState;
			}
		});
		
		// odeslani na server
		$.post("/klient/" + clientId + "/pobocka/" + subsidiaryId + "/audit/" + auditId + "/mistakes/setstatus", { status: changes }, function () {
			alert("Neshody byly uloženy");
		});
	}
	
	function workplaceSelect() {
		var workplaceId = $(this).val();
		var url = "/audit/workplace/setplace?workplaceId=" + workplaceId + "&clientId=" + clientId + "&auditId=" + auditId;
		
		location.href = url;
	}
	
	function toggleWorkplace() {
		$("#new-workplace-form").toggle();
	}
	
	$("#table-mistakes button[name='edit-mistake'],#workplace-mistakes button[name='edit']").click(openMistake);
	$("#table-mistakes button[name='get-mistake'],#workplace-mistakes button[name='show']").click(showMistake);
	$("#tabs").tabs();
	
	var semaphores = $(".semaphore");
	semaphores.semaphore();
	semaphores.each(function () {
		var input = $(this).next();
		
		$(this).semaphore("set", input.val());
	});
	
	$("#save-mistakes").click(sendMistakes);
	$("#paginator-workplace").change(workplaceSelect);
	$("#new-workplace").click(toggleWorkplace);
});