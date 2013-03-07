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
	
	//VŠEOBECNÉ FUNKCE
	$(".ajaxSave").click(function(){
		var elementClass = $(this).attr('class').split(' ');
		var identifier = elementClass[0];
		var controller = $("h2").next("form").attr('id');
		if($('#' + identifier).valid()){
			ajaxSaveItem(identifier, controller);
			ajaxPopulateSelects(identifier, controller);
		}
	});
	
	function ajaxSaveItem(identifier, controller){
		$.ajax({
			type: "POST",
			url: baseUrl + '/' + controller + '/add' + identifier + '/format/html',
			data: $("#" + identifier).serializeArray(),
			async: false,
			success: function(newElement){
				console.log("OK");
				$('#new_' + identifier + '_form').dialog("close");
			}
		});
	}
	
	function capitalizeFirstLetter(string){
		return string.charAt(0).toUpperCase() + string.slice(1);
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
					if($.inArray(value, labelArray) == -1){
						$("div.multiCheckbox" + identifierCap + "s").append('<label><input id=\"' + identifier + 'List-' +
								key + '\" type=\"checkbox\" checked=\"checked\" value=\"' + key 
								+ '\" name=\"' + identifier + 'List[]\">' + value
								+ '</label><br/>');
					}
					else if($.inArray(key, vals) == -1){
						$("div.multiCheckbox" + identifierCap + "s").append('<label><input id=\"' + identifier + 'List-' +
							key + '\" type=\"checkbox\" value=\"' + key + '\" name=\"' + identifier + 'List[]\">' + value + '</label><br/>');	
					}
					else{
						$("div.multiCheckbox" + identifierCap + "s").append('<label><input id=\"' + identifier + 'List-' +
								key + '\" type=\"checkbox\" checked=\"checked\" value=\"' + key + '\" name=\"' + identifier + 'List[]\">' + value
								+ '</label><br/>');
					}
				});
			}
		});
	}
});