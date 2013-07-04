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
	
	$("div#filtered").on("click", ".concealer", function(){
		$(this).next("ul").toggleClass("hidden");
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
	
	$(".district").click(function(){
		$.get($(this).attr("action"));
		$("#filtered").load("./klienti/okres/ #filtered");
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
	$('#position').on("change", "select[id*=frequency]", function(){
		var value = $(this).find('option:selected').val();
		toggleHiddenFrequency(this, value);
	});

	$(document).ready(function(){
		var selectboxes = $(document).find('select[id*=frequency]');
		selectboxes.each(function(){
			var value = $(this).find('option:selected').val();
			toggleHiddenFrequency($(this), value);
		});
	});
	
	function toggleHiddenFrequency(selectbox, value){
		if(value == 6){
			$(selectbox).parent().next().children('label').removeClass('hidden');
			$(selectbox).parent().next().children('input').attr('hidden', false);
		}
		else{
			$(selectbox).parent().next().children('label').addClass('hidden');
			$(selectbox).parent().next().children('input').attr('hidden', true);
			$(selectbox).parent().next().children('input').val('');
		}
	}
	
	$(document).ready(function(){
		if($(".errors")[0]){
			$("form").before('<div class="form-error">Data nebyla uložena. Formulář je chybně vyplněn. Chybně vyplněná pole obsahují další informace.</div>');
		}
	});
	
	//dynamické záležitosti u klienta a pobočky
	$("#new_contact_person").click(function(){
		ajaxAddContactPerson();
	});
	
	function ajaxAddContactPerson(){
		var id = $("#id_contact_person").val();
		var clientId = $("#id_client").val();
		$.ajax({
			type: "POST",
			url: baseUrl + '/client/newcontactperson/format/html',
			data: "id_contact_person=" + id + "&clientId=" + clientId,
			success: function(newElement){
				$('#new_contact_person').parents('tr').before(newElement);
				$('#id_contact_person').val(++id);
			}
		});
	}
	
	$("#new_doctor").click(function(){
		ajaxAddDoctor();
	});
	
	function ajaxAddDoctor(){
		var id = $("#id_doctor").val();
		var clientId = $("#id_client").val();
		$.ajax({
			type: "POST",
			url: baseUrl + '/client/newdoctor/format/html',
			data: "id_doctor=" + id + "&clientId=" + clientId,
			success: function(newElement){
				$('#new_doctor').parents('tr').before(newElement);
				$('#id_doctor').val(++id);
			}
		});
	}
	
	$("#new_responsible").click(function(){
		ajaxAddResponsible();
	});
	
	function ajaxAddResponsible(){
		var id = $("#id_responsible").val();
		var clientId = $("#id_client").val();
		$.ajax({
			type: "POST",
			url: baseUrl + '/client/newresponsible/format/html',
			data: "id_responsible=" + id + "&clientId=" + clientId,
			success: function(newElement){
				$('#new_responsible').parents('tr').before(newElement);
				$('#id_responsible').val(++id);
			}
		});
	}
	
	//přidávání odpovědnosti
	var validatorResponsibility = $('#responsibility').validate({
		rules: {
			responsibility: {
				required: true
			},
		},
		messages: {
			responsibility: "Uveďte název nové odpovědnosti.",
		}
	});
	
	$('form').on('click', '#new_responsibility', function(){
		$('#new_responsibility_form input[type=text]').val('');
		validatorResponsibility.resetForm();
		var rowId = $(this).parent().parent().attr("id");
		$('#new_responsibility_form #rowId').val(rowId);
		$('#new_responsibility_form').dialog('open');
	});
	
	$('#new_responsibility_form').dialog({
		autoOpen: false,
		height: 500,
		width: 700,
		modal: true,
		title: 'Zadejte název nové odpovědnosti',
	});
	
	$('#save_responsibility').click(function(){
		ajaxSaveResponsibility();
		ajaxPopulateResponsibilitySelect();
	});
	
	function ajaxSaveResponsibility(){
		$.ajax({
			type: "POST",
			url: baseUrl + '/client/addresponsibility/format/html',
			data: $("#responsibility").serializeArray(),
			async: false,
			success: function(){
				$('#new_responsibility_form').dialog("close");
			}
		});
	}
	
	function ajaxPopulateResponsibilitySelect(){
		var clientId = $("id_client").val();
		$.ajax({
			type: "POST",
			dataType: 'json',
			url: baseUrl + '/client/populateresponsibility',
			data: (clientId != undefined) ? 'clientId=' + clientId : undefined,
			async: false,
			success: function(json){
				var rowId = $('#new_responsibility_form #rowId').val();
				var elSelected = $('select[id="' + rowId + '-id_responsibility"]');
				elSelected.empty();
				var el = $("select[id*='id_responsibility']:not([id*='" + rowId + "'])");
				var vals = [];
				var i = 0;
				el.children("option").each(function(){
					vals[i++] = $(this).val();
				});
				$.each(json, function(key, value){
					if($.inArray(key, vals) != -1){
						elSelected.append($("<option></option>").attr("value", key).text(value));
					}
					else{
						el.append($("<option></option>").attr("value", key).text(value));
						elSelected.append($("<option></option>").attr("value", key).attr("selected", "selected").text(value));
					}
				});
			}
		});
	}
	
	//přidávání odpovědného zaměstnance
	var validatorResponsibleEmployee = $('#responsible_employee').validate({
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
	
	$('form').on('click', '#new_responsible_employee', function(){
		$('#new_responsible_employee_form input[type=text]').val('');
		validatorResponsibleEmployee.resetForm();
		var rowId = $(this).parent().parent().attr("id");
		$('#new_responsible_employee_form #rowId').val(rowId);
		$('#new_responsible_employee_form').dialog('open');
	});
	
	$('#new_responsible_employee_form').dialog({
		autoOpen: false,
		height: 500,
		width: 700,
		modal: true,
		title: 'Zadejte údaje nového zaměstnance',
	});
	
	$('#save_responsible_employee').click(function(){
		ajaxSaveResponsibleEmployee();
		ajaxPopulateResponsibleEmployeeSelect();
	});
	
	function ajaxSaveResponsibleEmployee(){
		$.ajax({
			type: "POST",
			url: baseUrl + '/client/addresponsibleemployee/format/html',
			data: $("#responsible_employee").serializeArray(),
			async: false,
			success: function(){
				$('#new_responsible_employee_form').dialog("close");
			}
		});
	}
	
	function ajaxPopulateResponsibleEmployeeSelect(){
		var clientId = $("id_client").val();
		$.ajax({
			type: "POST",
			dataType: 'json',
			url: baseUrl + '/client/populateresponsibleemployee',
			data: (clientId != undefined) ? 'clientId=' + clientId : undefined,
			async: false,
			success: function(json){
				var rowId = $('#new_responsible_employee_form #rowId').val();
				var elSelected = $('select[id="' + rowId + '-id_employee"]');
				elSelected.empty();
				var el = $("select[id*='id_employee']:not([id*='" + rowId + "'])");
				var vals = [];
				var i = 0;
				el.children("option").each(function(){
					vals[i++] = $(this).val();
				});
				$.each(json, function(key, value){
					if($.inArray(key, vals) != -1){
						elSelected.append($("<option></option>").attr("value", key).text(value));
					}
					else{
						el.append($("<option></option>").attr("value", key).text(value));
						elSelected.append($("<option></option>").attr("value", key).attr("selected", "selected").text(value));
					}
				});
			}
		});
	}
	
	//zaškrtnutí všech poboček - pracovní pozice	
	$("form#position").on("click", "#subsidiariesAll", function(){
		var checkboxes = $(".multiCheckboxSubsidiaries").find(':checkbox');
		if($(this).is(':checked')){
			checkboxes.prop('checked', true);
		}
		else{
			checkboxes.prop('checked', false);
		}
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
