package org.deri.eurostat.dsdParser;

import java.io.BufferedWriter;
import java.io.FileWriter;
import java.io.IOException;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.Iterator;
import java.util.Map;

import javax.xml.parsers.DocumentBuilderFactory;
import javax.xml.parsers.ParserConfigurationException;
import javax.xml.xpath.XPath;
import javax.xml.xpath.XPathFactory;

import org.deri.eurostat.DataModel.DataStoreModel;
import org.deri.eurostat.elements.*;
import org.w3c.dom.Document;
import org.w3c.dom.Element;
import org.w3c.dom.NodeList;
import org.xml.sax.SAXException;

/*
 * Todo : should we use sdmx-concept instead of concept where code lists are from SDMX agency ? Because the codelists
 * which are from SDMX dont have their concepts defined in the Concept heirarchy.
 * How we identify the rdfs:range for those components who has no associated codelist ? e.g. how we decide to  
 * associate 'xsd:date' or 'xsd:double' to a component named TIME_PERIOD . If there are only few components then can 
 * we hard-code their rdfs:range in the program ?
 */
public class dsdParser {

    private Document xmlDocument;
    private XPath xPath;	
    private static String xmlFilePath = "E:/EU Projects/EuroStat/tsieb010.sdmx/tsieb010.dsd.xml";
    private static String outputFilePath = "E:/EU Projects/EuroStat/datacube mapping/RDF/";
    ArrayList<Code> lstCode = new ArrayList<Code>();
    ArrayList<Concept> lstConcepts = new ArrayList<Concept>();
    ArrayList<CodeList> lstCodeLists = new ArrayList<CodeList>();
    ArrayList<Dimension> lstDimensions = new ArrayList<Dimension>();
    ArrayList<Dimension> lstTimeDimensions = new ArrayList<Dimension>();
    ArrayList<Attribute> lstAttributes = new ArrayList<Attribute>();
    ArrayList<Measure> lstMeasures = new ArrayList<Measure>();
    static BufferedWriter write = null;
	static FileWriter fstream = null;
	String fileName = "";
	String codeListURL = "http://example.org/EuroStat/";
	static DataStoreModel dsModel;
	public final String baseURI = "http://purl.org/linked-data/sdmx#";
	
	public String getCodeList(String codeList)
	{
		dsModel = new DataStoreModel();
		dsModel.addRDFtoDataModel("sdmx-code/sdmx-code.ttl", baseURI, "TURTLE");
		return dsModel.returnCodeListURI(codeList);
		
	}
	
    private void initObjects(){        
        try {
            xmlDocument = DocumentBuilderFactory.
			newInstance().newDocumentBuilder().
			parse(xmlFilePath);            
            xPath =  XPathFactory.newInstance().
			newXPath();
        } catch (IOException ex) {
            ex.printStackTrace();
        } catch (SAXException ex) {
            ex.printStackTrace();
        } catch (ParserConfigurationException ex) {
            ex.printStackTrace();
        }       
    }

    
	public void parseFile()
	{
		Element element = xmlDocument.getDocumentElement();
		NodeList nl;
		getFileName(element);
		
		// parse CodeLists from DSD
		nl = element.getElementsByTagName("CodeLists");
		
		if(nl != null && nl.getLength() > 0)
		{
			for(int i = 0 ; i < nl.getLength();i++)
			{
				Element ele = (Element)nl.item(i);
				
				getAllCodeLists(ele);
				
			}
		}
		
		// parse KeyFamilies from DSD
		nl = element.getElementsByTagName("KeyFamilies");
		if(nl != null && nl.getLength() > 0)
		{
			for(int i = 0 ; i < nl.getLength();i++)
			{
				Element ele = (Element)nl.item(i);
				
				getKeyFamilies(ele);
				
			}
		}
		
		
		// parse Concepts from DSD
		nl = element.getElementsByTagName("Concepts");
		if(nl != null && nl.getLength() > 0)
		{
			for(int i = 0 ; i < nl.getLength();i++)
			{
				Element ele = (Element)nl.item(i);
				
				getConcepts(ele);
				
			}
		}
		
		writeDatatoFile();
	}

	public void getConcepts(Element element)
	{
		NodeList nl = element.getElementsByTagName("structure:ConceptScheme");
		if(nl != null && nl.getLength() > 0)
		{
			for(int i = 0 ; i < nl.getLength();i++)
			{
				Element key = (Element)nl.item(i);
				getConceptInfo(key);
			}
		}
	}
	
	public void getConceptInfo(Element ele)
	{
		HashMap<String, String> hshName = new HashMap<String, String>();
		
		NodeList concept = ele.getElementsByTagName("structure:Concept");
		
		if(concept != null && concept.getLength() > 0)
		{
			for(int i = 0 ; i < concept.getLength();i++)
			{
				hshName = new HashMap<String, String>();
				Element con = (Element)concept.item(i);
				//System.out.println("ID : " + con.getAttribute("id"));
				NodeList lst = con.getElementsByTagName("structure:Name");
				
				for(int j = 0 ; j < lst.getLength() ; j++)
				{
					Element desc = (Element)lst.item(j);
					//System.out.println(desc.getAttribute("xml:lang") + " -- " +  desc.getTextContent());
					hshName.put(desc.getAttribute("xml:lang"), desc.getTextContent());
				}
				
				Concept obj = new Concept(con.getAttribute("id"),hshName);
				lstConcepts.add(obj);
			}
		}
	}
	
	public void getFileName(Element element)
	{
		NodeList nl = element.getElementsByTagName("Header");
		Element ele = (Element)nl.item(0);
		NodeList name = ele.getElementsByTagName("ID");
		
		fileName = name.item(0).getTextContent();

	}
	
	public void getKeyFamilies(Element ele)
	{
		NodeList nl = ele.getElementsByTagName("structure:KeyFamily");
		if(nl != null && nl.getLength() > 0)
		{
			for(int i = 0 ; i < nl.getLength();i++)
			{
				Element key = (Element)nl.item(i);
				getKeyFamilyInfo(key);
			}
		}
	}
	
	public void getKeyFamilyInfo(Element key)
	{
		NodeList name = key.getElementsByTagName("structure:Name");
		//KeyFamily obj = new KeyFamily(key.getAttribute("id"),key.getAttribute("agencyID"),key.getAttribute("isFinal"),name.item(0).getTextContent(),key.getAttribute("isExternalReference"));
		
		getComponents(key);
	}
	
	public void getComponents(Element key)
	{
		NodeList name = key.getElementsByTagName("structure:Components");
		
		Element comp = (Element)name.item(0);

		// Dimension
		NodeList dimension = comp.getElementsByTagName("structure:Dimension");
		if(dimension != null && dimension.getLength() > 0)
		{
			for(int i = 0 ; i < dimension.getLength();i++)
			{
				Element dim = (Element)dimension.item(i);
				Dimension obj = new Dimension(dim.getAttribute("conceptRef"),dim.getAttribute("conceptSchemeRef"),dim.getAttribute("codelist"), getType(dim));
				lstDimensions.add(obj);
				
				/*
				NodeList lstType = dim.getElementsByTagName("structure:TextFormat");
				if(lstType != null && lstType.getLength() > 0)
				{
					Element type = (Element) lstType.item(0);
					System.out.println("type : " + type.getAttribute("textType"));
				}
				*/
			}
		}

		// TimeDimension
		NodeList tDimension = comp.getElementsByTagName("structure:TimeDimension");
		if(tDimension != null && tDimension.getLength() > 0)
		{
			for(int i = 0 ; i < tDimension.getLength();i++)
			{
				Element measure = (Element)tDimension.item(i);
				Dimension obj = new Dimension(measure.getAttribute("conceptRef"),measure.getAttribute("conceptSchemeRef"),measure.getAttribute("codelist"), getType(measure));
				lstTimeDimensions.add(obj);
				/*
				NodeList lstType = measure.getElementsByTagName("structure:TextFormat");
				if(lstType != null && lstType.getLength() > 0)
				{
					Element type = (Element) lstType.item(0);
					System.out.println("type : " + type.getAttribute("textType"));
				}
				*/
			}
		}

		// PrimaryMeasure
		NodeList pMeasure = comp.getElementsByTagName("structure:PrimaryMeasure");
		if(pMeasure != null && pMeasure.getLength() > 0)
		{
			for(int i = 0 ; i < pMeasure.getLength();i++)
			{
				Element measure = (Element)pMeasure.item(i);
				Measure obj = new Measure(measure.getAttribute("conceptRef"),measure.getAttribute("conceptSchemeRef"),measure.getAttribute("codelist"),getType(measure));
				lstMeasures.add(obj);
			}
		}

		// Attribute
		NodeList attribute = comp.getElementsByTagName("structure:Attribute");
		if(attribute != null && attribute.getLength() > 0)
		{
			for(int i = 0 ; i < attribute.getLength();i++)
			{
				Element att = (Element)attribute.item(i);
				Attribute obj = new Attribute(att.getAttribute("conceptRef"),att.getAttribute("conceptSchemeRef"),att.getAttribute("codelist"),"","", getType(att));
				lstAttributes.add(obj);
			}
		}
		
	}
	
	public String getType(Element ele)
	{
		NodeList lstType = ele.getElementsByTagName("structure:TextFormat");
		if(lstType != null && lstType.getLength() > 0)
		{
			Element type = (Element) lstType.item(0);
			//System.out.println("type : " + type.getAttribute("textType"));
			return type.getAttribute("textType");
		}
		else
			return "";
	
	}
	
	public void getAllCodeLists(Element ele)
	{
		NodeList nl = ele.getElementsByTagName("structure:CodeList");
		
		if(nl != null && nl.getLength() > 0)
		{
			for(int i = 0 ; i < nl.getLength();i++)
			{
				Element code = (Element)nl.item(i);
				getCodeListInfo(code);
			}
		}

		
	}
	
	public void getCodeListInfo(Element code)
	{
		HashMap<String, String> hshName = new HashMap<String, String>();
		
		NodeList name = code.getElementsByTagName("structure:Name");
		for(int j = 0 ; j < name.getLength() ; j++)
		{
			Element desc = (Element)name.item(j);
			hshName.put(desc.getAttribute("xml:lang"), desc.getTextContent());
		}
		
		getCodes(code);
		
		CodeList obj = new CodeList(code.getAttribute("id"),code.getAttribute("agencyID"),code.getAttribute("isFinal"),hshName,lstCode);
		lstCodeLists.add(obj);
		
	}
	
	public void getCodes(Element codes)
	{
		lstCode = new ArrayList<Code>();
		
		
		NodeList name = codes.getElementsByTagName("structure:Code");
		
		if(name != null && name.getLength() > 0)
		{
			for(int i = 0 ; i < name.getLength();i++)
			{
				HashMap<String, String> hshDescription = new HashMap<String, String>();
				
				Element ele = (Element)name.item(i);
				
				NodeList code = ele.getElementsByTagName("structure:Description");
				
				for(int j = 0 ; j < code.getLength() ; j++)
				{
					Element desc = (Element)code.item(j);
					hshDescription.put(desc.getAttribute("xml:lang"), desc.getTextContent());
				}
				
				Code obj = new Code(ele.getAttribute("value"),hshDescription);
				//obj.setDescription(code.item(0).getTextContent());
				//obj.setValue(ele.getAttribute("value"));
				lstCode.add(obj);
			}
		}
		
	}
	
	
	public void writeDatatoFile()
	{
		String codeListID = "";
		int counter = 1;
		String name;
		HashMap<String, String> hshName = new HashMap<String, String>();
		
		createRDFFile(fileName);
		
		writeLinetoFile("@prefix rdfs: <http://www.w3.org/2000/01/rdf-schema#> .");
		writeLinetoFile("@prefix skos: <http://www.w3.org/2004/02/skos#> .");
		writeLinetoFile("@prefix qb: <http://purl.org/linked-data/cube#> .");
		writeLinetoFile("@prefix sdmx: <http://purl.org/linked-data/sdmx#> .");
		writeLinetoFile("@prefix sdmx-concept: <http://purl.org/linked-data/sdmx/2009/concept#> .");
		writeLinetoFile("@prefix xsd: <http://www.w3.org/2001/XMLSchema#> .");
		writeLinetoFile("@prefix sdmx-code: <http://purl.org/linked-data/sdmx/2009/code#> .");
		writeLinetoFile("@prefix concept: <http://example.org/EuroStat/concept#> .");
		writeLinetoFile("@prefix property: <http://example.org/EuroStat/property#> .");
		writeLinetoFile("@prefix cl: <http://example.org/EuroStat/CodeList/> .");
		
		// define DataStructureDefinition based on the components identified in the 'KeyFamilies' tag of DSD
		writeLinetoFile("<" + codeListURL + "dsd/" + fileName.substring(0,fileName.indexOf("_")) + ">	a qb:DataStrucutreDefinition;");
		writeLinetoFile("		skos:notation \"" + fileName + "\";");
		
		for(Dimension dim:lstDimensions)
			writeLinetoFile("		qb:component [qb:dimension	property:" + dim.getConceptRef() + "	qb:order " + counter++ + "];");
		
		for(Dimension dim:lstTimeDimensions)
			writeLinetoFile("		qb:component [qb:dimension	property:" + dim.getConceptRef() + "	qb:order " + counter++ + "];");
		
		for(Measure measure:lstMeasures)
			writeLinetoFile("		qb:component [qb:measure	property:" + measure.getConceptRef() + "];");
		
		for(Attribute att:lstAttributes)
			writeLinetoFile("		qb:component [qb:attribute	property:" + att.getConceptRef() + "];");
		
		// there is an extra ; before '.'. Fix this issue
		writeLinetoFile("		.");
		
		for(Dimension dim:lstDimensions)
		{
			writeLinetoFile("property:" + dim.getConceptRef() + " a qb:DimensionProperty, qb:CodedProperty;");
			writeLinetoFile("		rdfs:domain		qb:Observation;");
			writeLinetoFile("		qb:concept		concept:" + dim.getConceptRef() + ";");
			
			if(!dim.getCodeList().equals(""))
			{
				for(CodeList obj:lstCodeLists)
				{
					if(obj.getId().toString().equals(dim.getCodeList().toString()))
					{
						if(obj.getAgencyID().equals("SDMX"))
						{
							// re-use the URI from sdmx-code.ttl file
							String codeList = getCodeList(obj.getId());
							writeLinetoFile("		qb:codeList		sdmx-code:" + codeList + ";");
							writeLinetoFile("		rdfs:range		sdmx-code:" + codeList);
						}
						else
						{
							writeLinetoFile("		qb:codeList		cl:" + obj.getId().substring(obj.getId().indexOf("_")+1) + ";");
							writeLinetoFile("		rdfs:range		cl:" + obj.getId().substring(obj.getId().indexOf("_")+1));
						}
					}
				}
			}
			else
				writeLinetoFile("		rdfs:range		xsd:" + dim.getDataType().toLowerCase());
//			for(CodeList obj:lstCodeLists)
//			{
//				if(obj.getId().toString().equals(dim.getCodeList().toString()))
//				{
//					if(obj.getAgencyID().equals("SDMX"))
//					{
//						// re-use the URI from sdmx-code.ttl file
//						String codeList = getCodeList(obj.getId());
//						writeLinetoFile("		qb:codeList		sdmx-code:" + codeList + ";");
//						writeLinetoFile("		rdfs:range		sdmx-code:" + codeList);
//					}
//					else
//					{
//						writeLinetoFile("		qb:codeList		cl:" + obj.getId().substring(obj.getId().indexOf("_")+1) + ";");
//						writeLinetoFile("		rdfs:range		cl:" + obj.getId().substring(obj.getId().indexOf("_")+1));
//					}
//				}
//			}
			writeLinetoFile("		.");
		}
		
		for(Dimension dim:lstTimeDimensions)
		{
			writeLinetoFile("property:" + dim.getConceptRef() + " a qb:DimensionProperty;");
			writeLinetoFile("		rdfs:domain		qb:Observation;");
			writeLinetoFile("		qb:concept		concept:" + dim.getConceptRef() + ";");
			// TODO : define rdfs:range of type xsd:date
			
			if(!dim.getCodeList().equals(""))
			{
				for(CodeList obj:lstCodeLists)
				{
					if(obj.getId().toString().equals(dim.getCodeList().toString()))
					{
						if(obj.getAgencyID().equals("SDMX"))
						{
							// re-use the URI from sdmx-code.ttl file
							String codeList = getCodeList(obj.getId());
							writeLinetoFile("		qb:codeList		sdmx-code:" + codeList + ";");
							writeLinetoFile("		rdfs:range		sdmx-code:" + codeList);
						}
						else
						{
							writeLinetoFile("		qb:codeList		cl:" + obj.getId().substring(obj.getId().indexOf("_")+1) + ";");
							writeLinetoFile("		rdfs:range		cl:" + obj.getId().substring(obj.getId().indexOf("_")+1));
						}
					}
				}
			}
			else
				writeLinetoFile("		rdfs:range		xsd:" + dim.getDataType().toLowerCase());
			
			writeLinetoFile("		.");
		}
		
		for(Measure measure:lstMeasures)
		{
			writeLinetoFile("property:" + measure.getConceptRef() + " a qb:MeasureProperty, qb:CodedProperty;");
			writeLinetoFile("		rdfs:domain		qb:Observation;");
			writeLinetoFile("		qb:concept		concept:" + measure.getConceptRef() + ";");
			// TODO : define rdfs:range of the datatype used
			
			if(!measure.getCodeList().equals(""))
			{
				for(CodeList obj:lstCodeLists)
				{
					if(obj.getId().toString().equals(measure.getCodeList().toString()))
					{
						if(obj.getAgencyID().equals("SDMX"))
						{
							// re-use the URI from sdmx-code.ttl file
							String codeList = getCodeList(obj.getId());
							writeLinetoFile("		qb:codeList		sdmx-code:" + codeList + ";");
							writeLinetoFile("		rdfs:range		sdmx-code:" + codeList);
						}
						else
						{
							writeLinetoFile("		qb:codeList		cl:" + obj.getId().substring(obj.getId().indexOf("_")+1) + ";");
							writeLinetoFile("		rdfs:range		cl:" + obj.getId().substring(obj.getId().indexOf("_")+1));
						}
					}
				}
			}
			else
				writeLinetoFile("		rdfs:range		xsd:" + measure.getDataType().toLowerCase());
			
			writeLinetoFile("		.");
		}
		
		for(Attribute att:lstAttributes)
		{
			writeLinetoFile("property:" + att.getConceptRef() + " a qb:AttributeProperty, qb:CodedProperty;");
			writeLinetoFile("		rdfs:domain		qb:Observation;");
			writeLinetoFile("		qb:concept		concept:" + att.getConceptRef() + ";");
			
			if(!att.getCodeList().equals(""))
			{
				for(CodeList obj:lstCodeLists)
				{
					if(obj.getId().toString().equals(att.getCodeList().toString()))
					{
						if(obj.getAgencyID().equals("SDMX"))
						{
							// re-use the URI from sdmx-code.ttl file
							String codeList = getCodeList(obj.getId());
							writeLinetoFile("		qb:codeList		sdmx-code:" + codeList + ";");
							writeLinetoFile("		rdfs:range		sdmx-code:" + codeList);
						}
						else
						{
							writeLinetoFile("		qb:codeList		cl:" + obj.getId().substring(obj.getId().indexOf("_")+1) + ";");
							writeLinetoFile("		rdfs:range		cl:" + obj.getId().substring(obj.getId().indexOf("_")+1));
						}
					}
				}
			}
			else
				writeLinetoFile("		rdfs:range		xsd:" + att.getDataType().toLowerCase());
//			for(CodeList obj:lstCodeLists)
//			{
//				if(obj.getId().toString().equals(att.getCodeList().toString()))
//				{
//					if(obj.getAgencyID().equals("SDMX"))
//					{
//						// re-use the URI from sdmx-code.ttl file
//						String codeList = getCodeList(obj.getId());
//						writeLinetoFile("		qb:codeList		sdmx-code:" + codeList + ";");
//						writeLinetoFile("		rdfs:range		sdmx-code:" + codeList);
//					}
//					else
//					{
//						writeLinetoFile("		qb:codeList		cl:" + obj.getId().substring(obj.getId().indexOf("_")+1) + ";");
//						writeLinetoFile("		rdfs:range		cl:" + obj.getId().substring(obj.getId().indexOf("_")+1));
//					}
//				}
//			}
			writeLinetoFile("		.");
		}
		
		// translate all codelists from DSD which are defined by agencies other than SDMX.
		for(CodeList obj:lstCodeLists)
		{
			
			if(!obj.getAgencyID().equals("SDMX"))
			{
				codeListID = obj.getId().substring(obj.getId().indexOf("_")+1);
				writeLinetoFile("<" + codeListURL + "CodeList/" + codeListID + ">	a skos:ConceptScheme;");
				
				// print multilingual labels
				hshName = obj.gethshName();
				Iterator entrySetIterator = hshName.entrySet().iterator();
				while (entrySetIterator.hasNext())
				{
					Map.Entry entry = (Map.Entry) entrySetIterator.next();
		            String key = (String) entry.getKey();
		            name = hshName.get(key);
		            writeLinetoFile("		rdfs:label \"" + name + "\"@" + key + ";");
				}
				//writeLinetoFile("		rdfs:label \"" + obj.getName() + "\"@en;");
				
				writeLinetoFile("		skos:notation \"" + obj.getId() + "\";");
				
				ArrayList<Code> arrCode = obj.getCode();
				for(Code code:arrCode)
				{
					writeLinetoFile("		skos:hasTopConcept <" + codeListURL + "CodeList/" + codeListID + "#" + code.getValue() + ">;");
				}
				// there is an extra ; before '.'. Fix this issue
				writeLinetoFile("		.");
				
				for(Code code:arrCode)
				{
					writeLinetoFile("<" + codeListURL + "CodeList/" + codeListID + "#" + code.getValue() + ">	a skos:Concept;");
					
					// print multilingual labels
					hshName = code.gethshDescription();
					Iterator entryIterator = hshName.entrySet().iterator();
					while (entryIterator.hasNext())
					{
						Map.Entry entry = (Map.Entry) entryIterator.next();
			            String key = (String) entry.getKey();
			            name = hshName.get(key);
			            writeLinetoFile("		skos:prefLabel \"" + name + "\"@" + key + ";");
					}
					//writeLinetoFile("		skos:prefLabel \"" + code.getDescription() + "\"@en;");
					
					writeLinetoFile("		skos:inScheme <" + codeListURL + "CodeList/" + codeListID + ">;");
					writeLinetoFile("		skos:notation \"" + code.getValue() + "\"");
					writeLinetoFile("		.");
				}

			}
		}

		// translates concepts from DSD
		for(Concept concept:lstConcepts)
		{
			writeLinetoFile("<" + codeListURL + "concept#" + concept.getId() + ">	a sdmx:Concept;");
			writeLinetoFile("		skos:inScheme <" + codeListURL + "concept#>;");
			writeLinetoFile("		skos:notation \"" + concept.getId() + "\";");
			
			//print multilingual labels
			hshName = concept.gethshName();
			Iterator entrySetIterator = hshName.entrySet().iterator();
			while (entrySetIterator.hasNext())
			{
				Map.Entry entry = (Map.Entry) entrySetIterator.next();
	            String key = (String) entry.getKey();
	            name = hshName.get(key);
	            writeLinetoFile("		skos:prefLabel \"" + name + "\"@" + key + ";");
			}
			writeLinetoFile("		.");
		}
	}
	
	public static void writeLinetoFile(String line)
	{
		
	   	try{
	       	
	   		write.newLine();
	       	write.write(line);
	       	write.flush();
	       }
	       catch (Exception e){//Catch exception if any
	       	      System.err.println("Error: " + e.getMessage());
	       	}

	}
	public void createRDFFile(String fileName)
	{
		try
	   	{
			fstream = new FileWriter(outputFilePath + fileName + ".rdf",false);
			write = new BufferedWriter(fstream);
	   	}catch(Exception e)
	   	{
	   		System.out.println("Error while creating file ...");
	   	}
	}
	
	public static void main(String[] args)
	{
		dsdParser obj = new dsdParser();
		
		xmlFilePath = args[0];
		outputFilePath = args[1];
		
		obj.initObjects();
		obj.parseFile();
	}
}
