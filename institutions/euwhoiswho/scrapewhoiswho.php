<?php

require 'inc.php';
require_once 'scrapers/euscraper.php';
require_once 'scrapers/agenciesandotherbodiesscraper.php';
require_once 'scrapers/agencylistingscraper.php';
require_once 'scrapers/eupersonscraper.php';
require_once 'scrapers/hierarchicviewscraper.php';
require_once 'scrapers/institutionscraper.php';

$scraper = new HierarchicViewScraper('http://europa.eu/whoiswho/public/index.cfm?fuseaction=idea.hierarchy&nodeid=1');
//$scraper = new HierarchicViewScraper('http://europa.eu/whoiswho/public/index.cfm?fuseaction=idea.hierarchy&nodeID=17546');
$scraper->scrape();
//echo $scraper->get_graph()->to_turtle();
file_put_contents('name-translations.json', json_encode($nameTranslations));
file_put_contents('roles.nt', $RolesGraph->to_ntriples());









?>
