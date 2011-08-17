# Eurostat Data Ingestion Workflow

## 1. Table of contents -> dataset URLs

Parse the [Table of Contents](http://epp.eurostat.ec.europa.eu/NavTree_prod/everybody/BulkDownloadListing?sort=1&amp;file=table_of_contents.xml "Bulk Download") of EuroStat and retrieve all dataset URLs:

* INPUT: URL of `table_of_contents.xml`
* OUTPUT:  A list of dataset URLs

## 2. Dataset URL -> DSD+observations

Using a dataset URL, download and parse the contents of the compressed file:

* INPUT:  dataset URL, for example `http://someURL?file=data/tsieb010.sdmx.zip`
* OUTPUT:  `tsieb010.dsd.xml` and `tsieb010.sdmx.xml`

## 3. Parse Data Structure Definition (DSD) file

* INPUT:  `tsieb010.dsd.xml`
* OUTPUT:  `~/dsd/tsieb010.rdf` (represented in DataCube vocabulary)

## 4. Parse the observations/SDMX file

* INPUT:  `tsieb010.sdmx.xml`
* OUTPUT:  `~/data/tsieb010.rdf` (represented in DataCube vocabulary)

## 5. Generate dataset summary (DSS)

For example:

	@prefix data: <http://eurostat.linked-statistics.org/data/> .
	@prefix dss: <http://eurostat.linked-statistics.org/dss/> .
	@prefix dsd: <http://eurostat.linked-statistics.org/dsd#> .
	@prefix qb: <http://purl.org/linked-data/cube#> .
	@prefix void: <http://rdfs.org/ns/void#> .

	dss:ds_1 a qb:DataSet, void:Dataset;
	         qb:DataStructureDefinition dsd:dsd_1;
	         void:dataDump data:ds_1.ttl;
	.

## 6. Generate VoID for DSDs only (INTERNAL)

This will be solely used to populate the triple stores (see next step).

* INPUT: URL of `table_of_contents.xml`
* OUTPUT:  one file in the file system with all DSDs
 
One DSD looks for example:

	@prefix data: <http://eurostat.linked-statistics.org/data/> .
	@prefix dss: <http://eurostat.linked-statistics.org/dss/> .
	@prefix dsd: <http://eurostat.linked-statistics.org/dsd#> .
	@prefix qb: <http://purl.org/linked-data/cube#> .
	@prefix void: <http://rdfs.org/ns/void#> .

	dsd:dsd_1 a qb:DataStructureDefinition, void:Dataset;
	          void:dataDump dsd:dsd_1.ttl
	.

## 7. Use the SMCS to populate the triple store

With the VoID file described in step 6 and the [SMCS](https://github.com/data-gov-ie/data-ingestion-pipeline), populate the triple store.