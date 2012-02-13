<?php
set_include_path(get_include_path().':../');
require 'inc.php';
require 'scrapers/euscraper.php';
require 'scrapers/eupersonscraper.php';
require 'scrapers/institutionscraper.php';
/*
$url = 'http://europa.eu/whoiswho/public/index.cfm?fuseaction=idea.hierarchy&nodeID=54023&lang=en';
$url = 'http://europa.eu/whoiswho/public/index.cfm?fuseaction=idea.hierarchy&nodeID=4180';
$scraper = new InstitutionScraper($url, $publicInstitutionsGraph);
$scraper->scrape();
 */

$scraper = new EUPersonScraper("http://europa.eu/whoiswho/public/index.cfm?fuseaction=idea.hierarchy&personID=97699" ,$publicInstitutionsGraph);
$scraper->scrape();


echo "\n\n";

?>
