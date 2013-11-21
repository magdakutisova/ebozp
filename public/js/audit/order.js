$(function () {
	
	// zobrazeni okna s informacemi o obednavce
	function displayOrder() {
		var id = $(this).attr("name").split("-")[1];
		
		var url = "/audit/order/edit.html?orderId=" + id;
		
		$.iframeDialog(url, 800, 400);
	}
	
	$("#order-table button").click(displayOrder);
});