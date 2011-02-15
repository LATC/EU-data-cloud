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

	// Load the configuration file and display the editor (Ajax)
	$.get('../api/task/' + id + '/configuration', function(data) {
		initEditor(id, data);
	});
});

/**
 * Initialize the editor
 * 
 * @param id
 *            the identifier of the configuration to load
 * @param data
 *            the XML data fetched
 */
function initEditor(id, data) {
	// Create the editor
	var editor = new CodeMirror(CodeMirror.replace("anchor"), {
		parserfile : "parsexml.js",
		stylesheet : "xmlcolors.css",
		height : "500px",
		width : "100%",
		lineNumbers : "true",
		content : (new XMLSerializer()).serializeToString(data)
	});

	// Style the save button and bind it
	$("#save-button").click(function() {
		saveLinkingConfiguration(id, editor);
	});
}

/**
 * Upload the content of the configuration file to the server
 * 
 * @param id
 *            identification of the configuration
 * @param editor
 *            the editor which contains the XML text
 */
function saveLinkingConfiguration(id, editor) {
	// Issue a PUT on the URL
	$.ajax({
		type : 'PUT',
		url : '../api/task/' + id + '/configuration',
		data : {
			configuration : editor.getCode()
		},
		dataType : "text",
		success : function(data) {
			alert('Changes saved');
			window.close(); 
		}
	});
	
}
