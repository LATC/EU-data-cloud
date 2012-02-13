$(function(){
	
	initIDs();
	
	
	$("#show-eui-schema-details").click(function () {
		$('#eui-schema-details').toggle('slow', function() {
			if ($("#show-eui-schema-details").text() == "Show details ...")
				$("#show-eui-schema-details").text("Hide details ...");
			else
				$("#show-eui-schema-details").text("Show details ...");	
		});
	});	
	
	$(".entity").hover(function() {
			$(this).addClass('entity-hover');
		}, 
		function() {
			$(this).removeClass('entity-hover');
		}
	);
	
});

function initIDs(){
	$("#main div").each(function(index) {
		var about = $(this).attr('about');
		if (about) {
			$(this).find('h3').append("<span class='entity'><a href='" + about + "' title='entity identifier'>ID</a></span>");
		}
	});
	
	$("#eui-schema .l1").each(function(index) {
		var about = $(this).attr('about');
		if (about) {
			$(this).find('span:first').append("<span class='entity'><a href='" + about + "' title='class identifier'>ID</a></span>");
		}
	});
	
	$("#eui-schema .l2").each(function(index) {
		var about = $(this).attr('about');
		if (about) {
			$(this).find('span').append("<span class='entity'><a href='" + about + "' title='class identifier'>ID</a></span>");
		}
	});
}
