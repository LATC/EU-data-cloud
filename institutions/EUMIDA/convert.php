<?php
define('MORIARTY_ARC_DIR', 'lib/arc/');
define('MORIARTY_ALWAYS_CACHE_EVERYTHING', true);
require_once 'lib/moriarty/simplegraph.class.php';
require 'lib/curieous/curieous.php';
require_once 'lib/curieous/rdfbuilder.class.php';
define('EUMIDA', 'http://data.kasabi.com/dataset/eumida/');
define('NS', EUMIDA.'terms/');
define('YEAR_NS', 'http://reference.data.gov.uk/id/year/');
$langs = json_decode(file_get_contents('langs.json'), 1);
$langCodes = array();
foreach($langs['results']['bindings'] as $row){
  if(preg_match('/\.([a-z]+)/', $row['code']['value'], $m)){
    $countryCode = strtoupper($m[1]);
    $langCode = $row['iso']['value'];
    $langCodes[$countryCode]=$langCode;
  }
}

$langCodes = array(
  'AT' => 'de',
  'UK' => 'en',
  'BE' => 'nl',
  'BG' => 'bg',
  'IT' => 'ita',
  'RO' => 'ro',
  'PL' => 'szl',
  'TR' => 'tr',
  'GR' => 'el',
  'GE' => 'ka',
  'BY' => 'be',
  'CZ' => 'sk',
  'AZ' => 'az',
  'MT' => 'mt',
  'CY' => 'tr',
  'SK' => 'sk',
  'BE' => 'de',
  'HU' => 'hun',
  'LV' => 'lav',
  'UA' => 'ukr',
  'BA' => 'bs',
  'SM' => 'ita',
);


$file = fopen('eumida.csv', 'r');
$countries = json_decode(file_get_contents('countries.json'),1);
$columnNames = fgetcsv($file);

function varify($i){ return preg_replace('/[^0-9a-zA-Z]+/','_', $i); }

$vars = array_map('varify', $columnNames);


$Rdf = new RdfBuilder();
register('eum', NS);
register('aiiso', $curieous->uri('aiiso:'));
$Rdf->create_vocabulary('eum', NS, 'EUMIDA Vocabulary', 'http://keithalexander.co.uk/id/me');

$doctoratesDatasetUri = EUMIDA.'dataset/doctorates-awarded';
$studentsISCED5DatasetUri = EUMIDA.'dataset/ISCED5-students-enrolled';
$internationStudentsDatasetUri = EUMIDA.'dataset/international-ISCED5-students-enrolled';
$studentsISCED6DatasetUri = EUMIDA.'dataset/ISCED-6-students-enrolled';
$isced6InternationalStudentsDatasetUri = EUMIDA.'dataset/international-ISCED-6-students-enrolled';
$totalStaffDatasetUri = EUMIDA.'dataset/total-staff';

$Rdf->thing($doctoratesDatasetUri)->a('qb:DataSet')
  ->label('Numbers of Doctorates Awarded by Higher Education Institutions', 'en');
$Rdf->thing($studentsISCED5DatasetUri)->a('qb:DataSet')
    ->label('Numbers of ISCED 5 Students Enrolled in European Higher Education Institutions', 'en');
$Rdf->thing($internationStudentsDatasetUri)->a('qb:DataSet')
  ->label('Numbers of International ISCED 5 Students at European Higher Education Institutions', 'en');

$Rdf->thing($studentsISCED6DatasetUri)->a('qb:DataSet')
    ->label('Numbers of ISCED 6 Students Enrolled in European Higher Education Institutions', 'en');
$Rdf->thing($isced6InternationalStudentsDatasetUri)->a('qb:DataSet')
  ->label('Numbers of International ISCED 6 Students at European Higher Education Institutions', 'en');

$Rdf->thing($totalStaffDatasetUri)->a('qb:DataSet')
  ->label('Numbers of Members of Staff at European Higher Education Institutions', 'en');


foreach(array('eum:numberOfStaff',
  'eum:numberOfDoctoratesAwarded', 
  'eum:numberOfInternationalISCED6Students',
  'eum:numberOfISCED6Students',
  'eum:numberOfInternationalISCED5Students',
  'eum:numberOfISCED5Students',
) as $curie) { 
  $Rdf->get_vocab_builder()->thing(uri($curie))->a('qb:MeasureProperty');
}


while($line = fgetcsv($file)){
  foreach($vars as $n => $name){
    $$name = trim($line[$n]);
  }
  

  $instUri = EUMIDA.'institution/'.$EUMIDA_ID;
  $countryUri = EUMIDA.'country/'.$Country_Code;
  $countryName = $countries[$Country_Code];
  
  $categoryUri = $Rdf->thing_from_label(EUMIDA.'category/', $Institution_Category_English, 'en')->get_uri();
  $regionUri = $Rdf->thing_from_identifier(EUMIDA.'region/', $NUTS_Region)
    ->a('dcterms:Location')
    ->has('owl:sameAs')->r('http://nuts.psi.enakting.org/id/'.trim($NUTS_Region))
      ->r('http://nuts.geovocab.org/id/'. trim($NUTS_Region))
    ->get_uri();

  $Rdf->thing($countryUri)->a('ov:Country')
      ->has('rdfs:label')->l($countryName)
      ->has('dcterms:identifier')->l($Country_Code)
      ->has('owl:sameAs')
        ->r('http://data.kasabi.com/dataset/european-election-results/country/'.strtolower($Country_Code))
        ->r('http://airports.dataincubator.org/countries/' . strtoupper($Country_Code))
        ->r('http://www.geonames.org/countries/#' . strtoupper($Country_Code))
        ->r('http://dbtune.org/musicbrainz/resource/country/' . strtoupper($Country_Code))
        ->r('http://lexvo.org/id/iso3166/' . strtoupper($Country_Code))
        ->r('http://telegraphis.net/data/countries/' . strtoupper($Country_Code) . '#' . strtoupper($Country_Code) );


  $Rdf->thing($categoryUri)->a('skos:Concept')
      ->label($Institution_Category_English, 'en');

  $legalStatusUri = $Rdf->thing_from_label(EUMIDA . 'legal-status/', $Legal_Status, 'en')
      ->a('skos:Concept')->get_uri();

  $Institution =  $Rdf->thing($instUri)
      ->a('aiiso:Institution')
      ->label($English_Institution_Name, 'en')
      ->has('foaf:name')->l($Institution_Name)
      ->has('eum:country')->r($countryUri)
      ->has('ov:category')->r($categoryUri)
      ->has('spatial:P')->r($regionUri)
      ->has('eum:legalStatus')->r($legalStatusUri)
      ->has('eum:yearOfCurrentStatus')->r('http://reference.data.gov.uk/id/year/'. trim($Current_Status_Year))
      ->has('eum:yearOfFoundation')->r('http://reference.data.gov.uk/id/year/'. trim($Foundation_Year));
  
  if(strtolower(trim($Distance_Education))=='yes'){
    $Institution->has('eum:feature')->r($instUri.'/distance')
      ->object()->a('eum:DistanceEducationProvision')
      ->label('Distance Education at '. $Institution_Name, 'en')
      ->has('dcterms:description')->l($Distance_Education_Comments, 'en');
  }
   if(strtolower(trim($Research_Active))=='yes'){
     $Institution->has('eum:feature')->r($instUri.'/research')->object()
       ->a('eum:ResearchProvision')
      ->label('Active Research at ' . $Institution_Name, 'en')
      ->has('dcterms:description')->l($Research_Active_Comments, 'en');
   }  
  if(strtolower(trim($University_Hospital))=='yes'){
    $Institution->has('eum:feature')->r($instUri.'/university-hospital')->object()
      ->a('eum:UniversityHospital')
      ->label('University Hospital for ' . $Institution_Name, 'en')
      ->has('dcterms:description')->l($University_Hospital_Comments, 'en');
  }  

  foreach($vars as $var){
    if(strpos($var, 'Education_Field_')){
      $fieldName = str_replace('_', ' ', substr($var, strlen('Education_Field')));
      if($fieldName=='Comments'){
        
      } else if(!empty($$var) && strtolower($$var) != 'no') {
        $Field = $Rdf->thing_from_label(EUMIDA . 'education-field/', $fieldName, 'en')
          ->a('aiiso:Subject')
          ->is('aiiso:teaches')->of($instUri);
      }
    }
  }

  if(!empty($Highest_Degree_Awarded)){
    $Rdf->thing_from_label(EUMIDA.'degree/', $Highest_Degree_Awarded, 'en')->a('eum:EducationDegree')
      ->is('eum:highestDegreeAwarded')->of($instUri);
  }

  $dt = empty($Doctorate_Degrees_Awarded)? false : 'xsd:integer';
  $Rdf->thing($instUri.'/doctorates-awarded')
    ->a('qb:Observation')
    ->has('eum:numberOfDoctoratesAwarded')->dt($Doctorate_Degrees_Awarded, $dt)
    ->has('eum:institution')->r($instUri)
    ->has('sdmxdim:refPeriod')->r(YEAR_NS.$Doctorate_Degrees_Awarded_Reference_Year)
    ->is('eum:awardedDoctoratesFigure')->of($instUri)
    ->has('rdfs:comment')->l($Doctorate_Degrees_Comments, 'en')
    ->has('qb:dataSet')->r($doctoratesDatasetUri);

  $dt = empty($Students_ISCED5)? false : 'xsd:integer';
  $Rdf->thing($instUri.'/ISCED5-Students-enrolled')->a('qb:Observation')
    ->has('eum:numberOfISCED5Students')->dt($Students_ISCED5, $dt)
    ->has('eum:institution')->r($instUri)
    ->has('sdmxdim:refPeriod')->r(YEAR_NS.$Students_ISCED5_Reference_Year)
    ->is('eum:enrolledISCED5StudentsFigure')->of($instUri)
    ->has('rdfs:comment')->l($Students_ISCED5_Comments, 'en')
    ->has('qb:dataSet')->r($studentsISCED5DatasetUri);

  
  $dt = empty($International_Students_ISCED5)? false : 'xsd:integer';
 $Rdf->thing($instUri.'/ISCED5-International-Students-enrolled')->a('qb:Observation')
    ->has('eum:numberOfInternationalISCED5Students')->dt($International_Students_ISCED5, $dt)
    ->has('eum:institution')->r($instUri)
    ->has('sdmxdim:refPeriod')->r(YEAR_NS.$International_Students_ISCED5_Reference_Year)
    ->is('eum:enrolledISCED5StudentsFigure')->of($instUri)
    ->has('rdfs:comment')->l($International_Students_ISCED5_Comments, 'en')
    ->has('qb:dataSet')->r($internationStudentsDatasetUri);

  $dt = empty($Students_ISCED6)? false : 'xsd:integer';
  $Rdf->thing($instUri.'/ISCED6-Students-enrolled')->a('qb:Observation')
    ->has('eum:numberOfISCED6Students')->dt($Students_ISCED6, $dt)
    ->has('eum:institution')->r($instUri)
    ->has('sdmxdim:refPeriod')->r(YEAR_NS.$Students_ISCED6_Reference_Year)
    ->is('eum:enrolledISCED6StudentsFigure')->of($instUri)
    ->has('rdfs:comment')->l($Students_ISCED6_Comments, 'en')
    ->has('qb:dataSet')->r($studentsISCED6DatasetUri);

  $dt = empty($International_Students_ISCED6)? false : 'xsd:integer';
  $Rdf->thing($instUri.'/ISCED6-International-Students-enrolled')->a('qb:Observation')
    ->has('eum:numberOfInternationalISCED6Students')->dt($International_Students_ISCED6,$dt)
    ->has('eum:institution')->r($instUri)
    ->has('sdmxdim:refPeriod')->r(YEAR_NS.$International_Students_ISCED6_Reference_Year)
    ->is('eum:enrolledISCED6StudentsFigure')->of($instUri)
    ->has('rdfs:comment')->l($International_Students_ISCED6_Comments, 'en')
    ->has('qb:dataSet')->r($isced6InternationalStudentsDatasetUri);

  $dt = empty($Total_Staff)? false : 'xsd:integer';
 $Rdf->thing($instUri.'/total-staff')->a('qb:Observation')
    ->has('eum:numberOfStaff')->dt($Total_Staff, $dt)
    ->has('eum:institution')->r($instUri)
    ->has('sdmxdim:refPeriod')->r(YEAR_NS. $Total_Staff_Reference_Year)
    ->is('eum:enrolledISCED6StudentsFigure')->of($instUri)
    ->has('rdfs:comment')->l($Total_Staff_Comments, 'en')
    ->has('qb:dataSet')->r($totalStaffDatasetUri);


   echo $Rdf->dump_ntriples();

}

$Rdf->write_vocabulary_to_file('eum', 'eumida.vocab.ttl');

?>
