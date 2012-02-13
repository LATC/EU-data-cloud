<?php

define("WHOISWHO_ROLE_XPATH", '//table[2]//tr/td/a[contains(@href, "index.cfm?fuseaction=idea.hierarchy&nodeID=")]');

class EUPersonScraper extends EUScraper {

  
  function scrape(){
      global $RolesGraph;
      parse_str(parse_url($this->pageUri, PHP_URL_QUERY), $pageQueryParams);
      $personID = $pageQueryParams['personID'];
      if(@!$node = $this->xpath->query('//tr/td/h3')->item(0)){
        $this->log_message("couldn't find name for Person");
        return null;
      }
      $name = $node->nodeValue;
      $uri = WHOISWHO.'people/'.$personID.'/'.urlize($name);
      if(preg_match('/^([\p{Lu} ´\-’\'"ß\(\)]+) ([\p{L} \-’\'\."´š]+)$/u', $name, $matches)){
        $surname= ucwords(strtolower($matches[1]));
        $fullName = trim($matches[2]).' '.$surname;
        $this->graph->add_literal_triple($uri, FOAF.'familyName', $surname);
      } else {
        $this->log_message("Name doesn't match regular expression\t" . $name);
        $fullName = $name;
      }

      $this->add_resource($uri, FOAF.'Person', $fullName );
      $this->graph->add_literal_triple($uri, FOAF.'name', $fullName);
      $this->graph->add_resource_triple($uri, FOAF.'isPrimaryTopicOf', $this->pageUri);
      $address = '';
      foreach( $this->xpath->query('//tr/td[contains(. ,"Tel:")][preceding::h3]/node()') as $node){
        $address.= $this->parseAddressDetails($node, $uri);
      }
      if(!empty($address)) $this->graph->add_literal_triple($uri, OV.'postalAddress', trim($address));

      $rolePositions = array();
      foreach($this->xpath->query(WHOISWHO_ROLE_XPATH) as $a){
        $orgBreadcrumbPath = $a->nodeValue;
        $roleLabel = trim(strip_tags($this->dom->saveXML($a->parentNode->parentNode->nextSibling),'<br>'));
        if(strpos($roleLabel, 'Tel:')===0 || empty($roleLabel)){
          $roleLabel = 'Member';
        }
        $roleLabels = explode('<br/>', $roleLabel);
        foreach($roleLabels as $role_label){
          $roleSlug = urlize($role_label);
          $roleURI = WHOISWHO.'roles/'.$roleSlug;
          $rolePositions[]=$roleURI;
          $orgNodeID = $this->getNodeIdFromUrl($a->getAttribute('href'));
          $orgURI = INST.'institutions/'.$orgNodeID;
          $membershipURI = $orgURI.'/memberships/'.$personID.'/'.$roleSlug;
          $RolesGraph->add_resource_triple($roleURI, RDF_TYPE, ORG.'Role');
          $RolesGraph->add_literal_triple($roleURI, RDFS_LABEL, $role_label, 'en');
 
          $this->add_resource($membershipURI, ORG.'Membership', $fullName.', '.$role_label.' of '.array_pop(explode('; ', $orgBreadcrumbPath)), 'en-gb');
          $this->graph->add_resource_triple($membershipURI, ORG.'member', $uri);
          $this->graph->add_resource_triple($membershipURI, ORG.'organization', $orgURI);
          $this->graph->add_resource_triple($membershipURI, ORG.'role', $roleURI);
          $this->graph->add_resource_triple($uri, ORG.'memberOf', $orgURI);
        }
      }

      foreach($this->translate_langs as $lang) {
        $this->translateRolesTo($rolePositions, $lang);
      }
    echo $this->graph->to_ntriples();
    $this->graph = new SimpleGraph();
  }

 
  function translateRolesTo($rolePositions, $lang){
    global $RolesGraph;
    $xpath = false;
    foreach($rolePositions as $no => $roleUri){
        if(!$this->roleHasLabelWithLang($roleUri, $lang)){
          if(!$xpath){
            $doc = $this->fetchDocFromUrl($this->translatePageUrlTo($lang));
            $xpath = new DomXpath($doc);
          }
              $count = $no;
            foreach($xpath->query(WHOISWHO_ROLE_XPATH) as $node){
              $roleLabels = $this->getRoleLabelsFromNode($node, $doc);
              foreach($roleLabels as $label){
                $RolesGraph->add_literal_triple($roleUri, RDFS_LABEL, $label, $lang);
                @$roleUri = $rolePositions[++$count];
              }
            }
          }
        }
    } 

  function roleHasLabelWithLang($roleUri, $lang){
    global $RolesGraph;
    $existingLabels = $RolesGraph->get_subject_property_values($roleUri, RDFS_LABEL);
    foreach($existingLabels as $labelObject){
      if(isset($labelObject['lang']) AND $labelObject['lang']==$lang){
        return true;
      }
    }
    return false;
  }

  function getRoleLabelsFromNode($a, $dom=false){
    if(!$dom) $dom = $this->dom;
    $roleLabel = trim(strip_tags($dom->saveXML($a->parentNode->parentNode->nextSibling),'<br>'));
     if(strpos($roleLabel, 'Tel:')===0 || empty($roleLabel)){
          $roleLabel = 'Member';
    }
    $roleLabels = explode('<br/>', $roleLabel);

    return $roleLabels;
    
  }
}



?>
