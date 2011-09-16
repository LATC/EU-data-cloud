<?php
require_once 'scrapewhoiswho.php';


$HierarchicViewScraper = new HierarchicViewScraper('http://europa.eu/whoiswho/public/index.cfm?fuseaction=idea.hierarchy&nodeid=1', $publicInstitutionsGraph);
$HierarchicViewScraper->scrape();
$DataGraph = $HierarchicViewScraper->get_graph();
$VoIDGraph = new SimpleGraph(file_get_contents('expected-void.ttl'));
file_put_contents('hierarchies.ttl', $DataGraph->to_turtle());
require '../ldconvtest/inmemoryconversiontester.class.php';

$Tester = new InMemoryConversionTester($DataGraph, $VoIDGraph);

echo $Tester->get_report();

?>
