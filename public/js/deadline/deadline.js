$(function () {
	var clientId = $("#CLIENT_ID").val();
	var boundary = "asf513saf43asf165sa4dfs54f";
	
	var pairs = {
			"deadline_type" : "type",
			"type" : "kind",
			"kind" : "specific"
	};
	
	var parents = {
		"specific" : "kind",
		"kind" : "type",
		"type" : "deadline_type"
	};
	
	var replaced = false;
	var replacer = null;
    
    if (window.DEADLINE_CATEGORIES === undefined) DEADLINE_CATEGORIES = [];
	
	objs = {
			"deadline_type" : DEADLINE_CATEGORIES,
			"type" : null,
			"kind" : null,
			"specific" : null
	};
	
	var selects = $("#deadline-deadline_type,#deadline-type,#deadline-kind,#deadline-specific");
    var oldSpecificVal = selects.filter("#deadline-specific").val();
	var justLoaded = true;
    
	function changeSelects() {
		// nacteni jmena zmeneneho selectu
		var name = $(this).attr("id").split("-")[1];
		doChange(name);
	}
	
	function doChange(name) {
		// nacteni selektu
		var s = selects.filter("#deadline-" + name);
		k = $._data(s[0], 'events');
		// nacteni vybraneho objektu
		var selectedVal = s.val();
		
		if (selectedVal === boundary) {
			replacer = $("<input type='text' />").attr("name", s.attr("name")).attr("id", s.attr("id"));
			s.replaceWith(replacer);
			replacer.focus();
            
            if (oldSpecificVal !== null) {
                replacer.val(oldSpecificVal);
                oldSpecificVal = null;
            }
            
			replacer.blur(onBlur);
			
			replaced = true;
			
			return;
		}
		
		// vybrani kategorie
		var indexItem = objs[name][selectedVal];
        
        if (indexItem === undefined) return;
		
		/**
		 * prozatim predpokladame, ze kategorie existuje
		 */
		
		// nastaveni novych hodnot do do predka a prepsani selectu
		var nextName = pairs[name];
		
		if (nextName === undefined) {
            // prave zmenena informace je posledni z retezu - kontrola periody
            if (indexItem.period !== null && !justLoaded) {
                $("#deadline-period").val(indexItem.period);
            }
            
            return;
        }
		
		// kontrola, jestli je dalsi objekt specifikace a jestli je specifikace nahrazena textem
		if (nextName === "specific" && replaced) {
			var original = selects.filter("#deadline-" + nextName);
			replacer.replaceWith(original);
			replaced = false;
			replacer = null;
			
			original.change(changeSelects);
		}
		
		var nextObj = selects.filter("#deadline-" + nextName);
		objs[nextName] = indexItem.children;
		
		switchOptions(nextObj, indexItem.children, pairs[nextName] === undefined);
		
		doChange(nextName);
	}
	
	function onBlur() {
		var replacer = $(this);
		
		if (replacer.val() === "") {
			// prohozeni dat
			replaced = false;
			
			var original = selects.filter("[name='" + replacer.attr("name") + "']");
			replacer.replaceWith(original);
			original.change(changeSelects);
			
			// nastaveni prvniho optionu jako aktivniho a vyvoleni zmeny
			original.val(original.children(":first").attr("value"));
			original.change();
			
		}
	}
	
	function switchOptions(obj, opts, appendCustom) {
		var cnt = 0;
		
		// zaloha puvodni hodnoty
		var oldVal = obj.val();
		
		obj.children().remove();
		for (var c in opts) {
			var item = opts[c];
			cnt++;
			
			// vygenerovani polozky
			var currVal = item.value ? item.value : item.name;
			var opt = $("<option />").attr("value", currVal).text(item.name);
			
			if (currVal === oldVal) opt.attr("selected", "selected");
			
			obj.append(
					opt
			);
		}
		
		if (appendCustom) {
			obj.append($("<option />").attr("value", boundary).text("- Jiné -"));
		}
	}
	
	selects.change(changeSelects);
	selects.filter("#deadline-deadline_type").change();
    justLoaded = false;
	
	// prepinani periodicke a neperiodicke lhuty
	function togglePeriodic() {
		var field = $("#deadline-period");
		
		if ($(this).filter(":checked").length) {
			field.removeAttr("disabled");
		} else {
			field.attr("disabled", "disabled");
		}
	}
	
	// prepinani zodpovedne osoby guard/neguard
	function toggleGuard() {
		submitDeadlineForm();
	}
	
	// odesle formular pro aktualizaci nekterych hodnot
	function submitDeadlineForm() {
		$("#deadlineform").removeAttr("action").submit();
	}
	
	function openEdit() {
		// nacteni id lhuty a sestaveni adresy
		var deadId = $(this).parent().find(":hidden").val();
		var url = "/deadline/deadline/edit?clientId=" + clientId + "&deadlineId=" + deadId;
		
		// otevreni iframe dialogu s editaci lhuty
		$.iframeDialog(url, 800, 400, "Úprava lhůty");
	}
	
	function openGet() {
		// nacteni id lhuty a sestaveni adresy
		var deadId = $(this).parent().find(":hidden").val();
		var url = "/deadline/deadline/get?clientId=" + clientId + "&deadlineId=" + deadId;
		
		// otevreni iframe dialogu s editaci lhuty
		$.iframeDialog(url, 800, 400, "Úprava lhůty");
	}
	
	function toggleFilter() {
		$("#deadlinefilter").toggle();
	}
	
	$("#deadline-is_period").click(togglePeriodic);
	$("#deadline-resp_type").change(toggleGuard);
    $("#deadline-subsidiary_id,#deadline-deadline_type").change(submitDeadlineForm);
	$("#deadlinetable tbody tr td button").filter("[name='edit']").click(openEdit).end().filter("[name='get']").click(openGet);
	$("#deadline-filter-toggle").click(toggleFilter);
	
	$("#deadline-done_at,#deadline-last_done").datepicker({
		"dateFormat" : "yy-mm-dd",
		"dayNamesMin" : ["Ne", "Po", "Út", "St", "Čt", "Pá", "So"],
		"monthNames" : ["Leden", "Únor", "Březen", "Duben", "Květen", "Červen", "Červenec", "Srpen", "Září", "Říjen", "Listopad", "Prosinec"],
		"firstDay" : 1
	});
	
});
