/**************************************************/
/* Dashboard UI and client-side interaction code  */
/**************************************************/

/* config */
var dashboardServiceURI = "dashboard.php";
/* end of config */

// jQuery main interaction code
$(function(){
	initDashboard();
	
	$("#about").click(function () {

	});
	
	$('.show-ds-details').live('click', function(){
		var dsURI = $(this).attr('id');
		activeDSURI = dsURI;
		getDatasetDetails();
	});
	
	$('#show-ds-desc').live('click', function(){
		$("#infobox").html($("#dsdetailsbox-desc-content").text());
		$("#infobox").dialog('option', 'width', 600);
		$("#infobox").dialog('option', 'height', 400);
		$("#infobox").dialog('option', 'title', 'Dataset Description');
		$("#infobox").dialog('open');
	});
		
	$('#show-ds-ls').live('click', function(){
		getDatasetLinksets();
	});

	$('#show-ds-examples').live('click', function(){
		getDatasetExamples();
	});
});
