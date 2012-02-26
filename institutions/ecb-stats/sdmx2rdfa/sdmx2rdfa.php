<?php
$series_id=str_replace('_','.',$_GET['serieskey']);
$seriesUri = 'http://ecb.publicdata.eu/series/'.$_GET['serieskey'];
$sdmx_source = 'http://sdw.ecb.europa.eu/quickviewexport.do?trans=&start=&end=&snapshot=&periodSortOrder=&SERIES_KEY='.$series_id.'&type=sdmx';

$reader = new XMLReader();

$reader->open($sdmx_source);

$series_title="Unknown Title";
$observations = array();
$counter = 0;

while ($reader->read()) {
	if ($reader->localName=="Group") {
		$node = $reader->expand();
		$series_title = $node->getAttribute('TITLE_COMPL');
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
	xmlns:foaf="http://xmlns.com/foaf/0.1/"
	xmlns:xsd="http://www.w3.org/2001/XMLSchema#">
<head>
	<title>ECB Data Series | $series_title</title>
	<style type="text/css" title="currentStyle" media="screen">
		@import "/sdmx2rdfa/css/latc_ecb_stats.css";
	</style>
</head>
<body typeof="sioc:Site" about="">
<h1>ECB Data Series</h1>
<div id="page">
	<h2 property="rdfs:label">$series_title</h2>
	<div id="content">
		<div id="left">
			<h3 class="box-header">Information</h3>
			<div id="inner-left">
				<p>This page makes statistical data from the <a href="http://sdw.ecb.europa.eu/">European Central Bank's Statistical Data Warehouse</a> available as linked data. See below for download links.</p>
			</div>
			<h3 class="box-header">Data Download</h3>
			<div id="inner-left">
				<div id="links">
					<ul>
						<li>Download as <a href="http://www.w3.org/2007/08/pyRdfa/extract?uri=referer">RDF/XML</a>.</li>
					</ul>
				</div>
			</div>
			<h3 class="box-header">Original Sources</h3>
			<div id="inner-left">
				<div id="links">
					<ul>
						<li>Source as <a href="http://sdw.ecb.europa.eu/quickview.do?SERIES_KEY=$series_id" target="_blank">web page</a>.</li>
						<li>Source as <a href="http://sdw.ecb.europa.eu/quickviewexport.do?SERIES_KEY=$series_id&type=sdmx">SDMX-XML</a>.</li>
						<li>Source as <a href="http://sdw.ecb.europa.eu/quickviewexport.do?SERIES_KEY=$series_id&type=csv">CSV</a>.</li>
					</ul>
				</div>
			</div>
		</div>
		<div id="right">
			<div id="inner-right">
				<h3 class="box-header">Data</h3>
				<div id="table-box">
					<table class="data" about="{$seriesUri}" typeof="qb:Slice" rel="foaf:primaryTopicOf" href="" property="rdfs:label" content="{$series_title}">
						<tr>
							<th class="data">Period</th>
							<th class="data">Value</th>
							<th class="data">Status</th>
							<th class="data">Conf</th>
						</tr>
						<div about="{$seriesUri}" rel="qb:observation">

HEADER;

echo $header;

$i=0;
foreach ($observations as $observation) {
	$id = $observation['id'];
	$period = $observation['period'];
	$value = $observation['value'];
	$status = $observation['status'];
	$conf = $observation['conf'];
	$color_class = "d".($i & 1);
	$table_row = <<<ROW
						<tr about="#{$id}" typeof="qb:Observation" class="{$color_class}">
							<td rel="sdmx-dim:refPeriod"><a href="http://reference.data.gov.uk/id/year/{$period}" property="rdfs:label">{$period}</a></td>
							<td property="sdmx-measure:obsValue" datatype="xsd:decimal">$value</td>
							<td>$status</td>
							<td>$conf</td>
						</tr>

ROW;
	echo $table_row;
	$i++;
}


$footer = <<<FOOTER
						</div>
						</table>
					</div>
				</div>
		</div>
	</div>
</div>
<div id="footer">
ECB SDMX->RDF data conversion created for the <a href="http://latc-project.eu/">LATC Project</a>.
</div>
</body>
</html>

FOOTER;

echo $footer;


?>
