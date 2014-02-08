$(function () {
	var clientId = $("#CLIENT_ID").val();
	var boundary = "asf513saf43asf165sa4dfs54f";
	
	var pairs = {
			"deadline_type" : "kind",
			"kind" : "type",
			"type" : "specific"
	};
	
	var parents = {
		"specific" : "type",
		"type" : "kind",
		"kind" : "deadline_type"
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
        
        if (indexItem === undefined) {
            return;
        }
		
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
		
		switchOptions(nextObj, indexItem.children, pairs[nextName] === undefined || nextName === "specific");
		
		doChange(nextName);
	}
	
	function onBlur() {
		var replacer = $(this);
		
        if (objs.specific === undefined) return true;
        if (objs.specific.length == 0) return true;
        
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
        var found = false;
		
		obj.children().remove();
		for (var c in opts) {
			var item = opts[c];
			cnt++;
			
			// vygenerovani polozky
			var currVal = item.value ? item.value : item.name;
			var opt = $("<option />").attr("value", currVal).text(item.name);
			
			if (currVal === oldVal) {
                opt.attr("selected", "selected");
                found = true;
            }
			
			obj.append(
					opt
			);
		}
		
        if (appendCustom && !found) {
            obj.append($("<option />").attr("selected", "selected").attr("value", oldVal).text(oldVal));
        }
        
		if (appendCustom) {
			obj.append($("<option />").attr("value", boundary).text("- Jiné -"));
		}
        /*
        if (!found) {
            obj.parent().append($("<spam />").text(" (původně \"" + oldVal + "\")"));
        }
        */
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
		$.iframeDialog(url, 800, 400, "Úprava lhůty", "refresh");
	}
	
	function openGet() {
		// nacteni id lhuty a sestaveni adresy
		var deadId = $(this).parent().find(":hidden").val();
		var url = "/deadline/deadline/get?clientId=" + clientId + "&deadlineId=" + deadId;
		
		// otevreni iframe dialogu s editaci lhuty
		$.iframeDialog(url, 800, 400, "Úprava lhůty");
	}
    
    function sendNewObject() {
        // nacteni dat z formulare
        var form = $(this);
        var dataItems = form.find("input, textarea,select");
        var data = {};
        
        dataItems.each(function () {
            var context = $(this);
            var val = context.val();
            
            if (val === null) return;
            
            if (val.trim().length > 0)
                data[context.attr("name")] = context.val();
        });
        
        data["clientId"] = CLIENT_ID;
        data["subsidiaryId"] = $("#deadline-subsidiary_id").val();
        
        // pokus o odeslani dat
        $.post(form.attr("action"), data, function (response) {
            // kontrola, jeslti byl objekt vytvoren
            if (response.created) {
                // vytvoreni noveho opt a prirazeni do selectu
                var opt = $("<option />").attr("value", response.objId).text(response.objName);
                var target = $("#deadline-object_id");
                
                opt.appendTo(target);
                target.val(response.objId);
                
                submitDeadlineForm();
            } else {
                // oznaceni chybnych poli
                for (var n in response.errors) {
                    var errList = response.errors[n];
                    
                    // kontrola, jeslti jsou pritomny nejake chyby
                    if (errList.length) {
                        // chyby jsou pritomny, oznaceni radku
                        $("#" + n).parents("tr:first").addClass("error");
                    } else {
                        // odznaceni radku
                        $("#" + n).parents("tr:first").removeClass("error");
                    }
                }
            }
        }, "json");
        
        return false;
    }
	
    function newObject() {
        if ($(this).val() !== "-1") return;
        
        // vyhodnoceni, ktereho typu objektu se lhuta tyka
        var objType = $("#deadline-deadline_type").val();
        var url = "";
        var action = "";
        
        switch(objType) {
            case "1":
                url = "/employee/create.part";
                action = "/employee/create.json";
                break;
                
            case "3":
                url = "/technical/create.part";
                action = "/technical/create.json";
                break;
                
            default:
                return;
        }
        
        // nacteni formulare a jeho zobrazeni
        $.get(url, {clientId : CLIENT_ID, "subsidiaryId" : $("#deadline-subsidiary_id").val()}, function (response) {
            response = $(response);
            
            $("<div />").append(response).dialog({
                modal : true,
                width : "700",
                height : "500"
            });
            
            // prepsani akce odeslani
            response.attr("action", action).submit(sendNewObject);
            
        }, "html");
    }
    
    function openNewDeadlineForm() {
        var url = "/deadline/deadline/create.html?clientId=" + CLIENT_ID + "&subsidiaryId=" + SUBSIDIARY_ID;
        
        $.iframeDialog(url, 700, 500, "Nová lhůta", "refresh")
    }
    
    $("button#new-deadline").click(openNewDeadlineForm);
	$("#deadline-is_period").click(togglePeriodic);
	$("#deadline-resp_type").change(toggleGuard);
    $("#deadline-subsidiary_id,#deadline-deadline_type").change(submitDeadlineForm);
	$("#deadlinetable tbody tr td button").filter("[name='edit']").click(openEdit).end().filter("[name='get']").click(openGet);
	
	$("#deadline-done_at,#deadline-last_done").datepicker({
		"dateFormat" : "yy-mm-dd",
		"dayNamesMin" : ["Ne", "Po", "Út", "St", "Čt", "Pá", "So"],
		"monthNames" : ["Leden", "Únor", "Březen", "Duben", "Květen", "Červen", "Červenec", "Srpen", "Září", "Říjen", "Listopad", "Prosinec"],
		"firstDay" : 1
	});
	
    $("#deadline-object_id").change(newObject);
});
