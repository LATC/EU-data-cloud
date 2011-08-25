package com.ontologycentral.estatwrap;

import java.io.IOException;
import java.io.Reader;
import java.util.HashMap;
import java.util.Map;
import java.util.logging.Logger;

import javax.xml.stream.XMLStreamException;
import javax.xml.stream.XMLStreamWriter;

public class DictionaryUnits extends Dictionary {
	public static String PREFIX = "/dic/";
	
	Logger _log = Logger.getLogger(this.getClass().getName());

	Map<String, String> _map;
	
	public DictionaryUnits(Reader is) throws IOException {
		super(is);
		_map = new HashMap<String, String>();
		
		_map.put("1000", "Thousand");
		_map.put("MIO", "Million");
		_map.put("T", "Tonne");
		_map.put("KG", "Kilogram");
		_map.put("GR", "Gram");
		_map.put("LT", "Litre");
		_map.put("OZ", "Ounce");
		_map.put("MN", "Minute");
		_map.put("HOUR", "Hour");
		_map.put("DAY", "Day");
		_map.put("MONTH", "Month");
		_map.put("YEAR", "Year");
		_map.put("ECU", "European_Currency_Unit");
		_map.put("EUR", "Euro");
	}

	public void addMappings(XMLStreamWriter out, String id) throws IOException, XMLStreamException {
		if (_map.containsKey(id)) {
			out.writeStartElement("owl:sameAs");
			out.writeAttribute("rdf:resource", "http://dbpedia.org/resource/" + id);				
			out.writeEndElement();			
		}
	}
}