<?php
// Config
$startYear = 2003;
$endYear = 2009;
// Config End

// TODO add comment for measure properties

define ('URI_BASE', 'http://unodc.publicdata.eu/r/');

$vocab = array(
    'rdf'   => 'http://www.w3.org/1999/02/22-rdf-syntax-ns#',
    'rdfs'  => 'http://www.w3.org/2000/01/rdf-schema#',
    'owl'   => 'http://www.w3.org/2002/07/owl#',
    'dct'   => 'http://purl.org/dc/terms/',
    'foaf'  => 'http://xmlns.com/foaf/0.1/',
    'frbr'  => 'http://purl.org/vocab/frbr/core#',
    'unodc' => 'http://unodc.publicdata.eu/ontology/',
    'qb'    => 'http://purl.org/linked-data/cube#'
);

define('RDF_TYPE', $vocab['rdf'] . 'type');

require_once 'lib/excel_reader2.php';

$fileName = 'data/CTS12_Formal_contact.xls';
$datasetName = 'CTS12_Formal_contact';
$data = new Spreadsheet_Excel_Reader($fileName);

$regions = array();
$subRegions = array();
$countries = array();
$propURIs = array();

$dirs = scandir('data');
$append = false;
foreach ($dirs as $file) {
    if ($file[0] === '.') {
        continue;
    }
    if (strpos($file, '.xls') === false) {
        continue;
    }
    
    $fileName = 'data/' . $file;
    $result = _handleDataset($fileName);
    _exportRDF($result, $append);
    $append = true;
}



// other instance data
$rdfData = array();
foreach ($regions as $region) {
    $regionURI = URI_BASE . 'region/' . urlencode($region);
    $rdfData[$regionURI] = array(
        RDF_TYPE => array(array(
            'type'  => 'uri',
            'value' => $vocab['unodc'].'Region'
        )),
        $vocab['rdfs'].'label' => array(array(
            'type'  => 'literal',
            'value' => $region
        ))
    );
}
foreach ($subRegions as $subRegion=>$region) {
    $subRegionURI = URI_BASE . 'subregion/' . urlencode($subRegion);
    $regionURI = URI_BASE . 'region/' . urlencode($region);
    $rdfData[$subRegionURI] = array(
        RDF_TYPE => array(array(
            'type'  => 'uri',
            'value' => $vocab['unodc'].'Subregion'
        )),
        $vocab['rdfs'].'label' => array(array(
            'type'  => 'literal',
            'value' => $subRegion
        )),
        $vocab['unodc'].'locatedInRegion' => array(array(
            'type'  => 'uri',
            'value' => $regionURI
        ))
    );
}
foreach ($countries as $country=>$subRegion) {
    $countryURI = URI_BASE . 'country/' . urlencode($country);
    $subRegionURI = URI_BASE . 'subregion/' . urlencode($subRegion);
    $rdfData[$countryURI] = array(
        RDF_TYPE => array(array(
            'type'  => 'uri',
            'value' => $vocab['unodc'].'Country'
        )),
        $vocab['rdfs'].'label' => array(array(
            'type'  => 'literal',
            'value' => $country
        )),
        $vocab['unodc'].'locatedInSubRegion' => array(array(
            'type'  => 'uri',
            'value' => $subRegionURI
        ))
    );
}
for ($i=$startYear; $i<=$endYear; ++$i) {
    $yearURI = URI_BASE . 'year/' . $i;
    $rdfData[$yearURI] = array(
        RDF_TYPE => array(array(
            'type'  => 'uri',
            'value' => $vocab['unodc'].'Year'
        )),
        $vocab['rdfs'].'label' => array(array(
            'type'  => 'literal',
            'value' => $i
        ))
    );
}
_exportRDF($rdfData, true);

// schema 
$rdfData = array();
foreach ($propURIs as $uri=>$label) {
    $rdfData[$uri] = array(
        RDF_TYPE => array(array(
            'type'  => 'uri',
            'value' => $vocab['qb'].'MeasureProperty'
        )),
        $vocab['rdfs'].'label' => array(array(
            'type'  => 'literal',
            'value' => $label
        ))
    );
}
_exportRDF($rdfData, false, 'schema');


// Functions
function _handleDataset($fileName)
{
    global $startYear;
    global $endYear;
    global $vocab;
    global $regions;
    global $subRegions;
    global $countries;
    global $propURIs;
    
    $data = new Spreadsheet_Excel_Reader($fileName);
    $datasetName = str_replace('data/', '', $fileName);
    $datasetName = str_replace('.xls', '', $fileName);
    
    $datasetComment = null;
    $datasetURI = URI_BASE . 'dataset/' . urlencode($datasetName);
    $slices = array();

    $rdfData = array();

    $sheetCount = count($data->boundsheets);
    for ($i=0; $i<$sheetCount; ++$i) {
        $rowCount = $data->rowcount($i);
        $colCount = $data->colcount($i);
    
        $sheetTitle = $data->boundsheets[$i]['name'];
    
        $currentRow = 0;
        $currentRegion = null;
        $currentSubRegion = null;
    
        $sliceTitle = null;
    
        // Search for slice title
        for ($row=0; $row<$rowCount; ++$row) {
            for ($col=0; $col<$colCount; ++$col) {
                $cellValue = $data->val($row, $col, $i);
                if ($cellValue !== '') {
                    $sliceTitle = trim($cellValue);
                    $currentRow = $row+1;
                    break 2;
                }
            }
        }
    
        // Search for dataset definition
        for ($row=$currentRow; $row<$rowCount; ++$row) {
            for ($col=0; $col<$colCount; ++$col) {
                $cellValue = $data->val($row, $col, $i);
                if ($cellValue !== '') {
                    if (strpos(strtolower($cellValue), 'definition:') !== false) {
                        $datasetComment = trim($cellValue);
                        $currentRow = $row+1;
                        break 2;
                    }
                }
            }
        }
    
        // Search for start of data
        for ($row=$currentRow; $row<$rowCount; ++$row) {
            $cellValue = $data->val($row, 1, $i);
            if ($cellValue !== '') {
                $currentRow = $row+1;
                break;
            }
        }
    
        $sheetInstances = array();
        for ($row=$currentRow; $row<$rowCount; ++$row) {
            if ($data->val($row, 3, $i) === '') {
                // We break, if col 3 is empty (country), since all data rows have a country!
                break;
            }
        
            $region = $data->val($row, 1, $i);
            if ($region === '') {
                $region = $currentRegion;
            } else {
                // new region... store it
                $regions[$region] = $region;
            }
        
            $subRegion = $data->val($row, 2, $i);
            if ($subRegion === '') {
                $subRegion = $currentSubRegion;
            } else {
                // new subregion... store it
                $subRegions[$subRegion] = $currentRegion;
            }
        
            $country = $data->val($row, 3, $i);
            $countries[$country] = $subRegion;
        
            $countValues = array();
            // Now the values for count
            $currentCol = 4;
            for ($j=$startYear; $j<=$endYear; ++$j) {
                $val = $data->val($row, $currentCol++, $i);
                if (($val != '-1') && ($val != '')) {
                    $countValues[$j] = floatval(str_replace(',', '.', $val));
                }
            }
        
            $rateValues = array();
            $currentCol++;
            for ($j=$startYear; $j<=$endYear; ++$j) {
                $val = $data->val($row, $currentCol++, $i);
                if (($val != '-1') && ($val != '')) {
                    $rateValues[$j] = floatval(str_replace(',', '.', $val));
                }
            }
        
            $regionURI = URI_BASE . 'region/' . urlencode($region);
            $subRegionURI = URI_BASE . 'subregion/' . urlencode($subRegion);
            $countryURI = URI_BASE . 'country/' . urlencode($country);
            $countPropURI = $vocab['unodc'] . strtolower($datasetName) . urlencode(ucfirst(strtolower($sheetTitle))) . 'Count';
            $propURIs[$countPropURI] = $datasetName . ' ' . $sheetTitle . ' count';
            foreach ($countValues as $year=>$val) {
                $yearURI = URI_BASE . 'year/' . $year;
            
                $instanceURI = URI_BASE . 'observation/' . strtolower($datasetName) . urlencode(ucfirst(strtolower($sheetTitle))) . 'Count' . urlencode($country) . $year;
                $sheetInstances[] = $instanceURI;
                $label = $datasetName . ' ' . $sheetTitle . ' count of ' . $country . ' in ' . $year;
            
                $rdfData[$instanceURI] = array(
                    RDF_TYPE => array(array(
                        'type'  => 'uri',
                        'value' => $vocab['qb'].'Observation'
                    )),
                    $vocab['rdfs'].'label' => array(array(
                        'value' => $label,
                        'type'  => 'literal'
                    )),
                    $countPropURI => array(array(
                        'value'    => $val,
                        'type'     => 'literal',
                        'datatype' => 'http://www.w3.org/2001/XMLSchema#float'
                    )),
                    $vocab['unodc'].'country' => array(array(
                        'type'  => 'uri',
                        'value' => $countryURI
                    )),
                    $vocab['unodc'].'subregion' => array(array(
                        'type'  => 'uri',
                        'value' => $subRegionURI
                    )),
                    $vocab['unodc'].'region' => array(array(
                        'type'  => 'uri',
                        'value' => $regionURI
                    )),
                    $vocab['unodc'].'year' => array(array(
                        'type'  => 'uri',
                        'value' => $yearURI
                    )),
                    $vocab['qb'].'dataSet' => array(array(
                        'type'  => 'uri',
                        'value' => $datasetURI
                    ))
                );
            }
            
            $ratePropURI = $vocab['unodc'] . strtolower($datasetName) . urlencode(ucfirst(strtolower($sheetTitle))) . 'Rate';
            $propURIs[$ratePropURI] = $datasetName . ' ' . $sheetTitle . ' rate';
            foreach ($rateValues as $year=>$val) {
                $yearURI = URI_BASE . 'year/' . $year;
            
                $instanceURI = URI_BASE . 'observation/' . strtolower($datasetName) . urlencode(ucfirst(strtolower($sheetTitle))) . 'Rate' . urlencode($country) . $year;
                $sheetInstances[] = $instanceURI;
                $label = $datasetName . ' ' . $sheetTitle . ' rate of ' . $country . ' in ' . $year;
            
                $rdfData[$instanceURI] = array(
                    RDF_TYPE => array(array(
                        'type'  => 'uri',
                        'value' => $vocab['qb'].'Observation'
                    )),
                    $vocab['rdfs'].'label' => array(array(
                        'value' => $label,
                        'type'  => 'literal'
                    )),
                    $countPropURI => array(array(
                        'value'    => $val,
                        'type'     => 'literal',
                        'datatype' => 'http://www.w3.org/2001/XMLSchema#float'
                    )),
                    $vocab['unodc'].'country' => array(array(
                        'type'  => 'uri',
                        'value' => $countryURI
                    )),
                    $vocab['unodc'].'subregion' => array(array(
                        'type'  => 'uri',
                        'value' => $subRegionURI
                    )),
                    $vocab['unodc'].'region' => array(array(
                        'type'  => 'uri',
                        'value' => $regionURI
                    )),
                    $vocab['unodc'].'year' => array(array(
                        'type'  => 'uri',
                        'value' => $yearURI
                    )),
                    $vocab['qb'].'dataSet' => array(array(
                        'type'  => 'uri',
                        'value' => $datasetURI
                    ))
                );
            }
        }
    
        // sheet metadata
        $sheetURI = URI_BASE . 'sheet/'. urlencode(strtolower($datasetName)) . urlencode(ucfirst(strtolower($sheetTitle)));
        $slices[] = $sheetURI;
        $rdfData[$sheetURI] = array(
            RDF_TYPE => array(array(
                'type'  => 'uri',
                'value' => $vocab['qb'].'Slice'
            )),
            $vocab['rdfs'].'label' => array(array(
                'type'  => 'literal',
                'value' => $sheetTitle
            ))
        );
        $oArray = array();
        foreach ($sheetInstances as $inst) {
            $oArray[] = array(
                'value' => $inst,
                'type'  => 'uri'
            );
        }
        $rdfData[$sheetURI][$vocab['qb'].'observation'] = $oArray;
    }

    // write dataset metadata
    $rdfData[$datasetURI] = array(
        RDF_TYPE => array(array(
            'type'  => 'uri',
            'value' => $vocab['qb'].'DataSet'
        )),
        $vocab['rdfs'].'label' => array(array(
            'type'  => 'literal',
            'value' => $datasetName
        )),
        $vocab['rdfs'].'comment' => array(array(
            'type'  => 'literal',
            'value' => $datasetComment
        ))
    );
    $oArray = array();
    foreach ($slices as $slice) {
        $oArray[] = array(
            'value' => $slice,
            'type'  => 'uri'
        );
    }
    $rdfData[$sheetURI][$vocab['qb'].'slice'] = $oArray;

    return $rdfData;
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
