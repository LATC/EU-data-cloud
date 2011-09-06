#!/bin/bash
java -cp "./build:./lib/*" -Xmx2G com.ontologycentral.estatwrap.SDMXParser "$@"