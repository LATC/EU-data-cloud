<?php
include_once("../arc/ARC2.php");

$DEBUG = false;

$PRIMARY_DATA_SOURCE = "http://localhost:8888/latc-dashboard/data/lod-cloud-void.ttl";

$PREFIXES = "PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#> PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#> PREFIX dcterms: <http://purl.org/dc/terms/> PREFIX foaf: <http://xmlns.com/foaf/0.1/> PREFIX void: <http://rdfs.org/ns/void#> PREFIX skos: <http://www.w3.org/2004/02/skos/core#> PREFIX ov: <http://open.vocab.org/terms/> PREFIX tag: <http://www.holygoat.co.uk/owl/redwood/0.1/tags/> ";


/* ARC2 RDF store config - START */
$config = array(
	'db_name' => 'arc2',
	'db_user' => 'root',
	'db_pwd' => 'root',
	'store_name' => 'dashboard'
); 

$store = ARC2::getStore($config);

if (!$store->isSetUp()) {
  $store->setUp();
  echo 'set up';
}
/* ARC2 RDF store config - END */


/* LATC 24/7 Interlinking Platform Dashboard INTERFACE */

//// GET interface
if(isset($_GET['reset'])) {
	$store->reset();
	echo "RESET store done.<br />\n";
	echo "<p>go <a href='index.html'>home</a> ...</p>\n";     
}

if(isset($_GET['init'])){
	echo initStore();
}

if(isset($_GET['list'])){
	$listType = $_GET['list'];
	$themeType = $_GET['theme'];
	
	$queryStr = "";
	
	if($listType == "ds") {
		$queryStr = "SELECT * WHERE { ?ds a void:Dataset ; dcterms:subject <$themeType>; dcterms:title ?title ; void:triples ?triples . OPTIONAL { ?ds foaf:homepage ?hp ; } }";
	}
	if($listType == "ls") {
		$dsURI = $_GET['for'];
		$queryStr = "SELECT ?srctitle ?targettitle ?triples ?ls WHERE { <$dsURI> a void:Dataset ; void:subset ?ls . ?ls a void:Linkset ; void:target ?src  ; void:target ?target ; void:triples ?triples . ?src dcterms:title ?srctitle . ?target dcterms:title ?targettitle .  FILTER(?src != ?target && ?src = <$dsURI>) }";
	}
	
	header('Content-type: application/json');
	echo execQuery($queryStr);
}


if(isset($_GET['themes'])){
	$queryStr = "SELECT * WHERE { ?theme a skos:Concept; skos:prefLabel ?themelabel. }";
	header('Content-type: application/json');
	echo execQuery($queryStr);
}

if(isset($_GET['ds'])){
	$dsURI = $_GET['ds'];
	$queryStr = "SELECT DISTINCT * WHERE { <$dsURI> a void:Dataset ; dcterms:title ?title ; void:triples ?triples .";
	$queryStr .= " OPTIONAL { <$dsURI> foaf:homepage ?hp  } ";
	$queryStr .= " OPTIONAL { <$dsURI> dcterms:description ?desc }";
	$queryStr .= " OPTIONAL { <$dsURI> void:sparqlEndpoint ?ep }";
	$queryStr .= " OPTIONAL { <$dsURI> dcterms:contributor ?con . ?con rdfs:label ?contributor .}";
	$queryStr .= " OPTIONAL { <$dsURI> dcterms:license ?lic . ?lic rdfs:label ?license .}}";
	header('Content-type: application/json');
	echo execQuery($queryStr);
}

if(isset($_GET['ls'])){
	$dsURI = $_GET['ls'];
	$queryStr = "SELECT ?srctitle ?targettitle ?triples WHERE { ?ls a void:Linkset ; void:target ?src  ; void:target ?target ; void:triples ?triples . ?src dcterms:title ?srctitle . ?target dcterms:title ?targettitle .  }";	
	header('Content-type: application/json');
	echo execQuery($queryStr);
}




if(isset($_GET['examplefromds'])){
	$dsURI = $_GET['examplefromds'];
	$queryStr = "SELECT DISTINCT * WHERE { <$dsURI> a void:Dataset ; void:exampleResource ?example . }";
	header('Content-type: application/json');
	echo execQuery($queryStr);
}


///// POST interface
//if(isset($_POST['lParams'])){ // 
//	$lParams = json_decode($_POST['lParams'], true);
//	echo executeLookup($lParams);
//}


/* METHODS */

function initStore(){
	global $PRIMARY_DATA_SOURCE;
	loadData($PRIMARY_DATA_SOURCE);
}

// executes a SPARQL query and returns JSON result 
function execQuery($queryStr){
	global $DEBUG;
	global $PREFIXES;
	global $store;
	
	$cmd = $PREFIXES;
	$cmd .= $queryStr;
	
	if($DEBUG) echo htmlentities($cmd) . "<br />";
	
	$results = $store->query($cmd);
	return json_encode($results);
}




// low-level ARC2 store methods
function isDataLocal($graphURI){
	global $store;
	
	$cmd = "SELECT ?s FROM <$graphURI> WHERE { ?s ?p ?o .}";

	$results = $store->query($cmd);
	
	if($results['result']['rows']) return true;
	else return false;
}

function loadData($dataURI) {
	global $store;
	global $DEBUG;
	
	$cmd .= "LOAD <$dataURI> INTO <$dataURI>"; 
	
	if($DEBUG) echo htmlentities($cmd) . "<br />";

	$store->query($cmd);
	$errs = $store->getErrors();
	
	return $errs;
}

function removeData($dataURI) {
	global $store;
	global $DEBUG;
	
	$cmd .= "DELETE FROM <$dataURI>"; 
	
	if($DEBUG) echo htmlentities($cmd) . "<br />";

	$store->query($cmd);
	$errs = $store->getErrors();
	
	return $errs;
}

?>