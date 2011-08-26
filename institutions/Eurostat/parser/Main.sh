#!/bin/bash
startTime=$(date)
FILES=~/downloadZip/zip/*
unCompressPath=~/downloadZip/UnCompress/
dsdPath=~/downloadZip/dsd/
dataPath=~/downloadZip/data/
i=1
for f in $FILES
do
  echo "UnCompressing file#$i ... filename is $f"
  sh UnCompressFile.sh -p $unCompressPath -u $f
  i=`expr $i + 1`
done

i=1
for f in $unCompressPath*
do
  echo "Processing file#$i ... filename is $f"
  echo $f | grep -qE ".dsd.xml"
  if [ $? -eq 0 ]
   then
      sh DSDParser.sh -o $dsdPath -i $f
  else
   echo $f | grep -qE ".sdmx.xml"
   if [ $? -eq 0 ]
    then
      filename=${f##*/}
      fname=`echo $filename | awk '{ print substr($filename,0,length($filename)-8)}'`
      sh SDMXParser.sh -f $fname -o $dataPath
      i=`expr $i + 1`
   fi
  fi
done
echo "Shell script started the job at $startTime"
echo "Shell script finished the job at $(date)"