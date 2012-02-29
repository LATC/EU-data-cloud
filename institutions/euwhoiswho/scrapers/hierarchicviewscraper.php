<?php
define('WHOISWHO_INSTITUTION_PAGE_LINKS', '//td/ul/li/a');

class HierarchicViewScraper extends EUScraper {

  const uriPattern = 'http://europa.eu/whoiswho/public/index.cfm?fuseaction=idea.hierarchy&nodeid=1';

  function scrape(){
    $this->translateLabelsOnPage(WHOISWHO_INSTITUTION_PAGE_LINKS);
    global $scrapedNodes;
    global $publicInstitutionsGraph;

    foreach($this->xpath->query(WHOISWHO_INSTITUTION_PAGE_LINKS) as $link){
          $webPageUri = str_replace('lang=en','', 'http://europa.eu/whoiswho/public/'.$link->getAttribute('href'));
          $nodeId = $this->getNodeIdFromUrl($webPageUri);
          if(in_array($nodeId, $scrapedNodes)){
            continue;
          } else {
            $scrapedNodes[]=$nodeId;
          }
          $label = $link->textContent;
          if($label == ''){
          
          } else if (!strpos($webPageUri, 'nodeID=18&')){
          $uri = INST.'institutions/'.$nodeId;
          $type = $this->choose_type($label);
          $this->add_resource($uri, $type, $label, 'en-gb');
          $this->graph->add_resource_triple($uri, FOAF_PAGE, $webPageUri);
          $sameAs = array_shift($publicInstitutionsGraph->get_subjects_where_literal(DCT.'title', $label)); 
          if($sameAs) $this->graph->add_resource_triple($uri, OWL_SAMEAS, $sameAs);
          $InstitutionScraper = new InstitutionScraper($webPageUri.'&lang=en');
          $InstitutionScraper->setTopLevelInstitution($uri);
          //$InstitutionScraper->translate_langs = array('fr','de','it','nl', 'es', 'no', 'da','bg','pl','fi');
         // $InstitutionScraper->setSlugPrefix(urlize($label));
          $InstitutionScraper->scrape($uri, ORG.'hasSubOrganization', ORG.'transitiveSubOrganisationOf');
          $this->graph->add_graph($InstitutionScraper->get_graph());
          } else if (strpos($webPageUri, 'nodeID=18&')) { //Agencies and other bodies
           //scrape agencies
            $AgenciesScraper = new AgenciesAndOtherBodiesScraper($webPageUri );
            $AgenciesScraper->translate_langs = array('fr','de','it','nl', 'es', 'no', 'da','bg','pl','fi');
            $AgenciesScraper->scrape();
          }
        $this->flushNtriples();
      }    
  
  }
 
}

?>
