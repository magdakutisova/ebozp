$(function () {
	
	/*
	 * prepnuti zobrazeni dat
	 */
	function switchContent() {
		var contents = $("#mistakes-forms,#mistakes-others");
		var val = $(this).val();
		
		contents.hide("fast");
		
		switch (val) {
		case "ALL":
			contents.show("fast");
			break;
			
		case "FORM":
			contents.filter("#mistakes-forms").show("fast");
			break;
			
		case "OTHER":
			contents.filter("#mistakes-others").show("fast");
			break;
		}
	}
	
	function showDetails() {
		// nactnei id neshody
		var mistakeId = $(this).parent().find("input[name='mistakeId']").val();
		
		var url = "/klient/" + CLIENT_ID + "/mistake/" + mistakeId + "/html";
		
		$.iframeDialog(url, 800, 400);
	}
	
	function openDeadline() {
		var deadlineId = $(this).parent().find(":hidden").val();
		
		var url = "/audit/audit/getdead.html?deadlineId=" + deadlineId + "&auditId=" + AUDIT_ID;
		
		$.iframeDialog(url, 800, 400, "Lhůta");
	}
	
	$("#tabs").tabs();
	$("#display-mistakes").change(switchContent);
	$("#tab-others button,#tab-forms button").click(showDetails);
	$("#deadlinetable button[name='show']").click(openDeadline);
});