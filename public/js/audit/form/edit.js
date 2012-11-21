$((new function () {
	var questionary = new QUESTIONARY.Questionary();
	questionary.setFromArray(QDATA);
	questionary.setDesingMode(true);
	
	// obsahuje tooltip
	var tooltip = null;
	
	// prvky pro vyber
	var selects = {
		"A" : "ANO",
		"NT" : "NT",
		"C" : "Č",
		"N" : "N"
	};
	
	/****************
	 * pomocne funkce
	 ****************/
	
	/*
	 * vypise boxik s napovedou
	 */
	function showTooltip(text) {
		// pokud je tooltip aktivni, schova se
		if (tooltip != null) hideTooltip();
		
		// vytvoreni wrapperu
		tooltip = $("<div>");
		
		// nastaveni textu
		tooltip.text(text);
		
		// nastaveni zobrazeni
		tooltip.css({
			"border" : "1px solid black",
			"max-width" : "100px",
			"top" : "100px",
			"right" : "50px",
			"position" : "fixed",
			"padding" : "5px",
			"background-color" : "yellow"
		});
		
		tooltip.appendTo("body");
	}
	
	/*
	 * odstrani boxik s napovedou
	 */
	function hideTooltip() {
		if (tooltip) tooltip.remove();
	}
	
	/*
	 * vykrelsi formular
	 */
	function render() {
		var target = $("#form-container");
		target.children().remove();
		
		questionary.render().appendTo(target);
	}
	
	/*
	 * zvyrazni prvek
	 */
	function highlightOn() {
		$(this).css("background-color", "red");
	}
	
	/*
	 * odstrani zvirezneni prvku
	 */
	function highlightOff() {
		$(this).css("background-color", "");
	}
	
	/*
	 * nastavi zvoraznovani
	 */
	function enableHighlighting(targets) {
		targets.mouseover(highlightOn).mouseout(highlightOff).css("cursor", "crosshair");
	}
	
	/**********************
	 * operace se skupinami
	 **********************/
	/*
	 * vraci seznam kontejneru skupin
	 */
	function getGroups() {
		var groups = $("#form-container .questionary-item-group");
		var retVal = $();
		
		/*
		 * hack, protoze pri primem hledani predka se seznam reversoval
		 */
		groups.each(function () {
			var parent = $(this).parents(".questionary-item");
			
			retVal = retVal.add(parent[0]);
		});
		
		return retVal;
	}
	
	/*
	 * vytvori novou skupinu
	 */
	function createGroup() {
		var name = prompt("Jméno skupiny");
		var itemName = "";
		
		// vygenerovani jmena
		try {
			var i = 1;
			
			while (i) {
				// generovani jmena
				itemName = "group-" + i++;
				
				// ziskani prvku daneho jmena - pokud jmeno neexistuje, je vyhozena vyjimka
				questionary.getByName(itemName);
			}
		} catch (e) {
			
		}
		
		if (!name) return;
		
		var group = questionary.addItem(itemName, "Group");
		group.label(name);
		group.defVal(1);
		
		// vlozeni hodnoceni
		var labels = questionary.addItem(itemName + "labels", "ValueList");
		labels.label("Hodnocení");
		
		labels.setOptions(selects);
		
		group.addItem(labels);
		
		render();
	}
	
	/*
	 * smaze skupinu
	 */
	function deleteGroup() {
		// zapsani tooltipu
		showTooltip("Klikněte na skupinu, kterou chcete smazat");
		
		// ziskani elemetu
		var groups = getGroups();
		
		groups.css("cursor", "crosshair");
		
		// anonymni funkce pro zmenu stylu
		enableHighlighting(groups);
		
		groups.click(finishDeleteGroup);
	}
	
	/*
	 * zapocne editaci labelu skupiny
	 */
	function editGroup() {
		var groups = getGroups();
		
		// zvirazneni
		enableHighlighting(groups);
		
		// udalost kliknuti
		groups.click(editGroupFinish);
		
		// tootltip
		showTooltip("Klikněte na skupinu, u které bude upraven nadpis");
	}
	
	/*
	 * dokonci editaci labelu skupiny
	 */
	function editGroupFinish() {
		// nacteni jmena a prvku
		var name = $(this).find(">input[name='itemName']").val();
		var item = questionary.getByName(name);
		
		var label = prompt("Nový nadpis", item.label());
		
		if (label.length) {
			item.label(label);
		} else {
			alert("Jméno skupiny nesmí být prázdné");
		}
		
		// prekresleni
		render();
		hideTooltip();
	}
	
	/*
	 * dokonci odebrani skupiny
	 */
	function finishDeleteGroup() {
		// potvrzeni
		if (confirm("Skutečne chcete odstranit skupinu?")) {
			var context = $(this);
			
			// nacteni jmena
			var name = context.find(">input[name='itemName']").val();
			
			// nacteni prvku a jeho odebrani
			var item = questionary.getByName(name);
			
			questionary.removeItem(item);
		}
		// odstraneni tooltipu
		hideTooltip();
		
		// prekresleni
		render();
	}
	
	/*
	 * zapocne razeni skupin
	 */
	function sortGroups() {
		showTooltip("Najeďte na skupinu a přetáhněte ji na nové místo. Poté klikněte na dokončit řazení");
		
		// nastaveni highlightu
		enableHighlighting(getGroups);
		
		// nastaveni sortable
		$("#form-container>.questionary").sortable({
			"items" : ">.questionary-item"
		});
		
		$(this).hide();
		$("#endsort-group").show();
	}
	
	/*
	 * dokonci razeni funkci
	 */
	function endSortGroups() {
		// nacteni itemu
		var groups = getGroups();
		
		// nacteni jmen
		var names = [];
		
		groups.each(function () {
			var item = $(this).find(">input[name='itemName']");
			
			names.push(item.val());
		});
		
		$(this).hide();
		$("#sort-group").show();
		
		// ziskani seznamu prvku
		var items = [];
		
		for (var i in names) {
			items.push(questionary.getByName(names[i]));
		}
		
		//nastaveni poradi
		questionary.setOrder(items);
		
		render();
		hideTooltip();
	}
	
	/**********
	 * OTAZKY
	 **********/
	
	/*
	 * vezme seznam otazek ze skupiny nebo celeho formulare
	 */
	function getQuestions(container) {
		var questions = null;
		
		if (container == undefined) {
			questions = $("#form-container .questionary .questionary-item-radio");
		} else {
			questions = container.find(".questionary-item-radio");
		}
		
		// navratova hodnota
		var retVal = $();
		
		// zapis do retVal
		questions.each(function () {
			var item = $(this).parents(".questionary-item:first");
			retVal = retVal.add(item);
		});
		
		return retVal;
	}
	
	/*
	 * vraci bazove jmeno otazky
	 */
	function getQuestionBaseName(name) {
		var position = name.lastIndexOf("[");
		
		return name.substr(0, position);
	}
	
	/*
	 * rozlozi otazku na zavaznost a zneni
	 */
	function explodeQuestion(text) {
		var pos = text.indexOf(" ");
		
		var weight = text.substr(0, pos);
		var question = text.substr(pos + 1);
		
		// trim zavorek
		weight = weight.substr(1, weight.length - 2);
		
		var retVal = {
				question : question,
				weight : Number(weight)
		};
		
		return retVal;
	}
	
	/**
	 * slozi zneni otazky a zavaznost
	 */
	function buildQuestion(question, weight) {
		return "(" + weight + ") " + question;
	}
	
	/*
	 * prida otazku
	 */
	function addQuestion() {
		// nacteni skupin a aktivace
		var groups = getGroups();
		
		enableHighlighting(groups);
		groups.click(addQuestionFinish);
		
		showTooltip("Vyberte skupinu, do které přidat otázku");
	}
	
	/*
	 * dokonci pridani
	 */
	function addQuestionFinish() {
		var groupName = $(this).find(">input[name='itemName']").val();
		var group = questionary.getByName(groupName);
		
		// zjisteni popisku
		var label = prompt("Otázka:");
		
		if (!label.length) {
			alert("Otázka nesmí být prázdná");
			render();
			return;
		}
		
		// zjisteni indexu skupiny
		var groupIndex = group.name().split("-")[1];
		
		// vygenerovani jmena
		var itemName = "";
		var itemBaseName = "";
		var i = 0;
		
		try {
			while (1) {
				itemBaseName = "question[" + groupIndex + "][" + (i++) + "]";
				itemName = itemBaseName + "[score]";
				
				questionary.getByName(itemName);
			}
		} catch (e) {
			
		}
		
		// vytvoreni pole zaznamu
		var score = questionary.addItem(itemName, "Radio");
		score.label(label);
		score.setOptions(selects).defVal("NT");
		
		group.addItem(score);
		
		// misto pro poznamku
		var note = questionary.addItem(itemBaseName + "[note]", "TextArea");
		note.label("Poznámka");
		
		group.addItem(note);
		
		render();
		hideTooltip();
	}
	
	/*
	 * odebere otazku
	 */
	function removeQuestion() {
		// nacteni otazek
		var questions = getQuestions();
		
		enableHighlighting(questions);
		
		questions.click(removeQuestionFinish);
	}
	
	/*
	 * dokonci odebrani otazky
	 */
	function removeQuestionFinish() {
		if (confirm("Skutečně odstranit otázku?")) {
			// nacteni jmena a zjisteni baze
			var name = $(this).find(">input[name='itemName']").val();
			var baseName = getQuestionBaseName(name);
			
			// nacteni itemu
			var item = questionary.getByName(name);
			var note = questionary.getByName(baseName + "[note]");
			
			questionary.removeItem(item)
			questionary.removeItem(note);
		}
		hideTooltip();
		render();
	}
	
	/*
	 * upravi zneni otazky
	 */
	function editQuestion() {
		var questions = getQuestions();
		
		enableHighlighting(questions);
		questions.click(editQuestionFinish);
		
		showTooltip("Vyberte otázku k editaci");
	}
	
	/*
	 * dokonci upravu otazky
	 */
	function editQuestionFinish() {
		// nacteni jmena elementu
		var elementName = $(this).find(">input[name='itemName']").val();
		var item = questionary.getByName(elementName);
		
		var qData = explodeQuestion(item.label());
		
		var question = prompt("Nové znění otázky:", qData.question);
		
		if (question.length) {
			// sestaveni dat
			var text = buildQuestion(question, qData.weight);
			
			item.label(text);
		} else {
			alert("Otázka nesmí být prázdná");
		}
		
		hideTooltip();
		render();
	}
	
	/*
	 * aktivuje vyber skupiny pro razeni otazek
	 */
	var selectedGroup = null;
	
	function sortQuestionSelectGroup() {
		var groups = getGroups();
		
		enableHighlighting(groups);
		
		groups.click(sortQuestionBeginSort);
		showTooltip("Vyberte skupinu, kde se budou radit otázky");
		
		// nastaveni tlacitek
		$(this).hide();
		$("#endsort-question").show();
	}
	
	/*
	 * nastavni skupinu razeni otazek
	 */
	function sortQuestionBeginSort() {
		// odstraneni zvyraznovani
		var groups = getGroups();
		
		selectedGroup = $(this);
		
		// odstraneni razeni
		groups.css("cursor", "");
		groups.unbind("mouseover", highlightOn);
		groups.unbind("mouseout", highlightOff);
		groups.unbind("click", sortQuestionBeginSort);
		
		highlightOff.apply(this);
		
		showTooltip("Seřaďte otázky a potvrďte tlačítkem Ukončit řazení");
		
		// zabaleni otazek do logickych dvojic s poznamkama
		var target = selectedGroup.find(".questionary-item-group");
		var sortings = $();
		
		target.find(">.questionary-item:odd").each(function () {
			var node = $(this);
			var note = node.next();
			
			var set = $("<div class='sortings'>");
			
			node.appendTo(set);
			note.appendTo(set);
			
			target.append(set);
			
			// pridani do sortings
			sortings = sortings.add(set);
		});
		
		// aktivace zvirazneni
		enableHighlighting(sortings);
		
		// nastaveni razeni
		selectedGroup.find(".questionary-item-group").sortable({
			items : ".sortings"
		});
	}
	
	/*
	 * dokonci razeni a ulozi hodnoty
	 */
	function sortQuestionFinish() {
		// jmeno skupiny a objekt
		var groupName = selectedGroup.find(">input[name='itemName']").val();
		var group = questionary.getByName(groupName);
		
		// nacteni jmen a poradi prvku
		var names = selectedGroup.find(".questionary-item input[name='itemName']");
		var items = [];
		
		names.each(function () {
			var itemName = $(this).val();
			
			items.push(questionary.getByName(itemName));
		});
		
		// zapis do skupiny
		group.clear();
		
		for (var i in items) {
			group.addItem(items[i]);
		}
		
		// nalezeni prvku a zarazeni
		
		$(this).hide();
		$("#sort-question").show();
		hideTooltip();
		render();
		selectedGroup = null;
	}
	
	/*
	 * pripravi otazky na zmenu zavaznosti
	 */
	function weightQuestion() {
		var questions = getQuestions();
		
		enableHighlighting(questions);
		
		questions.click(weightQuestionFinish);
	}
	
	function weightQuestionFinish() {
		// nalezeni jmena
		var name = $(this).find(">input[name='itemName']").val();
		var item = questionary.getByName(name);
		
		var qParts = explodeQuestion(item.label());
		
		var newWeight = Number(prompt("Zadejte novou váhu:", qParts.weight));
		
		if (!newWeight) {
			alert("Neplatná závažnost");
		} else {
			qParts.weight = newWeight;
		}
		
		item.label(buildQuestion(qParts.question, qParts.weight));
		
		render();
	}
	
	/*******************
	 * ulozeni formulare
	 *******************/
	
	function saveForm() {
		var data = {};
		
		data.id = $("#form-id").val();
		data.name = $("#form-name").val();
		data.def = window.JSON.stringify(questionary.toArray());
		
		// odeslani na server
		$.post("/audit/form/put.json", { form : data}, function (response) {
			if (response) {
				alert("Data byla uložena");
			} else {
				alert("Chyba při ukládání dat");
			}
		}, "json");
		
		return false;
	}
	
	this.init = function () {
		// nastaveni tlacitek
		$("#add-group").click(createGroup);
		$("#remove-group").click(deleteGroup);
		$("#sort-group").click(sortGroups);
		$("#endsort-group").click(endSortGroups);
		$("#edit-group").click(editGroup);
		
		$("#add-question").click(addQuestion);
		$("#remove-question").click(removeQuestion);
		$("#edit-question").click(editQuestion);
		$("#weight-question").click(weightQuestion);
		$("#sort-question").click(sortQuestionSelectGroup);
		$("#endsort-question").click(sortQuestionFinish);
		
		$("#formpost").submit(saveForm);
		
		render();
		
	};
}()).init);