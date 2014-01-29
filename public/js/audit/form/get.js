$(function () {
	
	/*
	 * funkce prepnuti zobrazeni skupin
	 */
	function switchGroup() {
		var group = $(this).val();
		
		// kontrola skupiny
		if (group == "ALL") {
			// zobrazeni vsech skupin
			$("#group-contents>div").show("fast");
		} else {
			// skryti skupin
			$("#group-contents>div").hide("fast");
			
			// zobrazeni skupiny
			$("#" + group).show("fast");
		}
	}
	
	/*
	 * filtrace dle odpovedi
	 */
	function switchAnswer() {
		// nacteni hodnoty
		var value = $(this).val();
		
		value = Number(value);
		
		// zobrazeni vseho
		$("#group-contents tr").show();
		
		// vyhodnoceni dat
		switch (value) {
		case 1:
			// skryti ANO a NT
			$(".auditforma,.auditformnt").hide();
			break;
			
		case 2:
			// skryti NE a NT
			$(".auditformn,.auditformnt").hide();
			break;
			
		case 3:
			// skryti NE a ANO
			$(".auditformn,.auditforma").hide();
			break;
		}
	}
    
    function showNote(e) {
        $(this).next().show();
    }
    
    function hideNote() {
        $(this).next().hide();
    }
	
	$("#group-navi").change(switchGroup);
	$("#filter-navi").change(switchAnswer);
    $("#group-contents table tbody a.note").mouseover(showNote).mouseout(hideNote);
});