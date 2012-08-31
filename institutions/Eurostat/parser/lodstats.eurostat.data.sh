#!/bin/bash

#Takes RDF files and generates LODStats per file, and logs the process in a CSV file
#Author: http://csarven.ca/#i
#TODO:
#  allow input arguments from shell (e.g., path/files to process, output directory, path/to/logfile)
#  perhaps use guess input format in lodstats instead or allow input argument to define that

. $HOME/lodstats-env/bin/activate;

files="/3TB/eurostat/data/*.rdf";
pathoutput="/home/sarcap/lodstats/eurostat/data/";
logfile="$pathoutput""lodstats.eurostat.data.log.csv";

columns="dtstart, duration, file, lodstats";
echo "$columns";
echo "$columns" >> "$logfile";

for file in $files;
    do
        filename=$(basename $file);

        dtstart="$(date +%s)";
        date=$(date +"%Y-%m-%dT%TZ");
        lodstats -f rdf -va "$file" > "$pathoutput$filename.stats.ttl";
        dtend="$(date +%s)";

        duration="$(expr $dtend - $dtstart)";

        log="$date, $duration, $file, $pathoutput$filename.stats.ttl";
        echo "$log";
        echo "$log" >> "$logfile";
    done;
