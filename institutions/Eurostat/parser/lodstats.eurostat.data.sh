#!/bin/bash
#Author: http://csarven.ca/#i

. $HOME/lodstats-env/bin/activate;

files="/3TB/eurostat/data/a*.rdf";
pathoutput="/home/sarcap/lodstats/eurostat/data/";
logfile="$pathoutput""lodstats.eurostat.data.log.csv";

columns="dtstart, duration, file, lodstats";
echo "$columns";
echo "$columns" >> "$logfile";

for file in $files;
    do
        filename=$(basename $file);
        graph=${filename%.*};

        dtstart="$(date +%s)";
        date=$(date +"%Y-%m-%dT%TZ");
        lodstats -f rdf -va "$file" > "$pathoutput$filename.stats.ttl";
        dtend="$(date +%s)";

        duration="$(expr $dtend - $dtstart)";

        log="$date, $duration, $file, $pathoutput$filename.stats.ttl";
        echo "$log";
        echo "$log" >> "$logfile";
    done
