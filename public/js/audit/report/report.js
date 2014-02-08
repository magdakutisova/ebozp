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
		var form = $(this);
		
		$(".report").each(function () {
			var table = $(this);
			var index = table.attr("id").split("-")[1];
			
			// vygenerovani retezce obrazku
			var imgSrc = $("#chart-" + index).jqplotToImageStr();
			
			form.append($("<input type='hidden' name='chart[]' />").val(imgSrc));
		});
	}
    
    function downloadProtocol() {
        $("#loader").remove();
        var form = $("#download-form");
        form.attr("target", "_self");
        form.submit();
        form.attr("target", "_blank");
    }
	
	// razeni polozek seznamu prubehu
	$("#progres-items").sortable({ axis : "y" });
	$("#add-progres-item").click(addProgres);
	$("#progres-items button").click(removeItem);
	$("#download-form,#send-form").submit(appendImages);
	
	// vygenerovani grafu
	$(".report").each(function () {
		var table = $(this);
		var index = table.attr("id").split("-")[1];
		
		var vals = new Array([]);
		var ticks = new Array();
		var tick = 0;
		
		// zpracovani hodnocnei
		table.find(".report-percent").each(function () {
			var text = $(this).text();
			var percent = Number(text.substr(0, text.length - 1));
			
			vals[0].push(percent);
			ticks.push($(this).parent().children(":first").text());
		});
		
		var opts = {
				seriesDefaults:{
		            renderer:$.jqplot.BarRenderer,
		            rendererOptions: {
		            	fillToZero: true, 
		            	barPadding : 1,
		            	barMargin : 5,
		            	barDirection : "vertical",
		            	barWidth: 15
		            },
		            color : "#ff0000"
		        },
		        
		        axes : {
	            	xaxis : {
	            		showTicks : false
					},
					
					yaxis : {
						max : 100,
						min : 0,
						padMax: 5
					}
	            }
			};
		
		$.jqplot("chart-" + index, vals, opts);
	});
    /*
    if (location.href.indexOf("__autodownload__=1") !== -1) {
        var wrapper = $("<div id='loader' style='left: 0; top: 0; width: 100%; height: 100%; position: absolute; background: fixed rgba(0, 0, 0, 0.1);'>").appendTo("body");
        
        $("<div style='position: absolute; left: 40%; top: 45%; width: 20%; height: 10%; background: #ffffff url(/images/loader.gif) center no-repeat; border: 1px solid #acacac; padding: 5px; margin: 5px; text-align: center'>").text("Chvíli strpení prosím").appendTo(wrapper)
        window.setTimeout(downloadProtocol, 1500);
    }*/
});