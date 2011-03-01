<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<title>Linked Data Publication & Consumption Tool Library</title>
<meta name="keywords" content="" />
<meta name="description" content="" />
<link href="default.css" rel="stylesheet" type="text/css" />
<link href="tool.css" rel="stylesheet" type="text/css" />
</head>
<body>

<?php

require_once ('toollibrary.php');
$tool = $_GET["name"];

?>

<!-- start header -->
<div id="header">
	<div id="logo">
		<h1><a href="#">Data Publication & Consumption Tools Library</a></h1>
	</div>
	<div id="menu">
		<ul>
			<li><a href="index.php">Tool Categories</a></li>
			<li class="active"><a href="example.php">Example</a></li>
			<li><a href="contact.html">Contact </a></li>
			<li><a href="http://latc-project.eu" title="LATC" style="background: none; align:right; margin-left: 169px; margin-top: 10px;padding: 7px;"><img src="images/logo-latc.png"/></a></li>
		</ul>
	</div>
</div>
<!-- end header -->
<!-- start page -->
<div id="page">
	<!-- start content -->
	<div id="content">

		
		<div class="post">
			<h2 class="title">Example</h2>
			<div class="entry">
                            <p>The following example demonstrates the publication and consumption process for the <a href="http://cordis.europa.eu/">Community Research and Development Information Service</a> (CORDIS).
                            <p>The example makes use of the following tools:
                            <ul>
                              <li><a href="tool.php?name=neologism">Neologism</a></li>
                              <li><a href="tool.php?name=d2rserver">D2R Server</a></li>
                              <li><a href="tool.php?name=silk">Silk Link Discovery Framework</a></li>
                              <li><a href="tool.php?name=ontowiki">OntoWiki</a></li>
                              <li><a href="tool.php?name=sparqlviews">SPARQL Views</a></li>
                            </ul>
                            </p>
                            <p>Prerequisites:
                            <ul>
                                <li>The CORDIS data set is originally available in a relational database.</li>
                            </ul>
                            </p>
                            <p>Step by step publication process:
                                <ol>
                                    <li>Model the ontology for CORDIS using <a href="tool.php?name=neologism">Neologism</a>.</li>
                                    <li>Publish the data set as Linked Data using <a href="tool.php?name=d2rserver">D2R Server</a>. Map the database schema to RDF using the D2RQ Mapping Language.</li>
                                    <li>Find Linked Data sets that thematically overlap with CORDIS and that can be linked to, e.g. on <a href="http://www.ckan.net/group/lodcloud">CKAN</a>.</li>
                                    <li>Use the <a href="tool.php?name=silk">Silk Link Discovery Framework</a> to interlink the data sets and publish the links along with the data set.</li>
                                </ol>
                            </p>
                            <p>The Linked Data version of CORDIS can be consumed in different ways, e.g.:
                                <ul>
                                      <li><a href="tool.php?name=ontowiki">OntoWiki</a> can be used to collaboratively edit and comment on the CORDIS data set.</li>
                                      <li>In order to integrate CORDIS data set into any Drupal site, use <a href="tool.php?name=sparqlviews">SPARQL Views</a> to generate views on the data.</li>
                            </ul>
                             </p>


			</div>
		</div>
	</div>
	<!-- end content -->
	<!-- start sidebar -->
	<div id="sidebar">
		<ul>
			<li id="search">
				<h2><b>Search</b></h2>
				<form method="get" action="">
					<fieldset>
					<input type="text" id="s" name="s" value="" />
					<input type="submit" id="x" value="Search" />
					</fieldset>
				</form>
			</li>
		</ul>
	</div>
	<!-- end sidebar -->
	<div style="clear: both;">&nbsp;</div>
</div>
<!-- end page -->
<!-- start footer -->
<div id="footer">
	<div class="wrap">
		<div id="fbox1" class="box2">
			<h2></h2>
			<p></p>
		</div>
		<div id="fbox2" class="box2">
			<h2></h2>
			<p></p>
		</div>
		<div id="fbox3" class="box2">
			<h2></h2>
			<p></p>
		</div>
	</div>
</div>
<!-- end footer -->
</body>
</html>
