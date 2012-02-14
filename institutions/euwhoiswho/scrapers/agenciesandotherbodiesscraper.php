<?php

class AgenciesAndOtherBodiesScraper extends EUScraper {
  function scrape(){
    foreach($this->xpath->query('//td/table//tr/td/table//tr/td/ul/li/a') as $el){
        $linkText = $el->textContent;
        $linkHref = $el->getAttribute('href');
        $scraper = new AgencyListingScraper('http://europa.eu/whoiswho/public/'.$linkHref);
        $scraper->scrape();
        $this->flushNtriples();
        //$this->graph->add_graph($scraper->get_graph());
    }
  }
}

?>
