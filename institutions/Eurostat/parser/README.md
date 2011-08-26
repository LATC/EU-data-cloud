#Batch Scripts

## ParseToC
Parses the [Table of Contents](http://epp.eurostat.ec.europa.eu/NavTree_prod/everybody/BulkDownloadListing?sort=1&amp;file=table_of_contents.xml "Bulk Download") and retrieve all dataset URLS.

How to Run : `ParseToC.bat -n 5`

Type `-h` for help.

## UnCompressFile
Parses the contents of the compressed dataset file:

How to Run : `UnCompressFile.bat -p c:/test/ -u "http://epp.eurostat.ec.europa.eu/NavTree_prod/everybody/BulkDownloadListing?sort=1&downfile=data/apro_cpb_sugar.sdmx.zip"`

Type `-h` for help.

## DonwloadZipFile
Downloads the compressed dataset file from the URL:

How to Run : `DownloadZip.bat -p c:/test/ -u "http://epp.eurostat.ec.europa.eu/NavTree_prod/everybody/BulkDownloadListing?sort=1&downfile=data/apro_cpb_sugar.sdmx.zip"`

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
Generates the void files which will be used to populate the triple store described in [Step 5 and Step6](https://github.com/LATC/EU-data-cloud/blob/master/institutions/Eurostat/design/workflow.md).

How to Run : `Catalog.bat -o c:/test/`

Type `-h` for help.

## EuroStatMirror
Downloads all the compressed Datasets files from the [Bulk Download page](http://epp.eurostat.ec.europa.eu/NavTree_prod/everybody/BulkDownloadListing) by extracting URLs from [Table of Contents](http://epp.eurostat.ec.europa.eu/NavTree_prod/everybody/BulkDownloadListing?sort=1&amp;file=table_of_contents.xml "Bulk Download").

How to Run : `EuroStatMirror.bat -p c:/test/`

Type `-h` for help.

## How to Run
In order to RDFize all the data from the compressed files and the VoiD files we need to store into the SPARQL endpoint, follow the following steps:

* Use `Main.sh` script to uncompress the downloaded compressed files and RDFize the DSDs and SDMXs files.
	
	How to Run : `sh Main.sh`
* Use `Catalog.sh` to generate the Catalog.ttl and Inventory.ttl
	
	How to Run : `sh Catalog.sh -o ~/catalog/` , make sure the target directory (e.g. catalog)exists before executing the script
* Use `DictionaryParser.sh` to RDFize the dictionaries which are used to represent SDMX datasets
	
	How to Run : `sh DictionaryParser.sh -i ~/dicPath/ -o ~/outputPath` , make sure the target directories (e.g. dicPath,outputPath)exists before executing the script

## License

The software provided in this repository is Open Source.