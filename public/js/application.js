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
		
	
	$(".messages-link").click(function(){
		$("#zpravy").toggleClass("hidden");
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
	
	//dynamické přidávání pracovních pozic
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
	
	$(".print").click(function(){
		window.print();
	});
	
});
