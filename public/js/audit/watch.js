$(function() {
	
	var clientId = $("#CLIENTID").val();
	var watchId = $("#WATCHID").val();
	
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
	
	function openMistake() {
		// nacteni id lhuty a sestaveni adresy
		var mistakeId = $(this).parent().find(":hidden[name='mistakeId']").val();
		var url = "/audit/mistake/" + $(this).attr("name") + ".html?__hideRemoved=1&clientId=" + clientId + "&mistakeId=" + mistakeId;
		
		// otevreni iframe dialogu s editaci lhuty
		$.iframeDialog(url, 800, 400, "Neshoda", "refresh");
	}
	
	function openDeadline() {
		var deadlineId = $(this).attr("g7:deadlineId")
		
		var url = "/audit/watch/getdead.html?deadlineId=" + deadlineId + "&watchId=" + watchId;
		
		$.iframeDialog(url, 800, 400, "Lhůta", "refresh");
	}
	
	function newDeadline() {
		$("#deadlineform-container").toggle();
	}
	
	function editDeadline() {
		var deadlineId = $(this).attr("g7:deadlineId");
		
		var url = "/deadline/deadline/edit.html?deadlineId=" + deadlineId + "&clientId=" + clientId;
		
		$.iframeDialog(url, 800, 400, "Lhůta", "refresh");
	}
	
	function openDeadList() {
		var url = "/audit/watch/deadlist.html?watchId=" + watchId;
		
		$.iframeDialog(url, 800, 400, "Vyberte lhůty", "refresh");
	}
    
    function createMistake() {
        var url = $(this).attr("action");
        
        $.iframeDialog(url, 800, 400, "Nová neshoda", "refresh");
        
        return false;
    }
	
	function checkContact() {
		if ($(this).val() == "0") {
			$("#other-contact-person").show();
		} else {
			$("#other-contact-person").hide();
		}
	}
	
	$("#watch-tabs").tabs({
		activate : function (e, ui) {
			var href = ui.newTab.find("a").attr("href");
			var y = window.pageYOffset;
			
			location.replace(href);
			
			window.scrollTo(window.pageXOffset, y);
		}
	});
	
	$("#discuss-list,#change-list,#order-list,#output-list").sortable();
	$("#discuss-list button,#change-list button,#order-list button,#output-list button").click(removeDiscuss).button(removeButtonSet);
	$("#add-discuss").click(addDiscuss);
	$("#add-change").click(addChange);
	$("#add-order").click(addOrder);
	$("#add-output").click(addOutput);
	$("#mistake-will_be_removed_at,#watch-watched_at").datepicker({
		"dateFormat" : "dd. mm. yy",
		"dayNamesMin" : ["Ne", "Po", "Út", "St", "Čt", "Pá", "So"],
		"monthNames" : ["Leden", "Únor", "Březen", "Duben", "Květen", "Červen", "Červenec", "Srpen", "Září", "Říjen", "Listopad", "Prosinec"]
	});
	
	$("#deadline-done_at").datepicker({
		"dateFormat" : "yy-mm-dd",
		"dayNamesMin" : ["Ne", "Po", "Út", "St", "Čt", "Pá", "So"],
		"monthNames" : ["Leden", "Únor", "Březen", "Duben", "Květen", "Červen", "Červenec", "Srpen", "Září", "Říjen", "Listopad", "Prosinec"],
		"firstDay" : 1
	});
	
	$("#close-watch").button();
	
	$("#watch-watched_at").datepicker();
	$("#mistakes button[name='get'],#mistakes button[name='edit']").click(openMistake);
	$("#deadlinetable button[name='show']").click(openDeadline);
	$("#deadlinetable button[name='edit']").unbind("click").click(editDeadline);
	$("#add-deadlines").click(openDeadList);
	
	$("#watch-contactperson_id").change(checkContact).change();
	$("#new-deadline").click(newDeadline);
    $("#create-new-mistake-form").submit(createMistake);
});