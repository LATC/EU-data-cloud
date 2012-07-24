#!/bin/bash

#This script should be babysat for the time being.

rm -rf /3TB/eurostat/dsd
mv /data/eurostat-sandbox/dsd /3TB/eurostat/

rm -rf /3TB/eurostat/dic
mv /data/eurostat-sandbox/dic /3TB/eurostat/

rm -rf /3TB/eurostat/catalog
mv /data/eurostat-sandbox/catalog /3TB/eurostat/

#Stop the read-only Fuseki service, and start in read-write (update) mode.
#for file in /3TB/eurostat/catalog/*.ttl; do /usr/lib/fuseki/./s-post --verbose http://localhost:3232/dataset/data http://eurostat.linked-statistics.org/graph/catalog "$file"; done
#Stop the Fuseki service in update mode, and start it in read-only mode.

#This takes a while.
rm -rf /3TB/eurostat/data
mv /data/eurostat-sandbox/data /3TB/eurostat/

