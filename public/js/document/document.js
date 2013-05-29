$(function () {
	
	function createDialog(item, width, title, height) {
		item.dialog({
			modal: true,
			width: width,
			height : height,
			resizable : false,
			draggable : false,
			title : title
		});
	}
	
	function openRenameForm() {
		createDialog($("#rename-form"), 450, "Přejmenovat soubor");
	}
	
	function openAttachForm() {
		createDialog($("#attach-form"), 600, "Kořenový adresář", 600);
	}
	
	function changeDir() {
		var id = $(this).next().val();
		
		loadDirectory(id);
	}
	
	function selectDir() {
		var context = $(this);
		var id = context.next().val();
		var name = context.text();
		
		$("#attach-selected").text(name);
		$("#dirId").val(id);
		
		$("#attach-submit").removeAttr("disabled");
	}
	
	function loadDirectory(id) {
		$.get("/document/directory/get.json", { directoryId : id}, function (response) {
			// zapis rodicovskych adresaru
			var target = $("#attach-content");
			target.children().remove();
			
			for (var i in response.childDirs) {
				var dir = response.childDirs[i];
				
				$("<li>").append(
						$("<a>").text(dir.name).dblclick(changeDir).click(selectDir)
				).append(
						$("<input type='hidden' name='directoryId'>").val(dir.id)
				).appendTo(target);
			}
			
			// zapis cesty
			target = $("#attach-path");
			target.children().remove();
			
			for (var i in response.path) {
				var dir = response.path[i];
				
				$("<span>").append(
						$("<a>").text(dir.name + "/").dblclick(changeDir).click(selectDir)
				).append(
						$("<input type='hidden' name='directoryId'>").val(dir.id)
				).appendTo(target);
			}
			
			// zapis aktualni polozky
			$("<span>").append(
					$("<a>").text(response.directory.name + "/").dblclick(changeDir).click(selectDir)
			).append(
					$("<input type='hidden' name='directoryId'>").val(response.directory.id)
			).appendTo(target);
			
			// anulace vybraneho adresare
			$("#attach-submit").attr("disabled", "disabled");
			$("#attach-selected").text("");
			$("#dirId").val(0);
		}, "json");
	}
	
	function rootChange() {
		loadDirectory($(this).val());
	}
	
	function generateConfirm(message) {
		return function () { return confirm(message); };
	}
	
	$("#rename-file").click(openRenameForm).button({ "icon-only" : true, icons : { primary : "ui-icon-pencil" }, "text" : false });
	
	$(".button").button();
	$("#attach-dir").click(openAttachForm);
	$("#root-list").change(rootChange);
	
	$(".dettach").click(generateConfirm("Skutečně odebrat z adresáře?")).button({ "icon-only" : true, icons : { primary : "ui-icon-close" }, "text" : false });
});