$((new function () {
	this.init = function () {
		$("#catlist").sortable({
			axis : "y"
		});
	};
}()).init);