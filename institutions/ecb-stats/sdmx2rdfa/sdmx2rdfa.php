<?php

$header = <<<HEADER
<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML+RDFa 1.0//EN" "http://www.w3.org/MarkUp/DTD/xhtml-rdfa-1.dtd">
<html
	xmlns="http://www.w3.org/1999/xhtml"
	xmlns:qb="http://purl.org/linked-data/cube#"
	xmlns:sdmx-measure="http://purl.org/linked-data/sdmx/2009/measure#"
	xmlns:sdmx-dim="http://purl.org/linked-data/sdmx/2009/dimension#"
	xmlns:sioc="http://rdfs.org/sioc/ns#">
<head><title>ECB Data Series</title></head>
<body typeof="sioc:Site" about="">
<table>
	<tr>
		<th>Period</th>
		<th>Value</th>
		<th>Status</th>
		<th>Conf</th>
	</tr>

HEADER;

echo $header;

$sdmx_source = $argv[1];

$reader = new XMLReader();

$reader->open($sdmx_source);

$series_id="unknown_series";
$counter = 0;
while ($reader->read()) {
	if ($reader->localName=="Series") {
		$node = $reader->expand();
		$series_id = $node->getAttribute('DOM_SER_IDS');
	} elseif ($reader->localName=="Obs") {
		$node = $reader->expand();
		// <Obs TIME_PERIOD="1988" OBS_VALUE="2.75" OBS_STATUS="A" OBS_CONF="F" />
		$period = $node->getAttribute('TIME_PERIOD');
		$time_uri = "http://reference.data.gov.uk/id/year/" . $period;
		$value = $node->getAttribute('OBS_VALUE');
		$status = $node->getAttribute('OBS_STATUS');
		$conf = $node->getAttribute('OBS_CONF');
		$row_id = $series_id . "_" . $counter;
		$table_row = <<<ROW
	<tr about="#$row_id" typeof="qb:Observation">
		<td rel="sdmx-dim:refPeriod"><a href="$time_uri" property="rdfs:label">$period</a></td>
		<td property="sdmx-measure:obsValue">$value</td>
		<td>$status</td>
		<td>$conf</td>
	</tr>

ROW;
		echo $table_row;
		$counter++;
	}

}

$reader->close();

$footer = <<<FOOTER
</table>
</body>
</html>

FOOTER;

echo $footer;


?>

