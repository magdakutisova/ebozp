$(function () {
	var clientId = $("#clientId").val();
	var subsidiaryId = $("#subsidiaryId").val();
	var auditId = $("#auditId").val();
	var formId = $("#formId").val();
	
	$("#audit-done_at").datepicker({
		"dateFormat" : "dd. mm. yy",
		"dayNamesMin" : ["Ne", "Po", "Út", "St", "Čt", "Pá", "So"],
		"monthNames" : ["Leden", "Únor", "Březen", "Duben", "Květen", "Červen", "Červenec", "Srpen", "Září", "Říjen", "Listopad", "Prosinec"],
		"firstDay" : 1
	});
	
	$("#deadline-done_at").datepicker({
		"dateFormat" : "yy-mm-dd",
		"dayNamesMin" : ["Ne", "Po", "Út", "St", "Čt", "Pá", "So"],
		"monthNames" : ["Leden", "Únor", "Březen", "Duben", "Květen", "Červen", "Červenec", "Srpen", "Září", "Říjen", "Listopad", "Prosinec"],
		"firstDay" : 1
	});
	
	function openMistake() {
		var mistakeId = $(this).parent().find(":hidden[name='mistakeId']").val();
		
		// sestaveni routy
		var url = "/klient/" + clientId + "/audit/" + auditId + "/mistake/" + mistakeId + "/html";
		
		$.iframeDialog(url, 730, 400, "Neshoda", "refresh");
	}
	
	function showMistake() {
		var mistakeId = $(this).parent().find(":hidden[name='mistakeId']").val();
		
		// sestaveni routy
		var url = "/klient/" + clientId + "/mistake/" + mistakeId + "/html";
		
		$.iframeDialog(url, 730, 400, "Neshoda", "refresh");
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
		var url = "/audit/workplace/setplace?workplaceId=" + workplaceId + "&clientId=" + clientId + "&auditId=" + auditId + "&subsidiaryId=" + subsidiaryId;
		
		location.href = url;
	}
	
	function toggleWorkplace() {
		$("#new-workplace-form").toggle();
	}
	
	function checkContact() {
		if ($(this).val() == "") {
			$("#other-contact-person").show();
		} else {
			$("#other-contact-person").hide();
		}
	}
	
	$("#table-mistakes button[name='edit-mistake'],#workplace-mistakes button[name='edit']").click(openMistake);
	$("#table-mistakes button[name='get-mistake'],#workplace-mistakes button[name='show']").click(showMistake);
	$("#tabs").tabs({
		activate : function (e, ui) {
			var href = ui.newTab.find("a").attr("href");
			var y = window.pageYOffset;
			
			location.replace(href);
			
			window.scrollTo(window.pageXOffset, y);
		}
	});
	
	var semaphores = $(".semaphore");
	semaphores.semaphore();
	semaphores.each(function () {
		var input = $(this).next();
		
		$(this).semaphore("set", input.val());
	});
	
	function openDeadList() {
		var deadlineId = $(this).parent().find(":hidden").val();
		
		var url = "/audit/audit/deadlist.html?deadlineId=" + deadlineId + "&auditId=" + auditId;
		
		$.iframeDialog(url, 800, 400, "Vyberte lhůty", "refresh");
	}
	
	function openDeadline() {
		var deadlineId = $(this).parent().find(":hidden").val();
		
		var url = "/audit/audit/getdead.html?deadlineId=" + deadlineId + "&auditId=" + auditId;
		
		$.iframeDialog(url, 800, 400, "Lhůta", "refresh");
	}
	
	function editDeadline() {
		var deadlineId = $(this).parent().find(":hidden").val();
		
		var url = "/deadline/deadline/edit?deadlineId=" + deadlineId + "&clientId=" + clientId;
		
		$.iframeDialog(url, 800, 400, "Lhůta", "refresh");
	}
    
    function createMistake() {
        var url = "/audit/mistake/createalone2.html?mistake[workplace_id]=0&auditId=" + auditId + "&clientId=" + clientId;
        
        $.iframeDialog(url, 800, 400, "Nová neshoda", "refresh");
        
        return false;
    }
    
    function addProgres() {
        $("#progreslist").append(
            $("<li></li>").append($("<input type='text' name='item[]'>")).append($("<input type='button' value='X'>").click(function () { if (confirm("Skutečně odstranit?")) $(this).parent().remove();}))
        );
    }
	
	$("#save-mistakes").click(sendMistakes);
	$("#paginator-workplace").change(workplaceSelect);
	$("#new-workplace").click(toggleWorkplace);
	$("#audit-contactperson_id").change(checkContact).change();
	
	$("#deadlinetable button[name='show']").unbind("click").click(openDeadline);
	$("#deadlinetable button[name='edit']").unbind("click").click(editDeadline);
	$("#add-deadlines").click(openDeadList);
    
    $("#new-mistake-form").submit(createMistake);
    $("#add-progres").click(addProgres);
    $("#progreslist").sortable();
});