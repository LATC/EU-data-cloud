#!/bin/bash
startTime=$(date)
#directory path where all the zip files are stored
FILES=/data/sandbox/eurostat/original-data/

#directory path where the uncompressed file should be stored
unCompressPath=/data/sandbox/eurostat/raw-data/

#directory path where the tsv file should be stored
tsvPath=/data/sandbox/eurostat/tsv/

#directory path where dsd (RDFs) will be stored
dsdPath=/data/sandbox/eurostat/dsd/

#directory path where sdmx (RDFs) will be stored
dataPath=/data/sandbox/eurostat/data/

#directory path where log file will be
logPath=/data/sandbox/eurostat/logs/

#directory path where sdmx-code file is located.
sdmxFile=/data/sdmx-code.ttl

ext=".tsv.gz"

### Deleting files from directories if exists

echo "deleting files from $unCompressPath ..."
for f in $unCompressPath*
do
      /bin/rm $f
done

echo "deleting files from $dsdPath ..."
for f in $dsdPath*
do
      /bin/rm $f
done

echo "deleting files from $dataPath ..."
for f in $dataPath*
do
      /bin/rm $f
done

echo "deleting files from $logPath ..."
for f in $logPath*
do
      /bin/rm $f
done

### RDFication code starts from here


i=1
for f in $FILES*
do
  echo "UnCompressing file#$i ... filename is $f"
  sh UnCompressFile.sh -i $f -o $unCompressPath
  i=`expr $i + 1`
done

i=1
for f in $unCompressPath*
do
  echo "Processing file#$i ... filename is $f"
  echo $f | grep -qE ".dsd.xml"
  if [ $? -eq 0 ]
   then
     sh DSDParser.sh -o $dsdPath -i $f -f turtle -a $sdmxFile
  else
   echo $f | grep -qE ".sdmx.xml"
   if [ $? -eq 0 ]
    then
      filename=${f##*/}
      fname=`echo $filename | awk '{ print substr($filename,0,length($filename)-8)}'`
      sh SDMXParser.sh -f $fname -o $dataPath -i $f -l $logPath -t $tsvPath$fname$ext
      i=`expr $i + 1`
   fi
fi
done
echo "Shell script started the job at $startTime"
echo "Shell script finished the job at $(date)"
