
var QB = 'http://purl.org/linked-data/cube#';
var RDFS = 'http://www.w3.org/2000/01/rdf-schema#';
var SNS = 'http://sns.linkedscotland.org/def/';
var SDMX = 'http://purl.org/linked-data/sdmx/2009/dimension#';

$(document).ready(function(){
  var sliceUri = $('p.id input').attr('value');
  $('tr.observation>td.value').each(function(){
    $(this).html('');
    var tdEl = this;
    var myTable = $('<table id="observations"><tr><th>Place</th><th class="measure">Value</th></tr></table>');

    $.getJSON(window.location.href,{_format: 'rdfjson', _view:'slice-observations'}, function(data){ 

      var observations = data[sliceUri][QB+'observation'];
      
      for(var x = 0; x < observations.length ; x++ ){
        var obUri = observations[x].value;
        var datasetUri = data[obUri][QB+'dataset'][0].value;
        var measureProperty = data[datasetUri][SNS+'measure'][0].value;
        var dataValue = data[obUri][measureProperty][0].value;
        var locationUri = data[obUri][SDMX+'refArea'][0].value;
        if(data[obUri][RDFS+'label']){
          var placeName = data[obUri][RDFS+'label'][0].value;
        } else {
          var placeName =locationUri.split(/#|\//).pop();
        }
        if( data[measureProperty]){
          var measurePropertyLabel = data[measureProperty][RDFS+'label'][0].value;
          $('th.measure', myTable).html(measurePropertyLabel.link(measureProperty));
        }

        myTable.append('<tr><td>'+placeName.link(locationUri)+'</td><td>'+dataValue.link(obUri)+'</td></tr>');
      };
      
      $('tr.observation>td.value').html(myTable);
      


        
    });
  });
});

$('tr.json td.value').each(function(){
function label(uri) {
	return data.labels[uri];
}
var json = this.innerHTML;
var data = jQuery.parseJSON(json);
var allplaces = [];
$('tr.json>th').html('data table');
$('tr.json>td.value').html('');
$.each(data.indicators, function(indicator, years) {
    var table = $('<div><table><caption>' + label(indicator).link(indicator) + '</caption><thead><tr><th>Place</th></tr></thead><tbody/></table></div>');
    $.each(years, function(year, places) {
        $('thead tr', table).append('<th>' + label(year).link(year) + '</th>');
        allplaces = places;
    });
    $.each(allplaces, function(place, observation) {
             var row = $('<tr><th>' + label(place).link(place) + '</th></tr>');
            $.each(years,  function(year_b, places_b) {
                row.append('<td>' + places_b[place] + '</td>');
            });
	        $('tbody', table).append(row);
        });
 
    $('tr.json td.value').append(table.html());
});
});


