#!/bin/bash
startTime=$(date)
#directory path where all the zip files are stored
FILES=~/downloadZip/zip/*

#directory path where the uncompressed file should be stored
unCompressPath=~/downloadZip/UnCompress/

#directory path where dsd (RDFs) will be stored
dsdPath=~/downloadZip/dsd/

#directory path where sdmx (RDFs) will be stored
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
      sh SDMXParser.sh -f $fname -o $dataPath -i $f
      i=`expr $i + 1`
   fi
 fi
done
echo "RDFication started at $startTime"
echo "RDFication finished at $(date)"