var baseUrl = BASE_URL;

$(function(){
	$(".view-link").click(function(){
		$("#pobocky").removeClass("hidden");
		$("#submit").val("Zobrazit");
		$("#submit").attr("name", "view");
		$("#submit").removeAttr("onClick");
	});
	
	$(".edit-subsidiary-link").click(function(){
		$("#pobocky").removeClass("hidden");
		$("#pracoviste").addClass("hidden");
		$("#submit").val("Editovat pobočku");
		$("#submit").attr("name", "editSubsidiary");
		$("#submit").removeAttr("onClick");
	});
	
	$(".delete-subsidiary-link").click(function(){
		$("#pobocky").removeClass("hidden");
		$("#pracoviste").addClass("hidden");
		$("#submit").val("Smazat pobočku");
		$("#submit").attr("name", "deleteSubsidiary");
		$("#submit").attr("onClick", "return confirm('Opravdu si přejete pobočku smazat? Budou smazána i veškerá závislá pracoviště!')");
	});
	
	$(".showTr").click(function(){
		$("tr").removeClass("hidden");
	});
	
	$("table").on("click", ".showNotes", function(){
		$(this).parent().parent().next().removeClass("hidden");
	});
		
	$("div").on("click", ".show-info", function(){
		$(this).parent().next().toggleClass("hidden");
	});
	
	$(".messages-link").click(function(){
		$("#zpravy").toggleClass("hidden");
	});
	
	$(".show-folder-form").click(function(){
		$(".folder-form").toggleClass("hidden");
	});
	
	$(".show-folder-delete").click(function(){
		$(".folder-delete").toggleClass("hidden");
	});
	
	$(".list").click(function(){
		$.get($(this).attr("action"));
		$("#filtered").load("./klienti/nazev/ #filtered");
	});
	
	$(".technician").click(function(){
		$.get($(this).attr("action"));
		$("#filtered").load("./klienti/bt/ #filtered");
	});
	
	$(".coordinator").click(function(){
		$.get($(this).attr("action"));
		$("#filtered").load("./klienti/koo/ #filtered");
	});
	
	$(".town").click(function(){
		$.get($(this).attr("action"));
		$("#filtered").load("./klienti/obec/ #filtered");
	});
	
	$(".lastOpen").click(function(){
		$.get($(this).attr("action"));
		$("#filtered").load("./klienti/naposledy/ #filtered");
	});
	
	$(".register").click(function(){
		$.get($(this).attr("action"));
		$("#user-content").load("./administrace-uzivatelu/vytvorit/ #user-content");
	});
	
	$(".rights").click(function(){
		$.get($(this).attr("action"));
		$("#user-content").load("./administrace-uzivatelu/prava/ #user-content");
	});
	
	$(".delete").click(function(){
		$.get($(this).attr("action"));
		$("#user-content").load("./administrace-uzivatelu/smazat/ #user-content");
	});
	
	$("#invoice_address").click(function(){
		var checkbox = $(this);
		if (checkbox.is(':checked')){
			$("#invoice_street").attr('disabled', true).val('');
			$("#invoice_code").attr('disabled', true).val('');
			$("#invoice_town").attr('disabled', true).val('');
		}
		else {
			$("#invoice_street").removeAttr('disabled');
			$("#invoice_code").removeAttr('disabled');
			$("#invoice_town").removeAttr('disabled');
		}
	});
	
	//checkboxy v adresáři
	$('form#tree > div > span > ul > li > input').click(function(){
		//vybrat vše
		if($(this).attr('id') == 'tree-0'){
			if($('form#tree input#tree-0').is(':checked')){
				$('form#tree input').attr('checked', true);
			}
			else{
				$('form#tree input').attr('checked', false);
			}
		}
		//když je klient zaškrtnutý, vybrat pobočky
		if($(this).is(':checked')){
			var nodeId = $(this).attr('id');
			$('input#' + nodeId + ' ~ ul > li > input').attr('checked', true);
		}
	});
	
	//když cokoli odškrtnu, odškrtnout "vybrat vše"
	$('form#tree input').click(function(){
		if(!$(this).is(':checked')){
			$('form#tree input#tree-0').attr('checked', false);
		}
	});
	
	//formulář pracovní pozice podmíněné zobrazování četnosti
	$('select[id*=frequency]').change(function(){
		toggleHiddenFrequency(this);
	});

	$(document).ready(function(){
		var selectbox = $(document).find('select[id*=frequency]');
		toggleHiddenFrequency(selectbox);
	});
	
	function toggleHiddenFrequency(selectbox){
		if($(selectbox).val() == 6){
			$(selectbox).parent().next().children('label').removeClass('hidden');
			$(selectbox).parent().next().children('input').attr('hidden', false);
		}
		else{
			$(selectbox).parent().next().children('label').addClass('hidden');
			$(selectbox).parent().next().children('input').attr('hidden', true);
			$(selectbox).parent().next().children('input').val('');
		}
	}
	
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
	
	$("#save_employee").click(function(){
		if($('#employee').valid()){
			ajaxSaveEmployee();
			ajaxPopulateSelects();
		}
	});
	
	function ajaxSaveEmployee(){
		$.ajax({
			type: "POST",
			url: baseUrl + '/position/addemployee/format/html',
			data: $("#employee").serializeArray(),
			async: false,
			success: function(newElement){
				console.log("OK");
				$('#new_employee_form').dialog("close");
			}
		});
	}
	
	function ajaxPopulateSelects(){
		var clientId = $("#client_id").val();
		$.ajax({
			type: "POST",
			dataType: 'json',
			url: baseUrl + '/position/populateselects',
			data: "clientId=" + clientId,
			async: false,
			success: function(json){	
				var checkedItems = $("div.multiCheckboxEmployees label input:checked");
				var vals = [];
				var i = 0;
				checkedItems.each(function(){
					vals[i++] = $(this).val();
				});
				var labels = $("div.multiCheckboxEmployees label");
				var labelArray = [];
				var j = 0;
				labels.each(function(){
					labelArray[j++] = $(this).text();
				});
				$("div.multiCheckboxEmployees").empty();
				$.each(json, function(key, value){
					if($.inArray(value, labelArray) == -1){
						$("div.multiCheckboxEmployees").append('<label><input id=\"employeeList-' +
								key + '\" type=\"checkbox\" checked=\"checked\" value=\"' + key 
								+ '\" name=\"employeeList[]\">' + value
								+ '</label><br/>');
					}
					else if($.inArray(key, vals) == -1){
						$("div.multiCheckboxEmployees").append('<label><input id=\"employeeList-' +
							key + '\" type=\"checkbox\" value=\"' + key + '\" name=\"employeeList[]\">' + value + '</label><br/>');	
					}
					else{
						$("div.multiCheckboxEmployees").append('<label><input id=\"employeeList-' +
								key + '\" type=\"checkbox\" checked=\"checked\" value=\"' + key + '\" name=\"employeeList[]\">' + value
								+ '</label><br/>');
					}
				});
			}
		});
	}
	
	//PŘIDÁVÁNÍ PRACOVNÍ POZICE
	$("#new_position").click(function(){
		ajaxAddPosition();
	});
	
	function ajaxAddPosition(){
		var id = $("#id_position").val();
		var clientId = $("#client_id").val();
		$.ajax({
			type: "POST",
			url: baseUrl + '/workplace/newposition/format/html',
			data: "id_position=" + id + "&clientId=" + clientId,
			success: function(newElement){
				$('#new_position').parents('tr').before(newElement);
				$("#id_position").val(++id);
			}
		});
	}
	
	//dynamické přidávání pracovních činností
	$("#new_work").click(function(){
		ajaxAddWork();
	});
	
	function ajaxAddWork(){
		var id = $("#id_work").val();
		var clientId = $("#client_id").val();
		$.ajax({
			type: "POST",
			url: baseUrl + '/workplace/newwork/format/html',
			data: "id_work=" + id + "&clientId=" + clientId,
			success: function(newElement){
				$('#new_work').parents('tr').before(newElement);
				$('#id_work').val(++id);
			}
		});
	}
	
	$("#new_work_to_position").click(function(){
		ajaxAddWorkToPosition();
	});
	
	function ajaxAddWorkToPosition(){
		var id = $("#id_work").val();
		var clientId = $("#client_id").val();
		$.ajax({
			type: "POST",
			url: baseUrl + '/position/newwork/format/html',
			data: "id_work=" + id + "&clientId=" + clientId,
			success: function(newElement){
				$('#new_work_to_position').parents('tr').before(newElement);
				$('#id_work').val(++id);
			}
		});
	}
	
	//dynamické přidávání technických prostředků
	$("#new_technical_device").click(function(){
		ajaxAddTechnicalDevice();
	});
	
	function ajaxAddTechnicalDevice(){
		var id = $("#id_technical_device").val();
		var clientId = $("#client_id").val();
		$.ajax({
			type: "POST",
			url: baseUrl + '/workplace/newtechnicaldevice/format/html',
			data: "id_technical_device=" + id + "&clientId=" + clientId,
			success: function(newElement){
				$('#new_technical_device').parents('tr').before(newElement);
				$('#id_technical_device').val(++id);
			}
		});
	}
	
	$("#new_technical_device_to_position").click(function(){
		ajaxAddTechnicalDeviceToPosition();
	});
	
	function ajaxAddTechnicalDeviceToPosition(){
		var id = $("#id_technical_device").val();
		var clientId = $("#client_id").val();
		$.ajax({
			type: "POST",
			url: baseUrl + '/position/newtechnicaldevice/format/html',
			data: "id_technical_device=" + id + "&clientId=" + clientId,
			success: function(newElement){
				$('#new_technical_device_to_position').parents('tr').before(newElement);
				$('#id_technical_device').val(++id);
			}
		});
	}
	
	//dynamické přidávání chemických látek
	$("#new_chemical").click(function(){
		ajaxAddChemical();
	});
	
	function ajaxAddChemical(){
		var id = $("#id_chemical").val();
		var clientId = $("#client_id").val();
		$.ajax({
			type: "POST",
			url: baseUrl + '/workplace/newchemical/format/html',
			data: "id_chemical=" + id + "&clientId=" + clientId,
			success: function(newElement){
				$('#new_chemical').parents('tr').before(newElement);
				$('#id_chemical').val(++id);
			}
		});
	}
	
	$("#new_chemical_to_position").click(function(){
		ajaxAddChemicalToPosition();
	});
	
	function ajaxAddChemicalToPosition(){
		var id = $("#id_chemical").val();
		var clientId = $("#client_id").val();
		$.ajax({
			type: "POST",
			url: baseUrl + '/position/newchemical/format/html',
			data: "id_chemical=" + id + "&clientId=" + clientId,
			success: function(newElement){
				$('#new_chemical_to_position').parents('tr').before(newElement);
				$('#id_chemical').val(++id);
			}
		});
	}
	
	//dynamické přidávání zaměstnanců
	$("#new_current_employee").click(function(){
		ajaxAddCurrentEmployee();
	});
	
	function ajaxAddCurrentEmployee(){
		var id = $("#id_current_employee").val();
		var clientId = $("#client_id").val();
		$.ajax({
			type: "POST",
			url: baseUrl + '/position/newcurrentemployee/format/html',
			data: "id_current_employee=" + id + "&clientId=" + clientId,
			success: function(newElement){
				$('#new_current_employee').parents('tr').before(newElement);
				$('#id_current_employee').val(++id);
			}
		});
	}
	
	//dynamické přidávání FPP
	$("#new_environment_factor").click(function(){
		ajaxAddEnvironmentFactor();
	});
	
	function ajaxAddEnvironmentFactor(){
		var id = $("#id_environment_factor").val();
		var clientId = $("#client_id").val();
		$.ajax({
			type: "POST",
			url: baseUrl + '/position/newenvironmentfactor/format/html',
			data: "id_environment_factor=" + id + "&clientId=" + clientId,
			success: function(newElement){
				$('#new_environment_factor').parents('tr').before(newElement);
				$('#id_environment_factor').val(++id);
			}
		});
	};
	
	//dynamické přidávání školení
	$("#new_schooling").click(function(){
		ajaxAddSchooling();
	});
	
	function ajaxAddSchooling(){
		var id = $("#id_schooling").val();
		var clientId = $("#client_id").val();
		$.ajax({
			type: "POST",
			url: baseUrl + '/position/newschooling/format/html',
			data: "id_schooling=" + id + "&clientId=" + clientId,
			success: function(newElement){
				$('#new_schooling').parents('tr').before(newElement);
				$('#id_schooling').val(++id);
			}
		});
	}
	
	$("#new_newSchooling").click(function(){
		ajaxAddNewSchooling();
	});
	
	function ajaxAddNewSchooling(){
		var id = $("#id_newSchooling").val();
		var clientId = $("#client_id").val();
		$.ajax({
			type: "POST",
			url:baseUrl + '/position/newnewschooling/format/html',
			data: "id_newSchooling=" + id + "&clientId=" + clientId,
			success: function(newElement){
				$('#new_newSchooling').parents('tr').before(newElement);
				$('#id_newSchooling').val(++id);
			}
		});
	}
	
	//dynamické odebírání pracovních pozic
	$(".remove_position").click(function(){
		removePositionFromDb(this);
		removePositionFromHtml(this);
	});
	
	function removePositionFromDb(row){
		var positionId = $(row).parent().siblings().filter(":first").val();
		var clientId = $("#client_id").val();
		var subsidiaryId = $("#subsidiary_id").val();
		var workplaceId = $("#id_workplace").val();
		$.ajax({
			type: "POST",
			url: baseUrl + '/workplace/removeposition/format/html',
			data: "positionId=" + positionId + "&clientId=" + clientId + "&subsidiaryId=" + subsidiaryId + "&workplaceId=" + workplaceId,
			success: function(){
				console.log("OK");
			}
		});
	}
	
	function removePositionFromHtml(row){
		$(row).parent().parent().next().remove();
		$(row).parent().parent().remove();
	}
	
	//dynamické odebírání pracovních činností
	$(".remove_work").click(function(){
		removeWorkFromDb(this);
		removeWorkFromHtml(this);
	});
	
	function removeWorkFromDb(row){
		var workId = $(row).parent().siblings().filter(":first").val();
		var clientId = $("#client_id").val();
		var subsidiaryId = $("#subsidiary_id").val();
		var workplaceId = $("#id_workplace").val();
		alert(workId + ' ' + clientId + ' ' + subsidiaryId + ' ' + workplaceId);
		$.ajax({
			type: "POST",
			url: baseUrl + '/workplace/removework/format/html',
			data: "workId=" + workId + "&clientId=" + clientId + "&subsidiaryId=" + subsidiaryId + "&workplaceId=" + workplaceId,
			success: function(){
				console.log("OK");
			}
		});
	}
	
	function removeWorkFromHtml(row){
		$(row).parent().parent().next().remove();
		$(row).parent().parent().remove();
	}
	
	//dynamické odebírání technických prostředků
	$(".remove_technical_device").click(function(){
		removeTechnicalDeviceFromDb(this);
		removeTechnicalDeviceFromHtml(this);
	});
	
	function removeTechnicalDeviceFromDb(row){
		var technicalDeviceId = $(row).parent().siblings().filter(":first").val();
		var clientId = $("#client_id").val();
		var subsidiaryId = $("#subsidiary_id").val();
		var workplaceId = $("#id_workplace").val();
		$.ajax({
			type: "POST",
			url: baseUrl + '/workplace/removetechnicaldevice/format/html',
			data: "technicalDeviceId=" + technicalDeviceId + "&clientId=" + clientId + "&subsidiaryId=" + subsidiaryId + "&workplaceId=" + workplaceId,
			success: function(){
				console.log("OK");
			}
		});
	}
	
	function removeTechnicalDeviceFromHtml(row){
		$(row).parent().parent().next().remove();
		$(row).parent().parent().remove();
	}
	
	//dynamické odebírání chemických látek
	$(".remove_chemical_complete").click(function(){
		removeChemicalCompleteFromDb(this);
		removeChemicalCompleteFromHtml(this);
	});
	
	function removeChemicalCompleteFromDb(row){
		var chemicalId = $(row).parent().siblings().filter(":first").val();
		var clientId = $("#client_id").val();
		var subsidiaryId = $("#subsidiary_id").val();
		var workplaceId = $("#id_workplace").val();
		$.ajax({
			type: "POST",
			url: baseUrl + '/workplace/removechemical/format/html',
			data: "chemicalId=" + chemicalId + "&clientId=" + clientId + "&subsidiaryId=" + subsidiaryId + "&workplaceId=" + workplaceId,
			success: function(){
				console.log("OK");
			}
		});
	}
	
	function removeChemicalCompleteFromHtml(row){
		$(row).parent().parent().next().remove();
		$(row).parent().parent().next().remove();
		$(row).parent().parent().remove();
	}
	
	$(".print").click(function(){
		window.print();
	});
	
});

/**
 * otevre plovouci okno s vlozenou strankou IFRAME
 * rozmery jsou v pixelech
 * vraci DIV ktery je obsahem dialogu
 */
$.iframeDialog = function (src, width, height, title) {
	var iframe = $("<iframe width='" + width + "px' height='" + height + "px'>").attr("src", src);
	
	var retVal = $("<div>").append(iframe);
	
	width += 20;
	
	if (title === undefined) title = "";
	
	retVal.dialog({
		modal: true,
		width: width,
		height: height + 50,
		draggable: false,
		title : title,
		close: function () {
			retVal.remove();
		}
	});
	
	
	return retVal;
};

(function ($) {
	
	var availableStatuses = ["green", "yellow", "red"];
	var actualStatus = 0;
	var element = null;
	
	function removeStatus(sem) {
		for (var i in availableStatuses) sem.removeClass("sem-" + availableStatuses[i]);
	}
	
	function setValue() {
		// zjisteni pozice
		var i = 0;
		var item = $(this);
		var currItem = item;
		while (currItem.prev().length) {
			i++;
			currItem = currItem.prev();
		}
		
		var sem = item.parent();
		removeStatus(sem);
		sem.addClass("sem-" + availableStatuses[i]);
		actualStatus = i;
	}
	
	var methods = {
		"set" : function (value) {
			removeStatus(this);
			actualStatus = value;
			
			this.addClass("sem-" + availableStatuses[value]);
		},
		
		"status" : function () {
			for (var i in availableStatuses) {
				if (this.hasClass("sem-" + availableStatuses[i])) return i;
			}
			
			return -1;
		}
	};
	
	$.fn.semaphore = function (option, value) {
		if (option === undefined || option.constructor == Object) {
			
			options = $.extend({
				"status" : 0,
				"click" : $.noop,
				"readonly" : false
			}, option);
			
			element = $(this);
			
			options.status = Number(options.status);
			
			for (var i = 0; i < 3; i++) {
				var patch = $("<div></div>");
				
				if (!options.readonly)
					patch.click(setValue);
				
				$(this).append(patch);
				
				if (i == options.status) patch.click();
			}
			
			$(this).click(options.click);
			actualStatus = options.status;
			
			
			return;
		}
		
		switch (option) {
		case "set":
			methods["set"].apply(this, [Number(value)]);
			break;
			
		case "status":
			return methods["status"].apply(this, []);
		}
	};
})(jQuery);
