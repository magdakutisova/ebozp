$(function () {
	
	var nextIndex = $("#progres-items li").length;
	
	// smazani polozky
	function removeItem() {
		$(this).parent().remove();
	}
	
	// pridani polozky prubehu
	function addProgres() {
		var li = $("<li>").append(
				$("<button type='button'>Odebrat</button>").click(removeItem)
		).append(
				$("<input type='text' style='width: 500px'>").attr("name", "report[item][" + nextIndex++ + "]")
		);
		
		$("#progres-items").append(li).sortable("refresh");
	}
	
	function appendImages() {
		// vygenerovani grafu
		var form = $("#download-form");
		
		$(".report").each(function () {
			var table = $(this);
			var index = table.attr("id").split("-")[1];
			
			// vygenerovani retezce obrazku
			var imgSrc = $("#chart-" + index).jqplotToImageStr();
			
			form.append($("<input type='hidden' name='chart[]' />").val(imgSrc));
		});
	}
	
	// razeni polozek seznamu prubehu
	$("#progres-items").sortable({ axis : "y" });
	$("#add-progres-item").click(addProgres);
	$("#progres-items button").click(removeItem);
	$("#download-form").submit(appendImages);
	
	// vygenerovani grafu
	$(".report").each(function () {
		var table = $(this);
		var index = table.attr("id").split("-")[1];
		
		var vals = new Array();
		var ticks = new Array("0");
		var tick = 0;
		
		// zpracovani hodnocnei
		table.find(".report-percent").each(function () {
			var text = $(this).text();
			var percent = Number(text.substr(0, text.length - 1));
			
			vals.push([percent]);
			ticks.push(String(tick++));
		});
		
		ticks.push(String(tick++));
		
		$.jqplot("chart-" + index, vals, {
			seriesDefaults:{
	            renderer:$.jqplot.BarRenderer,
	            rendererOptions: {fillToZero: true},
	            color : "#ff0000"
	        },
			
			axes : {
				xaxis : {
					renderer : $.jqplot.CategoryAxisRenderer,
					ticks : ticks
				},
				
				yaxis : {
					max : 100,
					min : 0
				}
			}
		});
	});
});