/**
 * Initialisation
 */
$(function() {
	// Load the GET variables, code from
	// http://techfeed.net/blog/index.cfm/2007/2/6/JavaScript-URL-variables
	document.getVars = [];
	var urlHalves = String(document.location).split('?');
	if (urlHalves[1]) {
		var urlVars = urlHalves[1].split('&');
		for ( var i = 0; i <= (urlVars.length); i++) {
			if (urlVars[i]) {
				var urlVarPair = urlVars[i].split('=');
				document.getVars[urlVarPair[0]] = urlVarPair[1];
			}
		}
	}
	var id = document.getVars.id;

	// Load and display the information
	$.getJSON('../api/configuration/' + id + '/about', function(data) {
		displayInformation(id, data);
	});

	// Load and display the latest reports
	$.getJSON('../api/configuration/' + id + '/reports', function(data) {
		displayReports(id, data);
	});
});

/**
 * @param id
 * @param data
 */
function displayInformation(id, data) {
	$("<p>").html("<b>Identifier</b> = " + data.identifier).appendTo(
			$("#anchor_info"));
	$("<p>").html("<b>Position in processing queue</b> = " + data.position)
			.appendTo($("#anchor_info"));
	$("<p>").html("<b>Title</b> = " + data.title).appendTo($("#anchor_info"));
	$("<p>").html("<b>Description</b> = " + data.description).appendTo(
			$("#anchor_info"));
}

/**
 * @param id
 * @param data
 */
function displayReports(id, data) {
	var table = $("<table>");
	$("<tr>").html("<th>Date</th><th>Message</th><th>Result</th>").appendTo(
			table);
	$.each(data.report, function(index, item) {
		var row = $("<tr>");
		$("<td>").text(item.date).appendTo(row);
		$("<td>").text(item.status).appendTo(row);
		if (item.size > 0) {
			$("<td>").html(
					"<a href=\"" + item.location + "\">" + item.size + " links")
					.appendTo(row);
		} else {
			$("<td>").text(" - ").appendTo(row);
		}
		row.appendTo(table);
	});
	table.appendTo($("#anchor_reports"));
}