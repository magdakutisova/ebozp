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
		return false;
	});
	
	$('#new_employee_form').dialog({
		autoOpen: false,
		height: 500,
		width: 700,
		modal: true,
		title: 'Vyplňte údaje nového zaměstnance.',
	});
	
	//PŘIDÁVÁNÍ PRACOVNÍ POZICE
	var validatorPosition = $('#position').validate({
		invalidHandler: function(form, validator){
			var errors = validator.numberOfInvalids();
			if(errors){
				$("#error-message").show().text("Pracovní pozice nebyla uložena. Formulář obsahuje " + errors + " neplatných polí");
				$("html, body").animate({scrollTop: 0}, "fast");
			} else {
				$("#error-message").hide();
			}
		},
		rules: {
			"subsidiaryListError": {
				required: function(element){
					if($(document).find('select#subsidiaryList').length){
						return false;
					}
					else{
						var checkboxes = $('input[id*=subsidiaryList]');
						if(checkboxes.filter(':checked').length == 0){
							return true;
						}
						return false;
					}
					
				},
				minlength: 1,
			},
			position: {
				required: true,
			},
			working_hours: {
				//required: true
			},
		},
		messages: {
			"subsidiaryListError": {
				required: "Vyberte alespoň jednu pobočku.",
			},
			position: {
				required: "Uveďte název pracovní pozice.",
			},
			//working_hours: "Uveďte pracovní dobu.",
		}
	});
	
	$('#new_position').click(function(){
		var subsidiary = $(this).attr('class');
		$('#new_position_form input[type=checkbox]').attr('checked', false);
		$('#new_position_form div.multiCheckboxSubsidiaries input[type=checkbox][value="' + subsidiary + '"]').prop('checked', true);
		$('#new_position_form div.multiCheckboxSchoolings input[type=checkbox][value="1"]').prop('checked', true);
		$('#new_position_form div.multiCheckboxSchoolings input[type=checkbox][value="2"]').prop('checked', true);
		$('#new_position_form input[type=text]').val('');
		$('#new_position_form textarea').val('');
		$('#new_position_form select#categorization').val('0');
		$('#new_position_form tr[id*=environmentFactorDetail]').next().next().next().next().remove();
		$('#new_position_form tr[id*=environmentFactorDetail]').next().next().next().remove();
		$('#new_position_form tr[id*=environmentFactorDetail]').next().next().remove();
		$('#new_position_form tr[id*=environmentFactorDetail]').next().remove();
		$('#new_position_form tr[id*=environmentFactorDetail]').remove();
		$('#new_position_form div.multiCheckboxEnvironmentfactors').parent().parent().addClass('hidden');
		$('#new_position_form div.multiCheckboxEnvironmentfactors').parent().parent().prev().addClass('hidden');
		$('#new_position_form tr[id*=schoolingDetail] > input[id*=schoolingDetail][value!="1"][value!="2"]').parent().next().remove();
		$('#new_position_form tr[id*=schoolingDetail] > input[id*=schoolingDetail][value!="1"][value!="2"]').parent().remove();
		$('#new_position_form tr[id*=workDetail]').next().remove();
		$('#new_position_form tr[id*=workDetail]').remove();
		$('#new_position_form tr[id*=chemical2Detail]').next().remove();
		$('#new_position_form tr[id*=chemical2Detail]').remove();
		validatorPosition.resetForm();
		$('#new_position_form').dialog("open");
		return false;
	});
	
	$('#new_position_form').dialog({
		autoOpen: false,
		height: 500,
		width: 900,
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
	
	$('.new_work').click(function(){
		$('#new_work_form input[type=text]').val('');
		validatorWork.resetForm();
		if($(this).hasClass('background')){
			$("#save_work").addClass('calledFromBackground');
		}
		else{
			$("#save_work").removeClass('calledFromBackground');
		}
		$('#new_work_form').dialog("open");
		return false;
	});
	
	$('#new_work_form').dialog({
		autoOpen: false,
		height: 500,
		width: 700,
		modal: true,
		title: 'Zadejte název pracovní činnosti',
	});
	
	//detaily pracovní činnosti
	$('.multiCheckboxWorks.position').on("click", "input[id*='workList']", function(){
		var checkbox = $(this);
		var id = checkbox.val();
		var label = checkbox.parent().text();
		if(checkbox.is(':checked')){
			ajaxAddWorkDetail(id, label);
		}
		else{
			ajaxRemoveWorkDetail(id, label);
		}
	});
	
	function ajaxAddWorkDetail(id, label){
		var elementId = $("#id_work").val();
		var clientId = $("#client_id").val();
		$.ajax({
			type: "POST",
			url: baseUrl + '/position/workdetail/format/html',
			data: "id_work=" + elementId + "&clientId=" + clientId + "&idWork=" + id + "&work=" + label,
			success: function(newElement){
				$(".new_work.position").parents('tr').before(newElement);
				$("#id_work").val(++elementId);
			}
		});
	}
	
	function ajaxRemoveWorkDetail(id, label){
		$("input[id*='workDetail'][value='" + id + "']").parent().next().remove();
		$("input[id*='workDetail'][value='" + id + "']").parent().remove();
	}
	
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
	
	$('.new_technicaldevice').click(function(){
		$('#new_technicaldevice_form input[type=text]').val('');
		validatorTechnicalDevice.resetForm();
		if($(this).hasClass('background')){
			$("#save_technicaldevice").addClass('calledFromBackground');
		}
		else{
			$("#save_technicaldevice").removeClass('calledFromBackground');
		}
		$('#new_technicaldevice_form').dialog("open");
		return false;
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
	
	//PŘIDÁVÁNÍ VEDOUCÍHO
	var validatorBoss = $('#boss').validate({
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
	
	$('#new_boss').click(function(){
		$('#new_boss_form input[type=text]').val('');
		validatorBoss.resetForm();
		$('#new_boss_form').dialog('open');
	});
	
	$('#new_boss_form').dialog({
		autoOpen: false,
		height: 500,
		width: 700,
		modal: true,
		title: 'Zadejte údaje nového zaměstnance',
	});
	
	//PŘIDÁVÁNÍ PRACOVIŠTĚ
	var validatorWorkplace = $('#workplace').validate({
		invalidHandler: function(form, validator){
			var errors = validator.numberOfInvalids();
			if(errors){
				$("#error-message").show().text("Pracoviště nebylo uloženo. Formulář obsahuje " + errors + " neplatných polí");
				$("html, body").animate({scrollTop: 0}, "fast");
			} else {
				$("#error-message").hide();
			}
		},
		rules: {
			name: {
				required: true,
				remote: {
					url: baseUrl + "/workplace/validate",
					type: "post",
					data:{
						name: function(){
							return $('#name').val();
						},
						clientId: function(){
							return $('#client_id').val();
						},
						workplaceId: function(){
							return $('#id_workplace').val();
						}
					}
				}
			},
			business_hours: {
				//required: true
			},
			description: {
				//required: true
			},
			boss_email: {
				email: true
			},
		},
		messages: {
			name:{
				required: "Uveďte jméno pracoviště.",
				remote: "Klient již má pracoviště s tímto názvem, zvolte jiný."
			},
			//business_hours: "Uveďte pracovní dobu.",
			//description: "Uveďte popis pracoviště.",
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
		$('#new_workplace_form td[id*="chemicalDetail"]').parent().remove();
		$('#new_workplace_form select#folder_id').val('0');
		validatorWorkplace.resetForm();
		$('#new_workplace_form').dialog('open');
		return false;
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
	
	$('.new_chemical').click(function(){
		$('#new_chemical_form input[type=text]').val('');
		validatorChemical.resetForm();
		if($(this).hasClass('background')){
			$("#save_chemical").addClass('calledFromBackground');
		}
		else{
			$("#save_chemical").removeClass('calledFromBackground');
		}
		$('#new_chemical_form').dialog("open");
		return false;
	});
	
	$('#new_chemical_form').dialog({
		autoOpen: false,
		height: 500,
		width: 700,
		modal: true,
		title: 'Zadejte název chemické látky.',
	});
	
	//detaily chemické látky
	$(".multiCheckboxChemicals.workplace").on("click", "input[id*='chemicalList']", function(){
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
				$('.new_chemical.workplace').parents('tr').before(newElement);
				$('#id_chemical').val(++elementId);
			}
		});
	}
	
	function ajaxRemoveChemicalDetail(id, label){
		$("input[id*='chemicalDetail'][value='" + id + "']").parent().next().remove();
		$("input[id*='chemicalDetail'][value='" + id + "']").parent().remove();
	}
	
	//detaily chemické látky 2
	$(".multiCheckboxChemicals.position").on("click", "input[id*='chemicalList']", function(){
		var checkbox = $(this);
		var id = checkbox.val();
		var label = checkbox.parent().text();
		if(checkbox.is(':checked')){
			ajaxAddChemical2Detail(id, label);
		}
		else{
			ajaxRemoveChemical2Detail(id, label);
		}
	});
	
	function ajaxAddChemical2Detail(id, label){
		var elementId = $("#id_chemical2").val();
		var clientId = $("#client_id").val();
		$.ajax({
			type: "POST",
			url: baseUrl + '/position/chemical2detail/format/html',
			data: 'id_chemical2=' + elementId + "&clientId=" + clientId + "&idChemical=" + id + "&chemical=" + label,
			success: function(newElement){
				$('.new_chemical.position').parents('tr').before(newElement);
				$('#id_chemical2').val(++elementId);
			}
		});
	}
	
	function ajaxRemoveChemical2Detail(id, label){
		$("input[id*='chemical2Detail'][value='" + id + "']").parent().next().remove();
		$("input[id*='chemical2Detail'][value='" + id + "']").parent().remove();
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
		return false;
	});
	
	$('#new_schooling_form').dialog({
		autoOpen: false,
		height: 500,
		width: 700,
		modal: true,
		title: 'Zadejte název školení.',
	});
	
	$(".multiCheckboxSchoolings").on("click", "input[id*='schoolingList']", function(){
		var checkbox = $(this);
		var id = checkbox.val();
		var label = checkbox.parent().text();
		if(checkbox.is(':checked')){
			ajaxAddSchoolingDetail(id, label);
		}
		else{
			ajaxRemoveSchoolingDetail(id, label);
		}
	});
	
	function ajaxAddSchoolingDetail(id, label){
		var elementId = $("#id_schooling").val();
		var clientId = $("#client_id").val();
		$.ajax({
			type: "POST",
			url: baseUrl + '/position/schoolingdetail/format/html',
			async: false,
			data: "id_schooling=" + elementId + "&clientId=" + clientId + "&idSchooling=" + id + "&schooling=" + label,
			success: function(newElement){
				$('#new_schooling').parents('tr').before(newElement);
				$('#id_schooling').val(++elementId);
			}
		});
	}
	
	function ajaxRemoveSchoolingDetail(id, label){
		$("input[id*='schoolingDetail'][value='" + id + "']").parent().next().remove();
		$("input[id*='schoolingDetail'][value='" + id + "']").parent().remove();
	}
	
	//povinná školení
	$(document).ready(function(){
		$('input#schoolingList-1').attr('checked', true).attr('disabled', true);
		$('input#schoolingList-2').attr('checked', true).attr('disabled', true);
		if($('label[for*=schoolingDetail]:contains("Požární ochrana")').length == 0){
			ajaxAddSchoolingDetail(1, 'Požární ochrana');
		}
		if($('label[for*=schoolingDetail]:contains("Bezpečnost práce")').length == 0){
			ajaxAddSchoolingDetail(2, 'Bezpečnost práce');
		}
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
		var calledFromBackground = false;
		if($(this).hasClass('calledFromBackground')){
			calledFromBackground = true;
		}
		if($('#' + identifier).valid()){
			ajaxSaveItem(identifier, controller);
			if(identifier == 'folder' || identifier == 'boss'){
				ajaxPopulateSelect(identifier, controller);
			}
			else{
				if(identifier == 'schooling'){
					ajaxAppendCheckbox(identifier, controller);
				}
				else{
					if(identifier == 'work' || identifier == 'technicaldevice' || identifier == 'chemical'){
						ajaxPopulateSelectsAmbiguous(identifier, controller, calledFromBackground);
					}
					else{
						if(identifier == 'workplace'){
							ajaxToggleWorkplaces();
						}
						else{
							ajaxPopulateSelects(identifier, controller);
						}
					}
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
	
	//volat pri pridavani workplace
	//zobrazovani jen relevantnich pracovist pri pridavani pracovni pozice
	$('.multiCheckboxSubsidiaries input').click(function(){
		ajaxToggleWorkplaces();
	});
	
	$('select#subsidiaryList').change(function(){
		ajaxToggleWorkplaces();
	});
	
	$(document).ready(function(){
		ajaxToggleWorkplaces();
	});
	
	function ajaxToggleWorkplaces(){
		var clientId = $("#client_id").val();
		var checkedSubsidiaries = $(".multiCheckboxSubsidiaries input:checked");
		var subIds = [];
		var i = 0;
		checkedSubsidiaries.each(function(){
			subIds[i++] = $(this).val();
		});
		subIds[i++] = $('select#subsidiaryList option:selected').val();
		$.ajax({
			type: "POST",
			dataType: "json",
			url: baseUrl + '/position/toggleworkplaces',
			data: "clientId=" + clientId + "&subIds=" + subIds,
			async: false,
			success: function(json){
				console.log("clientId=" + clientId + "&subIds=" + subIds);
				var checkedWorkplaces = $(".multiCheckboxWorkplaces input:checked");
				var workplaceIds = [];
				var j = 0;
				checkedWorkplaces.each(function(){
					workplaceIds[j++] = $(this).val();
				});
				$(".multiCheckboxWorkplaces").empty();
				$.each(json, function(key, value){
					if($.inArray(key, workplaceIds) == -1){
						$("div.multiCheckboxWorkplaces").append('<br/><label><input id=\"workplaceList-' + key +
								'\" type=\"checkbox\" value=\"' + key + '\" name=\"workplaceList[]\">' +
								value + '</label>');
					}
					else{
						$("div.multiCheckboxWorkplaces").append('<br/><label><input id=\"workplaceList-' + key +
								'\" type=\"checkbox\" checked=\"checked\" value=\"' + key + '\" name=\"workplaceList[]\">' +
								value + '</label>');						
					}
				});
			}
		});
	}
	
	//volat při přidávání folder, boss
	function ajaxPopulateSelect(identifier, controller){
		var clientId = $("#client_id").val();
		$.ajax({
			type: "POST",
			dataType: 'json',
			url: baseUrl + '/' + controller + '/populate' + identifier + 's',
			data: "clientId=" + clientId,
			async: false,
			success: function(json){
				var el = $("select[id*='" + identifier + "_id']");
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
	
	//volat při přidávání position, employee
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
	
	//volat při přidávání work, technicalDevice, chemical - ošetřuje problémy s dvěma stejnými seznamy s odlišným počtem
	//zaškrtnutých položek (jedna v klasickém formu a druhá v dialogu nad tím)
	function ajaxPopulateSelectsAmbiguous(identifier, controller, calledFromBackground){
		var clientId = $("#client_id").val();
		$.ajax({
			type: "POST",
			dataType: 'json',
			url: baseUrl + '/' + controller + '/populate' + identifier + 's',
			data: "clientId=" + clientId,
			async: false,
			success: function(json){
				var identifierCap = capitalizeFirstLetter(identifier);
				var checkedItems;
				var checkedItems2 = null;
				//obecně ve dvojce budou vždycky seznamy z formu Position, protože přidávací jsou ve Workplace, takže
				//nebude souhlasit název controlleru - jedná se o odlišení ve kterém seznamu jsou zaškrtnuty které položky
				if($("div.multiCheckbox" + identifierCap + "s." + controller).length){
					checkedItems2 = $("div.multiCheckbox" + identifierCap + "s:not(." + controller + ") label input:checked");
					checkedItems = $("div.multiCheckbox" + identifierCap + "s." + controller + " label input:checked");
				}
				else{
					checkedItems = $("div.multiCheckbox" + identifierCap + "s label input:checked");
				}

				var vals = [];
				var i = 0;
				checkedItems.each(function(){
					vals[i++] = $(this).val();
				});
				var vals2 = [];
				if(checkedItems2){
					var i = 0;
					checkedItems2.each(function(){
						vals2[i++] = $(this).val();
					});
				}
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
						//přidávání je voláno z workplace formu - zaškrtne se ve workplace, ale v position ne
						if(!calledFromBackground){
							//form Workplace nová hodnota
							$("div.multiCheckbox" + identifierCap + "s." + controller).append('<label><input id=\"' + identifier + 'List-' +
									key + '\" type=\"checkbox\" checked=\"checked\" value=\"' + key 
									+ '\" name=\"' + identifier + 'List[]\">' + value
									+ '</label><br/>');
							if(identifier == 'chemical'){
								ajaxAddChemicalDetail(key, value);
							}
							//form Position nová hodnota
							$("div.multiCheckbox" + identifierCap + "s:not(." + controller + ")").append('<label><input id=\"' + identifier + 'List-' +
									key + '\" type=\"checkbox\" value=\"' + key 
									+ '\" name=\"' + identifier + 'List[]\">' + value
									+ '</label><br/>');
						}
						//přidávání je voláno z position formu - zaškrtne se v position, ale ve workplace ne
						else{
							//form Workplace nová hodnota
							$("div.multiCheckbox" + identifierCap + "s." + controller).append('<label><input id=\"' + identifier + 'List-' +
									key + '\" type=\"checkbox\" value=\"' + key 
									+ '\" name=\"' + identifier + 'List[]\">' + value
									+ '</label><br/>');
							//form Position nová hodnota
							$("div.multiCheckbox" + identifierCap + "s:not(." + controller + ")").append('<label><input id=\"' + identifier + 'List-' +
									key + '\" type=\"checkbox\" checked=\"checked\" value=\"' + key 
									+ '\" name=\"' + identifier + 'List[]\">' + value
									+ '</label><br/>');
							if(identifier == 'work'){
								ajaxAddWorkDetail(key, value);
							}
							if(identifier == 'chemical'){
								ajaxAddChemical2Detail(key, value);
							}
						}
					}
					else{
						//FORM WORKPLACE
						// nezaškrtnutá hodnota
						if($.inArray(key, vals) == -1){
							console.log('d');
							$("div.multiCheckbox" + identifierCap + "s." + controller).append('<label><input id=\"' + identifier + 'List-' +
								key + '\" type=\"checkbox\" value=\"' + key + '\" name=\"' + identifier + 'List[]\">' + value + '</label><br/>');
						}
						// ostatní hodnoty
						else{
							console.log('e');
							$("div.multiCheckbox" + identifierCap + "s." + controller).append('<label><input id=\"' + identifier + 'List-' +
									key + '\" type=\"checkbox\" checked=\"checked\" value=\"' + key + '\" name=\"' + identifier + 'List[]\">' + value
									+ '</label><br/>');
						}
						//FORM POSITION
						// nezaškrtnutá hodnota
						if($.inArray(key, vals2) == -1){
							console.log('f');
							$("div.multiCheckbox" + identifierCap + "s:not(." + controller + ")").append('<label><input id=\"' + identifier + 'List-' +
								key + '\" type=\"checkbox\" value=\"' + key + '\" name=\"' + identifier + 'List[]\">' + value + '</label><br/>');
						}
						// ostatní hodnoty
						else{
							console.log('g');
							$("div.multiCheckbox" + identifierCap + "s:not(." + controller + ")").append('<label><input id=\"' + identifier + 'List-' +
									key + '\" type=\"checkbox\" checked=\"checked\" value=\"' + key + '\" name=\"' + identifier + 'List[]\">' + value
									+ '</label><br/>');
						}
					}
				});
			}
		});
	}
	
	//volat při přidávání schooling
	function ajaxAppendCheckbox(identifier, controller){
		var clientId = $('#client_id').val();
		$.ajax({
			type: "POST",
			dataType: 'json',
			url: baseUrl + '/' + controller + '/populate' + identifier + 's',
			data: "clientId=" + clientId,
			async: false,
			success: function(json){
				var identifierCap = capitalizeFirstLetter(identifier);
				var oldItems = $("div.multiCheckbox" + identifierCap + "s label input");
				var vals = [];
				var i = 0;
				oldItems.each(function(){
					vals[i++] = $(this).val();
				});				
				$.each(json, function(key, value){
					if($.inArray(key, vals) == -1){
						$("div.multiCheckbox" + identifierCap + "s").append('<br/><label><input id=\"' + identifier + 'List-' + key +
								'\" type=\"checkbox\" checked=\"checked\" value=\"' + key + '\" name=\"' + identifier + 'List[]\">' +
								value + '</label>');
						if(identifier == 'schooling'){
							ajaxAddSchoolingDetail(key, value);
						}
					}
				});
			}
		});
	}
	
	//formulář pracovní pozice podmíněné zobrazování FPP
	$('select[id=categorization]').change(function(){
		toggleHiddenFactors(this);
	});
	
	$(document).ready(function(){
		var selectbox = $(document).find('select[id=categorization]');
		toggleHiddenFactors(selectbox);
	});
	
	function toggleHiddenFactors(selectbox){
		if($(selectbox).val() == 0){
			$(selectbox).parent().parent().next().next().addClass('hidden');
			$(selectbox).parent().parent().next().addClass('hidden');
			$('tr[id*=environmentFactorDetail]').next().next().next().next().addClass('hidden');
			$('tr[id*=environmentFactorDetail]').next().next().next().addClass('hidden');
			$('tr[id*=environmentFactorDetail]').next().next().addClass('hidden');
			$('tr[id*=environmentFactorDetail]').next().addClass('hidden');
			$('tr[id*=environmentFactorDetail]').addClass('hidden');
		}
		else{
			$(selectbox).parent().parent().next().next().removeClass('hidden');
			$(selectbox).parent().parent().next().removeClass('hidden');
			$('tr[id*=environmentFactorDetail]').next().next().next().next().removeClass('hidden');
			$('tr[id*=environmentFactorDetail]').next().next().next().removeClass('hidden');
			$('tr[id*=environmentFactorDetail]').next().next().removeClass('hidden');
			$('tr[id*=environmentFactorDetail]').next().removeClass('hidden');
			$('tr[id*=environmentFactorDetail]').removeClass('hidden');
		}
	}
});