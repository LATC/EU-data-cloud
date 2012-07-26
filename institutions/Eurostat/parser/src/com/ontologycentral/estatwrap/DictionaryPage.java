package com.ontologycentral.estatwrap;

import java.io.IOException;
import java.io.Reader;
import java.text.SimpleDateFormat;
import java.util.Calendar;
import java.util.List;
import javax.xml.stream.XMLStreamException;
import javax.xml.stream.XMLStreamWriter;

public class DictionaryPage {
	
	public static SimpleDateFormat ISO8601 = new SimpleDateFormat("yyyy-MM-dd'T'HH:mm:ssZ");
	
	public static void convert(XMLStreamWriter ch, String id, List<Reader> rs, String[] langs) throws XMLStreamException, IOException {
		ch.writeStartDocument("utf-8", "1.0");

		ch.writeStartElement("rdf:RDF");
		ch.writeAttribute("xml:base", "http://eurostat.linked-statistics.org/");
		ch.writeDefaultNamespace(Data.PREFIX);
		ch.writeNamespace("rdf", "http://www.w3.org/1999/02/22-rdf-syntax-ns#");
		ch.writeNamespace("rdfs", "http://www.w3.org/2000/01/rdf-schema#");
		ch.writeNamespace("owl", "http://www.w3.org/2002/07/owl#");
		ch.writeNamespace("foaf", "http://xmls.com/foaf/0.1/");
		ch.writeNamespace("skos", "http://www.w3.org/2004/02/skos/core#");
		ch.writeNamespace("dcterms", "http://purl.org/dc/terms/");
		
		ch.writeStartElement("rdf:Description");
		ch.writeAttribute("rdf:about", "");
		ch.writeStartElement("rdfs:comment");
		ch.writeCharacters("Reused Eurostat Linked Data Wrapper (http://estatwrap.ontologycentral.com/) to rdfize Eurostat datasets (http://epp.eurostat.ec.europa.eu/) .");
		ch.writeEndElement();
		ch.writeStartElement("foaf:maker");
		ch.writeAttribute("rdf:resource", "http://harth.org/andreas/foaf#ah");
		ch.writeEndElement();
		ch.writeStartElement("rdfs:seeAlso");
		ch.writeAttribute("rdf:resource", "http://epp.eurostat.ec.europa.eu/portal/page/portal/about_eurostat/policies/copyright_licence_policy");
		ch.writeEndElement();
		ch.writeStartElement("rdfs:seeAlso");
		ch.writeAttribute("rdf:resource", "http://eurostat.linked-statistics.org/");
		ch.writeEndElement();
		
		Calendar cal = Calendar.getInstance();
		ch.writeStartElement("dcterms:modified");
		ch.writeCharacters(ISO8601.format(cal.getTime()));
		ch.writeEndElement();
		
		ch.writeStartElement("dcterms:source");
		ch.writeAttribute("rdf:resource","http://epp.eurostat.ec.europa.eu/NavTree_prod/everybody/BulkDownloadListing?file=dic%2Fen%2F" + id);
		ch.writeEndElement();
		
		ch.writeEndElement();

        Dictionary d = null;
        
        for (int i = 0; i < rs.size(); i++) {
        	Reader r = rs.get(i);
        	String lang = langs[i];
        	
        	if ("geo".equals(id)) {
        		d = new DictionaryGeo(r);
        	} else if ("unit".equals(id)) {
        		d = new DictionaryUnits(r);
        	} else if ("nace".equals(id)) {
        		d = new DictionaryNace(r);
        	} else {
        		d = new Dictionary(r);            	
        	}

        	d.convert(ch, lang,id);
        }
        
        // generate conceptscheme for the dictionary
        ch.writeStartElement("skos:ConceptScheme");
        ch.writeAttribute("rdf:about", Dictionary.PREFIX + id.substring(0,id.indexOf(".dic")) + "#");
        for(String concept:d.lstConcepts) {
        	ch.writeStartElement("skos:hasTopConcept");
        	ch.writeAttribute("rdf:resource", concept);
        	ch.writeEndElement();
        }
        ch.writeStartElement("skos:notation");
        ch.writeCharacters(id.substring(0,id.indexOf(".dic")));
        ch.writeEndElement();
        ch.writeEndElement();
        
        ch.writeEndElement();
        ch.writeEndDocument();
	}
}
