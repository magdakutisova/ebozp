$(function () {
	
	// inicializace sbalovani klientu
	var clients = $("#client-list");
	
	/**
	 * rozbali polozku
	 */
	function rollDown() {
		var context = $(this);
		
		context.text("-");
		context.parents("li:first").find(">ul").removeClass("hidden");
	}
	
	/**
	 * sbali polozku
	 */
	function rollUp() {
		var context = $(this);
		
		context.text("+");
		context.parents("li:first").find(">ul").addClass("hidden");
	}
	
	function rollToggle() {
		if ($(this).text() == "+") {
			rollDown.apply(this);
		} else {
			rollUp.apply(this);
		}
	}
	
	clients.find("span.tree-roller").click(rollToggle);
});