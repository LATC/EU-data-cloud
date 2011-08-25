package com.ontologycentral.estatwrap;

import java.io.IOException;
import java.io.Reader;
import java.util.HashMap;
import java.util.Map;
import java.util.logging.Logger;

import javax.xml.stream.XMLStreamException;
import javax.xml.stream.XMLStreamWriter;

public class DictionaryNace extends Dictionary {
	public static String PREFIX = "/dic/";
	
	Logger _log = Logger.getLogger(this.getClass().getName());

	Map<String, String> _map;
	
	public DictionaryNace(Reader is) throws IOException {
		super(is);
	}

	public void addMappings(XMLStreamWriter out, String id) throws IOException, XMLStreamException {
		out.writeStartElement("owl:sameAs");
		out.writeAttribute("rdf:resource", "http://rdfdata.eionet.europa.eu/eurostatdic/nace/" + id);				
		out.writeEndElement();
	}
}