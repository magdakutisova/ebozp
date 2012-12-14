$(function () {
	
	var clientId = $("#clientId").val();
	var subsidiaryId = $("#subsidiaryId").val();
	var auditId = $("#auditId").val();
	var formId = $("#formId").val();
	
	function setVisibility() {
		var score = $(this).val();
		
		var button = $(this).parents("tr:first").find(":button[name$='[mistake]']");
		
		if (score == "3") {
			button.css("visibility", "visible");
		} else {
			button.css("visibility", "hidden");
		}
	};
	
	function changePage() {
		var page = $(this).val();
		
		var location = window.location.href;
		var locationList = location.split("/");
		
		if (locationList[locationList.length - 1] !== "edit")
			locationList[locationList.length - 1] = page;
		else
			locationList.push(page);
		
		location = locationList.join("/");
		
		window.location.href = location;
	}
	
	function openMistake() {
		var mistakeId = $(this).parent().find(":hidden").val();
		
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
	
	function toggleComment() {
		// nacteni potrebnych dat
		var row = $(this).parents("tr:first");
		
		var comment = row.find("td:nth-child(5)");
		var radios = row.find(":radio").parents("td");
		radios = radios.add(row.find("td:first"));
		
		// vyhodnoceni jestli se jedna o zobrazeni nebo skryti
		if (comment.attr("colspan") == 5) {
			// bude se zmensovat
			comment.attr("colspan", 1).find("textarea").css("width", "100px");
			
			radios.show("slow");;
		} else {
			// bude se zvetsovat
			radios.hide("fast", false, function () {
				comment.attr("colspan", 5).find("textarea").css("width", "556px");
			});
		}
	}
	
	$("#form-fill-group").find(":radio").click(setVisibility);
	$("#navigation-page").change(changePage);
	$("#form-fill-group button[name$='[mistake]']").click(openMistake);
	$("#form-fill-group button[name$='[comment-button]']").click(toggleComment);
});