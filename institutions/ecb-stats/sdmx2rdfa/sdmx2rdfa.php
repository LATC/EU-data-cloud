<?php

$sdmx_source = $argv[1];

$reader = new XMLReader();

$reader->open($sdmx_source);

$series_id="unknown_series";
$series_title="Unknown Title";
$observations = array();
$counter = 0;

while ($reader->read()) {
	if ($reader->localName=="Group") {
		$node = $reader->expand();
		$series_title = $node->getAttribute('TITLE_COMPL');
	} elseif ($reader->localName=="Series") {
		$node = $reader->expand();
		$series_id = $node->getAttribute('DOM_SER_IDS');
	} elseif ($reader->localName=="Obs") {
		$node = $reader->expand();
		// <Obs TIME_PERIOD="1988" OBS_VALUE="2.75" OBS_STATUS="A" OBS_CONF="F" />
		$observation = array();
		$observation['id'] = $series_id . "_" . $counter;
		$observation['period'] = $node->getAttribute('TIME_PERIOD');
		$observation['value'] = $node->getAttribute('OBS_VALUE');
		$observation['status'] = $node->getAttribute('OBS_STATUS');
		$observation['conf'] = $node->getAttribute('OBS_CONF');
		$observations[] = $observation;
		$counter++;
	}

}

$reader->close();

$header = <<<HEADER
<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML+RDFa 1.0//EN" "http://www.w3.org/MarkUp/DTD/xhtml-rdfa-1.dtd">
<html
	xmlns="http://www.w3.org/1999/xhtml"
	xmlns:qb="http://purl.org/linked-data/cube#"
	xmlns:sdmx-measure="http://purl.org/linked-data/sdmx/2009/measure#"
	xmlns:sdmx-dim="http://purl.org/linked-data/sdmx/2009/dimension#"
	xmlns:sioc="http://rdfs.org/sioc/ns#"
	xmlns:foaf="http://xmlns.com/foaf/0.1/">
<head><title>ECB Data Series | $series_title</title></head>
<body typeof="sioc:Site" about="">
<h1 property="rdfs:label">$series_title</h1>
<table about="#$series_id" typeof="qb:Slice" rel="foaf:primaryTopicOf" href="" property="rdfs:label" content="$series_title">
	<tr>
		<th>Period</th>
		<th>Value</th>
		<th>Status</th>
		<th>Conf</th>
	</tr>
	<div about="#$series_id" rel="qb:observation">

HEADER;

echo $header;

foreach ($observations as $observation) {
	$id = $observation['id'];
	$period = $observation['period'];
	$value = $observation['value'];
	$status = $observation['status'];
	$conf = $observation['conf'];
	$table_row = <<<ROW
	<tr about="#$id" typeof="qb:Observation">
		<td rel="sdmx-dim:refPeriod"><a href="http://reference.data.gov.uk/id/year/$period" property="rdfs:label">$period</a></td>
		<td property="sdmx-measure:obsValue">$value</td>
		<td>$status</td>
		<td>$conf</td>
	</tr>

ROW;
	echo $table_row;
}


$footer = <<<FOOTER
	</div>
</table>
</body>
</html>

FOOTER;

echo $footer;


?>

