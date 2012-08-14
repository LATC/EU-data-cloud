#Batch Scripts
Detailed description on each script can be found at this [page](https://github.com/LATC/EU-data-cloud/wiki/Batch-Scripts)

## Steps to RDFize EuroStat data
The RDFication process can be found at this [page](https://github.com/LATC/EU-data-cloud/wiki/Eurostat-RDFication-process)

## How to convert a single dataset to RDF
* The best way to test the RDFication process is to use `Main.sh` script. You are required to download the *.zip file(s) in a directory before running the script. It can be achieved by running `EurostatMirror.sh.
* Change the directory path variables in the `Main.sh` to your desired directory paths. Make sure that the directories *exists* before running the script.
* How to run : `sh Main.sh -i ~/sdmx-code.ttl -l ~/logs/`

## License

The software provided in this repository is Open Source.