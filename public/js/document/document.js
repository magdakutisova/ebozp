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
	
	function changeDirWithFiles() {
		var id = $(this).next().val();
		
		loadDirectoryAndFiles(id);
	}
	
	function selectDir() {
		var context = $(this);
		var id = context.next().val();
		var name = context.text();
		
		$("#attach-selected").text(name);
		$("#dirId").val(id);
		
		$("#attach-submit").removeAttr("disabled");
	}
	
	function writeChildDirs(target, dirs, click, dblclick) {
		target.children().remove();
		
		for (var i in dirs) {
			var dir = dirs[i];
			
			$("<li>").append(
					$("<span class='directory'>")
			).append(
					$("<a>").text(dir.name).dblclick(dblclick).click(click)
			).append(
					$("<input type='hidden' name='directoryId'>").val(dir.id)
			).appendTo(target);
		}
	}
	
	function writePath(target, path, currentDir, click, dblclick) {
		target.children().remove();
		
		for (var i in path) {
			var dir = path[i];
			
			$("<span>").append(
					$("<a>").text(dir.name + "/").dblclick(dblclick).click(click)
			).append(
					$("<input type='hidden' name='directoryId'>").val(dir.id)
			).appendTo(target);
		}
		
		// zapis aktualni polozky
		$("<span>").append(
				$("<a>").text(currentDir.name + "/").dblclick(dblclick).click(click)
		).append(
				$("<input type='hidden' name='directoryId'>").val(currentDir.id)
		).appendTo(target);
	}
	
	function loadDirectory(id) {
		$.get("/document/directory/get.json", { directoryId : id}, function (response) {
			// zapis rodicovskych adresaru
			var target = $("#attach-content");
			writeChildDirs(target, response.childDirs, selectDir, changeDir);
			
			// zapis cesty
			target = $("#attach-path");
			writePath(target, response.path, response.directory, selectDir, changeDir);
			
			// anulace vybraneho adresare
			$("#attach-submit").attr("disabled", "disabled");
			$("#attach-selected").text("");
			$("#dirId").val(0);
		}, "json");
	}
	
	function setDocument() {
		// nacteni informaci
		var context = $(this).parents("li:first");
		
		var id = context.find(":hidden").val();
		var name = context.find("a").text();
		
		// zapis dat
		$("#attach-selected").text(name);
		$("#fileId1").val(id);
	}
	
	function writeChildFiles(target, files, click, dblclick) {
		for (var i in files) {
			var file = files[i];
			
			$("<li>").append(
					$("<span class='document'>")
			).append(
					$("<a>").text(file.name).dblclick(dblclick).click(click)
			).append(
					$("<input type='hidden' name='fileId'>").val(file.id)
			).appendTo(target);
		}
	}
	
	function checkAttach() {
		if ($(this).find(":hidden").val() == "0") {
			return confirm("Stávající soubor bude odpojen.\nChcete pokračovat?");
		}
	}
	
	function loadDirectoryAndFiles(id) {
		$.get("/document/directory/get.json", { directoryId : id}, function (response) {
			// zapis rodicovskych adresaru
			var target = $("#attach-content");
			writeChildDirs(target, response.childDirs, changeDirWithFiles, $.noop);
			writeChildFiles(target, response.childDocs, setDocument, $.noop);
			
			// zapis cesty
			target = $("#attach-path");
			writePath(target, response.path, response.directory, changeDirWithFiles, $.noop);
			
			// anulace vybraneho adresare
			$("#attach-selected").text("-");
			$("#directoryId1").val(id);
		}, "json");
	}
	
	function rootChange() {
		loadDirectory($(this).val());
	}
	
	function generateConfirm(message) {
		return function () { return confirm(message); };
	}
	
	function openEditDirForm() {
		createDialog($("#form-edit-dir"), "450px", "Úprava adresáře", undefined);
	}
	
	function switchUploadUrl() {
		// nacteni stavajici akce a zjisteni id klienta a adresare
		var oldUrl = $("#formupload").attr("action");
		var pattern = /^\/klient\/([0-9]+)\/directory\/([0-9]+)\/.*/;
		
		var result = oldUrl.match(pattern);
		var url;
		
		// vyhodnoceni zaskrtnuti
		if ($(this).filter(":checked").length) {
			// multiupload
			url = "/klient/" + result[1] + "/directory/" + result[2] + "/multiupload";
		} else {
			// klasicky upload
			url = "/klient/" + result[1] + "/directory/" + result[2] + "/post-document";
		}
		
		$("#formupload").attr("action", url);
	}
	
	function editDocSlot() {
		// nacteni id slotu a sestaveni url
		var id = $(this).attr("name").split("-")[1];
		var url = "/document/documentation/edit.html";
		
		$.get(url, { documentationId : id, clientId : CLIENT_ID, TYPE : TYPE }, function (response) {
			response = $(response);
			response.appendTo("body");
			response.find("#attach-file").submit(checkAttach);
			
			// aktivace adresaru
			var rootId = response.find("#root-id").val();
			loadDirectoryAndFiles(rootId);

			response.find("select[name='documentation[name]']").change(replaceSelect);
			
			createDialog(response, 800, "Editace dokumentace");
		});
	}
	
	function replaceSelect() {
		var src = $(this); //$("select#documentation-name");
		if (src.val() == "")
			src.replaceWith($("<input type='text' id='documentation-name', name='documentation[name]' required='true' />"));
	}
	
	$("#rename-file").click(openRenameForm).button({ "icon-only" : true, icons : { primary : "ui-icon-pencil" }, "text" : false });
	$("#edit-directory").click(openEditDirForm).button( { "icon-only" : true, icons : { primary : "ui-icon-pencil"}, "text" : false});
	
	$(".button").button();
	$("#attach-dir").click(openAttachForm);
	$("#root-list").change(rootChange);
	$("#multiupload").change(switchUploadUrl);
	$("#docs-table button").click(editDocSlot).button({ "icon-only" : true, icons : { primary : "ui-icon-pencil" }, "text" : false });
	
	$(".dettach").click(generateConfirm("Skutečně odebrat z adresáře?")).button({ "icon-only" : true, icons : { primary : "ui-icon-close" }, "text" : false });
	
	$("select#documentation-name").change(replaceSelect).change();
});