#Batch Scripts

### ParseToC
Parses the [Table of Contents](http://epp.eurostat.ec.europa.eu/NavTree_prod/everybody/BulkDownloadListing?sort=1&amp;file=table_of_contents.xml "Bulk Download") and retrieve all dataset URLS.

How to Run : `ParseToC.bat -n 5`

where `n` tells the number of dataset URLs to print

Type `-h` for help.

### UnCompressFile
Download and parse the contents of the compressed dataset file:

How to Run : `UnCompressFile.bat -p c:/test/ -u "http://epp.eurostat.ec.europa.eu/NavTree_prod/everybody/BulkDownloadListing?sort=1&downfile=data%2Fapro_cpb_sugar.sdmx.zip"`

where `p` specifies the directory path and `u` specify the URL of the dataset.

Type `-h` for help.

### License

The software provided in this repository is Open Source.