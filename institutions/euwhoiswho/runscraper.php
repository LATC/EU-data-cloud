<?php

require 'scrapewhoiswho.php';
/*
$PersonScraper  = new EUPersonScraper( 'http://europa.eu/whoiswho/public/index.cfm?fuseaction=idea.hierarchy&personID=134420', $publicInstitutionsGraph);
$PersonScraper->scrape();

echo $PersonScraper->get_graph()->to_turtle();
 */


$AgencyScraper = new AgenciesAndOtherBodiesScraper('http://europa.eu/whoiswho/public/index.cfm?fuseaction=idea.hierarchy&nodeID=18&lang=EN', $publicInstitutionsGraph);
$AgencyScraper->scrape();
echo $AgencyScraper->get_graph()->to_turtle();

?>
