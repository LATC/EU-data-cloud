<?php
include 'config.inc.php';

define('GRAPH_URI', 'http://prelex.publicdata.eu/');
define('VOCAB_BASE', 'http://prelex.publicdata.eu/ontology/');
define('URI_BASE', 'http://prelex.publicdata.eu/r/');

$events = array();
$documentSubTypes = array();
$organizations = array();
$persons = array();
$documentTitles = array();

$alreadySeen = array();
//$bNodeCounter = 0;

$prefixes = array(
    'http://www.w3.org/1999/02/22-rdf-syntax-ns#' => 'rdf',
    'http://www.w3.org/2000/01/rdf-schema#' => 'rdfs',
    'http://purl.org/dc/terms/' => 'dct',
    'http://www.w3.org/2002/07/owl#' => 'owl',
    'http://eur-lex.publicdata.eu/ontology/' => 'eurlex',
    'http://prelex.publicdata.eu/ontology/' => 'prelex'
);

$writtenTriples = array();


// 1. Fetch the first part of the data as JSON
$apiUrl = 'http://api.epdb.eu/prelex/document/?key=' . API_KEY;

echo 'Fetching first part of JSON data now...';
$data = _fetchJSON($apiUrl);
echo ' DONE' . PHP_EOL;

echo 'Handling API result now...';
$result = _handleData($data);
echo ' DONE' . PHP_EOL;

echo 'Writing triples to file...';
$ntriples = $result['ntriples'];
_writeTriples($ntriples, false);
echo ' DONE' . PHP_EOL;

$lastOffset = null;
while (true) {
    if (isset($result['next'])) {
        if (in_array($result['next'], $alreadySeen)) {
            break;
        }
        
        $offset = (int)substr($result['next'], strpos($result['next'], 'offset=')+7);
        if ($offset < $lastOffset) {
            break;
        }
        $lastOffset = $offset;
        
        echo 'Fetching next part of JSON data now (' . $result['next'] . ')...';
        $data = _fetchJSON($result['next'].'&key=' . API_KEY);
        echo ' DONE' . PHP_EOL;
        
        echo 'Handling API result now...';
        $result = _handleData($data);
        echo ' DONE' . PHP_EOL; 
        
        echo 'Writing triples to file...';
        $ntriples = $result['ntriples'];
        _writeTriples($ntriples);
        echo ' DONE' . PHP_EOL;
    } else {
        break;
    }
}

// Write other stuff
echo 'Writing events...';
$ntriples = array();
$eventSubTypes = array();
foreach ($events as $uri=>$spec) {
    
    $subTypeURI = URI_BASE . 'eventType/' . urlencode($spec['event']);
    $eventSubTypes[$subTypeURI] = $spec['event'];
    $ntriples[] = _createTriple($uri, 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', $subTypeURI, true);
        
    $ntriples[] = _createTriple($uri, 'http://prelex.publicdata.eu/ontology/event_id', $spec['event_id']);
    $ntriples[] = _createTriple($uri, 'http://prelex.publicdata.eu/ontology/eventOfDocument', (URI_BASE . 'document/' . $spec['doc_id']), true);
    $ntriples[] = _createTriple($uri, 'http://prelex.publicdata.eu/ontology/eventDate', $spec['date'], false, false, false, 'http://www.w3.org/2001/XMLSchema#date');

    $title = $spec['doc_id'] . ': ' . $spec['event'];
    $ntriples[] = _createTriple($uri, 'http://purl.org/dc/terms/title', $title);
}
_writeTriples($ntriples, false, 'events');
echo ' DONE' . PHP_EOL;
echo 'Writing event types...';
$ntriples = array();
foreach ($eventSubTypes as $uri=>$subType) {
    $ntriples[] = _createTriple($uri, 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://www.w3.org/2002/07/owl#Class', true);
    $ntriples[] = _createTriple($uri, 'http://www.w3.org/2000/01/rdf-schema#subClassOf', 'http://prelex.publicdata.eu/ontology/Event', true);
    $ntriples[] = _createTriple($uri, 'http://purl.org/dc/terms/title', $subType);
}
_writeTriples($ntriples, false, 'schema');
echo ' DONE' . PHP_EOL;

echo 'Writing document types...';
$ntriples = array();
foreach ($documentSubTypes as $uri=>$subType) {
    $ntriples[] = _createTriple($uri, 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://www.w3.org/2002/07/owl#Class', true);
    $ntriples[] = _createTriple($uri, 'http://www.w3.org/2000/01/rdf-schema#subClassOf', 'http://prelex.publicdata.eu/ontology/Document', true);
    $ntriples[] = _createTriple($uri, 'http://purl.org/dc/terms/title', $subType);
}
_writeTriples($ntriples, true, 'schema');
echo ' DONE' . PHP_EOL;

echo 'Writing organizations...';
$ntriples = array();
foreach ($organizations as $uri=>$org) {
    $ntriples[] = _createTriple($uri, 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://prelex.publicdata.eu/ontology/Organization', true);
    $ntriples[] = _createTriple($uri, 'http://www.w3.org/2000/01/rdf-schema#label', $org);
}
_writeTriples($ntriples, false, 'organizations');
echo ' DONE' . PHP_EOL;

echo 'Writing persons...';
$ntriples = array();
foreach ($persons as $uri=>$person) {
    $ntriples[] = _createTriple($uri, 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://prelex.publicdata.eu/ontology/Person', true);
    $ntriples[] = _createTriple($uri, 'http://www.w3.org/2000/01/rdf-schema#label', $person);
}
_writeTriples($ntriples, false, 'persons');
echo ' DONE' . PHP_EOL;

echo 'DONE!' . PHP_EOL;


function _handleData($data)
{
    global $organizations;
    global $documentSubTypes;
    global $persons;
    global $events;
    global $documentTitles;

    $result = array();
    $ntriples = array();
    
    foreach ($data as $i=>$itemSpec) {
        if (!is_array($itemSpec)) {
            $result['next'] = $itemSpec;
            continue;
        }
        
        $s = URI_BASE . 'document/' . $i;
        
        foreach ($itemSpec as $key=>$value) {
            if (is_string($value)) {
                $value = trim($value);
            }
            if ($value === '') {
                continue;
            }
            
            if ($key === 'doc_id') {
                $ntriples[] = _createTriple($s, 'http://prelex.publicdata.eu/ontology/doc_id', $value);
            } else if ($key === 'com_number') {
                $ntriples[] = _createTriple($s, 'http://prelex.publicdata.eu/ontology/com_number', $value);
            } else if ($key === 'prelex_perma_url') {
                $ntriples[] = _createTriple($s, 'http://prelex.publicdata.eu/ontology/perma_url', $value, true);
            } else if ($key === 'eurlex_perma_url') {
                $ntriples[] = _createTriple($s, 'http://eur-lex.publicdata.eu/ontology/perma_url', $value, true);
            } else if ($key === 'dg_responsible') {
                $orgUri = URI_BASE . 'organization/' . urlencode($value);
                $organizations[$orgUri] = $value;
                $ntriples[] = _createTriple($s, 'http://prelex.publicdata.eu/ontology/dg_responsible', $orgUri, true);
            } else if ($key === 'legal_basis') {
                // value may be an array
                if (is_array($value)) {
                    foreach ($value as $v) {
                        $ntriples[] = _createTriple($s, 'http://prelex.publicdata.eu/ontology/legal_basis', $v['legal_basis']);
                    }
                } else {
                    $ntriples[] = _createTriple($s, 'http://prelex.publicdata.eu/ontology/legal_basis', $value);
                }
            } else if ($key === 'prelex_procedure') {
                $ntriples[] = _createTriple($s, 'http://prelex.publicdata.eu/ontology/prelex_procedure', $value);
            } else if ($key === 'title') {
                $documentTitles[$i] = $value;
               $ntriples[] =  _createTriple($s, 'http://purl.org/dc/terms/title', $value);
            } else if ($key === 'legislative_type') {
                $typeURI = URI_BASE . 'legislativeType/' . urlencode($value);
                $documentSubTypes[$typeURI] = $value;
                $ntriples[] = _createTriple($s, 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', $typeURI, true);
            } else if ($key === 'commissioner') {
                $personUri = URI_BASE . 'person/' . urlencode($value);
                $persons[$personUri] = $value;
                $ntriples[] = _createTriple($s, 'http://prelex.publicdata.eu/ontology/commissioner', $personUri, true);
            } else if ($key === 'adoption_commission') {
                $ntriples[] = _createTriple($s, 'http://prelex.publicdata.eu/ontology/adoption_commission', $value, false, false, false, 'http://www.w3.org/2001/XMLSchema#date');
            } else if ($key === 'adoption_council') {
                $ntriples[] = _createTriple($s, 'http://prelex.publicdata.eu/ontology/adoption_council', $value, false, false, false, 'http://www.w3.org/2001/XMLSchema#date');
            } else if ($key === 'events') {
                // events is an array
                foreach ($value as $event) {
                    $eventURI = URI_BASE . 'event/' . $event['event_id'];
                    $events[$eventURI] = $event;
                    $ntriples[] = _createTriple($s, 'http://prelex.publicdata.eu/ontology/event', $eventURI, true);
                }
            } else if ($key === 'directory_codes') {
                // directory_codes is an array
                foreach ($value as $dirCode) {
                    $ntriples[] = _createTriple($s, 'http://prelex.publicdata.eu/ontology/directory_code', $dirCode['directory_code']);
                }
            }
        }
    }

    $result['ntriples'] = $ntriples;
    
    return $result;
}

function _createTriple($s, $p, $o, $oIsUri = false, $sIsBNode = false, $oIsBnode = false, $oDatatype = null)
{
    if (!$sIsBNode) {
        $s = '<' . $s . '>';
    }
    
    $p = '<' . $p . '>';
    
    if (!$oIsUri) {
        $o = '"""' . str_replace("'", "\'", $o) . '"""';
    } else if (!$oIsBnode) {
        $o = '<' . $o . '>';
    }

    if (null !== $oDatatype) {
        $o .= '^^<' . $oDatatype . '>';
    } else if (!$oIsUri && !$oIsBnode) {
        $o .= '@en';
    }
    
    return $s . ' ' . $p . ' ' . $o . ' . ' . PHP_EOL;
}

function _writeTriples($triples, $append = true, $file = null)
{
    global $writtenTriples;

    $ntriples = array();
    foreach ($triples as $t) {
        $md5 = md5($t);
        if (isset($writtenTriples[$md5])) {
            continue;
        }

        $ntriples[] = $t;
        $writtenTriples[$md5] = true;
    }

    $fileName = 'data/data.ttl';
    if (null !== $file) {
        $fileName = 'data/' . $file . '.ttl';
    }

    if ($append) {
        file_put_contents($fileName, $ntriples, FILE_APPEND);
    } else {
        file_put_contents($fileName, $ntriples);
    }
}

function _handleProperty(&$property, &$value)
{
    global $formProperties;

    if ($property === 'form') {
        if (!isset($formProperties[$value])) {
            $formProperties[$value] = VOCAB_BASE . urlencode($value);
        }
    } else if ($property === 'title') {
        if (substr($value, 0, 3) === '/* ') {
            $value = substr($value, 3);
        }
        if (substr($value, 0, 2) === '/*') {
            $value = substr($value, 2);
        }  
        if (substr($value, -3) === ' */') {
            $value = substr($value, 0, -3);
        } 
        if (substr($value, -2) === '*/') {
            $value = substr($value, 0, -2);
        } 
    }
}

function _fetchJSON($url)
{
    global $alreadySeen;
    
    $alreadySeen[] = $url;
    
    $md5 = md5($url);
    if (file_exists('cache/' . $md5)) {
        return json_decode(file_get_contents('cache/' . $md5), true);
    }
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $result = curl_exec($ch);
    curl_close($ch);
    
    file_put_contents('cache/' . $md5, $result);
    
    return json_decode($result, true);
}

