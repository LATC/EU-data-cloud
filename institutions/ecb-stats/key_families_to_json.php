<?php

$output = array();

$concepts=array();
$codeLists = array();
$keyFamilies = array();

$reader = new XMLReader();

$reader->open('xml-schema/KeyFamily.xml');

while ($reader->read()) {
    switch ($reader->nodeType) {
        case (XMLREADER::ELEMENT):
        if ($reader->localName == "Concept") {
            $node = $reader->expand();
            $agencyID = $node->getAttribute('agencyID');
            $id = $node->getAttribute('id');
            $Name = $node->getELementsByTagName('Name')->item(0)->textContent;
            $concepts[$agencyID][$id]['label'] = $Name;
        } else if($reader->localName== "Dimension") {
            $node = $reader->expand();
            $dom = new DomDocument();
            $n = $dom->importNode($node,true);
            $dom->appendChild($n);
            $concepts[$node->getAttribute('conceptAgency')][$node->getAttribute('conceptRef')]['codeLists'][]=$node->getAttribute('codelist');
            $concepts[$node->getAttribute('conceptAgency')][$node->getAttribute('conceptRef')]['codeLists'] = array_unique($concepts[$node->getAttribute('conceptAgency')][$node->getAttribute('conceptRef')]['codeLists']);
        } else if($reader->localName=="CodeList"){
            $node = $reader->expand();
            $id = $node->getAttribute('id');
            $Name = $node->getELementsByTagName('Name')->item(0)->textContent;
            $codeLists[$id]['label'] = $Name;
            foreach($node->getElementsByTagName('Code') as $Code){
              $codeVal = $Code->getAttribute('value');
              $codeLists[$id]['codes'][$codeVal] = $Code->getElementsByTagName('Description')->item(0)->textContent;
            }
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
          foreach($node->getElementsByTagName('Dimension') as $Dimension){
            $kf['dimension_count']++;
            $kf['dimensions'][$Dimension->getAttribute('conceptRef')] = $Dimension->getAttribute('codelist');
          }
          $keyFamilies[$id] = $kf;
        }

        break;
    }
}
$output['concepts'] =$concepts; 
$output['codes'] = $codeLists;
$output['key_families'] = $keyFamilies;
echo json_encode($output);
?>
