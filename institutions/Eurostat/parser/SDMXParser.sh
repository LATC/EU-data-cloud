#!/bin/bash
java -cp "./build:./lib/*" -Xmx256M com.ontologycentral.estatwrap.SDMXParser "$@"