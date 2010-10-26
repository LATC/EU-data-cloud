/* Authors: Christophe Guéret <cgueret@few.vu.nl>
 * 
 */
$(function() {
	// Makes the configuration list sortable
	$("#configurations-list").sortable({
		placeholder : 'ui-state-highlight'
	});

	// Bind the handler for changes
	$("#configurations-list").bind("sortupdate", handleOrderChange);
	$("#configurations-list").disableSelection();

	// Initialise the list with a request to the API
	$.getJSON('api/configuration/list', function(data) {
		$("#configurations-list").empty();
		$.each(data.queue, function(index, item) {
			var li = $("<li/>").text(item.description).attr('id',
					'item_' + item.identifier).addClass('ui-state-default');
			var edit = $("<button/>").text("edit");
			edit.appendTo(li);
			li.appendTo($("#configurations-list"));
		});
	});
});


/**
 * @param event
 * @param ui
 */
function handleOrderChange(event, ui) {
	$.ajax({
		type : 'PUT',
		url : 'api/configuration/list',
		data : $("#configurations-list").sortable('serialize'),
		dataType : "text"
	});
}