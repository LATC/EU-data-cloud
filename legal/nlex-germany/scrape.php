<?php
require 'scraperwiki/scraperwiki.php';
require 'scraperwiki/simple_html_dom.php';

$vocab = array(
    'rdf'     => 'http://www.w3.org/1999/02/22-rdf-syntax-ns#',
    'rdfs'    => 'http://www.w3.org/2000/01/rdf-schema#',
    'owl'     => 'http://www.w3.org/2002/07/owl#',
    'dct'     => 'http://purl.org/dc/terms/',
    'foaf'    => 'http://xmlns.com/foaf/0.1/',
    'frbr'    => 'http://purl.org/vocab/frbr/core#',
    'metalex' => 'http://www.metalex.eu/metalex/2008-05-02#',
    'nlex'    => 'http://n-lex.publicdata.eu/ontology/'
);

$rdfData = array();

define('RDF_TYPE', $vocab['rdf'] . 'type');
define('METALEX_BIBEXPR', $vocab['metalex'] . 'BibliographicExpression');
define('METALEX_BIBWORK', $vocab['metalex'] . 'BibliographicWork');

define('URL_BASE', 'http://www.gesetze-im-internet.de');
define('URI_BASE', 'http://n-lex.publicdata.eu/germany/id/');

// First get the URLs for the index pages...
$cacheFilename = 'cache/urls';
$allLexURLs = null;
if (!file_exists($cacheFilename)) {
    $startURL = 'http://www.gesetze-im-internet.de/aktuell.html';
    $html = scraperWiki::scrape($startURL);
    $dom = new simple_html_dom();
    $dom->load($html);

    $indexPages = array();
    foreach($dom->find("div[@id='paddingLR12'] p") as $data) {
        $as = $data->find("a");
        foreach ($as as $a) {
            $record = array(
                'title' => html_entity_decode($a->plaintext, ENT_COMPAT, 'ISO-8859-1'),
                'url' => URL_BASE . substr($a->href, 1)
            );
            $indexPages[] = $record;
        }
    }
    $dom->__destruct();

    $allLexURLs = array();
    foreach ($indexPages as $indexPageSpec) {
        echo 'Parsing index page: ' . $indexPageSpec['title'] . PHP_EOL;
        $url = $indexPageSpec['url'];
        $lexURLs = _scrapeIndexPage($url);
        $allLexURLs = array_merge($allLexURLs, $lexURLs);
    }
    
    echo 'Found ' . count($allLexURLs) . ' lex pages.' . PHP_EOL;
    file_put_contents($cacheFilename, serialize($allLexURLs));
} else {
    $allLexURLs = unserialize(file_get_contents($cacheFilename));
}

$append = false;
foreach ($allLexURLs as $lexURL) {
    echo 'Handling: ' . $lexURL['title'] . PHP_EOL;
    $html = _getHTML($lexURL['url']);
    _scrapeLexPage($html, $lexURL['url']);
    
    _exportRDF($rdfData, $append);
    $append = true;
    $rdfData = array(); // reset
}

echo PHP_EOL . "DONE" . PHP_EOL;

//
// Functions
//

function _scrapeLexPage($html, $url)
{
    global $vocab;
    global $rdfData;
    
    $dom = new simple_html_dom();
    $dom->load($html);
                     
    $h2a = $dom->find("h2[@class='headline'] a");
    $href = $h2a[0]->href;
    $id = substr($href, 0, strrpos($href, '.'));
    $fullURL = substr($url, 0, strrpos($url, '/')) . '/' . $href;
    $dom->__destruct();
    $uri = URI_BASE . $id;
    
    $html = _getHTML($fullURL);
    $dom = new simple_html_dom();
    $dom->load($html);
    
// x a http://www.metalex.eu/metalex/2008-05-02#BibliographicWork
// dct:title, dct:description, metalex:fragment/fragmentOf, rdfs:seeAlso, rdfs:label, dcterms:source

    // x rdf:type metalex:BibliographicWork
    $rdfData[$uri] = array(
        RDF_TYPE => array(array(
          'type'  => 'uri',
          'value' => METALEX_BIBWORK
        ))
    );
    
    // title, label
    $h1Element = $dom->find("div[@id='paddingLR12'] div[@class='jnheader'] h1");
    $title = html_entity_decode($h1Element[0]->plaintext, ENT_COMPAT, 'ISO-8859-1');
    $rdfData[$uri][$vocab['dct'].'title'] = array(array(
        'type'  => 'literal',
        'value' => $title,
        'lang'  => 'de'
    ));
    $rdfData[$uri][$vocab['rdfs'].'label'] = array(array(
        'type'  => 'literal',
        'value' => $title,
        'lang'  => 'de'
    ));
    
    // token
    $pElems = $dom->find("div[@id='paddingLR12'] div[@class='jnheader'] p");
    if (isset($pElems[0])) {
        $token = html_entity_decode($pElems[0]->plaintext, (ENT_COMPAT), 'ISO-8859-1');
        $rdfData[$uri][$vocab['nlex'].'token'] = array(array(
            'type'  => 'literal',
            'value' => $token
        ));
    }
    
    
    // seeAlso
    $rdfData[$uri][$vocab['rdfs'].'seeAlso'] = array(array(
        'type'  => 'uri',
        'value' => $fullURL
    ));
    
    $i = 0;
    $currentChapterURI = null;
    $jnNorms = $dom->find("div[@id='paddingLR12'] div[@class='jnnorm']");
    foreach ($jnNorms as $data) {
        if ($i === 0) {
            // We skip the first for now, since it contains only header info
            ++$i;
            continue;
        }
        ++$i;
        
        $result = false;
        // If the text contains a h3, it is a fragment
        $h3 = $data->find("div[@class='jnheader'] h3 span");
        if ($h3) {
            if (null !== $currentChapterURI) {
                $result = _handleFragmentForURI($data, $currentChapterURI, $fullURL); 
            } else {
                $result = _handleFragmentForURI($data, $uri, $fullURL); 
            }
        } else {
            // If we find a h2 this is a chapter
            $h2 = $data->find("div[@class='jnheader'] h2");
            if ($h2) {
                $currentChapterURI = _handleChapterForURI($data, $uri, $fullURL); 
                $result = true; // special case...
            } else {
                return false;
            }
        }
        if (!$result) {
             return false;
        }  
    }
    
    $dom->__destruct();
    
    return true;
}

function _handleChapterForURI($data, $uri, $docURL)
{
    global $vocab;
    global $rdfData;
    
    $h2 = $data->find("div[@class='jnheader'] h2");
    $chapterTitle = html_entity_decode($h2[0]->plaintext, ENT_COMPAT, 'ISO-8859-1');
    $chapterURI = $uri . '/' . urlencode($chapterTitle);
    
    $anchor = $data->find("div[@class='jnheader'] a");
    $chapterHTMLURL = $docURL . '#' . $anchor[0]->name;
    
    // fragment
    $rdfData[$uri][$vocab['metalex'].'fragment'] = array(array(
        'type'  => 'uri',
        'value' => $chapterURI
    ));
        
    // x rdf:type metalex:BibliographicExpression
    $rdfData[$chapterURI] = array(
        RDF_TYPE => array(array(
            'type'  => 'uri',
            'value' => METALEX_BIBEXPR
        ))
    );
    // title, label
    $rdfData[$chapterURI][$vocab['dct'].'title'] = array(array(
        'type'  => 'literal',
        'value' => $chapterTitle
    ));
    $rdfData[$chapterURI][$vocab['rdfs'].'label'] = array(array(
        'type'  => 'literal',
        'value' => $chapterTitle
    ));
        
    // fragmentOf
    $rdfData[$chapterURI][$vocab['metalex'].'fragmentOf'] = array(array(
        'type'  => 'uri',
        'value' => $uri
    ));
        
    // seeAlso
    $rdfData[$chapterURI][$vocab['rdfs'].'seeAlso'] = array(array(
        'type'  => 'uri',
        'value' => $chapterHTMLURL
    ));
    
    return $chapterURI;
}

function _handleFragmentForURI($data, $uri, $docURL)
{
    global $vocab;
    global $rdfData;
    
    $anchor = $data->find("div[@class='jnheader'] a");
    $fragmentHTMLURL = $docURL . '#' . $anchor[0]->name;
        
    $h3 = $data->find("div[@class='jnheader'] h3 span");
    $fragmentTitle = html_entity_decode($h3[0]->plaintext, ENT_COMPAT, 'ISO-8859-1');
    $fragmentURI = $uri . '/' . urlencode($fragmentTitle);
        
    // fragment
    $rdfData[$uri][$vocab['metalex'].'fragment'] = array(array(
        'type'  => 'uri',
        'value' => $fragmentURI
    ));
        
    // x rdf:type metalex:BibliographicExpression
    $rdfData[$fragmentURI] = array(
        RDF_TYPE => array(array(
            'type'  => 'uri',
            'value' => METALEX_BIBEXPR
        ))
    );
    // title, label
    $rdfData[$fragmentURI][$vocab['dct'].'title'] = array(array(
        'type'  => 'literal',
        'value' => $fragmentTitle
    ));
    $rdfData[$fragmentURI][$vocab['rdfs'].'label'] = array(array(
        'type'  => 'literal',
        'value' => $fragmentTitle
    ));
        
    // fragmentOf
    $rdfData[$fragmentURI][$vocab['metalex'].'fragmentOf'] = array(array(
        'type'  => 'uri',
        'value' => $uri
    ));
        
    // seeAlso
    $rdfData[$fragmentURI][$vocab['rdfs'].'seeAlso'] = array(array(
        'type'  => 'uri',
        'value' => $fragmentHTMLURL
    ));
        
    $clauses = $data->find("div[@class='jnhtml'] div[@class='jurAbsatz']");
    
    $j = 1;
    $clausesFullText = array();
    foreach ($clauses as $clause) {
        $clauseURI = $fragmentURI . '/' . $j++;
        $clauseText = html_entity_decode($clause->plaintext, ENT_COMPAT, 'ISO-8859-1');
        $clausesFullText[] = $clauseText;
        // fragment
        $rdfData[$fragmentURI][$vocab['metalex'].'fragment'] = array(array(
            'type'  => 'uri',
            'value' => $clauseURI
        ));
        
        // x rdf:type metalex:BibliographicExpression
        $rdfData[$clauseURI] = array(
            RDF_TYPE => array(array(
                'type'  => 'uri',
                'value' => METALEX_BIBEXPR
            ))
        );
        
        // title, label
        $clauseTitle = substr($clauseText, 0, 100) . '...';
        $rdfData[$clauseURI][$vocab['dct'].'title'] = array(array(
            'type'  => 'literal',
            'value' => $clauseTitle
        ));
        $rdfData[$clauseURI][$vocab['rdfs'].'label'] = array(array(
            'type'  => 'literal',
            'value' => $clauseTitle
        ));
    
        // fragmentOf
        $rdfData[$clauseURI][$vocab['metalex'].'fragmentOf'] = array(array(
            'type'  => 'uri',
            'value' => $fragmentURI
        ));
        
        // description
        $rdfData[$clauseURI][$vocab['dct'].'description'] = array(array(
            'type'  => 'literal',
            'value' => $clauseText
        ));
    }
        
    // description
    $rdfData[$fragmentURI][$vocab['dct'].'description'] = array(array(
        'type'  => 'literal',
        'value' => implode(PHP_EOL.PHP_EOL, $clausesFullText)
    ));
    
    return true;
}

function _scrapeIndexPage($url)
{
    $html = scraperWiki::scrape($url);
    $dom = new simple_html_dom();
    $dom->load($html);
    
    $result = array();
    
    foreach($dom->find("div[@id='paddingLR12'] p") as $data) {
        $as = $data->find("a");
        $record = array(
            'title' => $as[0]->plaintext, 
            'url' => URL_BASE . substr($as[0]->href, 1)
        );
        $result[] = $record;
    }
    
    $dom->__destruct();
    
    return $result;
}

function _exportRDF($spec, $append = false) 
{
    $lines = '';
    foreach ($spec as $s=>$pArray) {
        foreach ($pArray as $p=>$oArray) {
            foreach ($oArray as $oSpec) {
                $o = null;
                if ($oSpec['type'] === 'literal') {
                    $o = '"""' . str_replace('"', '\"', $oSpec['value']) . '"""';
                    
                    if (isset($oSpec['lang'])) {
                        $o .= '@' . $oSpec['lang'];
                    }
                    
                } else {
                    $o = '<' . $oSpec['value'] . '>';
                }
                
                $lines .= "<$s> <$p> $o ." . PHP_EOL;
            }
        }
    }
    
    if ($append) {
        file_put_contents('data.ttl', $lines, FILE_APPEND);
    } else {
        file_put_contents('data.ttl', $lines);
    }
}

function _getHTML($url)
{
    $cacheFilename = 'cache/' . md5($url);
    if (!file_exists($cacheFilename)) {
        $html = scraperWiki::scrape($url);
        file_put_contents($cacheFilename, $html);
    } else {
        $html = file_get_contents($cacheFilename);
    }
    
    return $html;
}