/* Author: 

 */

/*
 * Various initialisations performed once the document is scriptable
 */
$(document).ready(function() {
	// setup ul.tabs to work as tabs for each div directly under div.panes
	$("ul.tabs").tabs("div.panes > div");

	// move to the next tab (for debugging)
	// var tabs = $("ul.tabs").data("tabs");
	// tabs.next();

	// Plot a graph
    var points = [[0, 121613], [1, 32675]];
    $.plot($("#linksGraph"), [ points ], {
               series: {
                   lines: { show: true },
                   points: { show: true }
               },
               grid: { hoverable: true, clickable: false }}
	);
    
	// Configure the selection of task for the detail panel
	$('#taskSelector').dataTable({
		"bPaginate" : false,
		'bAutoWidth': false,
		"bLengthChange" : false,
		"bSort" : true,
		"bInfo" : false,
		"aoColumns" : [
		/* Identifier */{
			"bSearchable" : true,
			"bVisible" : false
		},
		/* Title */null]
	});
	$("#taskSelector tbody").click(function(event) {
		// Change the selected row
		var table = $('#taskSelector').dataTable();
		$(table.fnSettings().aoData).each(function() {
			$(this.nTr).removeClass('row_selected');
		});
		$(event.target.parentNode).addClass('row_selected');

		// Load the data panel
		var position = table.fnGetPosition(event.target.parentNode);
		var data = table.fnGetData(position);
		loadTaskDetails(data[0]);
	});

	// Configure the login panel
	$("#login-link").click(function() {
		$("#login-panel").toggle(0);
	});

	// Configure the template for the task details
	$.get("details-template.html", function(data) {
		$.template("taskDetails", data);
	});

	// Load the tasks
	reloadTasks();

	// Load the last notifications
	updateNotifications();
});

/*
 * Configure keyboard shortcuts
 */
$(document).keydown(function(e) {
	// Bind the escape key to closing the login panel
	if (e.keyCode == 27) {
		$("#login-panel").hide(0);
	}
});

/*
 * Log the user in
 */
function login() {
	$("#login-panel").hide(0);
}

/*
 * Log out
 */
function logout() {
}

/*
 * Update tasks
 */
function reloadTasks() {
	setLoading($("#tasksList"));
	$.getJSON('api/tasks.json?limit=5', function(data) {
		// Clean the previous content for the overview table
		$("#tasksList").empty();

		// Go through all the tasks
		$.each(data.task, function(index, item) {
			// Add a task block to the overview list
			var task = $("<div>").addClass('taskBlock');
			var title = $("<h3>").addClass('link').text(item.title);
			title.appendTo(task);
			var description = $("<p>").text(item.description);
			description.appendTo(task);
			task.appendTo($("#tasksList"));
		});
	});

	$.getJSON('api/tasks.json', function(data) {
		// Clean the previous content for the task selector
		$('#taskSelector').dataTable().fnClearTable();

		// Go through all the tasks
		$.each(data.task, function(index, item) {
			// Add an entry to the task selector table
			$('#taskSelector').dataTable().fnAddData(
					[ item.identifier, item.title]);
		});
	});
}

/*
 * Update the global list of notifications
 */
function updateNotifications() {
	setLoading($("#eventsList"));
	
	$.getJSON('api/notifications.json?limit=5', function(data) {
		// Clean the previous content for the overview table
		$("#eventsList").empty();

		// Go through all the tasks
		$.each(data.notification, function(index, item) {
			// Add a task block to the overview list
			var task = $("<div>").addClass('taskBlock');
			var title = $("<h3>").addClass(item.severity).text(item.title);
			title.appendTo(task);
			var description = $("<p>").text(item.message);
			description.appendTo(task);
			var description2 = $("<p>").text("(" + item.date + ")");
			description2.appendTo(task);
			task.appendTo($("#eventsList"));
		});
	});
}

/*
 * 
 */
function loadTaskDetails(identifier) {
	// Clean the details panel
	setLoading($("#taskDetailsContent"));

	// Get the basic information
	$.getJSON('api/task/' + identifier, function(data) {
		$("#taskDetailsContent").empty();

		// Initialise the template for this task
		$.tmpl("taskDetails", [ {
			identifier : identifier,
			title : data.title,
			description : data.description
		} ]).appendTo("#taskDetailsContent");

		// Initialise the table
		$('#taskReports').dataTable({
			"bLengthChange" : false,
			"bSort" : true,
			"bInfo" : false
		});

		// Connect the delete button
		$("[name=deleteTask]").overlay({
			mask : {
				color : '#ebecff',
				loadSpeed : 200,
				opacity : 0.9
			},
			closeOnClick : false
		});
		var buttons = $("#yesno button").click(function(e) {
			// get user input
			var yes = buttons.index(this) === 0;
			if (yes) {
				$.ajax({
					type : 'DELETE',
					url : 'api/task/' + identifier,
					dataType : "text",
					success : function() {
						reloadTasks();
					}
				});
			}
		});

		// Load the notifications
		$.getJSON('api/task/' + identifier + '/notifications', function(data) {
			// Add all the statuses to the table
			$.each(data.notification, function(index, item) {
				if (item.severity == 'info') {
					icon = "<img src=images/information.png></img>";
				} else if (item.severity == 'warn') {
					icon = "<img src=images/exclamation.png></img>";
				} else {
					icon = item.severity;
				}
				$('#taskReports').dataTable().fnAddData(
						[ icon, item.message, item.date ]);
			});
		});
	});
}

/*
 * 
 */
function setLoading(element) {
	$("#taskDetailsContent").empty();
	var load = $("<img>").attr('src', 'images/ajax-loader.gif');
	load.appendTo(element);
}

/*
 * Save the details about a task
 */
function saveDetails() {

	$.ajax({
		type : 'PUT',
		url : 'api/task/' + $("[name=task-identifier]").val(),
		data : {
			title : $("[name=task-title]").val(),
			description : $("[name=task-description]").val()
		},
		dataType : "text",
		success : function(data) {
			// Reload the tasks to take in account an eventual new title
			reloadTasks();
		}
	});
}
