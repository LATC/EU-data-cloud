/* Authors: Christophe Guéret <cgueret@few.vu.nl>
 * 
 */
var $updateDialog;
var $newDialog;
var $currentlyUpdatedId;

$(function() {
	// Makes the configuration list sortable and bind the handler for changes
	$("#configurations-list").sortable({
		placeholder : 'ui-state-highlight'
	});
	$("#configurations-list").bind("sortupdate", handleOrderChange);
	$("#configurations-list").disableSelection();

	// Style and bind the add button
	$("#add-button").button({
		label : 'Add a new file',
		icons : {
			primary : "ui-icon-plusthick"
		}
	});
	$("#add-button").click(function() {
		// Create the new dialog
		$("#dialog-upload").dialog({
			resizable : false,
			height : 200,
			width: 400,
			modal : true,
			buttons : {
				"Ok!" : function() {
					//$(this).trigger('submit');
					$("#dialog-upload-form").submit();
					$(this).dialog("close");
				},
				Cancel : function() {
					$(this).dialog("close");
				}
			},
			close : function() {
				$(this).dialog("close");
			}
		});
	});

	// Load the list of configuration files
	updateConfigurationList();
});

/**
 * 
 */
function updateConfigurationList() {
	// Load the list with a request to the API
	$.getJSON('api/queue', function(data) {
		// Clean the previous content
		$("#configurations-list").empty();

		// Create a block for each linking configuration file
		$.each(data.queue, function(index, item) {
			// Basic stuff
			var div = $("<div>").addClass('configuration').attr('id',
					'item_' + item.identifier);
			var toolbar = $("<span>").addClass('toolbar');

			// Title
			var title = $("<h2>").text(item.title);

			// About button
			var about = $("<button>").addClass('button-about').text('about');
			about.click(function() {
				$("#linkingconfiguration-inspection").attr('src',
						'information/information.html?id=' + item.identifier);
			});
			about.appendTo(toolbar);

			// Edit button
			var edit = $("<button>").addClass('button-edit').text('edit');
			edit.click(function() {
				$("#linkingconfiguration-inspection").attr('src',
						'editor/editor.html?id=' + item.identifier);

			});
			edit.appendTo(toolbar);

			// Delete button
			var del = $("<button>").addClass('button-delete').text('edit');
			del.click(function() {
				$("#dialog-confirm").dialog({
					resizable : false,
					height : 200,
					modal : true,
					buttons : {
						"Yep, go on!" : function() {
							$.ajax({
								type : 'DELETE',
								url : 'api/configuration/' + item.identifier,
								dataType : "text",
								success : function() {
									updateConfigurationList();
									handleOrderChange();
								}
							});
							$(this).dialog("close");
						},
						Cancel : function() {
							$(this).dialog("close");
						}
					},
					close : function() {
						$(this).dialog("close");
					}
				});
			});
			del.appendTo(toolbar);

			// Pack
			title.appendTo(div);
			toolbar.appendTo(div);

			div.appendTo($("#configurations-list"));
		});

		// Style all the buttons
		$(".button-edit").button({
			text : false,
			icons : {
				primary : "ui-icon-pencil"
			}
		});
		$(".button-delete").button({
			text : false,
			icons : {
				primary : "ui-icon-trash"
			}
		});
		$(".button-about").button({
			text : false,
			icons : {
				primary : "ui-icon-help"
			}
		});

	});
}

/**
 * Handle a change in the list of configuration files to execute
 * 
 * @param event
 * @param ui
 */
function handleOrderChange() {
	$.ajax({
		type : 'PUT',
		url : 'api/queue',
		data : $("#configurations-list").sortable('serialize'),
		dataType : "text"
	});
}
