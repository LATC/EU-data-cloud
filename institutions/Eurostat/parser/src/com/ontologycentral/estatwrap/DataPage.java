package com.ontologycentral.estatwrap;

import java.io.IOException;
import java.io.Reader;
import java.text.SimpleDateFormat;
import java.util.Calendar;
import java.util.Map;

import javax.xml.stream.XMLStreamException;
import javax.xml.stream.XMLStreamWriter;

public class DataPage {

	public static SimpleDateFormat ISO8601 = new SimpleDateFormat("yyyy-MM-dd'T'HH:mm:ssZ");
	
	public static void convert(XMLStreamWriter ch, String id, Reader in, Reader in1, String freq, String datasetID, String logPath) throws XMLStreamException, IOException {
		ch.writeStartDocument("utf-8", "1.0");

		ch.writeStartElement("rdf:RDF");
		ch.writeDefaultNamespace(Data.PREFIX);
		ch.writeNamespace("rdf", "http://www.w3.org/1999/02/22-rdf-syntax-ns#");
		ch.writeNamespace("rdfs", "http://www.w3.org/2000/01/rdf-schema#");
		ch.writeNamespace("foaf", "http://xmls.com/foaf/0.1/");
		ch.writeNamespace("qb", "http://purl.org/linked-data/cube#");
		ch.writeNamespace("sdmx-measure", "http://purl.org/linked-data/sdmx/2009/measure#");
		ch.writeNamespace("sdmx-dimension", "http://purl.org/linked-data/sdmx/2009/dimension#");
		ch.writeNamespace("property", "http://eurostat.linked-statistics.org/property#");
		ch.writeNamespace("dcterms", "http://purl.org/dc/terms/");
		
		ch.writeStartElement("rdf:Description");
		ch.writeAttribute("rdf:about", "");
		ch.writeStartElement("foaf:maker");
		ch.writeAttribute("rdf:resource", "http://harth.org/andreas/foaf#ah");
		ch.writeEndElement();
		Calendar cal = Calendar.getInstance();
		ch.writeStartElement("dcterms:date");
		ch.writeCharacters(ISO8601.format(cal.getTime()));
		ch.writeEndElement();
		ch.writeEndElement();

		ch.writeStartElement("qb:DataSet");
		//ch.writeAttribute("rdf:about", "/id/" + id  + "#ds");    		
		ch.writeAttribute("rdf:about", "/data/" + id); 
		
//--//		if (toc.containsKey(id)) {
//--//			ch.writeStartElement("rdfs:label");
//--//			ch.writeCharacters(toc.get(id));
//--//			ch.writeEndElement();
//--//		}
		
		ch.writeStartElement("rdfs:comment");
		ch.writeCharacters("Source: Eurostat (http://epp.eurostat.ec.europa.eu/) via Linked Eurostat (http://estatwrap.ontologycentral.com/).");
		ch.writeEndElement();
		ch.writeStartElement("rdfs:seeAlso");
		ch.writeAttribute("rdf:resource", "http://epp.eurostat.ec.europa.eu/portal/page/portal/about_eurostat/corporate/copyright_licence_policy");
		ch.writeEndElement();
		ch.writeStartElement("rdfs:seeAlso");
		ch.writeAttribute("rdf:resource", "http://ontologycentral.com/2009/01/eurostat/");
		ch.writeEndElement();

		ch.writeStartElement("qb:structure");
		//ch.writeAttribute("rdf:resource", "../dsd/" + id + "#dsd");
		ch.writeAttribute("rdf:resource", "../dsd/" + id);
		ch.writeEndElement();
		
		ch.writeEndElement();
		
		Data d = new Data(in);
		d.getObservationType(in1);
		d.convert(ch, id, freq, datasetID, logPath);
		
		//d.getObservationType(in);
		//Data d1 = new Data(in);
		//d.getObservationType(in1);
        //d.convert(ch, id, freq);
        
        ch.writeEndElement();
        ch.writeEndDocument();
	}

}
