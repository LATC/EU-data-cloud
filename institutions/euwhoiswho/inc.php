<?php
#requires
#
#
if(!defined('MORIARTY_ARC_DIR')) define('MORIARTY_ARC_DIR', 'lib/arc/');
define('MORIARTY_HTTP_CACHE_USE_STALE_ON_FAILURE', true);
define('MORIARTY_HTTP_CACHE_DIR', 'cache/');
define('MORIARTY_ALWAYS_CACHE_EVERYTHING', 1);
define('MORIARTY_HTTP_CACHE_READ_ONLY', 1);

require_once 'lib/moriarty/moriarty.inc.php';
require_once 'lib/moriarty/simplegraph.class.php';
require_once 'lib/moriarty/store.class.php';


define('DCT', 'http://purl.org/dc/terms/');
define('DCMI', 'http://purl.org/dc/dcmitype/');
define('ORG', 'http://www.w3.org/ns/org#');
define('WHOISWHO', 'http://euwhoiswho.dataincubator.org/');
define('INST', WHOISWHO.'');
define('OV', 'http://open.vocab.org/terms/');
define('FOAF', 'http://xmlns.com/foaf/0.1/');

function urlize($i){

  return urlencode(str_replace(' ','_',ucwords(trim($i))));
}




#
$publicInstitutionsGraph = new SimpleGraph(file_get_contents("institutions.publicdata.eu.ttl"));

$scrapedPeople = array();
$scrapedNodes = array();

$RolesGraph = new SimpleGraph();


?>
