This was a quick analysis to figure out the distribution of update dates of Eurostat bulk downloads.

The process went something like this:

curl -o 'toc.xml' 'http://epp.eurostat.ec.europa.eu/NavTree_prod/everybody/BulkDownloadListing?sort=1&file=table_of_contents.xml'
grep lastModified toc.xml > lm.txt
sed -E "s/(..)\.(..)\.(....)/\3-\2-\1/" lm.txt > lm2.txt
sort lm2.txt > lm3.txt
uniq -c lm3.txt > lm4.txt
sed -E "s/ *([0-9]+) (....-..-..)/\1,\2/" lm4.txt > lm5.csv
mv lm5.csv eurostat-lastmod.csv

Then open eurostat-lastmod.csv in Excel, chop off everything before 2012 (the bulk of updates is in 2012), add column headers and a chart.
