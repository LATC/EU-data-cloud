<?php
require 'scraperwiki/scraperwiki.php';
require 'scraperwiki/simple_html_dom.php';

$otherLanguages = array('bg', 'cs', 'da', 'de', 'et', 'el', 'es', 'fr', 'ga', 'it', 'lv', 
                        'lt', 'hu', 'mt', 'nl', 'pl', 'pt', 'ro', 'sk', 'sl', 'fi', 'sv');

$vocab = array(
    'rdf'      => 'http://www.w3.org/1999/02/22-rdf-syntax-ns#',
    'rdfs'     => 'http://www.w3.org/2000/01/rdf-schema#',
    'owl'      => 'http://www.w3.org/2002/07/owl#',
    'dct'      => 'http://purl.org/dc/terms/',
    'foaf'     => 'http://xmlns.com/foaf/0.1/',
    'frbr'     => 'http://purl.org/vocab/frbr/core#',
    'agencies' => 'http://agencies.publicdata.eu/ontology/'
);

define('RDF_TYPE', $vocab['rdf'] . 'type');
define('URI_BASE', 'http://agencies.publicdata.eu/r/');
define('EUROPA_URL_BASE', 'http://europa.eu');

$totalLinks = array();
$agenciesByTitle = array();
$agenciesByLink = array();

_handleGenericAgencyList('http://europa.eu/agencies/regulatory_agencies_bodies/policy_agencies/index_en.htm');
_handleGenericAgencyList('http://europa.eu/agencies/regulatory_agencies_bodies/security_agencies/index_en.htm');
_handleGenericAgencyList('http://europa.eu/agencies/regulatory_agencies_bodies/pol_agencies/index_en.htm');
_handleGenericAgencyList('http://europa.eu/agencies/executive_agencies/index_en.htm');
_handleGenericAgencyList('http://europa.eu/agencies/euratom_agencies/index_en.htm');
_handleGenericAgencyList('http://europa.eu/agencies/financial_supervisory_bodies/index_en.htm');
_handleRecruitmentPage();
_handleDocumentsPage();
_handlePublicContractsPage();
_handleCountryList();

$rdfData = array();
$countries = array();
$cities = array();
foreach ($agenciesByTitle as $agency) {
    $uri = URI_BASE . $agency['id'];
    
    $rdfData[$uri] = array(
        RDF_TYPE => array(array(
            'type'  => 'uri',
            'value' => $vocab['agencies'].'Agency'
        )),
        $vocab['dct'].'title' => array(array(
            'type'     => 'literal',
            'value'    => $agency['titleFull'],
            'lang' => 'en'
        )),
        $vocab['rdfs'].'label' => array(array(
            'type'     => 'literal',
            'value'    => $agency['title'],
            'lang' => 'en'
        ))
    );
    
    if (isset($agency['short'])) {
        $rdfData[$uri][$vocab['agencies'].'token'] = array(array(
           'type'  => 'literal',
           'value' => $agency['short']
        ));
    }
    
    foreach ($otherLanguages as $langCode) {
        if (isset($agency["titleFull_$langCode"])) {
            $rdfData[$uri][$vocab['dct'].'title'] = array(array(
                'type'  => 'literal',
                'value' => $agency["titleFull_$langCode"],
                'lang' => $langCode
            ));
        }
        if (isset($agency["title_$langCode"])) {
            $rdfData[$uri][$vocab['rdfs'].'label'] = array(array(
                'type'  => 'literal',
                'value' => $agency["title_$langCode"],
                'lang' => $langCode
            ));
        }
    }
    
    if (isset($agency['link'])) {
        if (is_array($agency['link'])) {
            var_dump($agency['link']);exit;
        }
        
        $rdfData[$uri][$vocab['foaf'].'homepage'] = array(array(
           'type'  => 'uri',
           'value' => $agency['link']
        ));
    }
    if (isset($agency['seeAlso'])) {
        $oArray = array();
        foreach ($agency['seeAlso'] as $link) {
            $oArray[] = array(
                'type'  => 'uri',
                'value' => $link
            );
        }
        
        $rdfData[$uri][$vocab['rdfs'].'seeAlso'] = $oArray;
    }
    if (isset($agency['country'])) {
        $countryURI = URI_BASE . 'country/' . urlencode($agency['country']);
        $countries[$countryURI] = array('title' => $agency['country']);
        
        $rdfData[$uri][$vocab['agencies'].'locatedInCountry'] = array(array(
           'type'  => 'uri',
           'value' => $countryURI
        ));
        
        foreach ($otherLanguages as $langCode) {
            if (isset($agency["country_$langCode"])) {
                $countries[$countryURI]["title_$langCode"] = $agency["country_$langCode"];
            }
        }
    }
    if (isset($agency['street'])) {
        $rdfData[$uri][$vocab['agencies'].'street'] = array(array(
           'type'  => 'literal',
           'value' => $agency['street']
        ));
    }
    if (isset($agency['zipCity'])) {
        $zipCity = str_replace(',', '', $agency['zipCity']);
        $zipCity = str_replace('– ', '', $zipCity);
        $parts = explode(' ', $zipCity);
        $zip = null;
        $city = null;
        if (count($parts) !== 2) {
            if (count($parts) === 3) {
                $zip = $parts[0] . ' ' . $parts[1];
                $city = $parts[2];
            } else {
                //var_dump($agency['link'], $parts);exit;
            }
        } else {
            $matches = array();
            preg_match('/^.*[0-9]+.*$/', $parts[0], $matches);
            if (count($matches) === 0) {
                // first is city
                $zip = $parts[1];
                $city = $parts[0];
            } else {
                $zip = $parts[0];
                $city = $parts[1];
            }
        }
        
        if (null !== $zip) {
            $rdfData[$uri][$vocab['agencies'].'zip'] = array(array(
                'type'  => 'literal',
                'value' => $zip
            ));
        }
        
        if (null !== $city) {
            $cityURI = URI_BASE . 'city/' . urlencode($city);
            $cities[$cityURI] = array(
                'title'   => $city,
                'country' => $countryURI = URI_BASE . 'country/' . urlencode($agency['country'])
                );
        
            $rdfData[$uri][$vocab['agencies'].'locatedInCity'] = array(array(
                'type'  => 'uri',
                'value' => $cityURI
            ));
        }
    }
    if (isset($agency['tel'])) {
        $rdfData[$uri][$vocab['agencies'].'phone'] = array(array(
           'type'  => 'uri',
           'value' => 'tel:' . str_replace(' ', '', $agency['tel'])
        ));
    }
    if (isset($agency['fax'])) {
        $rdfData[$uri][$vocab['agencies'].'fax'] = array(array(
           'type'  => 'uri',
           'value' => 'tel:' . str_replace(' ', '', $agency['fax'])
        ));
    }
    if (isset($agency['mailto'])) {
        $rdfData[$uri][$vocab['foaf'].'mbox'] = array(array(
           'type'  => 'uri',
           'value' => $agency['mailto']
        ));
    }
}
_exportRDF($rdfData, false);
$rdfData = array();
foreach ($countries as $uri=>$c) {
    $rdfData[$uri] = array(
        RDF_TYPE => array(array(
            'type'  => 'uri',
            'value' => $vocab['agencies'].'Country'
        )),
        $vocab['dct'].'title' => array(array(
            'type'  => 'literal',
            'value' => $c['title'],
            'lang'  => 'en'
        ))
    );
    foreach ($otherLanguages as $langCode) {
        if (isset($c["title_$langCode"])) {
            $rdfData[$uri][$vocab['dct'].'title'][] = array(
                'type'  => 'literal',
                'value' => $c["title_$langCode"],
                'lang'  => $langCode
            );
        }
    }
}
_exportRDF($rdfData, false, 'countries');

$rdfData = array();
foreach ($cities as $uri=>$c) {
    $rdfData[$uri] = array(
        RDF_TYPE => array(array(
            'type'  => 'uri',
            'value' => $vocab['agencies'].'City'
        )),
        $vocab['dct'].'title' => array(array(
            'type'  => 'literal',
            'value' => $c['title'],
            'lang'  => 'en'
        )),
        $vocab['agencies'].'locatedInCountry' => array(array(
            'type'  => 'uri',
            'value' => $c['country']
        ))
    );
}
_exportRDF($rdfData, false, 'cities');

echo 'DONE!' . PHP_EOL;exit;
var_dump($agenciesByTitle);exit;

function _handleRecruitmentPage()
{
    $url = 'http://europa.eu/agencies/recruitment/index_en.htm';
    $html = _getHTML($url);
    
    $dom = new simple_html_dom();
    $dom->load($html);
    
    $ulElements = $dom->find("div[@class='agencies_list'] ul");
    foreach ($ulElements as $ul) {
        $liElements = $ul->find("li");
        foreach ($liElements as $li) {
            _handleAgencyListItem($li);
        }
    }
    
    $dom->__destruct();
}

function _handleDocumentsPage()
{
    $url = 'http://europa.eu/agencies/document/index_en.htm';
    $html = _getHTML($url);
    
    $dom = new simple_html_dom();
    $dom->load($html);
    
    $strongElements = $dom->find("div[@class='agencies_list'] strong");
    foreach ($strongElements as $strong) {
        $title = trim($strong->plaintext);
        
        $ulElements = $strong->next_sibling();
        $aElements = $ulElements->find("a");
        $seeAlso = array();
        foreach ($aElements as $a) {
            $seeAlso[] = $a->href;
        }
        
        $result = _handleAgencyListItem(null, $title, null, $seeAlso);
    }
    
    $dom->__destruct();
}

function _handlePublicContractsPage()
{
    $url = 'http://europa.eu/agencies/public_contracts/index_en.htm';
    $html = _getHTML($url);
    
    $dom = new simple_html_dom();
    $dom->load($html);
    
    $h4Elements = $dom->find("div[@id='euCenter'] h4");
    foreach ($h4Elements as $h4) {
        $title = trim($h4->plaintext);
        
        $ulElement = $h4->next_sibling();
        $liElements = $ulElement->find("li");
        $seeAlso = array();
        foreach ($liElements as $li) {
            $a = $li->find("a");
            $link = $a[0]->href;
            
            if (strpos($link, 'http') === false) {
                var_dump($link);exit;
            }
            
            $seeAlso[] = $link;
         }
         
         $result = _handleAgencyListItem(null, $title, null, $seeAlso);
    }
    
    $dom->__destruct();
}

function _handleCountryList()
{
    global $agenciesByTitle;
    global $otherLanguages;
    
    $url = 'http://europa.eu/agencies/inyourcountry/index_en.htm';
    $html = _getHTML($url);
    
    $dom = new simple_html_dom();
    $dom->load($html);
    $h3Elements = $dom->find("div[@id='euCenter'] h3");
    foreach ($h3Elements as $h3) {
        $countryName = trim($h3->plaintext);
        $ulElement = $h3->next_sibling();
        $liElements = $ulElement->find("li");
        foreach ($liElements as $li) {
            $result = _handleAgencyListItem($li);
            $agenciesByTitle[$result['id']]['country'] = $countryName;
         }
    }
    
    $dom->__destruct();
    
    foreach ($otherLanguages as $langCode) {
        $url = "http://europa.eu/agencies/inyourcountry/index_$langCode.htm";
        $html = _getHTML($url);
        $dom = new simple_html_dom();
        $dom->load($html);
        $h3Elements = $dom->find("div[@id='euCenter'] h3");
        foreach ($h3Elements as $h3) {
            $countryName = trim($h3->plaintext);
            $ulElement = $h3->next_sibling();
            $liElements = $ulElement->find("li");
            foreach ($liElements as $li) {
                $result = _handleAgencyListItem($li, null, null, null, false);
                if (isset($agenciesByTitle[$result['id']])) {
                    // Only handle de items, where we already have a matching en item.
                    $agenciesByTitle[$result['id']]["title_$langCode"] = $result['title'];
                    $agenciesByTitle[$result['id']]["titleFull_$langCode"] = $result['titleFull'];
                    $agenciesByTitle[$result['id']]["country_$langCode"] = $countryName;
                }
            }
        }
        $dom->__destruct();
    }
}

function _handleGenericAgencyList($url, $class = 'Agency')
{
    global $otherLanguages;
    
    $html = _getHTML($url);
    
    $dom = new simple_html_dom();
    $dom->load($html);
    $ulElements = $dom->find("div[@id='euCenterIE6'] ul");
    
    $liElements = $ulElements[0]->find("li");
    foreach ($liElements as $li) {
        _handleAgencyListItem($li);
    }
    
    $dom->__destruct();
    
    foreach ($otherLanguages as $langCode) {
        $url = str_replace('_en.html', "_$langCode.html", $url);
        $html = _getHTML($url);
        $dom = new simple_html_dom();
        $dom->load($html);
        $ulElements = $dom->find("div[@id='euCenterIE6'] ul");
        $liElements = $ulElements[0]->find("li");
        foreach ($liElements as $li) {
            $result = _handleAgencyListItem($li, null, null, null, false);
            if (isset($agenciesByTitle[$result['id']])) {
                // Only handle de items, where we already have a matching en item.
                $agenciesByTitle[$result['id']]["title_$langCode"] = $result['title'];
                $agenciesByTitle[$result['id']]["titleFull_$langCode"] = $result['titleFull'];
            }
        }
    
        $dom->__destruct();
    }
}

function _handleAgencyListItem($li = null, $title = null, $link = null, $seeAlso = null, $add = true)
{
    global $totalLinks;
    global $agenciesByTitle;
    global $agenciesByLink;
    
    $titleSpec = null;
    if (null !== $title) {
        $titleSpec = _extractTitle($title);
    } else {
        $titleSpec = _extractTitle(trim($li->plaintext));
    }
        
    $titleFull = $titleSpec['titleFull']; 
    $title = $titleSpec['title']; 
    $id = $titleSpec['id']; 
    $short = isset($titleSpec['short']) ? $titleSpec['short'] : null;
    
    if ((null === $link) && (null !== $li)) {
        $a = $li->find("a");
        if (count($a) > 0) {
            $link = $a[0]->href;
        }
    }
        
    $result = array(
        'id'        => $id,
        'title'     => $title,
        'titleFull' => $titleFull
    );
    if (null !== $short) {
        $result['short'] = $short;
    }
    if (null !== $link) {
        if (strpos($link, 'http') === false) {
            $link = EUROPA_URL_BASE . $link;
            $result = _handleDetailPage($link, $result);
        }
        $result['link'] = $link;
    }
    if (null !== $seeAlso) {
        if (isset($result['seeAlso'])) {
            $result['seeAlso'] = array_merge($result['seeAlso'], $seeAlso);
        } else {
            $result['seeAlso'] = $seeAlso;
        }
    }
    
    if (!$add) {
        return $result;
    }
    
    if (isset($agenciesByTitle[$id])) {
        if ($agenciesByTitle[$id] != $result) {
            $newItem = $agenciesByTitle[$id];
            if ($agenciesByTitle[$id]['title'] != $result['title']) {
                $newItem['title'] = array($agenciesByTitle[$id]['title'], $result['title']);
                $newItem['titleFull'] = array($agenciesByTitle[$id]['titleFull'], $result['titleFull']);
            }
            if (isset($result['link'])) {
                if (isset($agenciesByTitle['id']['link'])) {
                    if ($agenciesByTitle['id']['link'] !== $result['link']) {
                        $newItem['link'] = array($agenciesByTitle['id']['link'], $result['link']);
                    }
                } else {
                    $newItem['link'] = $result['link'];
                }
            }
            if (isset($result['seeAlso'])) {
                if (isset($agenciesByTitle['id']['seeAlso'])) {
                    $newItem['link'] = array_merge($agenciesByTitle['id']['seeAlso'], $result['seeAlso']);
                } else {
                    $newItem['seeAlso'] = $result['seeAlso'];
                }
            }
            
            $agenciesByTitle[$id] = $newItem;
        }
    } else {
        $agenciesByTitle[$id] = $result;   
    }
    
    $agenciesByLink[$link] = $titleFull;
    $totalLinks[] = $link;
    
    return($result);
}

function _handleDetailPage($url, $result)
{
    $html = _getHTML($url);
    
    $dom = new simple_html_dom();
    $dom->load($html);
    
    $divBoxes = $dom->find("div[@class='featured_box margin_top_fb']");
    
    if (count($divBoxes) < 1) {
        return $result;
    }
    
    $style = $divBoxes[0]->style;
    $styleParts = explode("'", $style);
    $imageURL = EUROPA_URL_BASE . $styleParts[1];
    $result['logoURL'] = $imageURL;
    
    $addressText = $divBoxes[0]->xmltext;
    $addressTextParts = explode('</h3>', $addressText);
    
    if (count($addressTextParts) !== 2) {
        return $result;
    }
    
    $addressText = $addressTextParts[1];
    $addressText = str_replace('<br />', '<br>', $addressText);
    $addressTextParts = explode('<br>', $addressText);
    
    if (count($addressTextParts) < 3) {
        return $result;
    }
    
    $street = trim($addressTextParts[0]);
    $matches = array();
    $curPos = 1;
    preg_match('/^.*[0-9]+.*$/', $street, $matches);
    if (count($matches) === 0) {
        $street .= ' ' . trim($addressTextParts[$curPos++]);
    }
    $result['zipCity'] = trim($addressTextParts[$curPos++]);
    $result['country'] = trim($addressTextParts[$curPos++]);
    for ($i=$curPos; $i<count($addressTextParts); ++$i) {
        $val = strtolower(trim($addressTextParts[$i]));
        
        if (substr($val, 0, 4) === 'tel:') {
            $result['tel'] = trim(substr($val, 4));
        } else if (substr($val, 0, 4) === 'fax:') {
            $result['fax'] = trim(substr($val, 4));
        } else if (substr($val, 0, 2) === '<a') {
            $parts = explode('"', $val);
            $result['mailto'] = trim($parts[1]);
        }
    }
    
    $links = array();
    $aElements = $dom->find("div[@id='euCenter'] a");
    foreach ($aElements as $a) {
        if (strpos($a->href, 'http://') !== false) {
            $links[] = $a->href;
        }
    }
    if (count($links) > 0) {
        if (isset($result['seeAlso'])) {
            $result['seeAlso'] = array_merge($result['seeAlso'], $links);
        } else {
            $result['seeAlso'] = $links;
        }
    }
    
    $dom->__destruct();

    return $result;
}

function _extractTitle($title)
{
    $title = html_entity_decode($title, ENT_COMPAT, 'UTF-8');

    $pos = strrpos($title, '(');
    $short = null;
    if ($pos === false) {
        $name = $title;
        $id   = urlencode($title);
    } else {
        $name = trim(substr($title, 0, $pos));
        $id = trim(substr($title, $pos+1, -1));
        $short = $id;
    }
    
    $result = array(
        'titleFull' => $title,
        'title'     => $name,
        'id'        => urlencode($id)
    );
    
    if (null !== $short) {
        $result['short'] = $short;
    }
    
    return $result;
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

function _exportRDF($spec, $append = false, $file = null) 
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
                    } else if (isset($oSpec['datatype'])) {
                        $o .= '^^<' . $oSpec['datatype'] . '>';
                    }
                } else {
                    $o = '<' . $oSpec['value'] . '>';
                }
                
                $lines .= "<$s> <$p> $o ." . PHP_EOL;
            }
        }
    }
    
    $fileName = 'data.ttl';
    if (null !== $file) {
        $fileName = $file . '.ttl';
    }
    
    if ($append) {
        file_put_contents($fileName, $lines, FILE_APPEND);
    } else {
        file_put_contents($fileName, $lines);
    }
}
