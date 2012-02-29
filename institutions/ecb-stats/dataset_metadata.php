<?php

require 'inc.php';
require 'curieous/rdfbuilder.class.php';

$datasets = json_decode(file_get_contents('datasets_with_sample_series.json'), 1);
$rdf = new RdfBuilder();
foreach($datasets as $dataset => $serieskey){

  $url = 'http://sdw.ecb.europa.eu/quickviewexport.do?trans=&SERIES_KEY='.$serieskey.'&type=csv';
//  $url = 'http://sdw.ecb.europa.eu/export.do?SERIES_KEY='.$serieskey.'&exportType=csv';
  $csv = fopen($url, 'r');
  $row = fgetcsv($csv);
  $datasetfield = array_shift(explode(';',$row[0]));
  $datasetName = trim(array_pop(explode(':',$datasetfield)));
  $rdf->thing($dataset)->label($datasetName, 'en');
}
echo $rdf->dump_ntriples();
?>
