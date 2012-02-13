<?php
set_include_path(get_include_path().':../');
require 'scrapewhoiswho.php';

$url = 'http://europa.eu/whoiswho/public/index.cfm?fuseaction=idea.hierarchy&nodeID=54023&lang=en';

$scraper = new InstitutionScraper($url, $publicInstitutionsGraph);
$scraper->scrape();

?>
