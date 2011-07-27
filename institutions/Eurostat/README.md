# Eurostat dataset

About 
=====
This project is about publishing EuroStat as Linked Data on the Web. 


Design
======

1) Base URI would be "http://eurostat.linked-statistics.org".

2) VoID discovery mechanism would be delivered through "http://eurostat.linked-statistics.org/.well-known/void". Upon discovery, the system will serve the VoID description which would be like :

		:EuroStat a void:Dataset;
    			dcterms:title "EuroStat";
			...
			void:subset
			<http://eurostat.linked-statistics.org/dss/ds1>,
			<http://eurostat.linked-statistics.org/dss/ds2>
			.

3) Upon de-referencing any Eurostat dataset, we serve the DataSet Summary (DSS):
		
		loes:ds1 a qb:DataSet, void:Dataset;
			qb:DataStructureDefinition loes:dsd1;
			void:dataDump <http://eurostat.linked-statistics.org/data/ds1.ttl>
			.
Remarks
=======
1) Use N-Quads format to generate dataset triples.

2) Reuse Andreas code for Dataset RDFication.

3) Use our own code for DSD RDFication.

## License

The software provided in this repository is Open Source.