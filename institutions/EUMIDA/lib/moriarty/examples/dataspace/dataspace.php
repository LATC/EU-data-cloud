<?php
/*
 * This script is designed for producing linked data views backed by a Talis Platform store
 * 
 * See index.php in this directory for notes on usage and configuration
 * Also see http://blogs.talis.com/n2/archives/872 
 */
ini_set ( "memory_limit", "64M");

if (!defined('MORIARTY_HTTP_CACHE_READ_ONLY')) define('MORIARTY_HTTP_CACHE_READ_ONLY', TRUE);
if (!defined('MORIARTY_HTTP_CACHE_USE_STALE_ON_FAILURE')) define('MORIARTY_HTTP_CACHE_USE_STALE_ON_FAILURE', TRUE ); // use a cached response if network fails

$media_types = array(
                        'rdf' => array('type' => 'application/rdf+xml', 'label' => 'RDF/XML'), 
                        'html' => array('type' => 'text/html',  'label' => 'HTML'),
                        'json' => array('type' => 'application/json',  'label' => 'RDF/JSON'),
                        'ttl' => array('type' => 'text/plain', 'label' => 'Turtle'),
                    );  


// Looks nasty, but it orders the data in a more friendly way
$ordered_properties = array(
       'http://www.w3.org/2004/02/skos/core#prefLabel'
      ,'http://www.w3.org/2000/01/rdf-schema#label'
      ,'http://purl.org/dc/terms/title'
      ,'http://purl.org/dc/elements/1.1/title' 
      ,'http://xmlns.com/foaf/0.1/name' 
      ,'http://www.ordnancesurvey.co.uk/ontology/AdministrativeGeography/v2.0/AdministrativeGeography.rdf#hasOfficialName'
      ,'http://www.ordnancesurvey.co.uk/ontology/AdministrativeGeography/v2.0/AdministrativeGeography.rdf#hasName'
      ,'http://www.w3.org/2006/vcard/ns#label'
      ,'http://www.w3.org/2004/02/skos/core#definition'
      ,'http://education.data.gov.uk/ontology/school#establishmentName'
      ,'http://www.w3.org/1999/02/22-rdf-syntax-ns#value'
      
      ,'http://www.w3.org/2004/02/skos/core#scopeNote'
      ,'http://open.vocab.org/terms/subtitle'
      ,'http://purl.org/ontology/po/medium_synopsis'
      ,'http://www.w3.org/2000/01/rdf-schema#comment'
      ,'http://purl.org/dc/terms/description'
      ,'http://purl.org/dc/elements/1.1/description'
      ,'http://open.vocab.org/terms/firstSentence'
      ,'http://purl.org/stuff/rev#text'
      ,'http://purl.org/dc/terms/creator'
      ,'http://purl.org/dc/elements/1.1/creator'
      ,'http://purl.org/dc/terms/contributor'
      ,'http://purl.org/dc/elements/1.1/contributor'
      ,'http://xmlns.com/foaf/0.1/depiction'
      ,'http://xmlns.com/foaf/0.1/img'
      ,'http://xmlns.com/foaf/0.1/logo'
      ,'http://xmlns.com/foaf/0.1/title'
      ,'http://xmlns.com/foaf/0.1/givenname' 
      ,'http://xmlns.com/foaf/0.1/firstName' 
      ,'http://xmlns.com/foaf/0.1/surname' 
      ,'http://purl.org/vocab/bio/0.1/olb'
      ,'http://purl.org/vocab/bio/0.1/event'
      ,'http://purl.org/vocab/relationship/childOf'
      ,'http://purl.org/vocab/relationship/parentOf'
      ,'http://purl.org/vocab/relationship/ancestorOf'
      ,'http://purl.org/vocab/relationship/descendantOf'
      ,'http://purl.org/vocab/relationship/grandchildOf'
      ,'http://purl.org/vocab/relationship/grandparentOf'
      ,'http://purl.org/vocab/relationship/lifePartnerOf'
      ,'http://purl.org/vocab/relationship/siblingOf'
      ,'http://purl.org/vocab/relationship/spouseOf'
      ,'http://xmlns.com/foaf/0.1/phone' 
      ,'http://xmlns.com/foaf/0.1/mbox' 
      ,'http://rdfs.org/sioc/ns#email'
      ,'http://xmlns.com/foaf/0.1/icqChatID' 
      ,'http://xmlns.com/foaf/0.1/msnChatID' 
      ,'http://xmlns.com/foaf/0.1/aimChatID' 
      ,'http://xmlns.com/foaf/0.1/jabberID' 
      ,'http://xmlns.com/foaf/0.1/yahooChatID'
      ,'http://xmlns.com/foaf/0.1/nick'
      ,'http://www.w3.org/2004/02/skos/core#altLabel'
      ,'http://purl.org/net/schemas/space/alternateName'
      ,'http://purl.org/ontology/bibo/shortTitle'
      ,'http://www.ordnancesurvey.co.uk/ontology/AdministrativeGeography/v2.0/AdministrativeGeography.rdf#hasVernacularName'
      ,'http://www.ordnancesurvey.co.uk/ontology/AdministrativeGeography/v2.0/AdministrativeGeography.rdf#hasBoundaryLineName'
      ,'http://xmlns.com/foaf/0.1/workplaceHomepage'
      ,'http://purl.org/vocab/relationship/employedBy'
      ,'http://purl.org/vocab/relationship/employerOf'
      ,'http://purl.org/vocab/relationship/worksWith'
      ,'http://dbpedia.org/property/leaderName'
      ,'http://xmlns.com/foaf/0.1/schoolHomepage'
      ,'http://open.vocab.org/terms/regionalContextMap'
      ,'http://open.vocab.org/terms/nationalContextMap'
      ,'http://schemas.talis.com/2005/address/schema#streetAddress'
      ,'http://schemas.talis.com/2005/address/schema#localityName'
      ,'http://schemas.talis.com/2005/address/schema#regionName'
      ,'http://schemas.talis.com/2005/address/schema#postalCode'
      ,'http://www.gazettes-online.co.uk/ontology/location#hasAddress'
      ,'http://education.data.gov.uk/ontology/school#address'
      ,'http://education.data.gov.uk/ontology/school#administrativeWard'
      ,'http://education.data.gov.uk/ontology/school#districtAdministrative'
      ,'http://education.data.gov.uk/ontology/school#localAuthority'
      
      ,'http://www.w3.org/2003/01/geo/wgs84_pos#lat'
      ,'http://www.w3.org/2003/01/geo/wgs84_pos#long'
      ,'http://www.w3.org/2003/01/geo/wgs84_pos#lat_long'
      ,'http://www.w3.org/2003/01/geo/wgs84_pos#altitude'
      ,'http://education.data.gov.uk/ontology/school#easting'
      ,'http://education.data.gov.uk/ontology/school#northing'
      ,'http://dbpedia.org/ontology/elevation'
      ,'http://xmlns.com/foaf/0.1/based_near'
      ,'http://www.w3.org/2003/01/geo/wgs84_pos#location'
      ,'http://www.w3.org/2000/10/swap/pim/contact#nearestAirport'
      ,'http://purl.org/net/schemas/space/country'
      ,'http://purl.org/net/schemas/space/place'
      ,'http://purl.org/vocab/bio/0.1/place'
      ,'http://www.ordnancesurvey.co.uk/ontology/AdministrativeGeography/v2.0/AdministrativeGeography.rdf#borders'
      
      
      ,'http://www.w3.org/1999/02/22-rdf-syntax-ns#type'
      ,'http://purl.org/dc/terms/subject'
      ,'http://purl.org/dc/elements/1.1/subject'
      ,'http://www.w3.org/2004/02/skos/core#subject'
      ,'http://purl.org/ontology/po/genre'
      ,'http://purl.org/dc/terms/LCC'
      ,'http://xmlns.com/foaf/0.1/topic'
      ,'http://rdfs.org/sioc/ns#topic'
      ,'http://www.w3.org/2004/02/skos/core#broader'
      ,'http://www.w3.org/2004/02/skos/core#narrower'
      ,'http://www.w3.org/2004/02/skos/core#closeMatch'
      ,'http://www.w3.org/2004/02/skos/core#inScheme'
      ,'http://purl.org/ontology/po/format'
      ,'http://schemas.talis.com/2006/recordstore/schema#tags'
      ,'http://purl.org/ontology/bibo/edition' 
      ,'http://purl.org/dc/terms/publisher'
      ,'http://purl.org/dc/elements/1.1/publisher'
      ,'http://rdvocab.info/Elements/placeOfPublication'
      ,'http://purl.org/dc/terms/issued'
      ,'http://www.gazettes-online.co.uk/ontology#hasPublicationDate'
      ,'http://purl.org/dc/terms/isPartOf'
      ,'http://purl.org/ontology/bibo/volume'
      ,'http://purl.org/ontology/bibo/issue'
      ,'http://purl.org/ontology/bibo/pageStart'
      ,'http://purl.org/ontology/bibo/pageEnd'
      ,'http://www.gazettes-online.co.uk/ontology#hasIssueNumber'
      ,'http://www.gazettes-online.co.uk/ontology#hasEdition'
      ,'http://rdfs.org/ns/void#exampleResource'
      ,'http://rdfs.org/ns/void#sparqlEndpoint'
      ,'http://rdfs.org/ns/void#uriLookupEndpoint'
      ,'http://rdfs.org/ns/void#subset'
      ,'http://rdfs.org/ns/void#vocabulary'
      ,'http://rdfs.org/ns/void#uriRegexPattern'
      ,'http://purl.org/dc/terms/medium'
      ,'http://open.vocab.org/terms/numberOfPages'

      ,'http://open.vocab.org/terms/weight'
      ,'http://purl.org/net/schemas/space/mass'
      ,'http://www.ordnancesurvey.co.uk/ontology/AdministrativeGeography/v2.0/AdministrativeGeography.rdf#hasArea'
      
      ,'http://xmlns.com/foaf/0.1/homepage'
      ,'http://xmlns.com/foaf/0.1/page'
      ,'http://xmlns.com/foaf/0.1/weblog'
      ,'http://purl.org/ontology/po/microsite'
      ,'http://purl.org/ontology/mo/wikipedia'
      ,'http://rdfs.org/sioc/ns#feed'
      ,'http://www.w3.org/2000/01/rdf-schema#seeAlso'
      ,'http://www.w3.org/2002/07/owl#sameAs'
      ,'http://xmlns.com/foaf/0.1/isPrimaryTopicOf'
      
      ,'http://www.w3.org/1999/02/22-rdf-syntax-ns#_1'
      ,'http://www.w3.org/1999/02/22-rdf-syntax-ns#_2'
      ,'http://www.w3.org/1999/02/22-rdf-syntax-ns#_3'
      ,'http://www.w3.org/1999/02/22-rdf-syntax-ns#_4'
      ,'http://www.w3.org/1999/02/22-rdf-syntax-ns#_5'
      ,'http://www.w3.org/1999/02/22-rdf-syntax-ns#_6'
      ,'http://www.w3.org/1999/02/22-rdf-syntax-ns#_7'
      ,'http://www.w3.org/1999/02/22-rdf-syntax-ns#_8'
      ,'http://www.w3.org/1999/02/22-rdf-syntax-ns#_9'
      ,'http://www.w3.org/1999/02/22-rdf-syntax-ns#_10'
      ,'http://www.w3.org/1999/02/22-rdf-syntax-ns#_11'
      ,'http://www.w3.org/1999/02/22-rdf-syntax-ns#_12'
      ,'http://www.w3.org/1999/02/22-rdf-syntax-ns#_13'
      ,'http://www.w3.org/1999/02/22-rdf-syntax-ns#_14'
      ,'http://www.w3.org/1999/02/22-rdf-syntax-ns#_15'
      ,'http://www.w3.org/1999/02/22-rdf-syntax-ns#_16'
      ,'http://www.w3.org/1999/02/22-rdf-syntax-ns#_17'
      ,'http://www.w3.org/1999/02/22-rdf-syntax-ns#_18'
      ,'http://www.w3.org/1999/02/22-rdf-syntax-ns#_19'
      ,'http://www.w3.org/1999/02/22-rdf-syntax-ns#_20'
      ,'http://www.w3.org/1999/02/22-rdf-syntax-ns#_21'
      ,'http://www.w3.org/1999/02/22-rdf-syntax-ns#_22'
      ,'http://www.w3.org/1999/02/22-rdf-syntax-ns#_23'
      ,'http://www.w3.org/1999/02/22-rdf-syntax-ns#_24'
      ,'http://www.w3.org/1999/02/22-rdf-syntax-ns#_25'
      ,'http://www.w3.org/1999/02/22-rdf-syntax-ns#_26'
      ,'http://www.w3.org/1999/02/22-rdf-syntax-ns#_27'
      ,'http://www.w3.org/1999/02/22-rdf-syntax-ns#_28'
      ,'http://www.w3.org/1999/02/22-rdf-syntax-ns#_29'

      ,'http://purl.org/dc/terms/identifier'
      ,'http://purl.org/dc/elements/1.1/identifier'
      ,'http://purl.org/ontology/bibo/isbn10'
      ,'http://purl.org/ontology/bibo/isbn13'
      ,'http://purl.org/ontology/bibo/lccn'
      ,'http://purl.org/ontology/bibo/oclcnum'
      ,'http://purl.org/ontology/bibo/doi'
      ,'http://purl.org/ontology/bibo/uri'
      ,'http://purl.org/ontology/bibo/issn'
      ,'http://purl.org/ontology/bibo/eissn'
      ,'http://xmlns.com/foaf/0.1/mbox_sha1sum'
      ,'http://xmlns.com/foaf/0.1/openid'
      ,'http://purl.org/net/schemas/space/internationalDesignator'
      ,'http://www.daml.org/2001/10/html/airport-ont#icao'
      ,'http://www.daml.org/2001/10/html/airport-ont#iata'
      ,'http://rdfs.org/sioc/ns#id'
      ,'http://purl.org/vocab/aiiso/schema#code'
      ,'http://dbpedia.org/property/iata'
      ,'http://dbpedia.org/property/icao'
      ,'http://www.ordnancesurvey.co.uk/ontology/AdministrativeGeography/v2.0/AdministrativeGeography.rdf#hasCensusCode'

      ,'http://schemas.talis.com/2005/dir/schema#etag'
      ,'http://purl.org/dc/terms/created'
      ,'http://purl.org/dc/terms/modified'
      ,'http://education.data.gov.uk/ontology/school#lastChangedDate'
      ,'http://purl.org/dc/terms/source' 
      ,'http://purl.org/dc/elements/1.1/source' 
      ,'http://purl.org/dc/terms/coverage'
      ,'http://purl.org/dc/elements/1.1/coverage'
      ,'http://purl.org/dc/terms/rights' 
      ,'http://purl.org/dc/elements/1.1/rights'
      ,'http://purl.org/dc/terms/license'
      ,'http://creativecommons.org/ns#license'
);



$this_host = $_SERVER["HTTP_HOST"];
$path = $_SERVER["REQUEST_URI"];
$uri = 'http://' . $this_host . $path;

$resource_uri = null;
$doc_uri = null;
$doc_type = null;

if (preg_match('~^(.+)\.(html|rdf|ttl|json)$~', $path, $m) ) {
  $resource_uri = 'http://' . $this_host . $m[1];
  $doc_uri = $uri;
  $doc_type = $m[2];
}
else {
  $resource_uri = $uri;
  $doc_uri = 'http://' . $this_host . $path . '.html';
  $doc_type = 'html';
  
  $preferred_types = array('application/rdf+xml' => 'rdf', 'text/html' => 'html', 'application/xml' => 'rdf', 'application/json'=>'json', 'text/turtle' => 'ttl', 'text/plain' => 'ttl');

  $selected_extension = 'html';
  foreach ($preferred_types as $media_type => $extension) {
    if ( preg_match("~" . preg_quote($media_type) . "~i", $_SERVER["HTTP_ACCEPT"]) ) {
      $doc_uri = 'http://' . $this_host . $path . '.' . $extension;
      $doc_type = $extension;
      break;
    }
  }

}

//$resource_uri = preg_replace('~\.local/~', '/', $resource_uri);

$store_uri = null;
$describer_class = null;
$template = dirname(__FILE__) . '/plain.tmpl.html';
foreach ($uri_map as $uri_info) {
  if (preg_match('~' . $uri_info['regex'] . '~', $resource_uri)) {
    if (array_key_exists('store', $uri_info)) {
      $store_uri = $uri_info['store'];
    }
    if (array_key_exists('template', $uri_info)) {
      $template = $uri_info['template'];
    }
    if (array_key_exists('describer', $uri_info)) {
      $describer_class = $uri_info['describer'];
    }
    break;
  }
}

if ($store_uri == null && $describer_class == null) {
  send_not_found($uri, $template);
}

$sparql_service_uri = $store_uri . '/services/sparql'; 
$search_service_uri = $store_uri . '/items'; 

require_once MORIARTY_DIR . 'moriarty.inc.php';
require_once MORIARTY_DIR . 'simplegraph.class.php';

if ($describer_class) {
  $describer = new $describer_class();
}
else {
  require_once MORIARTY_DIR . 'store.class.php';
  $describer = new Store($store_uri);
}

$response = $describer->describe($resource_uri, 'cbd', 'json');
$body = '';
$content_location = '';
$etag = '';
if ($response->is_success()) {
  if (array_key_exists('etag', $response->headers)) {
    $etag = $response->headers['etag'];
  }
  $g = new SimpleGraph();
  $g->from_json($response->body);
  if (!$g->has_triples_about($resource_uri)) {
    send_not_found($uri, $template);
  }
  else {
    $g->remove_property_values($resource_uri, 'http://schemas.talis.com/2005/dir/schema#etag');
    
    if ($uri != $doc_uri) {
      header("HTTP/1.1 303 See Other"); 
      header("Location: " . $doc_uri);        
      exit;
    }
    else {
  
      $g->add_resource_triple( $doc_uri, RDF_TYPE, FOAF_DOCUMENT );
      $g->add_resource_triple( $doc_uri, RDF_TYPE, 'http://purl.org/dc/dcmitype/Text' );
      $g->add_resource_triple( $doc_uri, FOAF_PRIMARYTOPIC, $resource_uri );
      $g->add_literal_triple( $doc_uri, 'http://purl.org/dc/terms/title', 'Linked Data for ' . $g->get_label($resource_uri, TRUE) );
    
  
      foreach ($media_types as $extension => $type_info) {
        $alt_uri = $resource_uri . '.' . $extension;
        $g->add_resource_triple( $doc_uri, 'http://purl.org/dc/terms/hasFormat', $alt_uri );
        $g->add_resource_triple( $alt_uri, 'http://purl.org/dc/terms/isFormatOf', $doc_uri );
        $g->add_resource_triple( $alt_uri, RDF_TYPE, 'http://purl.org/dc/dcmitype/Text' );
        $g->add_resource_triple( $alt_uri, RDF_TYPE, FOAF_DOCUMENT );
        $g->add_resource_triple( $alt_uri, FOAF_PRIMARYTOPIC, $resource_uri );
        $g->add_literal_triple( $alt_uri , 'http://purl.org/dc/terms/format', $type_info['type'] );
        $g->add_literal_triple( $alt_uri, 'http://purl.org/dc/terms/title', 'Linked Data in ' . $type_info['label'] . ' format for ' . $g->get_label($resource_uri, TRUE) );
      }
      
      if ($doc_type == 'rdf') {
        send_rdfxml('200 OK', $g->to_rdfxml(), $content_location, $etag);
      }
      else if ($doc_type == 'ttl') {
        send_turtle('200 OK', $g->to_turtle(), $content_location, $etag);
      }
      else if ($doc_type == 'json') {
        send_json('200 OK', $g->to_json(), $content_location, $etag);
      }
      else {
        header("Content-Type: text/html; charset=UTF-8");
        $title =  $g->get_label($resource_uri, TRUE);
        $page_title = 'Linked Data for ' . $g->get_label($resource_uri, TRUE) . ' | ' . htmlspecialchars($this_host);

        $body .= '<p>A description of the resource identified by <a href="' . htmlspecialchars($resource_uri) . '">' . htmlspecialchars($resource_uri) . '</a></p>' . "\n";

        $body .= '          <table class="linkeddata" summary="RDF description of the resource identified by ' . htmlspecialchars($resource_uri) . '. Property names are in the first column, values are in the second.">' . "\n";
        $body .= '            <tbody>' . "\n";
        $properties = $g->get_subject_properties($resource_uri, TRUE);
        $priority_properties = array_intersect($ordered_properties, $properties);
        $remaining_properties = array_diff($properties, $priority_properties);
        sort($remaining_properties);
        $properties = array_merge($priority_properties, $remaining_properties);
        
        $class = 'oddrow';
        $index = $g->get_index();
        foreach ($properties as $p) {
          $body .= '              <tr class="' . $class . '">' . "\n";
          $body .= '                <th valign="top" class="' . $class . '"><a href="' . htmlspecialchars($p). '">' . htmlspecialchars($g->get_label($p, TRUE)). '</a></th>' . "\n";
          $body .= '                <td valign="top" class="' . $class . '">';
          for ($i = 0; $i < count($index[$resource_uri][$p]); $i++) {
            if ($i > 0) {
              $body .= ', ';
            }
            
            if ($index[$resource_uri][$p][$i]['type'] === 'literal') {
              if (array_key_exists('datatype', $index[$resource_uri][$p][$i]) && $index[$resource_uri][$p][$i]['datatype'] == 'http://www.w3.org/1999/02/22-rdf-syntax-ns#XMLLiteral') {
                $body .= $index[$resource_uri][$p][$i]['value'];
              }
              else {
                $body .= htmlspecialchars($index[$resource_uri][$p][$i]['value'] );
              }
            }
            else {
              $body .= '<a href="' . htmlspecialchars($index[$resource_uri][$p][$i]['value']). '">' . htmlspecialchars($g->get_label($index[$resource_uri][$p][$i]['value'], TRUE) ). '</a>';
            }
          }
          $body .= '</td>' . "\n";
          $body .= '              </tr>' . "\n";
          if ($class === "oddrow") {
            $class = "evenrow";
          }
          else {
            $class = "oddrow";
          }
        }
        $body .= '            </tbody>' . "\n";
        $body .= '          </table>' . "\n";
      
        $body .= '          <p>The data for this description was obtained from the SPARQL service at <a href="' . htmlspecialchars($sparql_service_uri) . '">' . htmlspecialchars($sparql_service_uri) . '</a>. A free text search service is available at <a href="' . htmlspecialchars($search_service_uri) . '">' . htmlspecialchars($search_service_uri) . '</a></p>';
       
        $alternates = array();
        foreach ($media_types as $extension => $type_info) {
          $alternates[] = array('type' => $type_info['type'], 'name' => $type_info['label'], 'uri' => $resource_uri . '.' . $extension);
        }

        send_html('200 OK', $template, $page_title, $title, $body, $content_location, $etag, $resource_uri, $alternates);

      }
    }
  }
}
elseif ($response->status_code === 404) {
  send_not_found($uri, $template);
}
else {
  header("HTTP/1.0 500 Internal Server Error");
  $body = "<p>This server encountered an error while obtaining the description of " . htmlspecialchars($uri) . "</p><!-- \n" . htmlspecialchars($response->to_string()) . "\n-->\n";
  $body .= '<p>Search for more data using the SPARQL service at <a href="' . htmlspecialchars($sparql_service_uri) . '">' . htmlspecialchars($sparql_service_uri) . '/sparql</a> or the free text search service at <a href="' . htmlspecialchars($search_service_uri) . '">' . htmlspecialchars($search_service_uri) . '</a></p>';

  $title = 'Server Error';
  $page_title = 'Server Error' . ' | ' . htmlspecialchars($this_host);
}
  

function send_not_found($uri, $template) {
  global $sparql_service_uri;
  global $search_service_uri;

  $body = "<p>". htmlspecialchars($uri) . " was not found on this server.</p>";
  $body .= '<p>Search for more data using the SPARQL service at <a href="' . htmlspecialchars($sparql_service_uri) . '">' . htmlspecialchars($sparql_service_uri) . '</a> or the free text search service at <a href="' . htmlspecialchars($search_service_uri) . '">' . htmlspecialchars($search_service_uri) . '</a></p>';
  send_html('404 Not Found', $template, 'Resource Not Found', 'Resource Not Found', $body);
  exit;
}

function send_rdfxml($status_line, $body, $content_location = '', $etag = '') {
  header("HTTP/1.0 " . $status_line);
  header("Content-Type: application/rdf+xml");  
  header("Cache-Control: max-age=7200, must-revalidate");
  if ($content_location) {
    header("Content-Location: " . $content_location);
    header("Vary: accept");
  }
  if ($etag) {
    header("ETag: " . $etag);
  }
  echo $body;
  exit;
}

function send_turtle($status_line, $body, $content_location = '', $etag = '') {
  header("HTTP/1.0 " . $status_line);
  header("Content-Type: text/turtle");  
  header("Cache-Control: max-age=7200, must-revalidate");
  if ($content_location) {
    header("Content-Location: " . $content_location);
    header("Vary: accept");
  }
  if ($etag) {
    header("ETag: " . $etag);
  }
  echo $body;
  exit;
}

function send_json($status_line, $body, $content_location = '', $etag = '') {
  header("HTTP/1.0 " . $status_line);
  header("Content-Type: application/json");  
  header("Cache-Control: max-age=7200, must-revalidate");
  if ($content_location) {
    header("Content-Location: " . $content_location);
    header("Vary: accept");
  }
  if ($etag) {
    header("ETag: " . $etag);
  }
  echo $body;
  exit;
}

function send_html($status_line, $template, $page_title, $title, $body, $content_location = '', $etag = '',$resource_uri = '', $alternates = array()) {
  header("HTTP/1.0 " . $status_line);
  header("Content-Type: text/html");  
  header("Cache-Control: max-age=7200, must-revalidate");
  if ($content_location) {
    header("Content-Location: " . $content_location);
    header("Vary: accept");
  }
  if ($etag) {
    header("ETag: " . $etag);
  }
  require_once($template);
  exit;
}
