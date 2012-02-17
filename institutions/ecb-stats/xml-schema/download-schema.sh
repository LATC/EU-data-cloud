#!/usr/bin/bash
curl http://sdw-ws.ecb.europa.eu/OrganisationScheme/ > OrganisationScheme.xml
curl http://sdw-ws.ecb.europa.eu/Dataflow/ >  Dataflow.xml
curl http://sdw-ws.ecb.europa.eu/Metadataflow/ > Metadataflow.xml
curl http://sdw-ws.ecb.europa.eu/CategoryScheme/ > CategoryScheme.xml 
curl http://sdw-ws.ecb.europa.eu/CodeList/ > CodeList.xml 
curl http://sdw-ws.ecb.europa.eu/Concept/ > Concept.xml 
curl http://sdw-ws.ecb.europa.eu/MetadataStructureDefinition/ > MetadataStructureDefinition.xml
curl http://sdw-ws.ecb.europa.eu/KeyFamily/ > KeyFamily.xml
curl http://sdw-ws.ecb.europa.eu/StructureSet/ > StructureSet.xml
curl http://sdw-ws.ecb.europa.eu/ReportingTaxonomy/ > ReportingTaxonomy.xml
curl http://sdw-ws.ecb.europa.eu/Process/ > Process.xml

