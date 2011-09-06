#!/bin/bash
java -cp "./build:./lib/*" -Xmx1024M com.ontologycentral.estatwrap.SDMXParser "$@"