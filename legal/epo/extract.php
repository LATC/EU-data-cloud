<?php

$availableCountries = array(
    'AT' => 'Austria',
    'BE' => 'Belgium', 
    'CH' => 'Switzerland',
    'DE' => 'Germany',
    'DK' => 'Denmark',
    'ES' => 'Spain',
    'FR' => 'France',
    'GB' => 'United Kingdom',
    'GR' => 'Greece',
    'IT' => 'Italy',
    'LI' => 'Liechtenstein', 
    'LU' => 'Luxembourg', 
    'NL' => 'Netherlands', 
    'SE' => 'Sweden', 
    'MC' => 'Monaco', 
    'PT' => 'Portugal', 
    'IE' => 'Ireland', 
    'SI' => 'Slovenia', 
    'LT' => 'Lithuania',
    'LV' => 'Latvia',
    'FI' => 'Finland',
    'RO' => 'Romania',
    'MK' => 'Macedonia',
    'CY' => 'Cyprus',
    'AL' => 'Albania',
    'TR' => 'Turkey',
    'BG' => 'Bulgaria',
    'CZ' => 'Czech Republic',
    'EE' => 'Estonia',
    'HU' => 'Hungary',
    'PL' => 'Poland',
    'SK' => 'Slovakia',
    'BA' => 'Bosnia and Herzegovina',
    'HR' => 'Croatia',
    'IS' => 'Iceland',
    'YU' => 'Serbia and Montenegro',
    'MT' => 'Malta',
    'NO' => 'Norway',
    'RS' => 'Republic of Serbia'
);

$applicationPlaces = array(
    '00' => 'Online filing',
    '10' => 'Munich, EPO',
    '20' => 'The Hague, EPO (branch)',
    '25' => 'Berlin, EPO (sub-office)',
    '30' => 'Newport and London, The (UK) Patent Office',
    '40' => 'Paris, Institut national de la propriété industrielle (INPI)',
    '41' => 'Grenoble, INPI (regional office)',
    '42' => 'Lyon, INPI (regional office)',
    '43' => 'Marseille, INPI (regional office)',
    '44' => 'Strasbourg, INPI (regional office)',
    '45' => 'Bordeaux, INPI (regional office)',
    '46' => 'Rennes, INPI (regional office)',
    '47' => 'Nancy, INPI (regional office)',
    '48' => 'Nice, INPI (regional office)',
    '49' => 'Lille, INPI (regional office)',
    '50' => 'Madrid, Oficina Espanola de Patentes y Marcas',
    '51' => 'Barcelona (Autonomous Community Catalonia)',
    '52' => 'Seville (Autonomous Community Andalusia)',
    '53' => 'Tenerife (Autonomous Community Canary Islands)',
    '54' => 'Santiago de Compostela (Autonomous Communit Galicia)',
    '55' => 'Pamplona (Autonomous Community Navarra)',
    '56' => 'Valencia (Autonomous Community Valencia)',
    '57' => 'Vitoria (Autonomous Community Basque Country)',
    '60' => 'Athens, Organismos Biomichanikis Idioktisias (OBI)',
    '61' => 'Taastrup, Patentdirektoratet',
    '63' => 'Luxembourg, Service de la propriété intellectuelle (Ministère l\'économie et des classes moyennes)',
    '64' => 'Monaco, Direction du Commerce, de l\'Industrie et de la Prop Industrielle',
    '65' => 'Dublin, The (Irish) Patents Office',
    '67' => 'Lisbon, Instituto Nacional da Propriedade Industrial (INPI)',
    '71' => 'Munich, Deutsches Patentamt (DPA)',
    '72' => 'The Hague, Octrooiraad',
    '73' => 'Berlin, DPA (Berlin Annex)',
    '81' => 'Berne, Bundesamt für geistiges Eigentum (BAGE)',
    '83' => 'Rome, Ufficio Centrale Brevetti',
    '85' => 'Stockholm, Kungl. Patent- och registreringsverket',
    '87' => 'Brussels, Service de la propriété industrielle et commercial (Ministère des affaires économiques)',
    '89' => 'Vienna, Österreichisches Patentamt'
); 

$changesArray = array();

$persons = array();

define('INSTANCE_BASE', 'http://epo.publicdata.eu/ebd/id/');
define('VOCAB_BASE', 'http://epo.publicdata.eu/ebd/ontology/');
define('RDF_TYPE', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type');
define('RDFS_LABEL', 'http://www.w3.org/2000/01/rdf-schema#label');

$xmlDeclString = '<?xml version="1.0" encoding="UTF-8"?>';

$files = array(
   's361151a.txt', 's361151b.txt',
   's361152a.txt', 's361152b.txt',
   's361201a.txt', 's361201b.txt',
   's361202a.txt', 's361202b.txt',
   's361203a.txt', 's361203b.txt',
   's361204a.txt', 's361204b.txt',
   's361205a.txt', 's361205b.txt'
);

$append = false;
foreach ($files as $file) {
    echo "Handling file: $file" . PHP_EOL;
    $xmlDocString = file_get_contents("tmp/$file");
    
    $i = 0;
    while (true) {
        $pos = strpos($xmlDocString, $xmlDeclString, 1);
        if ($pos === false) {
            break;
        }
        $xmlString = substr($xmlDocString, 0, $pos);
        $xmlDocString = substr($xmlDocString, $pos+strlen($xmlDeclString));
    
        $xml = simplexml_load_string($xmlString);
        $result = _parseEPBulletin($xml);
        echo "\rWriting triples for document number: " . $i++;
        _exportRDF($result, $append);
        $append = true;
    }
    echo PHP_EOL;
}

echo "Writing schema triples now..." . PHP_EOL;
_writeSchemaTriples();

##### Functions

function _parseEPBulletin($xml)
{
    $xml = (array)$xml;
    
    $attributes = $xml['@attributes'];
    $sdobi = (array)$xml['SDOBI'];
    
    $id        = $attributes['id'];
    $file      = $attributes['file'];
    $lang      = $attributes['lang'];
    $country   = $attributes['country'];
    $docNumber = $attributes['doc-number'];
    $kind      = $attributes['kind'];
    $correctionCode = isset($attributes['correction-code']) ? $attributes['correction-code'] : null;
    $datePubl  = $attributes['date-publ'];
    $seqNumber = isset($attributes['sequence']) ? $attributes['sequence'] : null;
    $status    = $attributes['status'];
       
    $uri = INSTANCE_BASE . $id;
    
    $result = array(
        VOCAB_BASE . 'bulletinIdentifier' => array(array('type' => 'literal', 'value' => $id)),
        VOCAB_BASE . 'bulletinFile' => array(array('type' => 'literal', 'value' => $file)),
        VOCAB_BASE . 'bulletinLang' => array(array('type' => 'literal', 'value' => $lang)),
        VOCAB_BASE . 'bulletinFile' => array(array('type' => 'literal', 'value' => $file)),
        VOCAB_BASE . 'bulletinCountry' => array(array('type' => 'literal', 'value' => $country)),
        VOCAB_BASE . 'publishedDate' => array(array('type' => 'literal', 'value' => $datePubl))
    );
    
    if (null !== $correctionCode) {
        $result[VOCAB_BASE . 'wipoCorrectionCode'] = array(array(
            'type'  => 'literal',
            'value' => $correctionCode
        ));
    }
    if (null !== $seqNumber) {
        $result[VOCAB_BASE . 'sequenceNumber'] = array(array(
            'type'  => 'literal',
            'value' => $seqNumber
        ));
    }
    
    if ($country === 'EP') {
        $result[VOCAB_BASE . 'epoPublicationNumber'] = array(array(
            'type'  => 'literal',
            'value' => $docNumber
        ));
    } else if ($country === 'WO') {
        $result[VOCAB_BASE . 'wipoPublicationNumber'] = array(array(
            'type'  => 'literal',
            'value' => $docNumber
        ));
    }
    
    $result[VOCAB_BASE . 'hasApplicationStatus'] = _parseApplicationStatus($status);
    $result[RDF_TYPE] = _parseApplicationKind($kind);
    
    $sdobiResult = _handleSDOBI($sdobi, $id);
    
    $result = array_merge($result, $sdobiResult);
    
    return array($uri => $result);
}

function _writeSchemaTriples()
{
    global $availableCountries;
    global $applicationPlaces;
    
    $lines = '';
    
    foreach ($availableCountries as $code=>$label) {
        $lines .= '<' . VOCAB_BASE . "country/$code> a <" . VOCAB_BASE . 'Country> .' . PHP_EOL;
        $lines .= '<' . VOCAB_BASE . "country/$code> <" . RDFS_LABEL . "> \"$label\"@en ." . PHP_EOL;
    }
    
    foreach ($applicationPlaces as $code=>$label) {
        $lines .= '<' . VOCAB_BASE . "applicationPlace/$code> a <" . VOCAB_BASE . 'ApplicationPlace> .' . PHP_EOL;
        $lines .= '<' . VOCAB_BASE . "applicationPlace/$code> <" . RDFS_LABEL . "> \"$label\"@en ." . PHP_EOL;
    }
    
    file_put_contents('schema.nt', $lines);
}

function _handleSDOBI($sdobi, $idBase)
{
    $sdobi = (array)$sdobi;
    
    $b000 = (array)$sdobi['B000'];
    $eptags = (array)$b000['eptags'];
    $b001EP = (array)$eptags['B001EP'];
    
    $countryMask = $b001EP[0];
    $countries = _parseCountryMask($countryMask);
    
    $changes = null;
    if (isset($eptags['B002EP'])) {
        $b002EP = (array)$eptags['B002EP'];
        $changes = _parseChangeInfo($b002EP, $idBase);
    }
    
    
    
    $b200 = (array)$sdobi['B200'];
    $b210 = (array)$b200['B210'];
    
    $placeNumberString = $b210[0];
    $placeNumber = $placeNumberString[2] . $placeNumberString[3];
    $placeURI = VOCAB_BASE . 'applicationPlace/' . $placeNumber;
        
        
    $b500 = (array)$sdobi['B500'];
    $titles = null;
    if (isset($b500['B540'])) {
        $titles = array();
        $b540 = (array)$b500['B540'];
        $b541 = (array)$b540['B541']; // lang
        $b542 = (array)$b540['B542']; // title
        
        for ($i=0; $i<count($b542); ++$i) {
            $titles[] = array(
                'type'  => 'literal',
                'value' => $b542[$i],
                'lang'  => $b541[$i]
            );
        }
    }
    
    $b700 = (array)$sdobi['B700'];
    $b720 = (array)$b700['B720'];
    $b721 = (array)$b720['B721'];
    
    $inventors = _handleB721Inventors($b721);
        
    $result = array();
    $result[VOCAB_BASE.'hasInvolvedCountry'] = $countries;
    
    if (null !== $changes) {
        $result[VOCAB_BASE.'hasPreviousChange'] = $changes;
    }
    
    $result[VOCAB_BASE.'placeOfApplication'] = array(array(
        'type'  => 'uri',
        'value' => $placeURI
    ));
    
    if (null !== $titles) {
        $result[RDFS_LABEL] = $titles;
    }

    $result[VOCAB_BASE.'hasInventor'] = $inventors;
    
    return $result;
}

function _handleB721Inventors($b721)
{
    global $persons;
    
    $result = array();
    
    $b721 = (array)$b721;
    foreach ($b721 as $b721Spec) {
        $b721Array = (array)$b721Spec;
        
        if (!isset($b721Array['snm'])) {
            continue;
        }
        $surname = $b721Array['snm'];
        
        $uri = INSTANCE_BASE . 'person/' . md5($surname);
        $result[] = array(
            'type'  => 'uri',
            'value' => $uri
        );
        
        $addressArray = (array)$b721Array['adr'];    
        
        $street = null;
        if (isset($addressArray['str'])) {
            $street = $addressArray['str'];
        }
        $city = null;
        if (isset($addressArray['city'])) {
            $city = $addressArray['city'];
        }
        $country = null;
        if (isset($addressArray['ctry'])) {
            $country = $addressArray['ctry'];
        }
        
        // TODO
        $pSpec = array();
        if (null !== $country) {
            $pSpec['country'] = $country;
        }
        if (null !== $city) {
            $pSpec['city'] = $city;
        }
        if (null !== $street) {
            $pSpec['street'] = $street;
        }
        
        $persons[$uri] = $pSpec;
    }
    
    return $result;
}

function _parseChangeInfo($changeInfoSpec, $idBase)
{
    $changeInfoSpec = (array)$changeInfoSpec;
    if (count($changeInfoSpec) === 0) {
        return null;
    }
    
    $chgInfo = (array)$changeInfoSpec['ep-chg-info'];
    $chg = (array)$chgInfo['ep-chg'];
    
    if (!isset($chg['@attributes'])) {
        return null;
    }
    $attributes = $chg['@attributes'];
    
    $idRef  = $attributes['idref'];
    $bTag   = $attributes['btag'];
    $date   = $attributes['date'];
    $status = $attributes['status'];
    
    global $changesArray;
    $uri = INSTANCE_BASE . "$idBase$idRef";
    
    // TODO
    $changesArray[$uri] = array(
        
    );
    
    $result = array(
        'type'  => 'uri',
        'value' => $uri
    );
    
    return $result;
}

function _parseCountryMask($mask)
{
    $countryCodes = array();
    
    for ($i=0; $i<100; $i+=2) {
        $substr = $mask[$i] . $mask[$i+1];
        if ($substr !== '..') {
            $countryCodes[] = $substr;
        }
    }
    
    $countries = array();
    foreach ($countryCodes as $code) {
        $countries[] = array(
            'type'  => 'uri',
            'value' => VOCAB_BASE . 'country/' . $code
        );
    }
    
    return $countries;
}

function _parseApplicationStatus($status) 
{
    if ($status === 'n') {
        return array(array(
           'type'  => 'uri',
           'value' => VOCAB_BASE . 'ApplicationStatusNew'  
        ));
    } else if ($status === 'r') {
        return array(array(
           'type'  => 'uri',
           'value' => VOCAB_BASE . 'ApplicationStatusReplace'  
        ));
    } else if ($status === 'd') {
        return array(array(
           'type'  => 'uri',
           'value' => VOCAB_BASE . 'ApplicationStatusDelete'  
        ));
    } else if ($status === 'c') {
        return array(array(
           'type'  => 'uri',
           'value' => VOCAB_BASE . 'ApplicationStatusCorrection'  
        ));
    }
    
    return null;
}

function _parseApplicationKind($kind)
{
    return array(array(
       'type'  => 'uri',
       'value' => VOCAB_BASE . 'EPBulletin' . $kind  
    ));
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