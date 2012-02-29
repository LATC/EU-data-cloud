<?php
require 'inc.php';

require_once 'moriarty/simplegraph.class.php';
require 'curieous/rdfbuilder.class.php';

$key_families_by_no_of_dimensions=array();
$datasetUris=array();
$dateRdf = new RdfBuilder();

function find_keyfamily_for_series_key($series_key_components){
  global $metadata;
  $key_families = $metadata['key_families'];
  $dimensions_in_key = array_slice($series_key_components, 2);
  $no_of_dimensions_in_key = count($dimensions_in_key);
  log_message( "No of possibilities: ".count(array_keys($key_families)) );
  $key_families = filter_key_families_by_number_of_dimensions($key_families, $no_of_dimensions_in_key);
  log_message( "No of keyfamilys with {$no_of_dimensions_in_key} dimensions: ".count(array_keys($key_families)));

  $n=$no_of_dimensions_in_key-1;
  while(count(array_keys($key_families)) > 1 AND $n  >= 0 ){
    $code = $dimensions_in_key[$n];
    $key_families = filter_key_families_by_code_in_nth_position($key_families, $code, $n);
    log_message( "No of keyfamilys with matching code ({$code}) in {$n}th position: ".count(array_keys($key_families)) . " " . implode(", ", array_keys($key_families)));

    $n--;
  }
  if(count(array_keys($key_families)) > 1){
    $datasetAbbreviation = $series_key_components[1];
    foreach($key_families as $k => $props){
      if(strstr($k, $datasetAbbreviation)){
        return array($k => $props);
      }
    }
  }
  return $key_families;
}

function filter_key_families_by_number_of_dimensions($kf,$no){
  
  global $key_families_by_no_of_dimensions;

  $matching=array();

  if(isset($key_families_by_no_of_dimensions[$no])){
    foreach($key_families_by_no_of_dimensions[$no] as $k_id){
      $matching[$k_id]=$kf[$k_id];
    } 
    return $matching; 
  }

  foreach($kf as $id => $props){
    if($props['dimension_count']==$no){
      $matching[$id]=$props;
    }
  }
  $key_families_by_no_of_dimensions[$no] = array_keys($matching);
  return $matching;
}

function filter_key_families_by_code_in_nth_position($kf, $sk_code, $no){
  global $metadata;
  $codes = $metadata['codes'];
  $matching=array();
  $checked_code_lists = array();
  foreach($kf as $id => $props){
    $dimensions = $props['dimension_list'];
    $dimension_key = $dimensions[$no];
    $cl_id =$props['dimensions'][$dimension_key];
    if(isset($codes[$cl_id]['codes'][$sk_code])){
   //   log_message("{$sk_code} is in $cl_id  for {$dimension_key}");
      $matching[$id] = $props;
    }  
  }
  return $matching;
}


$metadata = json_decode(file_get_contents('keyfamily.json'),1);


register('ecbstats', NS.'schema/');

$rdf = new RdfBuilder();
$rdf->create_vocabulary('ecbstats', NS.'schema/', 'European Central Bank Statistics RDF Vocabulary', 'http://keithalexander.co.uk/id/me');

$conceptRdf = new RdfBuilder();



function csv_row_to_rdf($row){
  global $metadata;
  global $rdf;
  global $conceptRdf;
  global $datasetUris;
  global $dateRdf;

  list($breadcrumb_trail, $series_key, $title, $fromDate, $toDate) = $row;

  $key_components= explode('.', $series_key);

  $matching_key_families = find_keyfamily_for_series_key($key_components);
  $matching_kf_ids = array_keys($matching_key_families);
  $no_of_matches = count($matching_kf_ids);
  if($no_of_matches > 1){
    log_message("more than one matching key family for series key " .$series_key );
    return false;
  } else if($no_of_matches < 1){
    log_message("no matches were found for series key " .$series_key );
    return false;
  }
  $key_family_id = $matching_kf_ids[0];

  list($threeDigit, $datasetCode) = $key_components;
  $dimensionValueCodes = array_slice($key_components, 2);

  $datasetUri = NS.'dataset/'.$threeDigit.'.'.$datasetCode;
  $datasetUris[$datasetUri]= $series_key;

  $series = $rdf->thing_from_identifier(NS.'series/', $series_key)->a('qb:Slice')
    ->label($title, 'en')
    ->has('foaf:page')->r('http://sdw.ecb.europa.eu/quickview.do?SERIES_KEY='.$series_key)
    ->is('qb:slice')->of($datasetUri);

  if($topicUris = breadcrumb_to_rdf($breadcrumb_trail)){
    foreach($topicUris as $topicUri) $series->has('dct:subject')->r($topicUri);
  }

  if($dateUri = Utils::dateToURI($fromDate.'-'.$toDate)){
    $series->has('dct:temporal')->r($dateUri);
    $dateRdf->thing($dateUri)
      ->a('time:Interval')
      ->label($fromDate.'-'.$toDate)
      ;
  }

  $kf = $metadata['key_families'][$key_family_id];
  $dimensions = $kf['dimension_list'];
  foreach($dimensionValueCodes as $no => $code){
      $concept_id = $dimensions[$no];
      $cl_id = $kf['dimensions'][$concept_id];
      if(!isset($metadata['codes'][$cl_id]['codes'][$code])){
        log_message("Error: '{$code}' not found in {$cl_id} ; Dimension $concept_id ; kf:  $key_family_id ; series: $series_key");
        continue;
      }
      $code_value = $metadata['codes'][$cl_id]['codes'][$code];
      $prop = 'ecbstats:'.strtolower($concept_id);
      if($concept_id=='REF_AREA') $prop = 'sdmxdim:refArea';
      if($concept_id=='FREQ') $prop = 'sdmxdim:freq';
      $ns = NS.'codes/'.str_replace('cl_','',strtolower($cl_id));

      $series->has($prop)->r($ns.'/' . $code);
  }

  echo $rdf->dump_ntriples();
}

function breadcrumb_to_rdf($breadcrumb){
  global $conceptRdf;
  $topics = explode('/', $breadcrumb);
  array_shift($topics);
  $lastTopic=false;
  $uris=array();
  while($topic = array_shift($topics)){
    $label = ucwords(str_replace('-',' ', $topic));
    $topicUri = NS.'concepts/'.$topic;
    $uris[]=$topicUri;
    $Topic = $conceptRdf->thing($topicUri)
      ->a('skos:Concept')
      ->label($label, 'en')
      ->has('skos:inScheme')->r(NS.'conceptscheme/ecb');

    if(!$lastTopic){
      $Topic->has('skos:topConceptOf')->r(NS.'conceptscheme/ecb')
        ->object()
          ->a('skos:ConceptScheme')
          ->label('European Central Bank Concepts', 'en')
          ->has('dct:description')->l("Concepts used by the European Central Bank Statistical Warehouse", 'en')
          ->has('skos:hasTopConcept')->r($topicUri);
    } else {
      $Topic->has('skos:broader')->r($lastTopic)
          ->is('skos:narrower')->of($lastTopic);
    }
    $lastTopic = $topicUri;
  }
  return $uris;
}


$filename = $argv[1];
$fh = fopen($filename, 'r');
while($row = fgetcsv($fh)){
  set_time_limit(9999999999999);
  if(count($row) > 2){
    csv_row_to_rdf($row); 
  } 
}

echo $conceptRdf->dump_ntriples();

foreach($datasetUris as $uri => $v){
  $rdf->thing($uri)
    ->a('qb:DataSet')
    ->has('dct:creator')->r('http://institutions.publicdata.eu/#ecb');
  echo $rdf->dump_ntriples();
}

file_put_contents('datasets_with_sample_series.json', json_encode($datasetUris));
echo $dateRdf->dump_ntriples();

?>
