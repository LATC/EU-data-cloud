<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<title>Linked Data Publication & Consumption Tool Library</title>
<meta name="keywords" content="" />
<meta name="description" content="" />
<link href="default.css" rel="stylesheet" type="text/css" />
</head>
<body>

<?php

require_once ('toollibrary.php');

?>
<!-- start header -->
<div id="header">
	<div id="logo">
		<h1><a href="#">LATC Data Publication & Consumption Tools Library</a></h1>
	</div>
	<div id="menu">
		<ul>
			<li><a href="index.php">Home</a></li>
			<li class="active"><a href="#">Tool Categories</a></li>
			<li><a href="example.php">Example</a></li>
			<li><a href="contact.html">About</a></li>
			<li><a href="http://latc-project.eu" title="LATC" style="background: none; align:right; margin-left: 169px; margin-top: 10px;padding: 7px;"><img src="images/logo-latc.png"/></a></li>
		</ul>
	</div>
</div>
<!-- end header -->
<!-- start page -->
<div id="page">
	<!-- start content -->
	<div id="content">
		<div class="box1">
                    <p>The main steps of the Linked Data publication and consumption process are:
                        <ol>
                            <li><a href="#modeling">Modeling</a></li>
                            <li><a href="#publishing">Publishing</a></li>
                            <li><a href="#discovery">Discovery</a>
                                <ol>
                                    <li><a href="#crawling">Crawling</a></li>
                                    <li><a href="#searching">Searching</a></li>
                                    <li><a href="#browsing">Browsing</a></li>
                                    <li><a href="#extracting">Extracting</a></li>
                                </ol>
                            </li>
                            <li><a href="#consolidation">Consolidation</a>
                                <ol>
                                    <li><a href="#vocabularymapping">Vocabulary Mapping</a></li>
                                    <li><a href="#identityresolution">Identity Resolution</a></li>
                                </ol>
                            </li>
                            <li><a href="#application">Application</a>
                                <ol>
                                    <li><a href="#exploration">Exploration</a></li>
                                    <li><a href="#integration">Integration</a></li>
                                </ol>
                            </li>
                        </ol>
                    </p>
                    <p>In the following we describe those steps in detail after
                    giving definitions for the main keywords.</p>

		</div>

		<div class="post">
			<h2 class="title">Definitions</h2>
			<div class="entry">
                            <p>In the following, we give some definitions in order to clarify the role of the different parts and components of the process.</p>
                            <h3 class="title">Linked Data</h3>
                            <p>A data set is considered to be "Linked Data" if the statements it contains are expressed according to the Linked Data publishing principles. For the present document, we restrain this definition further to focus on data that is freely available on the Web.</p>
                            <h3 class="title">Publication</h3>
                            <p>The publication of Linked Data consists in making available on the Web a given data set. The publication involves converting the data from its original format and making the necessary consolidation work to ensure compliance with the definition of Linked Data.</p>
                            <h3 class="title">Consumption</h3>
                            <p>The consumption of Linked Data consists in accessing the data and aggregating or integrating it. This allows for displaying the data in different ways.</p>
                            <p>The consumption of Linked Data is the process of acquisition of a subset of the Linked Data available on the Web in order to fulfil a specific goal (data visualisation, data aggregation, …). The consumption of Linked Data may also involve the usage of data which is not Linked Data itself (for instance, a local data base).</p>
			</div>
		</div>
		<div class="post">
			<h2 class="title" id="modeling">1 Modeling</h2>
			<div class="entry">
				<p>On the Web of Data, the relationships between resources are expressed by concepts defined in vocabularies (also called "ontologies"). The decentralised publication model of Linked data allows for re-using the same vocabularies across different data sets. When publishing data, one may consider using existing concepts from published ontologies or define a new set of concepts (and publish it). Vocabularies are typically created to fit a specific modelisation problem. For instance, GoodRelations is used to express business-related relations (vendor, price, …), FOAF contains concepts about social networks (knows, familyName, …) and DublinCore provides concepts that can be used for documents. Some of the existing vocabularies can be found on <a href="http://semanticweb.org/wiki/Ontology">http://semanticweb.org/wiki/Ontology</a>, <a href="http://swoogle.umbc.edu/">Swoogle</a>  provides a search engine to find more of them.</p>

				<p>If no vocabulary matches a specific modeling use case, there is a  need for creating, and publishing a new vocabulary. <a href="http://neologism.deri.ie/">Neologism</a> is a vocabulary editing and publishing platform for the Web of Data, with a focus on ease of use and compatibility with Linked Data principles. Neologism makes it possible to create classes and properties in an easy, fast and standard complient way.</p>

                                <?php gettoolbox("neologism"); ?>
			</div>
		</div>
		<div class="post">
			<h2 class="title" id="publishing">2 Publishing</h2>
			<div class="entry">
				<p>Publishing data as Linked Data on the Web of Data enables the integration of different data sources. It allows for displaying and querying different data sources. Furthermore it allows the integrating data describing the same entity.</p>
				<p>When publishing Linked Data on the Web, data is represented using the Resource Description Framework (RDF).</p>
				<p>The Web of Linked Data is <a href="http://www.w3.org/DesignIssues/LinkedData.html">built upon two simple ideas</a>: Structured data is published on the Web using dereferencable HTTP URIs to represent data items wherein related data items are connected using RDF links.</p>
				<p>It is desirable to not only publish the data but to also publish its schema as Linked Data. Thus, Linked Data applications can e.g. customize views on the data.</p>
				<p>The format of the original data which is to be published as Linked Data is relevant for the publications steps to take.</p>
				<p>If the original data is available in a relational database, we recommend using <a href="http://www4.wiwiss.fu-berlin.de/bizer/d2r-server/">D2R Server</a>  to publish the data along with its schema as Linked Data. D2R Server offers a HTML, Linked Data and SPARQL interface to the published data. It is written in Java and licensed under the Apache License V2.0.</p>
                                <?php gettoolbox("d2rserver"); ?>
                                <p>If the original data is available in any structured format, it has to be converted to RDF by a converter.</p>
				<p>For CSV and Excel files we recommend using the <a href="http://lab.linkeddata.deri.ie/2010">RDF Extension for Google Refine</a>. It adds a graphical user interface for exporting data of Google Refine projects in RDF format. The extension is available under the BSD license.</p>
				<?php gettoolbox("googlerefine"); ?>
                                <p>For XML files we recommend using XSLT to transform them into RDF.</p>
				<p>Any other formats have to be converted using your own converter. You can also check the converter list at the <a href="http://www.w3.org/2001/sw/wiki/Category:Converter">W3C Semantic Web wiki</a>.</p>
				<p>If you have converted your data into RDF, you have two options to publish it on the Web as Linked Data.</p>
				<p>You can serve the RDF file(s) using any web server. You will have to enable URL rewriting to make the Linked Data URIs in your data set dereferencable. We can recommend this method for publishing small data sets or vocabularies.</p>
				<p>For bigger data sets we recommend setting up a Triple store. Triple stores allow for storing Linked Data and providing it using different interfaces. Via a SPARQL interface the data is made accessible for querying. Ideally Triple stores provide a Linked Data interface. In addition, a HTML interface can be offered.</p>
				<p>A list of Triple stores is available at the <a href="http://www.w3.org/2001/sw/wiki/Category:Triple_Store">W3C Semantic Web wiki</a>. The <a href="http://www4.wiwiss.fu-berlin.de/bizer/BerlinSPARQLBenchmark">Berlin SPARQL Benchmark</a> (BSBM) is a benchmark for comparing the performance of Triple stores that expose SPARQL endpoints. The benchmark results on the store performances are available <a href="http://www4.wiwiss.fu-berlin.de/bizer/BerlinSPARQLBenchmark/results/V6/index.html">online</a>.</p>
				<p>As a Linked Data and HTML interface for any Triple store not offering those we recommend <a href="http://www4.wiwiss.fu-berlin.de/pubby/">Pubby</a>. Pubby makes it easy to turn a SPARQL endpoint into a Linked Data server. It is implemented as a Java web application and available under the Apache License V2.0.</p>
                                <?php gettoolbox("pubby"); ?>
                        </div>

		</div>
		<div class="post">
			<h2 class="title" id="discovery">3 Discovery</h2>
			<div class="entry">
				<p>The Web of Data is essentially a decentralised publication system for data, just like the Web of Documents is a decentralised publication system for documents. As for the Web of Documents, it is not possible to get a global view of the Web of Data and finding something in particular in it can turn into finding a needle in a haystack problem. The discovery of data in the Web of Data can be done through essentially four different means: crawling the Web, using a search engine, browsing an index or looking for data embedded in Web documents.</p>
			<h3 class="title" id="crawling">3.1 Crawling</h3>
				<p>Linked Data crawlers follow RDF links from a given set of seed URIs and store the retrieved data either in an RDF store or as local files. This approach is particularly useful for data that is not already available through SPARQL endpoints or RDF dumps.</p> 				<p><a href="http://code.google.com/p/ldspider/">LDSpider</a> is a web crawling framework for the Web of Data. It can be used through a command line application as well as through a flexible API for a usage within another application.</p>
                                <?php gettoolbox("ldspider"); ?>
			<h3 class="title" id="searching">3.2 Searching</h3>
				<p><a href="http://sindice.com">Sindice</a> is a state of the art infrastructure to process, consolidate and query the Web of Data. The web site provides a search engine that returns RDF documents matching the keyword(s) provided. In order to be able to do this, Sindice uses crawlers that browse the Web of Data, storing and consolidating the data found.</p>
                            <?php gettoolbox("sindice"); ?>
                        <h3 class="title" id="browsing">3.3 Browsing</h3>
				<p>Indexes are available for the Web of Data, they are manually curated and contain extra information about the content of the data sets such as the vocabularies used or the number of triples. These indexes are a more directed approach to finding data sets than Crawling and Searching, allowing for looking for data sets covering a specific topic. The index for the <a href="http://ckan.net/group/lodcloud">LOD Cloud on CKAN</a> currently (as of February 2011) lists 203 data sets.</p>
			<h3 class="title" id="extracting">3.4 Extracting</h3>
				<p>Syntactic extensions have been defined to embed structured data into the HTML that is used to create Web documents. Two main standards, Microformats and RDFa, enable a Web page author to add meta data describing the content of the document. The tool <a href="http://code.google.com/p/any23/">Any23</a> finds such embedded data and extracts it as RDF.</p>
                                <?php gettoolbox("any23"); ?>
			</div>
		</div>
		<div class="post">
			<h2 class="title" id="consolidation">4 Consolidation</h2>
			<div class="entry">
				<p>Once a data set is published as Linked Data, its value can be increased in different ways.</p>
				<p>If the Linked Data set vocabulary defines new concepts or relationships it should be mapped to existing vocabularies.</p>
				<p>It is desirable to publish links to data sets that are related to the newly published data set by applying Identity Resolution methods.</p>
			<h3 class="title" id="vocabularymapping">4.1 Vocabulary Mapping</h3>
				<p>Linked Data sources often use different vocabularies to represent data about the same type of entity. In order to achieve an integrated view on the data to their users, Linked Data applications may translate data from different vocabularies into the application’s target schema.</p>
				<p>The <a href="http://www4.wiwiss.fu-berlin.de/bizer/r2r/">R2R Framework</a> enables Linked Data applications which discover data on the Web that is represented using unknown terms, to search the Web for mappings and apply the discovered mappings to translate Web data to the application's target vocabulary. R2R provides the <a href="http://www4.wiwiss.fu-berlin.de/bizer/r2r/spec/">R2R Mapping Language</a> for publishing fine-grained term mappings on the Web.</p>
                                <?php gettoolbox("r2r"); ?>
                        <h3 class="title" id="identityresolution">4.2 Identity Resolution</h3>
				<p>Linked Data sources can overlap thematically. Thus, the same entity is described in different data sets, either in different detail or from different points of view. To integrate this data, identity resolution tools are needed. They identify duplicate entities and interlink them by owl:sameAs links. It is also desirable to link entities that are connected in the real world, e.g. a book to its author. The linking approaches can be manual, semi-automatic or automatic ones.</p>
				<p>The <a href="http://www4.wiwiss.fu-berlin.de/bizer/silk/">Silk Link Discovery Framework</a> is a tool for discovering relationships between data items within different Linked Data sources. Data publishers can use Silk to set RDF links from their data sources to other data sources on the Web. The framework is implemented in Scala and is available under the terms of the Apache Software License.</p>
				<p>Silk is provided in three different editions which address different use cases. Silk Single Machine is used to generate RDF links on a single machine. Silk MapReduce is used to generate RDF links between data sets using a cluster of multiple machines. Silk Server can be used as an identity resolution component within applications that consume Linked Data from the Web. Silk Server provides an HTTP API for matching instances from an incoming stream of RDF data while keeping track of known entities.</p>
                                <?php gettoolbox("silk"); ?>
                        </div>
		</div>
		<div class="post">
			<h2 class="title" id="application">5 Application</h2>
			<div class="entry">
				<p>They are many ways to make use of the data published on the Web of Data. The publication model facilitates data integration as all the data is published under the same format, and uses common identifiers and vocabularies. We will highlight two uses cases focused around exploring the content of data sets and enriching a Web site.</p>
			<h3 class="title" id="exploration">5.1 Exploration</h3>
				<p>Publication and storing tools often offer HTML interfaces to Linked Data, like D2R Server, allowing to directly explore the content of a particular data set from a Web browser. Most data-centric visualization tools help validating and displaying the data in a textual way, e.g. tables, but more complex information can be sought, like for instance the presence of other data related to a particular entity or the relation between two entities. <a href="http://sig.ma">Sig.ma</a> and <a href="http://relfinder.dbpedia.org/">RelFinder</a> are tools respectively matching these two use-cases. Sig.ma leverages the search engine <a href="http://sindice.com">Sindice</a> to look for data about a particular resource, all the data found is aggregated and can be filtered based on the data source. RelFinder looks for and displays relations between two, or more, resources on the Web of Data. In addition to these two tools, the Sindice Web Data Inspector can be used to visualize and validate RDF files, HTML pages embedding microformats, and XHTML pages embedding RDFa – thereby providing the means to the verification of the data being published. </p>
                                <?php gettoolbox("sigma"); ?>
                                <?php gettoolbox("relfinder"); ?>
                        <h3 class="title" id="integration">5.2 Integration</h3>
				<p>The data published on the Web of Data can be integrated into Web sites to enrich the information provided. <a href="http://ontowiki.net">OntoWiki</a> and <a href="http://drupal.org/project/sparql_views">SPARQL Views</a> are two examples of data integration. OntoWiki is a Semantic Data Wiki enabling the collaborative creation and publication of RDF knowledge bases as Linked Data. SPARQL Views is a Drupal query plugin. It allows querying RDF data in SPARQL endpoints and RDFa on Web pages, bringing the data into Drupal Views. The data can then be displayed and formatted using Views. </p>
                                <?php gettoolbox("ontowiki"); ?>
                                <?php gettoolbox("sparqlviews"); ?>
                        </div>
		</div>
	</div>
	<!-- end content -->
	<!-- start sidebar -->
	<div id="sidebar">
		<ul>
			<li id="search">
				<h2><b>Search</b></h2>
				<form method="get" action=">
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
