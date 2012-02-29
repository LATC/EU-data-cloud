<?php

class AgencyListingScraper extends EUScraper {
  function scrape(){
    
    foreach($this->xpath->query('//td/table//tr/td/table//tr/td/ul/li/a') as $el){
        $linkText = $el->textContent;
        $linkHref = $el->getAttribute('href');
        $scraper = new InstitutionScraper('http://europa.eu/whoiswho/public/'.$linkHref);
        $scraper->setSlugPrefix('agencies');
        $scraper->scrape() ;
        $this->flushNtriples();
    }
  }
}

?>
