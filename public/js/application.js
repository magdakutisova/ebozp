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
	
	$(".edit-workplace-link").click(function(){
		$("#pracoviste").removeClass("hidden");
		$("#pobocky").addClass("hidden");
		$("#submitWorkplace").val("Editovat pracoviště");
		$("#submitWorkplace").attr("name", "editWorkplace");
		$("#submitWorkplace").removeAttr("onClick");
	});
	
	$(".delete-workplace-link").click(function(){
		$("#pracoviste").removeClass("hidden");
		$("#pobocky").addClass("hidden");
		$("#submitWorkplace").val("Smazat pracoviště");
		$("#submitWorkplace").attr("name", "deleteWorkplace");
		$("#submitWorkplace").attr("onClick", "return confirm('Opravdu si přejete pracoviště smazat?')");
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
	
	//dynamické přidávání faktorů pracovního prostředí
	$("#new_factor").click(function(){
		ajaxAddFactor();
	});
	
	function ajaxAddFactor(){
		var id = $("#id_factor").val();
		$.ajax({
			type: "POST",
			url: baseUrl + '/workplace/newfactor/format/html',
			data: "id_factor=" + id,
			success: function(newElement){
				$('#new_factor').parents('tr').before(newElement);
				$("#id_factor").val(++id);
				//console.log(newElement);
			}
		});
	}
	
	//dynamické přidávání hlavních rizik
	$("#new_risk").click(function(){
		ajaxAddRisk();
	});
	
	function ajaxAddRisk(){
		var id = $("#id_risk").val();
		$.ajax({
			type: "POST",
			url: baseUrl + '/workplace/newrisk/format/html',
			data: "id_risk=" + id,
			success: function(newElement){
				$('#new_risk').parents('tr').before(newElement);
				$("#id_risk").val(++id);
			}
		});
	}
	
	$(".print").click(function(){
		window.print();
	});
});
