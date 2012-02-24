<?php

$appRoot = dirname(__FILE__);
set_include_path(get_include_path() . PATH_SEPARATOR . $appRoot . ':lib/' );
define('PUELIA_VERSION', '0.9');
define('PUELIA_RDF_ACCEPT_MIMES', 'application/json;q=1,text/turtle;q=0.9,application/rdf+xml;q=0.8,*/*;q=0.1');
define('PUELIA_SPARQL_ACCEPT_MIMES', 'application/sparql-results+json;q=1,application/sparql-results+xml;q=0.8,*/*;q=0.1');
define('MORIARTY_ARC_DIR', 'lib/arc/');
define('MORIARTY_HTTP_CACHE_DIR', dirname(__FILE__). '/cache/');
require 'lib/moriarty/simplegraph.class.php';

define('API', 'http://purl.org/linked-data/api/vocab#');
define('RDFS', 'http://www.w3.org/2000/01/rdf-schema#');
define("XSD", "http://www.w3.org/2001/XMLSchema#");
define("FOAF", 'http://xmlns.com/foaf/0.1/');
define("FOAF_KNOWS", 'http://xmlns.com/foaf/0.1/knows');
define("FOAF_HOMEPAGE", 'http://xmlns.com/foaf/0.1/homepage');
define("REL", 'http://vocab.org/relationship/');
define("RDF", "http://www.w3.org/1999/02/22-rdf-syntax-ns#");
define("XHV", "http://www.w3.org/1999/xhtml/vocab#");
define("DCT", "http://purl.org/dc/terms/");
define("PUELIA", "http://purl.org/puelia-php/ns#");
define("OPENSEARCH", "http://a9.com/-/spec/opensearch/1.1/");
define("OPMV", "http://purl.org/net/opmv#");
define("COPMV", "http://purl.org/net/opmv/types/common#");
define("SPARQL", "http://purl.org/net/opmv/types/sparql#");
define("DOAP", "http://usefulinc.com/ns/doap#");
define("SD", "http://www.w3.org/ns/sparql-service-description#");
define("VOID", "http://rdfs.org/ns/void#");
define('GEO', 'http://www.w3.org/2003/01/geo/wgs84_pos#');
define('SKOS', 'http://www.w3.org/2004/02/skos/core#');
define('XHTML', 'http://www.w3.org/1999/xhtml#');
define('OPEN', 'http://open.vocab.org/terms/');

define('PUELIA_LOG_DIR', dirname(__FILE__).'/logs/');

require_once('lib/log4php/src/main/php/Logger.php');

function queryStringToParams($query){
    $query = ltrim($query, '?');
    $pairs = explode('&', $query);
    $params = array();
    foreach($pairs as $pair){
        if($tuple = explode('=', $pair) AND isset($tuple[1])){
            $params[urldecode($tuple[0])]=urldecode($tuple[1]);
        }
    }
    return $params;
}


function logError($message){
    $logger = Logger::getLogger('Puelia');
    $logger->error($message);
}

function logSelectQuery($request, $query){
    $uri = $request->getUri();
    $message = "SELECT Query:{$uri}:\t<<<{$query}>>>";
    $logger = Logger::getLogger('Puelia');
    $logger->info($message);
    
}
function logViewQuery($request, $query){
    $uri = $request->getUri();
    $message = "{$uri}\t<<<{$query}>>>";
    $logger = Logger::getLogger('Puelia');
    $logger->info($message);
}

function logDebug($message){
    $logger = Logger::getLogger('Puelia');
    $logger->debug($message);
}


?>
