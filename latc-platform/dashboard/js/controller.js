/*******************************************************/
/* Dashbaord controller and service conmmunicate code  */
/*******************************************************/
var activeDSURI = null;

function initDashboard(){
	initThemes();
	initDSCarousel();
	$("#infobox").dialog({ 
		autoOpen: false,
		modal: true
	});
}

function initThemes(){
	$.ajax({
		type: "GET",
		url: dashboardServiceURI,
		data: "themes",
		dataTypeString: "json",
		success: function(data){
			if(data) {
				buffer = "<option>select a theme ...</option>";
				for(entry in data.result.rows) { 
					buffer += "<option value='" + data.result.rows[entry].theme + "'>";
					buffer +=  data.result.rows[entry].themelabel;
					buffer += "</option>";
				}
				$("#themes").html(buffer);
			}
		},
		error:  function(msg){
			alert(msg);
		} 
	});
}

// dataset handling
function initDSCarousel(){
	$('#dscarousel').jcarousel({
		vertical: true,
		initCallback: dscarouselInitCallback
	});
}

function dscarouselInitCallback(carousel, state) {
	$("#themes").change(function () {
		carousel.reset();
		$("#dsdetailsbox").slideUp("slow");
		$("#lsbox").slideUp("slow");
		$("#dslistbox").slideDown("slow");
	});

	theme = $('#themes :selected').val();
	$.ajax({
		type: "GET",
		url: dashboardServiceURI,
		data: "list=ds&theme=" + theme,
		dataTypeString: "json",
		success: function(data){
			if(data) {
				fillCarouselWithDatasetList(carousel, data);
			}
		},
		error:  function(msg){
			alert(msg);
		} 
	});
}

function fillCarouselWithDatasetList(carousel, data){
	carousel.size(data.result.rows.length);
	i = 0;
	for(entry in data.result.rows) { 
		var origtitle = data.result.rows[entry].title;
		var title = origtitle;
		if(title.length > 40) title = title.substring(0, 40) + "..."; // cut too long titles
		var buffer = "<div class='dataset'>";
		buffer += "<div class='datasetimg'><img src='img/ds-icon.png' alt='dataset details' title='dataset details'/></div>";
		buffer += "<div class='datasetdetails'>";
		buffer += "<a href='" + data.result.rows[entry].hp + "' target='_new' title='"+ origtitle +"'>" + title + "</a><br />";
		buffer += "Size: " + $.formatNumber(data.result.rows[entry].triples, {format:"###,###,###,###", locale:"us"}) + " triples";
		buffer += "</div>";
		buffer += "<div class='show-ds-details' id='" + data.result.rows[entry].ds + "'>";
		buffer += "Details ...";
		buffer += "</div>";
		buffer += "</div>";
		carousel.add(carousel.first + i, buffer);
		i++;
	}
}

function getDatasetDetails(){
	$.ajax({
		type: "GET",
		url: dashboardServiceURI,
		data: "ds=" + activeDSURI,
		dataTypeString: "json",
		success: function(data){
			if(data) {
				var buffer = "<div class='dsdetailsbox-container'>";
				buffer += "<div class='dsdetailsbox-meta'><strong>" + data.result.rows[0].title + "</strong><br />";
				buffer += "<span class='dsdetailsbox-label'>Homepage:</span> <a href='" + data.result.rows[0].hp + " target='_new' title='homepage'>" + data.result.rows[0].hp + "</a><br />";
				buffer += "<span class='dsdetailsbox-label'>Size:</span> " + $.formatNumber(data.result.rows[0].triples, {format:"###,###,###,###", locale:"us"}) + " triples<br />";
				if(data.result.rows[0].license) {
					buffer += "<span class='dsdetailsbox-label'>License:</span> " +data.result.rows[0].license + "<br />";
				}
				if(data.result.rows[0].contributor) {
					buffer += "<span class='dsdetailsbox-label'>Contributor:</span> " +data.result.rows[0].contributor + "<br />";
				}
				if(data.result.rows[0].ep) {
					buffer += "<span class='dsdetailsbox-label'>SPARQL Endpoint:</span> <a href='" + data.result.rows[0].ep + " target='_new' title='SPARQL Endpoint'>" + data.result.rows[0].ep + "</a><br />";
				}
				buffer += "</div>";
				buffer += "<div class='dsdetailsbox-desc'>";
				buffer += "<span id='show-ds-desc'>Description</span> | ";
				buffer += "<span id='show-ds-ls'>Interlinking</span> | ";
				buffer += "<span id='show-ds-examples'>Examples</span>";
				if(data.result.rows[0].desc) {
					buffer += "<div id='dsdetailsbox-desc-content'>" + data.result.rows[0].desc  + "</div>";
				}
				else {
					buffer += "<div id='dsdetailsbox-desc-content'>Sorry, there is no description of this dataset available.</div>";
				}
				buffer += " </div>";
				buffer += "</div>";
				$("#dsdetailsbox").html(buffer);
				$("#dsdetailsbox").slideDown("slow");
			}
		},
		error:  function(msg){
			alert(msg);
		} 
	});		
}

function getDatasetExamples() {
	$.ajax({
		type: "GET",
		url: dashboardServiceURI,
		data: "examplefromds=" + activeDSURI,
		dataTypeString: "json",
		success: function(data){
			var buffer = "<div>Sorry, there are no example resources of this dataset available.</div>";
			if(data) {
				buffer = "";
				for(entry in data.result.rows) { 
					var exURI = data.result.rows[entry].example;
					buffer += "<div class='ds-examples'>";
					buffer += "<a href='"+ exURI + "' target='_new'>" + exURI+ "</a>";
					buffer += "<div class='ds-example-views'>View in <a href='http://sig.ma/search?raw=1&singlesource="+ exURI + "' target='_new'>Sig.ma</a></div> ";
					buffer += "</div>";
				}
			}
			$("#infobox").html(buffer);
			$("#infobox").dialog('option', 'width', 600);
			$("#infobox").dialog('option', 'height', 400);
			$("#infobox").dialog('option', 'title', 'Example Resources from the Dataset');
			$("#infobox").dialog('open');
		},
		error:  function(msg){
			alert(msg);
		} 
	});
}


function getDatasetLinksets() {
	if(activeDSURI != null) {
		$.ajax({
			type: "GET",
			url: dashboardServiceURI,
			data: "list=ls&for=" + activeDSURI,
			dataTypeString: "json",
			success: function(data){
				if(data) {
					listLinksets(data);
				}
			},
			error:  function(msg){
				alert(msg);
			} 
		});
	}
	
}


function listLinksets(data){
	var buffer ="";
	for(entry in data.result.rows) { 
		//var origtitle = data.result.rows[entry].targettitle;
		//var title = origtitle;
		//if(title.length > 30) title = title.substring(0, 30) + "..."; // cut too long titles
		buffer += "<div class='linkset'>";
		buffer += "<div class='linksetimg'><img src='img/ls-icon.png' alt='linkset details' title='linkset details'/></div>";
		buffer += "<div class='linksetdetails'>";
		buffer += "<div><span class='lsdetailsbox-label'>Source:</span> "  + data.result.rows[entry].srctitle + "</div>";
		buffer += "<div><span class='lsdetailsbox-label'>Target:</span> "  + data.result.rows[entry].targettitle + "</div>";
		buffer += "<div><span class='lsdetailsbox-label'>Number of links:</span> "  + $.formatNumber(data.result.rows[entry].triples, {format:"###,###,###,###", locale:"us"}) + "</div>";				
		buffer += "<div><span class='lsdetailsbox-label'>Link type:</span> unknown</div>";
		buffer += "<div><span class='lsdetailsbox-label'>Origin:</span> dataset publisher</div>";
		buffer += "</div>";
		buffer += "</div>";
	}
	$("#infobox").html(buffer);
	$("#infobox").dialog('option', 'width', 600);
	$("#infobox").dialog('option', 'height', 400);
	$("#infobox").dialog('option', 'title', 'Linksets');
	$("#infobox").dialog('open');
}