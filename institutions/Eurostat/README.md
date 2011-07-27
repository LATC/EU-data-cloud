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

2) Reuse code from [linked-eurostat](http://code.google.com/p/linked-eurostat/) Dataset RDFication.

3) Use [our own code](https://github.com/LATC/EU-data-cloud/tree/master/institutions/Eurostat/src) for DSD RDFication.

## License

The software provided in this repository is Open Source.