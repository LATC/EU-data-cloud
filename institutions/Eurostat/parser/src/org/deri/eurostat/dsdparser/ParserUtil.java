package org.deri.eurostat.dsdparser;

import com.hp.hpl.jena.rdf.model.Model;
import com.hp.hpl.jena.rdf.model.ModelFactory;
import com.hp.hpl.jena.rdf.model.Property;

/**
 * 
 * @author Aftab Iqbal
 *
 */
public class ParserUtil {

	public static String rdfs = "http://www.w3.org/2000/01/rdf-schema#";
	public static String skos = "http://www.w3.org/2004/02/skos/core#";
	public static String qb = "http://purl.org/linked-data/cube#";
	public static String sdmx_concept = "http://purl.org/linked-data/sdmx/2009/concept#";
	public static String concepts = "http://eurostat.linked-statistics.org/concept#";
	public static String property = "http://eurostat.linked-statistics.org/property#";
	//-//public static String cl = "http://eurostat.linked-statistics.org/CodeList/";
	//public static String cl = "http://eurostat.linked-statistics.org/dic/";
	public static String rdf = "http://www.w3.org/1999/02/22-rdf-syntax-ns#";
	public static String dcterms = "http://purl.org/dc/terms/";
	public static String sdmx_code = "http://purl.org/linked-data/sdmx/2009/code#";
	public static String sdmx_measure = "http://purl.org/linked-data/sdmx/2009/measure#";
	public static String sdmx_dimension = "http://purl.org/linked-data/sdmx/2009/dimension#";
	public static String xsd = "http://www.w3.org/2001/XMLSchema#";
	public static String sdmxURI = "http://purl.org/linked-data/sdmx#";
	
	public static String voidURI = "http://rdfs.org/ns/void#";
	public static String dssURI = "http://eurostat.linked-statistics.org/dss#";
	public static String titleURI = "http://eurostat.linked-statistics.org/title#";
	public static String dsdURI = "http://eurostat.linked-statistics.org/dsd/";
	public static String dicURI = "http://eurostat.linked-statistics.org/dic/";
	public static String dataURI = "http://eurostat.linked-statistics.org/data/";
	public static String baseURI = "http://eurostat.linked-statistics.org/";
	
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
	public static Property dcTitle;
	public static Property sdmxConcept;
	public static Property sdmx;

	public static Property qbDataset;
	public static Property voidDataset;
	public static Property dataDump;
	public static Property subset;
	public static Property qb_structure;
	
	public static Model getModelProperties()
	{
		Model m = ModelFactory.createDefaultModel();
		
		dsd = m.createProperty(qb + "DataStructureDefinition");
		qb_structure = m.createProperty(qb + "structure");
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
		dcTitle = m.createProperty(dcterms + "title");
		sdmxConcept = m.createProperty(sdmx_concept + "Concept");
		sdmx = m.createProperty(sdmxURI + "Concept");
		
		qbDataset = m.createProperty(qb + "DataSet");
		voidDataset = m.createProperty(voidURI + "Dataset");
		dataDump = m.createProperty(voidURI + "dataDump");
		subset = m.createProperty(voidURI + "subset");
		
		m.setNsPrefix("skos", skos);
		m.setNsPrefix("qb", qb);
		m.setNsPrefix("rdfs",rdfs);
		m.setNsPrefix("sdmx-concept", sdmx_concept);
		m.setNsPrefix("sdmx", sdmxURI);
		m.setNsPrefix("concept", concepts);
		m.setNsPrefix("property", property);
		m.setNsPrefix("sdmx-measure", sdmx_measure);
		m.setNsPrefix("sdmx-dimension", sdmx_dimension);
		m.setNsPrefix("dic", dicURI);
		m.setNsPrefix("sdmx-code", sdmx_code);
		m.setNsPrefix("rdf", rdf);
		m.setNsPrefix("dcterms", dcterms);
		m.setNsPrefix("xsd", xsd);
		m.setNsPrefix("void", voidURI);
		m.setNsPrefix("dss", dssURI);
		m.setNsPrefix("dsd", dsdURI);
		m.setNsPrefix("data", dataURI);
		m.setNsPrefix("title", titleURI);
		return m;
	}
}
