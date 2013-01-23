$(function () {
	
<<<<<<< HEAD
	// otevreni dialogu s editaci neshody
	function openMistake() {
		
		var mistakeId = $(this).parent().find("input[name='mistakeId']").val();
		
		var url = "/klient/" + CLIENT_ID + "/pobocka/" + SUBSIDIARY_ID + "/check/" + CHECK_ID + "/mistake/ " + mistakeId + "/edit";
		
		$.iframeDialog(url, 800, 600, "Neshoda");
	}
	
	function changeSubmitStatus(response) {
		var parent = $("#mistake-table :hidden[name='mistakeId']").filter("[value='" + response.mistake.id + "']").parent();
		var button = parent.find("button").filter("[name='submit-mistake'],[name='unsubmit-mistake']");
		button.unbind("click");
		
		// vyhodnoceni stavu odeslani
		if (Number(response.assoc.submit_status)) {
			button.text("Nepotvrzovat");
			button.attr("name", "unsubmit-mistake");
			button.click(unsubmitMistake);
		} else {
			button.text("Potvrdit");
			button.attr("name", "submit-mistake");
			button.click(submitMistake);
		}
	}
	
	function submitMistake() {
		var mistakeId = $(this).parent().find("input[name='mistakeId']").val();
		var url = "/klient/" + CLIENT_ID + "/pobocka/" + SUBSIDIARY_ID + "/check/" + CHECK_ID + "/mistake/" + mistakeId + "/submit";
		
		$.get(url, {}, changeSubmitStatus, "JSON");
	}
	
	function unsubmitMistake() {
		var mistakeId = $(this).parent().find("input[name='mistakeId']").val();
		var url = "/klient/" + CLIENT_ID + "/pobocka/" + SUBSIDIARY_ID + "/check/" + CHECK_ID + "/mistake/" + mistakeId + "/unsubmit";
		$.get(url, {}, changeSubmitStatus, "JSON");
	}
	
	function checkAllSubmited() {
		if ($("#mistake-table button").filter("[name='submit-mistake']").length) return confirm("Některé neshody nejsou povrzeny.\nSkutečně chcete prověrku uzavřít?");
		
		return true;
	}
	
	$("#tabs").tabs();
	var buttons = $("#mistake-table button");
	buttons.filter("[name='edit-mistake']").click(openMistake);
	buttons.filter("[name='submit-mistake']").click(submitMistake);
	buttons.filter("[name='unsubmit-mistake']").click(unsubmitMistake);
	$("#checkcoordsubmit").submit(checkAllSubmited);
});
=======
	$("#tabs").tabs();
});
>>>>>>> d28d62044ee1e530cfd243c786ddbb6de6144f2c
