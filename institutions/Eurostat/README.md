# Eurostat dataset

About 
=====
This project is about publishing EuroStat as Linked Data on the Web. 


Design
======

1) Base URI would be http://eurostat.linked-statistics.org.

2) VoID discovery mechanism would be delivered through http://eurostat.linked-statistics.org/.well-known/void. Upon discovery, the system will serve the VoID description which would be like :

                @prefix meta: <http://eurostat.linked-statistics.org/meta#> . 
                @prefix dss: <http://eurostat.linked-statistics.org/dss/> .
                @prefix dcterms: <http://purl.org/dc/terms/> .
                @prefix void: <http://rdfs.org/ns/void#> .
                
		             meta:Eurostat a void:Dataset;
    		                        dcterms:title "EuroStat";
		                        void:subset
		                        dss:ds_1,
		                        dss:ds_2
		                        .

3) Upon de-referencing any dataset, we serve the DataSet Summary (DSS):

                @prefix data: <http://eurostat.linked-statistics.org/data/> .
                @prefix dss: <http://eurostat.linked-statistics.org/dss/> .
                @prefix dsd: <http://eurostat.linked-statistics.org/dsd#> .
                @prefix qb: <http://purl.org/linked-data/cube#> .
                @prefix void: <http://rdfs.org/ns/void#> .
		
	                     dss:ds_1 a qb:DataSet, void:Dataset;
			                qb:DataStructureDefinition dsd:dsd_1;
			                void:dataDump data:ds_1.ttl
			                .

Remarks
=======
1) Use N-Quads format to generate dataset triples.

2) Reuse code from [linked-eurostat](http://code.google.com/p/linked-eurostat/) for Dataset RDFication.

3) Use [our own code](https://github.com/LATC/EU-data-cloud/tree/master/institutions/Eurostat/parser/src) for DSD RDFication.


Example Query
=============
Here is an examplary quert that joins two SDMX datasets:

	PREFIX rdfs: http://www.w3.org/2000/01/rdf-schema#
	PREFIX qb: http://purl.org/linked-data/cube#
	PREFIX e: http://ontologycentral.com/2009/01/eurostat/ns#
	PREFIX sdmx-measure: http://purl.org/linked-data/sdmx/2009/measure#
	PREFIX skos: http://www.w3.org/2004/02/skos/core#
	PREFIX g: http://eurostat.linked-statistics.org/ontologies/geographic.rdf#
	PREFIX dataset: http://eurostat.linked-statistics.org/data/
	SELECT ?nuts2
	SUM(xsd:decimal(?pop)) AS ?population
	?wateruse
	xsd:decimal(?wateruse)*1000000/SUM(xsd:decimal(?pop)) AS
	?percapita WHERE { ?observation qb:dataset dataset:demo_r_pjanaggr3 ;
	e:time http://eurostat.linked-statistics.org/dic/time#2007;
	e:age http://eurostat.linked-statistics.org/dic/age#TOTAL;
	e:sex http://eurostat.linked-statistics.org/dic/sex#F;
	e:geo ?ugeo;
	sdmx-measure:obsValue ?pop.
	?ugeo g:hasParentRegion ?parent.
	?parent rdfs:label ?nuts2.
	?wuregion qb:dataset dataset:env_n2_wu ;
	e:geo ?parent;
	e:cons http://eurostat.linked-statistics.org/dic/cons#W18_2_7_2;
	e:time http://eurostat.linked-statistics.org/dic/time#2007;
	sdmx-measure:obsValue ?wateruse.
	} GROUP BY ?nuts2 ?wateruse ORDER BY DESC(?percapita)

The query above uses dataset `demo_r_pjanaggr3`, which contains `Population by sex and age groups on 1 January - NUTS level 3 regions`. We need populations for NUTS level 2 and we therefore aggregate the dataset by using the NUTS vocabulary to find the parent regions.

We only want data for 2007, and both sexes. We then join the data with the `env_n2_wu` dataset, which contains `Water use (NUTS2) - mio m3`. We can then find the regions with the most domestic water (code W18_2_7_2) use per million inhabitants.

URIs for Eurostat identities
===========================

* The base URI for Eurostat is http://eurostat.linked-statistics.org.

* The Data Structure Definition (DSD) can be found under `http://eurostat.linked-statistics.org/dsd/`

	For example: http://eurostat.linked-statistics.org/dsd/bsbu_q.rdf

* The SDMX data sets can be found under `http://eurostat.linked-statistics.org/data/`

	For example: http://eurostat.linked-statistics.org/data/bsbu_q.rdf

* The dictionaries can be found under `http://eurostat.linked-statistics.org/dic/`

	For example: http://eurostat.linked-statistics.org/dic/geo.rdf

* Concepts and Properties of DSD can be found under `http://eurostat.linked-statistics.org/concept#` and `http://eurostat.linked-statistics.org/property#`

## License

The software provided in this repository is Open Source.

To Do
=====
* Document the percentage of datasets that change per week, on average
* Explore how to generate a per-country subset of the data
* Load a .ie subset into data-gov.ie dataspace
* Interlinking
  * Regions: DBpedia, Geonames, LinkedGeoData
  * National regions: Data-gov.ie, GeoLinkedData.es, Ordnance Survey
  * Indicators: US Census
  * Topics/Subjects: DBpedia

