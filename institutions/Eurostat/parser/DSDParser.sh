#!/bin/bash
java -cp "./build:./lib/*" -Xmx256M org.deri.eurostat.dsdparser.DSDParser "$@"