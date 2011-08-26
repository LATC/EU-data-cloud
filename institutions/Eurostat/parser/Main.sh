#!/bin/bash
FILES=~/downloadZip/zip/*
unCompressPath=~/downloadZip/UnCompress/
dsdPath=~/downloadZip/dsd/
dataPath=~/downloadZip/data/
for f in $FILES
do
  echo "UnCompressing $f"
  sh UnCompressFile.sh -p $unCompressPath -u $f
done

for f in $unCompressPath*
do
  echo "Processing $f file..."
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
   fi
  fi
done
