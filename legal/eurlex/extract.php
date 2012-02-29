<?php
include 'config.inc.php';

define('GRAPH_URI', 'http://eur-lex.publicdata.eu/');
define('VOCAB_BASE', 'http://eur-lex.publicdata.eu/ontology/');
define('URI_BASE', 'http://eur-lex.publicdata.eu/id/');

// meta data
define('CREATOR', 'Philipp Frischmuth');
define('VERSION', '0.1');
define('COMMENT', 'The EUR-LEX dataset provided as Linked Data.');
define('LABEL', 'EUR-LEX Dataset');


$alreadySeen = array();
$bNodeCounter = 0;

$prefixes = array(
    'http://eur-lex.publicdata.eu/resource/'      => 'ns0',
    'http://eur-lex.publicdata.eu/ontology/'      => 'ns1',
    'http://purl.org/dc/terms/'                   => 'ns2',
    'http://www.w3.org/1999/02/22-rdf-syntax-ns#' => 'rdf'
);

$propertyMapping = array(
    // 'form'                => '',
    //'title'               => 'http://purl.org/dc/terms/title',
    // 'api_url'             => '',
    //     'eurlex_perma_url'    => '',
    //     'doc_id'              => '',
    //     'date_document'       => '',
    //     'of_effect'           => '',
    //     'end_validity'        => '',
    //     'oj_date'             => '',
    //     'directory_codes'     => '',
    //     'legal_basis'         => '',
    //     'addressee'           => '',
    //     'internal_ref'        => '',
    //     'additional_info'     => '',
    //     'text_url'            => '',
    //     'prelex_relation'     => '',
    //     'relationships'       => '',
    //     'eurovoc_descriptors' => '',
    //     'subject_matter'      => ''
);

$objectProperties = array(
    // 'form'                => '',
    //'title'               => 'http://purl.org/dc/terms/title',
    // 'api_url'             => '',
    'eurlex_perma_url',
    //     'doc_id'              => '',
    //     'date_document'       => '',
    //     'of_effect'           => '',
    //     'end_validity'        => '',
    //     'oj_date'             => '',
    'directory_code',
    //     'legal_basis'         => '',
    //     'addressee'           => '',
    //     'internal_ref'        => '',
    //     'additional_info'     => '',
    //     'text_url'            => '',
    //     'prelex_relation'     => '',
    //     'relationships'       => '',
    'eurovoc_descriptor',
    //     'subject_matter'      => ''
);

$skippedProperties = array(
    'api_url'
);

$formProperties = array();
$relations = array();

$writtenTriples = array();

#$prefixString = '';
#foreach ($prefixes as $ns=>$prefix) {
#    $prefixString .= '@prefix ' . $prefix . ': <' . $ns . '> . ' . PHP_EOL;
#}
#$prefixString .= PHP_EOL;
#file_put_contents('data.ttl', $prefixString);


// 1. Fetch the first part of the data as JSON
$apiUrl = 'http://api.epdb.eu/eurlex/document/?key=' . API_KEY;

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

while (true) {
    if (isset($result['next'])) {
        if (in_array($result['next'], $alreadySeen)) {
            break;
        }
        
        echo 'Fetching next part of JSON data now (' . $result['next'] . ')...';
        $data = _fetchJSON($result['next']);
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

// Now write the schema triples
$schemaTriples = array();
$schemaTriples[] = _createTriple(
    GRAPH_URI, 
    'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 
    'http://www.w3.org/2002/07/owl#Ontology',
    true
);
$schemaTriples[] = _createTriple(
    GRAPH_URI, 
    'http://purl.org/dc/terms/creator', 
    CREATOR
);
$schemaTriples[] = _createTriple(
    GRAPH_URI, 
    'http://www.w3.org/2002/07/owl#versionInfo', 
    VERSION
);
$schemaTriples[] = _createTriple(
    GRAPH_URI, 
    'http://www.w3.org/2000/01/rdf-schema#comment', 
    COMMENT
);
$schemaTriples[] = _createTriple(
    GRAPH_URI, 
    'http://www.w3.org/2000/01/rdf-schema#label', 
    LABEL
);

// forms
$docClass = VOCAB_BASE . 'Document';
$schemaTriples[] = _createTriple(
    $docClass, 
    'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 
    'http://www.w3.org/2002/07/owl#Class',
    true
);
$schemaTriples[] = _createTriple(
    $docClass, 
    'http://www.w3.org/2000/01/rdf-schema#label', 
    'Document'
);
foreach ($formProperties as $form=>$formUri) {
    $schemaTriples[] = _createTriple(
        $formUri, 
        'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 
        'http://www.w3.org/2002/07/owl#Class',
        true
    );
    $schemaTriples[] = _createTriple(
        $formUri, 
        'http://www.w3.org/2000/01/rdf-schema#label', 
        ucfirst(strtolower($form))
    );
    $schemaTriples[] = _createTriple(
        $formUri, 
        'http://www.w3.org/2000/01/rdf-schema#subClassOf', 
        $docClass,
        true
    );
}

// relations
foreach ($relations as $rel=>$relUri) {
    $schemaTriples[] = _createTriple(
        $relUri, 
        'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 
        'http://www.w3.org/2002/07/owl#Class',
        true
    );
    $schemaTriples[] = _createTriple(
        $relUri, 
        'http://www.w3.org/2000/01/rdf-schema#label', 
        $rel
    );
}

// write schema
_writeTriples($schemaTriples, false, true);

echo ' DONE!' . PHP_EOL;

function _handleData($data)
{
    global $bNodeCounter;
    global $propertyMapping;
    global $objectProperties;
    global $skippedProperties;
    global $formProperties;
    global $relations;
#var_dump($data);exit;
    $result = array();
    $ntriples = array();
    
    foreach ($data as $i=>$itemSpec) {
        if (!is_array($itemSpec)) {
            $result['next'] = $itemSpec;
            continue;
        }
        
        foreach ($itemSpec as $key=>$value) {
            $s = URI_BASE . $i;

            // Add a (short) label
            _createTriple($s, 'http://www.w3.org/2000/01/rdf-schema#label', $i);


            #if ($key !== 'title') {
            #    continue;
            #}

            // Skip iff defined in skip array
            if (in_array($key, $skippedProperties)) {
                continue;
            }

            // Handle special Properties
            _handleProperty($key, $value);
            // Special case: form
            if ($key === 'form') {
                $p = 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type';
                $o = $formProperties[$value];
                $ntriples[] = _createTriple($s, $p, $o, true); 
                continue;
            }

            $p = null;
            if (isset($propertyMapping[$key])) {
                $p = $propertyMapping[$key];
            } else {
                $p = VOCAB_BASE . urlencode($key);
            }

            // Skip emtpy values
            if ($value === '') {
                continue;
            }
        
            $o = null;
            if (is_string($value)) {
                $o = $value;

                if (in_array($key, $objectProperties)) {
                    $o = URI_BASE . urlencode($o);
                    $ntriples[] = _createTriple($s, $p, $o, true); 
                } else {
                    $ntriples[] = _createTriple($s, $p, $o);
                }
            } else {
                // Special case: relationships
                if ($key === 'relationships') {
                    foreach ($value as $oItemSpec) {
                        $rel = strtolower(trim($oItemSpec['relationship']));
                        $rel = str_replace(':', '', $rel);

                        $p = VOCAB_BASE . ucfirst(urlencode($rel));

                        $relations[$rel] = $p;

                        $o = null;
                        if ($oItemSpec['link'] !== '') {
                            $o = str_replace(' ', '+', $oItemSpec['link']);
                        } else {
                            $o = VOCAB_BASE . urlencode($oItemSpec['relation']);
                        }

                        $ntriples[] = _createTriple($s, $p, $o, true);
                        $ntriples[] = _createTriple(
                            $o, 
                            'http://www.w3.org/1999/02/22-rdf-syntax-ns#type',
                            $p,
                            true
                        );
                        $ntriples[] = _createTriple(
                            $o, 
                            'http://www.w3.org/2000/01/rdf-schema#label',
                            $oItemSpec['relation']
                        );
                    }

                    continue;
                }


                foreach ($value as $oItemSpec) {
                    if (count($oItemSpec) === 1) {
                        foreach ($oItemSpec as $itemKey=>$itemValue) {
                            $p = VOCAB_BASE . $itemKey;

                            if (in_array($itemKey, $objectProperties)) {
                                $o = URI_BASE . urlencode($itemValue);
                                $ntriples[] = _createTriple($s, $p, $o, true);

                            } else {
                                $ntriples[] = _createTriple($s, $p, $itemValue);
 
                            }
                        }
                    } else {
                        $bNodeID = '_:bnode' . $bNodeCounter++;
                        $ntriples[] = _createTriple($s, $p, $bNodeID, false, false, true);
                    
                        foreach ($oItemSpec as $itemKey=>$itemValue) {
                            $ntriples[] = $bNodeID . ' <' . $itemKey . '> "' . $itemValue . '" . ' . PHP_EOL;
                            $ntriples[] =_createTriple($bNodeID, $itemKey, $itemValue, false, true, false);
                        }
                    }
                }
            }   
        }
    }

    $result['ntriples'] = $ntriples;
    
    return $result;
}

function _createTriple($s, $p, $o, $oIsUri = false, $sIsBNode = false, $oIsBnode = false)
{
    global $prefixes;

    $sHandled = false;
    $pHandled = false;
    $oHandled = false;
    /*foreach ($prefixes as $ns=>$prefix) {
        if (!$sHandled && !$sIsBNode) {
            if (strpos($s, $ns) !== false) {
                $s = $prefix . ':' . substr($s, strlen($ns));
                $sHandled = true;
            }        }
        if (!$pHandled) {
            if (strpos($p, $ns) !== false) {
                $p = $prefix . ':' . substr($p, strlen($ns));
                $pHandled = true;
            }
        }
        if (!$oHandled && !$oIsBnode) {
            if ($oIsUri) {
                 if (strpos($o, $ns) !== false) {
                    $o = $prefix . ':' . substr($o, strlen($ns));
                    $oHandled = true;
                 }
            } else {
                $o = '"' . $o . '"';
                $oHandled = true;
            }
        }
    }*/
    if (!$sHandled && !$sIsBNode) {
        $s = '<' . $s . '>';
    }
    if (!$pHandled) {
        $p = '<' . $p . '>';
    }
    if (!$oIsUri) {
        $o = '"' . $o . '"';
    } else if (!$oHandled && !$oIsBnode) {
        $o = '<' . $o . '>';
    }

    return $s . ' ' . $p . ' ' . $o . ' . ' . PHP_EOL;
}

function _writeTriples($triples, $append = true, $schema = false)
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

    $fileName = 'data.ttl';
    if ($schema) {
        $fileName = 'schema.ttl';
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

