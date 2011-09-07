#!/bin/bash
java -cp "./build:./lib/*" -Xmx512M com.ontologycentral.estatwrap.SDMXParser "$@"