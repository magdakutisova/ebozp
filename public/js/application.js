$(function(){
	$(".view-link").click(function(){
		$("#pobocky").removeClass("hidden");
		$("#submit").val("Zobrazit");
		$("#submit").attr("name", "view");
		$("#submit").removeAttr("onClick");
	});
	
	$(".edit-link").click(function(){
		$("#pobocky").removeClass("hidden");
		$("#submit").val("Editovat");
		$("#submit").attr("name", "edit");
		$("#submit").removeAttr("onClick");
	});
	
	$(".delete-link").click(function(){
		$("#pobocky").removeClass("hidden");
		$("#submit").val("Smazat");
		$("#submit").attr("name", "delete");
		$("#submit").attr("onClick", "return confirm('Opravdu si přejete pobočku smazat?')");
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
});
