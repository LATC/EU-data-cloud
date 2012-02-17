<?php
//TODO rewrite code to take kf_id, and series key components, and get codelist id, code value, and concept id
define('MORIARTY_ARC_DIR', 'arc/');
require_once 'moriarty/simplegraph.class.php';
require 'curieous/rdfbuilder.class.php';

function find_keyfamily_for_series_key($series_key_components){
  global $metadata;
  $key_families = $metadata['key_families'];
  $dimensions_in_key = array_slice($series_key_components, 2);
  $no_of_dimensions_in_key = count($dimensions_in_key);
  echo "\n\nNo of possibilities: ".count(array_keys($key_families));
  $key_families = filter_key_families_by_number_of_dimensions($key_families, $no_of_dimensions_in_key);
  echo "\n\nNo of keyfamilys with {$no_of_dimensions_in_key} dimensions: ".count(array_keys($key_families));

  $n=$no_of_dimensions_in_key-1;
  while(count(array_keys($key_families)) > 1 AND $n  >= 0 ){
    $code = $dimensions_in_key[$n];
    $key_families = filter_key_families_by_code_in_nth_position($key_families, $code, $n);
    echo "\n\nNo of keyfamilys with matching code in {$n}th position: ".count(array_keys($key_families));

    $n--;
  }
  return $key_families;
}

function filter_key_families_by_number_of_dimensions($kf,$no){
  $matching=array();
  foreach($kf as $id => $props){
    if($props['dimension_count']==$no){
      $matching[$id]=$props;
    }
  }
  return $matching;
}

function filter_key_families_by_code_in_nth_position($kf, $sk_code, $no){
  global $metadata;
  $codes = $metadata['codes'];
  $matching=array();
  $checked_code_lists = array();
  foreach($kf as $id => $props){
    echo "\n\tchecking keyf {$id}";
    $dimensions = $props['dimension_list'];
    $dimension_key = $dimensions[$no];
    $cl_id =$props['dimensions'][$dimension_key];
    echo "\n\t\tchecking {$cl_id} for {$sk_code}";
    if(isset($checked_code_lists[$cl_id])){
     echo "\n\t {$cl_id} already checked";
     continue;
    }
    if(isset($codes[$cl_id]['codes'][$sk_code])){
//      die(var_dump($cl_id, $sk_code));
      $checked_code_lists[$cl_id] = true;
      $matching[$id] = $props;
    } else {
      $checked_code_lists[$cl_id] = false;
    }
  }

  return $matching;
}
function get_concept_for_list($cl_id){
  global $metadata;
  foreach($metadata['concepts']['ECB'] as $id => $props ){
    if(isset($props['codeLists'])){
      foreach($props['codeLists'] as $cl){
        if($cl==$cl_id) return $id;
      }
    }
  }
}

function get_value_for_code($input_code){
  global $metadata;
  $return=array();
  foreach($metadata['codes'] as $cl_id => $codeList){
    foreach($codeList['codes'] as $code => $desc){
      if($code==$input_code){
        $return[$cl_id] = $desc;
      }
    }
  }
  return $return;
}
function get_value_for_code_in_list($input_code, $list_id){
  global $metadata;
  $codeList = $metadata['codes'][$list_id];
    foreach($codeList['codes'] as $code => $desc){
      if($code==$input_code){
        return $desc;
      }
    }
}

define('NS', 'http://ecb.publicdata.eu/');
define('KASABI_COUNTRIES', 'http://data.kasasbi.com/dataset/countries/');
$metadata = json_decode(file_get_contents('keyfamily.json'),1);

$series_key = $argv[1];

$key_components= explode('.', $series_key);

$matching_key_families = find_keyfamily_for_series_key($key_components);
$matching_kf_ids = array_keys($matching_key_families);
$no_of_matches = count($matching_kf_ids);
if($no_of_matches > 1){
  echo("more than one matching key family");
  var_dump($matching_key_families);
  exit;
} else if($no_of_matches < 1){
  die("no matches were found");
}
$key_family_id = $matching_kf_ids[0];

list($threeDigit, $datasetCode) = $key_components;
$dimensionValueCodes = array_slice($key_components, 2);



register('ecbstats', NS.'schema/');

$rdf = new RdfBuilder();
$rdf->create_vocabulary('ecbstats', NS.'schema/', 'European Central Bank Statistics RDF Vocabulary', 'http://keithalexander.co.uk/id/me');

// $freq = $rdf->thing_from_identifier(NS.'codes/frequency/', $seriesFreq)
//   ->label(get_value_for_code_in_list($seriesFreq, 'CL_FREQ'))
//   ->a('skos:Concept')
//   ->has('skos:inScheme')->r(NS.'codes/frequency');


$series = $rdf->thing_from_identifier(NS.'series/', $series_key)->a('qb:Slice')
//  ->has('sdmxdim:refArea')->r(KASABI_COUNTRIES.$refArea)
//  ->has('ecbstats:adjustment')->r($adjustment->get_uri())
//  ->has('sdmxdim:freq')->r($freq->get_uri())
//  ->has($y_prop)->r($yThing->get_uri())
 // ->has($z_prop)->r($zThing->get_uri())
  ->has('foaf:page')->r('http://sdw.ecb.europa.eu/quickview.do?SERIES_KEY='.$series_key);

$kf = $metadata['key_families'][$key_family_id];
$dimensions = $kf['dimension_list'];

foreach($dimensionValueCodes as $no => $code){
    $concept_id = $dimensions[$no];
    $cl_id = $kf['dimensions'][$concept_id];
    $code_value = $metadata['codes'][$cl_id]['codes'][$code];
    $prop = 'ecbstats:'.strtolower($concept_id);
    if($concept_id=='REF_AREA') $prop = 'sdmxdim:refArea';
    if($concept_id=='FREQ') $prop = 'sdmxdim:freq';
    $ns = NS.'codes/'.str_replace('cl_','',strtolower($cl_id));
    $Thing = $rdf->thing_from_identifier($ns.'/', $code)
          ->label($code_value, 'en')
          ->a('skos:Concept')
          ->has('skos:inScheme')->r($ns);
    $series->has($prop)->r($Thing->get_uri());
}

echo $rdf->turtle();
?>
