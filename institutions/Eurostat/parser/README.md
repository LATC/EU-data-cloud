#Batch Scripts

## ParseToC
Parses the [Table of Contents](http://epp.eurostat.ec.europa.eu/NavTree_prod/everybody/BulkDownloadListing?sort=1&amp;file=table_of_contents.xml "Bulk Download") and retrieve all dataset URLS.

How to Run : `ParseToC.bat -n 5`

Type `-h` for help.

## UnCompressFile
Download and parse the contents of the compressed dataset file:

How to Run : `UnCompressFile.bat -p c:/test/ -u "http://epp.eurostat.ec.europa.eu/NavTree_prod/everybody/BulkDownloadListing?sort=1&downfile=data%2Fapro_cpb_sugar.sdmx.zip"`

Type `-h` for help.

## DSDParser
Parse the Data Structure Definition (DSD) file and represent it in RDF using Data Cube vocabulary.

How to Run : `DSDParser.bat -o c:/test/ -i C:/tempZip/bsbu_m.dsd.xml -f RDF/XML`

Type `-h` for help.

## SDMXParser
Parse the SDMX file and represent the observations in RDF using DataCube vocabulary.

How to Run : `SDMXParser.bat -f tsieb010 -o c:/test/`

Type `-h` for help.

## Catalog
Generates the Catalog and Inventory void files which will be used to populate the triple store.

How to Run : `Catalog.bat -o c:/test/`

Type `-h` for help.

## License

The software provided in this repository is Open Source.