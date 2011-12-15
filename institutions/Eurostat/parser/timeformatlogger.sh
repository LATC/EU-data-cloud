#!/bin/bash
startTime=$(date)
#directory path where all the zip files are stored
FILES=/data/eurostat/original-data/*

#directory path where the uncompressed file should be stored
unCompressPath=/data/eurostat/raw-data/

#directory path where dsd (RDFs) will be stored
dsdPath=/data/eurostat/dsd/

#directory path where sdmx (RDFs) will be stored
dataPath=/data/eurostat/data/

#directory path where sdmx (RDFs) will be stored
logPath=/data/eurostat/logs/

i=1
#for f in $FILES
#do
#  echo "UnCompressing file#$i ... filename is $f"
#  sh UnCompressFile.sh -i $f -o $unCompressPath
#  i=`expr $i + 1`
#done

i=1
for f in $unCompressPath*
do
#  echo "Processing file#$i ... filename is $f"
#  echo $f | grep -qE ".dsd.xml"
#  if [ $? -eq 0 ]
#   then
#     sh DSDParser.sh -o $dsdPath -i $f -f turtle
#  else
   echo $f | grep -qE ".sdmx.xml"
   if [ $? -eq 0 ]
    then
	echo "Processing file#$i ... filename is $f"
      filename=${f##*/}
      fname=`echo $filename | awk '{ print substr($filename,0,length($filename)-8)}'`
#      sh SDMXParser.sh -f $fname -o $dataPath -i $f -l $logPath
      sh timeformat.sh -i $f -l $logPath
      i=`expr $i + 1`
   fi
#i=`expr $i + 1`
# fi
done
echo "Shell script started the job at $startTime"
echo "Shell script finished the job at $(date)"
