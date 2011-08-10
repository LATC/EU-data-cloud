package org.deri.eurostat.dsdParser;

import com.hp.hpl.jena.rdf.model.Model;
import com.hp.hpl.jena.rdf.model.ModelFactory;
import com.hp.hpl.jena.rdf.model.Property;

public class ParserUtil {

	public static String rdfs = "http://www.w3.org/2000/01/rdf-schema#";
	public static String skos = "http://www.w3.org/2004/02/skos#";
	public static String qb = "http://purl.org/linked-data/cube#";
	public static String sdmx_concept = "http://purl.org/linked-data/sdmx/2009/concept#";
	public static String concepts = "http://example.org/EuroStat/concepts#";
	public static String property = "http://example.org/EuroStat/property#";
	public static String cl = "http://example.org/EuroStat/CodeList/";
	public static String rdf = "http://www.w3.org/1999/02/22-rdf-syntax-ns#";
	public static String sdmx_code = "http://purl.org/linked-data/sdmx/2009/code#";
	public static String xsd = "http://www.w3.org/2001/XMLSchema#";
	public static String sdmxURI = "http://purl.org/linked-data/sdmx#";
	
	public static Property dsd;
	public static Property type;
	public static Property notation;
	public static Property conceptScheme;
	public static Property topConcept;
	public static Property skosConcept;
	public static Property skosLabel;
	public static Property skosScheme;
	public static Property component;
	public static Property dimension;
	public static Property attribute;
	public static Property measure;
	public static Property dimensionProperty;
	public static Property measureProperty;
	public static Property attributeProperty;
	public static Property codedProperty;
	public static Property observation;
	public static Property concept;
	public static Property codeList;
	public static Property rdfsDomain;
	public static Property rdfsLabel;
	public static Property rdfsRange;
	public static Property sdmxConcept;
	public static Property sdmx;
	
	public static Model getModelProperties()
	{
		Model m = ModelFactory.createDefaultModel();
		
		dsd = m.createProperty(qb + "DataStructureDefinition");
		type = m.createProperty(rdf + "type");
		notation = m.createProperty(skos + "notation");
		conceptScheme = m.createProperty(skos + "ConceptScheme");
		topConcept = m.createProperty(skos + "hasTopConcept");
		skosConcept = m.createProperty(skos + "Concept");
		skosLabel = m.createProperty(skos + "prefLabel");
		skosScheme = m.createProperty(skos + "inScheme");
		component = m.createProperty(qb + "component");
		dimension = m.createProperty(qb + "dimension");
		attribute = m.createProperty(qb + "attribute");
		measure = m.createProperty(qb + "measure");
		dimensionProperty = m.createProperty(qb + "DimensionProperty");
		measureProperty = m.createProperty(qb + "MeasureProperty");
		attributeProperty = m.createProperty(qb + "AttributeProperty");
		codedProperty = m.createProperty(qb + "CodedProperty");
		observation = m.createProperty(qb + "Observation");
		concept = m.createProperty(qb + "concept");
		codeList = m.createProperty(qb + "codeList");
		rdfsDomain = m.createProperty(rdfs + "domain");
		rdfsLabel = m.createProperty(rdfs + "label");
		rdfsRange = m.createProperty(rdfs + "range");
		sdmxConcept = m.createProperty(sdmx_concept + "Concept");
		sdmx = m.createProperty(sdmxURI + "Concept");
		
		m.setNsPrefix("skos", skos);
		m.setNsPrefix("qb", qb);
		m.setNsPrefix("rdfs",rdfs);
		m.setNsPrefix("sdmx-concept", sdmx_concept);
		m.setNsPrefix("sdmx", sdmxURI);
		m.setNsPrefix("concept", concepts);
		m.setNsPrefix("property", property);
		m.setNsPrefix("cl", cl);
		m.setNsPrefix("sdmx-code", sdmx_code);
		m.setNsPrefix("rdf", rdf);
		m.setNsPrefix("xsd", xsd);
		
		return m;
	}
}
