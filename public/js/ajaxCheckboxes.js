var baseUrl = BASE_URL;

$(function(){
	
	//PŘIDÁVÁNÍ ZAMĚSTNANCE
	var validatorEmployee = $('#employee').validate({
		rules: {
			first_name: {
				required: true
			},
			surname: {
				required: true
			},
			email: {
				email: true
			}
		},
		messages: {
			first_name: "Uveďte křestní jméno",
			surname: "Uveďte příjmení",
			email: "Uveďte platnou emailovou adresu."
		}
	});
	
	$('#new_employee').click(function(){
		$('#new_employee_form input[type=text]').val('');
		$('#new_employee_form textarea').val('');
		$('#new_employee_form select#year_of_birth').val('1960');
		$('#new_employee_form select#manager').val('0');
		$('#new_employee_form select#sex').val('0');
		validatorEmployee.resetForm();
		$('#new_employee_form').dialog("open");
	});
	
	$('#new_employee_form').dialog({
		autoOpen: false,
		height: 500,
		width: 700,
		modal: true,
		title: 'Vyplňte údaje nového zaměstnance.',
	});
	
	//PŘIDÁVÁNÍ PRACOVNÍ POZICE
	$('#new_position').click(function(){
		$('#new_position_form').dialog("open");
	});
	
	$('#new_position_form').dialog({
		autoOpen: false,
		height: 500,
		width: 700,
		modal: true,
		title: 'Zadejte oficiální název pracovní pozice tak, jak je uveden v pracovní smlouvě.',
	});
	
	//PŘIDÁVÁNÍ PRACOVNÍ ČINNOSTI
	var validatorWork = $('#work').validate({
		rules: {
			work: {
				required: true
			},
		},
		messages: {
			work: "Uveďte název pracovní pozice.",
		}
	});
	
	$('#new_work').click(function(){
		$('#new_work_form input[type=text]').val('');
		validatorWork.resetForm();
		$('#new_work_form').dialog("open");
	});
	
	$('#new_work_form').dialog({
		autoOpen: false,
		height: 500,
		width: 700,
		modal: true,
		title: 'Zadejte název pracovní činnosti',
	});
	
	//PŘIDÁVÁNÍ TECHNICKÉHO PROSTŘEDKU
	var validatorTechnicalDevice = $('#technicaldevice').validate({
		rules: {
			sort: {
				required: true
			},
		},
		messages: {
			sort: "Uveďte druh technického prostředku.",
		}
	});
	
	$('#new_technicaldevice').click(function(){
		$('#new_technicaldevice_form input[type=text]').val('');
		validatorTechnicalDevice.resetForm();
		$('#new_technicaldevice_form').dialog("open");
	});
	
	$('#new_technicaldevice_form').dialog({
		autoOpen: false,
		height: 500,
		width: 700,
		modal: true,
		title: 'Zadejte druh a typ technického prostředku.',
	});
	
	//PŘIDÁVÁNÍ UMÍSTĚNÍ
	var validatorFolder = $('#folder').validate({
		rules: {
			folder: {
				required: true
			},
		},
		messages: {
			folder: "Uveďte název umístění.",
		}
	});
	
	$('#new_folder').click(function(){
		$('#new_folder_form input[type=text]').val('');
		validatorFolder.resetForm();
		$('#new_folder_form').dialog('open');
	});
	
	$('#new_folder_form').dialog({
		autoOpen: false,
		height: 500,
		width: 700,
		modal: true,
		title: 'Zadejte nové umístění pro pracoviště',
	});
	
	//PŘIDÁVÁNÍ PRACOVIŠTĚ
	var validatorWorkplace = $('#workplace').validate({
		rules: {
			name: {
				required: true
			},
			business_hours: {
				required: true
			},
			description: {
				required: true
			},
			boss_email: {
				email: true
			},
		},
		messages: {
			name: "Uveďte jméno pracoviště.",
			business_hours: "Uveďte pracovní dobu.",
			description: "Uveďte popis pracoviště.",
			boss_email: "Uveďte platnou emailovou adresu.",
		}
	});
	
	$('#new_workplace').click(function(){
		var subsidiary = $(this).attr('class');
		$('#new_workplace_form select#subsidiary_id').val(subsidiary);
		$('#new_workplace_form input[type=text]').val('');
		$('#new_workplace_form textarea').val('');
		$('#new_workplace_form input[type=checkbox]').attr('checked', false);
		$('#new_workplace_form tr[id*="chemicalDetail"]').remove();
		$('#new_workplace_form select#folder_id').val('0');
		validatorWorkplace.resetForm();
		$('#new_workplace_form').dialog('open');
	});
	
	$('#new_workplace_form').dialog({
		autoOpen: false,
		height: 500,
		width: 700,
		modal: true,
		title: 'Zadejte údaje nového pracoviště',
	});
	
	//PŘIDÁVÁNÍ CHEMICKÝCH LÁTEK
	var validatorChemical = $('#chemical').validate({
		rules: {
			chemical: {
				required: true
			},
		},
		messages: {
			chemical: "Uveďte název chemické látky.",
		}
	});
	
	$('#new_chemical').click(function(){
		$('#new_chemical_form input[type=text]').val('');
		validatorChemical.resetForm();
		$('#new_chemical_form').dialog("open");
	});
	
	$('#new_chemical_form').dialog({
		autoOpen: false,
		height: 500,
		width: 700,
		modal: true,
		title: 'Zadejte název chemické látky.',
	});
	
	$(".multiCheckboxChemicals").on("click", "input[id*='chemicalList']", function(){
		var checkbox = $(this);
		var id = checkbox.val();
		var label = checkbox.parent().text();
		if(checkbox.is(':checked')){
			ajaxAddChemicalDetail(id, label);
		}
		else{
			ajaxRemoveChemicalDetail(id, label);
		}
	});
	
	function ajaxAddChemicalDetail(id, label){
		var elementId = $("#id_chemical").val();
		var clientId = $("#client_id").val();
		$.ajax({
			type: "POST",
			url: baseUrl + '/workplace/chemicaldetail/format/html',
			data: "id_chemical=" + elementId + "&clientId=" + clientId + "&idChemical=" + id + "&chemical=" + label,
			success: function(newElement){
				$('#new_chemical').parents('tr').before(newElement);
				$('#id_chemical').val(++elementId);
			}
		});
	}
	
	function ajaxRemoveChemicalDetail(id, label){
		$("input[id*='chemicalDetail'][value='" + id + "']").parent().next().remove();
		$("input[id*='chemicalDetail'][value='" + id + "']").parent().remove();
	}
	
	//PŘIDÁVÁNÍ ŠKOLENÍ
	var validatorSchooling = $('#schooling').validate({
		rules: {
			schooling: {
				required: true
			},
		},
		messages: {
			schooling: "Uveďte název školení.",
		}
	});
	
	$('#new_schooling').click(function(){
		$('#new_schooling_form input[type=text]').val('');
		validatorSchooling.resetForm();
		$('#new_schooling_form').dialog("open");
	});
	
	$('#new_schooling_form').dialog({
		autoOpen: false,
		height: 500,
		width: 700,
		modal: true,
		title: 'Zadejte název školení.',
	});
	
	//DETAILY FAKTORŮ PRACOVNÍHO PROSTŘEDÍ
	$(".multiCheckboxEnvironmentfactors").on("click", "input[id*='environmentfactorList']", function(){
		var checkbox = $(this);
		var id = checkbox.val();
		var label = checkbox.parent().text();
		if(checkbox.is(':checked')){
			ajaxAddEnvironmentFactorDetail(id, label);
		}
		else{
			ajaxRemoveEnvironmentFactorDetail(id, label);
		}
	});
	
	function ajaxAddEnvironmentFactorDetail(id, label){
		var elementId = $("#id_environment_factor").val();
		var clientId = $("#client_id").val();
		$.ajax({
			type: "POST",
			url: baseUrl + '/position/environmentfactordetail/format/html',
			data: "id_environment_factor=" + elementId + "&clientId=" + clientId + "&idEnvironmentFactor=" + id + "&environmentFactor=" + label,
			success: function(newElement){
				$('#schoolings').parents('tr').before(newElement);
				$('#id_environment_factor').val(++elementId);
			}
		});
	}
	
	function ajaxRemoveEnvironmentFactorDetail(id, label){
		$("input[id*='environmentFactorDetail'][value='" + id + "']").parent().next().next().next().next().remove();
		$("input[id*='environmentFactorDetail'][value='" + id + "']").parent().next().next().next().remove();
		$("input[id*='environmentFactorDetail'][value='" + id + "']").parent().next().next().remove();
		$("input[id*='environmentFactorDetail'][value='" + id + "']").parent().next().remove();
		$("input[id*='environmentFactorDetail'][value='" + id + "']").parent().remove();
	}
	
	//VŠEOBECNÉ FUNKCE
	$(".ajaxSave").click(function(){
		var elementClass = $(this).attr('class').split(' ');
		var identifier = elementClass[0];
		var controller = elementClass[1];
		if($('#' + identifier).valid()){
			ajaxSaveItem(identifier, controller);
			if(identifier == 'folder'){
				ajaxPopulateSelect(identifier, controller);
			}
			else{
				if(identifier == 'schooling'){
					ajaxAppendCheckbox(identifier, controller);
				}
				else{
					ajaxPopulateSelects(identifier, controller);
				}			
			}
		}
	});
	
	function ajaxSaveItem(identifier, controller){
		$.ajax({
			type: "POST",
			url: baseUrl + '/' + controller + '/add' + identifier + '/format/html',
			data: $("#" + identifier).serializeArray(),
			async: false,
			success: function(){
				console.log("OK");
				$('#new_' + identifier + '_form').dialog("close");
			}
		});
	}
	
	function capitalizeFirstLetter(string){
		return string.charAt(0).toUpperCase() + string.slice(1);
	}
	
	function ajaxPopulateSelect(identifier, controller){
		var clientId = $("#client_id").val();
		$.ajax({
			type: "POST",
			dataType: 'json',
			url: baseUrl + '/' + controller + '/populate' + identifier + 's',
			data: "clientId=" + clientId,
			async: false,
			success: function(json){
				var el = $("select[id*='folder_id']");
				var vals = [];
				var i = 0;
				el.children("option").each(function(){
					vals[i++] = $(this).val();
				});
				el.empty();
				$.each(json, function(key, value){
					if($.inArray(key, vals) != -1){
						el.append($("<option></option>").attr("value", key).text(value));
					}
					else{
						el.append($("<option></option>").attr("value", key).attr("selected", "selected").text(value));
					}
				});
			}
		});
	}
	
	function ajaxPopulateSelects(identifier, controller){
		var clientId = $("#client_id").val();
		$.ajax({
			type: "POST",
			dataType: 'json',
			url: baseUrl + '/' + controller + '/populate' + identifier + 's',
			data: "clientId=" + clientId,
			async: false,
			success: function(json){
				var identifierCap = capitalizeFirstLetter(identifier);
				var checkedItems = $("div.multiCheckbox" + identifierCap + "s label input:checked");
				var vals = [];
				var i = 0;
				checkedItems.each(function(){
					vals[i++] = $(this).val();
				});
				var labels = $("div.multiCheckbox" + identifierCap + "s label");
				var labelArray = [];
				var j = 0;
				labels.each(function(){
					labelArray[j++] = $(this).text();
				});
				$("div.multiCheckbox" + identifierCap + "s").empty();
				$.each(json, function(key, value){
					// nová hodnota
					if($.inArray(value, labelArray) == -1){
						$("div.multiCheckbox" + identifierCap + "s").append('<label><input id=\"' + identifier + 'List-' +
								key + '\" type=\"checkbox\" checked=\"checked\" value=\"' + key 
								+ '\" name=\"' + identifier + 'List[]\">' + value
								+ '</label><br/>');
						if(identifier == 'chemical'){
							ajaxAddChemicalDetail(key, value);
						}
					}
					// nezaškrtnutá hodnota
					else if($.inArray(key, vals) == -1){
						$("div.multiCheckbox" + identifierCap + "s").append('<label><input id=\"' + identifier + 'List-' +
							key + '\" type=\"checkbox\" value=\"' + key + '\" name=\"' + identifier + 'List[]\">' + value + '</label><br/>');	
					}
					// ostatní hodnoty
					else{
						$("div.multiCheckbox" + identifierCap + "s").append('<label><input id=\"' + identifier + 'List-' +
								key + '\" type=\"checkbox\" checked=\"checked\" value=\"' + key + '\" name=\"' + identifier + 'List[]\">' + value
								+ '</label><br/>');
					}
				});
			}
		});
	}
	
	function ajaxAppendCheckbox(identifier, controller){
		var clientId = $('#client_id').val();
		$.ajax({
			type: "POST",
			dataType: 'json',
			url: baseUrl + '/' + controller + '/populate' + identifier + 's',
			data: "clientId=" + clientId,
			async: false,
			success: function(json){
				var newKey = json[Object.keys(json).sort().pop()];
				var newValue = json.newKey;
				var identifierCap = capitalizeFirstLetter(identifier);
				$("div.multiCheckbox" + identifierCap + "s").append('<label><input id=\"' + identifier + 'List-' + newKey +
						'\" type=\"checkbox\" checked=\"checked\" value\"' + newKey + '\" name=\"' + identifier + 'List[]\">' +
						newValue + '</label><br/>');
			}
		});
	}
});