<?php
ini_set('max_execution_time', 999999999999);
define('MORIARTY_ARC_DIR', 'arc/');
require_once 'moriarty/simplegraph.class.php';
require 'curieous/rdfbuilder.class.php';
define('NS', 'http://ecb.publicdata.eu/');

$rdf = new RdfBuilder();

$rdf->create_vocabulary('ecbstats', NS.'schema/', 'European Central Bank Statistics RDF Vocabulary', 'http://keithalexander.co.uk/id/me');
register('ecbstats', NS.'schema/');


$output = array();

$concepts=array();
$codeLists = array();
$keyFamilies = array();

$reader = new XMLReader();

$reader->open('xml-schema/KeyFamily.xml');

while ($reader->read()) {
set_time_limit(10000);
  
    switch ($reader->nodeType) {
        case (XMLREADER::ELEMENT):
        if ($reader->localName == "Concept") {
            $node = $reader->expand();
            $agencyID = $node->getAttribute('agencyID');
            $id = $node->getAttribute('id');
            $Name = $node->getELementsByTagName('Name')->item(0)->textContent;
            $rdf->thing_from_identifier(NS.'schema/', $id)
              ->a('qb:DimensionProperty')
              ->label($Name, 'en')
              ->has('rdfs:isDefinedBy')->r(NS.'schema/')
              ->is('ov:defines')->of(NS.'schema/')
              ->has('dct:creator')->r(NS.'agency/'. $agencyID);
            echo $rdf->dump_ntriples();
        } else if($reader->localName== "Dimension") {
       //     $node = $reader->expand();
//            $concepts[$node->getAttribute('conceptAgency')][$node->getAttribute('conceptRef')]['codeLists'][]=$node->getAttribute('codelist');
//            $concepts[$node->getAttribute('conceptAgency')][$node->getAttribute('conceptRef')]['codeLists'] = array_unique($concepts[$node->getAttribute('conceptAgency')][$node->getAttribute('conceptRef')]['codeLists']);
        } else if($reader->localName=="CodeList"){
            $node = $reader->expand();
            $id = $node->getAttribute('id');
            $Name = $node->getELementsByTagName('Name')->item(0)->textContent;
            $codeLists[$id]['label'] = $Name;
            $id = strtolower(str_replace('CL_','', $node->getAttribute('id')));
            $Scheme = $rdf->thing_from_identifier(NS.'codes/', $id)
              ->a('ecbstats:CodeList')
              ->label($Name, 'en');
            $scheme_uri = $Scheme->get_uri();
            echo $rdf->dump_ntriples();            
            
            foreach($node->getElementsByTagName('Code') as $Code){
              $codeVal = $Code->getAttribute('value');
              $desc = $Code->getElementsByTagName('Description')->item(0)->textContent;
              $rdf->thing_from_identifier($scheme_uri .'/', $codeVal)
                ->a('skos:Concept')
                ->label($desc, 'en')
                ->has('skos:inScheme')->r($scheme_uri)
                ->is('ov:defines')->of($scheme_uri);
//              $codeLists[$id]['codes'][$codeVal] =             }
              echo $rdf->dump_ntriples();            
            }
            echo $rdf->dump_ntriples();
        } else if($reader->localName=="KeyFamily"){
          $node = $reader->expand();
          $id = $node->getAttribute('id');
          $urn = $node->getAttribute('urn');
          $agencyID = $node->getAttribute('agencyID');
          $Name = $node->getELementsByTagName('Name')->item(0)->textContent;
          $kf=array();
          $kf['urn'] = $urn;
          $kf['agencyID'] = $agencyID;
          $kf['label'] = $Name;
          $kf['dimension_count'] = 0;
          $DSD = $rdf->thing_from_identifier(NS.'dsd/', $id)
            ->a('qb:DataStructureDefinition')
            ->label($Name, 'en')
            ->has('owl:sameAs')->r($urn)
            ->has('dct:creator')->r(NS.'agency/', $agencyID);
 
          foreach($node->getElementsByTagName('Dimension') as $Dimension){
            $kf['dimension_count']++;
            $kf['dimensions'][$Dimension->getAttribute('conceptRef')] = $Dimension->getAttribute('codelist');
            $rdf->thing_from_identifier(NS.'component-specification', $id.'-'.$Dimension->getAttribute('conceptRef'))
              ->a('qb:ComponentSpecification')
              ->has('qb:dimension')->r(NS.'schema/'. $Dimension->getAttribute('conceptRef') )
              ->is('qb:component')->of($DSD->get_uri());
            echo $rdf->dump_ntriples();
          }
            echo $rdf->dump_ntriples();
           
//          $keyFamilies[$id] = $kf;
        }

        break;
    }
}

$rdf->write_vocabulary_to_file('ecbstats', 'ecbstats.ttl');

exit;
$output['concepts'] =$concepts; 
$output['codes'] = $codeLists;
$output['key_families'] = $keyFamilies;
echo json_encode($output);
?>

