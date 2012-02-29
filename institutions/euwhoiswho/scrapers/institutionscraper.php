<?php
define("WHOISWHO_SUB_ORGANISATION_LINKS_XPATH", "//td/ul/li/a[preceding::h2[text()='Depending entity']]");

class InstitutionScraper extends EUScraper {


  var $slugPrefix = 'institutions';

  var $topLevelInstitution = null;

  function setSlugPrefix($val){
    $this->slugPrefix = $val;
  }

  function getSlugPrefix(){
    return $this->slugPrefix;
  }


  function setTopLevelInstitution($uri){
    $this->topLevelInstitution = $uri;
  }

  function scrape($linkSubject=false, $linkPredicate=false, $inverseLinkPredicate=false ){
    global $scrapedNodes;
    global $publicInstitutionsGraph;

    $this->translateLabelsOnPage(WHOISWHO_SUB_ORGANISATION_LINKS_XPATH);
    if($linkSubject){
      $uri = $linkSubject;
      $address = '';
      foreach( $this->xpath->query("//node()[preceding-sibling::h3][following::br]") as $detailsTextLine){
    
            $address.=$this->parseAddressDetails($detailsTextLine, $uri);
        }
       if(!empty($address)) $this->graph->add_literal_triple($uri, OV.'postalAddress', trim($address));
    }

    $subOrganisations = $this->xpath->query(WHOISWHO_SUB_ORGANISATION_LINKS_XPATH);
    foreach($subOrganisations as $link){
          $nodeId = $this->getNodeIdFromUrl($link->getAttribute('href'));
          if(in_array($nodeId, $scrapedNodes)){
            $this->log_message("Already scraped node $nodeId");
            continue;
          } else {
            $scrapedNodes[]=$nodeId;
          }

          $webPageUri = str_replace('&lang=en','', 'http://europa.eu/whoiswho/public/'.$link->getAttribute('href'));
          $label = $link->textContent;
          $uri = INST.$this->slugPrefix.'/'.$nodeId; //urlize($label);
          $type = $this->choose_type($label);
          $this->add_resource($uri, $type, $label, 'en-gb');
          $this->graph->add_resource_triple($uri, FOAF_PAGE, $webPageUri);
          $this->graph->add_resource_triple($uri, ORG.'transitiveSubOrganisationOf', $this->topLevelInstitution);
          if($this->getNodeIdFromUrl($this->pageUri)!=4180){
            $sameAs = array_shift($publicInstitutionsGraph->get_subjects_where_literal(DCT.'title', $label)); 
            if($sameAs){
              $this->graph->add_resource_triple($uri, OWL_SAMEAS, $sameAs);
              $this->graph->remove_property_values($uri, RDF_TYPE);
              foreach($publicInstitutionsGraph->get_resource_triple_values($sameAs, RDF_TYPE) as $type){
                $this->graph->add_resource_triple($uri, RDF_TYPE, $type);
              }
            }
          }
          if($linkSubject &&$linkPredicate){
            $this->graph->add_resource_triple($linkSubject, $linkPredicate, $uri);
          } 
          if($linkSubject && $inverseLinkPredicate) {
            $this->graph->add_resource_triple($uri, $inverseLinkPredicate, $linkSubject);
          }

         $InstitutionScraper = new InstitutionScraper($webPageUri);
        $InstitutionScraper->setTopLevelInstitution($this->topLevelInstitution);
          
         $InstitutionScraper->scrape($uri, ORG.'hasSubOrganization', ORG.'subOrganisationOf');
         //$this->graph->add_graph($InstitutionScraper->get_graph());
          $this->flushNtriples();
      }    

    $thisNodeId = $this->getNodeIdFromUrl($this->pageUri);
    foreach($this->xpath->query("//a[contains(@href, 'personID=')]") as $a){
          global $scrapedPeople;
          $personId = $this->getPersonIdFromUrl($a->getAttribute('href'));
          if(in_array($personId, $scrapedPeople)){
            $this->log_message("Already scraped person $personId");
            continue;
          } else {
            $scrapedPeople[]=$personId;
          
          $webPageUri = str_replace('&lang=en','', 'http://europa.eu/whoiswho/public/'.$a->getAttribute('href'));
          $webPageUri = str_replace('&nodeID='.$thisNodeId, '', $webPageUri);
          $personScraper = new EUPersonScraper($webPageUri);
          $personScraper->scrape();
      }
    }
  
    $this->flushNtriples();
  }

}

?>
