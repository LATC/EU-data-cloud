<?php
define('API_KEY', '');
define('VOCAB_BASE', 'http://latc.aksw.org/eur-lex/vocab/');
define('URI_BASE', 'http://latc.aksw.org/eur-lex/resource/');

$alreadySeen = array();
$bNodeCounter = 0;

$prefixes = array(
    'http://latc.aksw.org/eur-lex/resource/' => 'ns0',
    'http://latc.aksw.org/eur-lex/vocab/'    => 'ns1'
);

$propertyMapping = array(
    // 'form'                => '',
    'title'               => 'http://purl.org/dc/terms/title',
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
file_put_contents('data.ttl', $ntriples);
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
        file_put_contents('data.ttl', $ntriples, FILE_APPEND);
        echo ' DONE' . PHP_EOL;
    } else {
        break;
    }
}

echo ' DONE!' . PHP_EOL;

function _handleData($data)
{
    global $bNodeCounter;
    global $propertyMapping;
    global $objectProperties;
    
    $result = array();
    $ntriples = array();
    
    foreach ($data as $i=>$itemSpec) {
        if (!is_array($itemSpec)) {
            $result['next'] = $itemSpec;
            continue;
        }
        
        foreach ($itemSpec as $key=>$value) {
            $s = URI_BASE . $i;
        
            $p = null;
            if (isset($propertyMapping[$key])) {
                $p = $propertyMapping[$key];
            } else {
                $p = VOCAB_BASE . $key;
            }
        
            $o = null;
            if (is_string($value)) {
                $o = $value;
            
                $ntriples[] = '<' . $s . '> <' . $p . '> "' . $o . '" . ' . PHP_EOL; 
            } else {
                foreach ($value as $oItemSpec) {
                    if (count($oItemSpec) === 1) {
                        foreach ($oItemSpec as $itemKey=>$itemValue) {
                            $ntriples[] = '<' . $s . '> <' . $itemKey . '> "' . $itemValue . '" . ' . PHP_EOL; 
                        }
                    } else {
                        $bNodeID = '_:bnode' . $bNodeCounter++;
                        $ntriples[] = '<' . $s . '> <' . $p . '> ' . $bNodeID . ' . ' . PHP_EOL; 
                    
                        foreach ($oItemSpec as $itemKey=>$itemValue) {
                            $ntriples[] = $bNodeID . ' <' . $itemKey . '> "' . $itemValue . '" . ' . PHP_EOL; 
                        }
                    }
                }
            }   
        }
    }

    $result['ntriples'] = $ntriples;
    
    return $result;
}

function _writeTriple($s, $p, $o)
{
    
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
