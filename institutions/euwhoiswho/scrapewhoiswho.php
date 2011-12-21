<?php

require 'inc.php';
require_once 'scrapers/euscraper.php';
require_once 'scrapers/agenciesandotherbodiesscraper.php';
require_once 'scrapers/agencylistingscraper.php';
require_once 'scrapers/eupersonscraper.php';
require_once 'scrapers/hierarchicviewscraper.php';
require_once 'scrapers/institutionscraper.php';

$scraper = new HierarchicViewScraper('http://europa.eu/whoiswho/public/index.cfm?fuseaction=idea.hierarchy&nodeid=1', $publicInstitutionsGraph);
$scraper->scrape();
//echo $scraper->get_graph()->to_turtle();
echo $RolesGraph->to_ntriples();










?>
