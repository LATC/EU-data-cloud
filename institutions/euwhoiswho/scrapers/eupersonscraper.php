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
        $roleLabels = $this->getRoleLabelsFromNode($a);
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
        $this->translateRolesTo($rolePositions, $lang, $RolesGraph);
      }
    //file_put_contents('roles.nt', $RolesGraph->to_ntriples());
    $this->flushNtriples();
  }

  function rolesNeedTranslating($rolePositions, $lang){
    global $RolesGraph;
    foreach($rolePositions as $uri){
      if(!$RolesGraph->roleHasLabelWithLang($uri, $lang, $RolesGraph)){
        return true;
      }
    }
    return false;
  }

  function translateRolesTo($rolePositions, $lang){
    global $RolesGraph;
    $xpath = false;
    if($this->rolesNeedTranslating($rolePositions, $lang)){
      $doc = $this->fetchDocFromUrl($this->translatePageUrlTo($lang));
      $xpath = new DomXpath($doc);
      $translatedRoles = array();
      foreach($xpath->query(WHOISWHO_ROLE_XPATH) as $node){
        $roleLabels = $this->getRoleLabelsFromNode($node, $doc);
        foreach($roleLabels as $label){
          $translatedRoles[]=$label;
        }
      }
      if(count($translatedRoles)!== count($rolePositions)){
        $this->log_message("different no of roles for $lang translation");
      }
      foreach($rolePositions as $no => $roleUri){
        if(!$RolesGraph->roleHasLabelWithLang($roleUri, $lang, $RolesGraph) AND isset($translatedRoles[$no])){ 
          $RolesGraph->add_literal_triple($roleUri, RDFS_LABEL, $translatedRoles[$no], $lang);
        }
      }
     }
    } 


  function getRoleLabelsFromNode($a, $dom=false){
    if(!$dom) $dom = $this->dom;
    $text = trim(strip_tags($dom->saveXML($a->parentNode->parentNode->nextSibling),'<br>'));
    $roleLabels = explode('<br/>', $text);
    $labels = array();
    foreach($roleLabels as $roleLabel){
      $roleLabel = trim($roleLabel);
      if(strpos($roleLabel, 'Tel:')===0 || empty($roleLabel)
      || strpos($roleLabel, 'Email:')===0
      || strpos($roleLabel, 'Téléphone:')===0
      || strpos($roleLabel, 'Adresse électronique:')===0
      || strpos($roleLabel, 'E-Mail:')===0
      || strpos($roleLabel, 'Tel.:')===0
      || strpos($roleLabel, 'Fax:')===0
      || strpos($roleLabel, 'Facsimile')===0
      || strpos($roleLabel, 'Télécopieur:')===0
      ){
          $roleLabel = 'Member';
      }
        $labels[]=$roleLabel;
    }
  
    return $labels;
    
  }
}



?>
