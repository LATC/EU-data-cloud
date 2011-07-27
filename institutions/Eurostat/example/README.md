# Eurostat dataset

About 
=====
This project is about publishing EuroStat as Linked Data on the Web. The current version of the code transforms DSD into RDF using DataCube vocabulary.


HOW TO RUN
==========
1) In order to run the example, get the EuroStat.jar file from EuroStat/example directory
2) The jar file requires two parameters
	a) dsd file path
	b) output file path
3) Do not specify the filename of the RDF as the program will create filename based on the name of the DSD file
4) example : java -jar EuroStat.jar /home/EuroStat/dsd/tsieb010.dsd.xml /home/EuroStat/RDF/

## License

The software provided in this repository is Open Source.