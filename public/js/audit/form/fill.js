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
			$(this).parents("tbody:first").find(">tr:gt(0)").remove();
			$(this).parents("tr:first").find("button[name$='[mistake]']").text("Neshoda");
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
		
		// kontrola jeslti je neshoda zobrazena
		var tbody = $(this).parents("tbody:first");
		var trs = tbody.find("tr:gt(0)");
		
		if (trs.length) {
			trs.remove();
			$(this).text("Neshoda");
			return;
		}
		
		// zmena popisku
		$(this).text("Skrýt neshodu");
		
		var mistakeId = $(this).parent().find(":hidden").val();
		
		// sestaveni routy
		var url = "/klient/" + clientId + "/audit/" + auditId + "/mistake/" + mistakeId + "/html";
		
		// sestaveni dialogu
		var iframe = $("<iframe width='700px' height='750px'>").attr("src", url);
		var tr = $("<tr></tr>").appendTo(tbody);
		$("<td></td>").attr("colspan", 6).appendTo(tr).append(iframe);
		
	}
	
	function toggleComment() {
		// nacteni potrebnych dat
		var comment = $(this).parent();
		var radios = comment.parent().find(":radio").parent().parent();
		
		// vyhodnoceni jestli se jedna o zobrazeni nebo skryti
		if (comment.attr("colspan") == 4) {
			// bude se zmensovat
			comment.attr("colspan", 1).find("textarea").css("width", "100px");
			
			radios.show("slow");;
		} else {
			// bude se zvetsovat
			radios.hide("fast", false, function () {
				comment.attr("colspan", 4).find("textarea").css("width", "556px");
			});
		}
	}
	
	function saveChange() {
		// ziskani id zaznamu a kontextu radku
		var context = $(this);
		var someName = context.attr("id");
		
		var nameArr = someName.split("-");
		
		// priprava dat k odeslani
		var data = {
				recordId : nameArr[1]
		};
		
		// vyhodnoceni typu zmeneneho policka
		if (nameArr[2] == "score") {
			data["score"] = Number(context.val());
		} else {
			data["note"] = context.val();
		}
		
		// odeslani dat na server
		$.post("/audit/form/saveone.json", data, $.noop);
	}
	
	$("#form-fill-group").find(":radio").click(setVisibility);
	$("#navigation-page").change(changePage);
	$("#form-fill-group button[name$='[mistake]']").click(openMistake);
	$("#form-fill-group textarea").focus(toggleComment).blur(toggleComment);
	$("#form-fill-group textarea").change(saveChange);
	$("#form-fill-group :radio").click(saveChange);
});