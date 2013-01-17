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
	
	$("#tabs").tabs();
	$("#display-mistakes").change(switchContent);
});