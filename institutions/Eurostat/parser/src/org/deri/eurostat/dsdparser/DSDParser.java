package org.deri.eurostat.dsdparser;

import java.io.BufferedWriter;
import java.io.FileOutputStream;
import java.io.FileWriter;
import java.io.IOException;
import java.io.InputStream;
import java.io.OutputStream;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.Iterator;
import java.util.Map;

import javax.xml.parsers.DocumentBuilderFactory;
import javax.xml.parsers.ParserConfigurationException;
import javax.xml.xpath.XPath;
import javax.xml.xpath.XPathFactory;

import org.apache.commons.cli.BasicParser;
import org.apache.commons.cli.CommandLine;
import org.apache.commons.cli.CommandLineParser;
import org.apache.commons.cli.Options;
import org.deri.eurostat.Main;
import org.deri.eurostat.datamodel.DataStoreModel;
import org.deri.eurostat.elements.*;

import org.deri.eurostat.elements.*;
import org.deri.eurostat.toc.DiffToC;
import org.openrdf.http.webclient.repository.modify.add.AddController;
import org.w3c.dom.Document;
import org.w3c.dom.Element;
import org.w3c.dom.NodeList;
import org.xml.sax.SAXException;

import com.hp.hpl.jena.rdf.model.Model;
import com.hp.hpl.jena.rdf.model.ModelFactory;
import com.hp.hpl.jena.rdf.model.Property;
import com.hp.hpl.jena.rdf.model.Resource;

/**
 * 
 * @author Aftab Iqbal
 *
 */

/*
 * Slovenia in french language is being written in different characters than represented in the DSD XML file. Check
 * Multi-Language issue in jena
 *  
 */
public class DSDParser {

    private Document xmlDocument;
    private XPath xPath;	
    //private static String xmlFilePath = "E:/EU Projects/EuroStat/tsiem010.sdmx/tsiem010.dsd.xml";
    public static String xmlFilePath = "";
    //private static String outputFilePath = "E:/EU Projects/EuroStat/datacube mapping/RDF/";
    public static String outputFilePath = "";
    //private static String outputFilePath = "C:/tempZip/dsd/";
    public static String serialization = "RDF/XML";
    public static String fileExt = ".rdf";
    ArrayList<Code> lstCode = new ArrayList<Code>();
    ArrayList<Concept> lstConcepts = new ArrayList<Concept>();
    ArrayList<CodeList> lstCodeLists = new ArrayList<CodeList>();
    ArrayList<Dimension> lstDimensions = new ArrayList<Dimension>();
    ArrayList<Dimension> lstTimeDimensions = new ArrayList<Dimension>();
    ArrayList<Attribute> lstAttributes = new ArrayList<Attribute>();
    ArrayList<Measure> lstMeasures = new ArrayList<Measure>();
    //static BufferedWriter write = null;
	//static FileWriter fstream = null;
	String fileName = "";
	String baseURI = "http://eurostat.linked-statistics.org/";
	static DataStoreModel dsModel;
	public final String base_uri = "http://purl.org/linked-data/sdmx#";
	public static String sdmx_codeFilePath = "";
	String obsValue = "";
	String freq = "";
	String timePeriod = "";
	
	
	public void addSDMXCodeList()
	{
		dsModel = new DataStoreModel();
		dsModel.addRDFtoDataModel(sdmx_codeFilePath, base_uri, "TURTLE");
	}

	public String getCodeList(String codeList)
	{
		return dsModel.returnCodeListURI(codeList);
	}

	// old code. this function is called multiple times and hence we are adding rdf file multiple times.
//	public String getCodeList(String codeList)
//	{
//		dsModel = new DataStoreModel();
//		dsModel.addRDFtoDataModel(sdmx_codeFilePath, base_uri, "TURTLE");
//		return dsModel.returnCodeListURI(codeList);
//	}

	public void initObjects(InputStream in){        
        try {
        	outputFilePath = Main.dsdDirPath;
            xmlDocument = DocumentBuilderFactory.
			newInstance().newDocumentBuilder().
			parse(in);            
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
	
    public void initObjects(){        
        try {
        	//System.out.println(xmlFilePath);
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
		
		addSDMXCodeList();
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
		
		produceRDF();
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
				
//				if(con.getAttribute("id").equalsIgnoreCase("obs_value"))
//					obsValue = "obsValue";
//				else if(con.getAttribute("id").equalsIgnoreCase("freq"))
//					freq = "freq";
//				else if(con.getAttribute("id").equalsIgnoreCase("time_period"))
//					timePeriod = "timePeriod";
//				else
//				{
//					Concept obj = new Concept(con.getAttribute("id"),hshName);
//					lstConcepts.add(obj);
//				}

				if(!con.getAttribute("id").equalsIgnoreCase("obs_value") & !con.getAttribute("id").equalsIgnoreCase("freq") & !con.getAttribute("id").equalsIgnoreCase("time_period"))
				{
					Concept obj = new Concept(con.getAttribute("id"),hshName);
					lstConcepts.add(obj);
				}
				
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
				if(dim.getAttribute("conceptRef").equalsIgnoreCase("obs_value"))
					obsValue = "obsValue";
				else if(dim.getAttribute("conceptRef").equalsIgnoreCase("freq"))
					freq = "freq";
				else if(!dim.getAttribute("conceptRef").equalsIgnoreCase("time_format"))
				{
					Dimension obj = new Dimension(dim.getAttribute("conceptRef"),dim.getAttribute("conceptSchemeRef"),dim.getAttribute("codelist"), getType(dim));
					lstDimensions.add(obj);
				}
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
				if(measure.getAttribute("conceptRef").equalsIgnoreCase("obs_value"))
					obsValue = "obsValue";
				else if(measure.getAttribute("conceptRef").equalsIgnoreCase("time_period"))
					timePeriod = "timePeriod";
				else if(!measure.getAttribute("conceptRef").equalsIgnoreCase("time_format"))
				{
					Dimension obj = new Dimension(measure.getAttribute("conceptRef"),measure.getAttribute("conceptSchemeRef"),measure.getAttribute("codelist"), getType(measure));
					lstTimeDimensions.add(obj);
				}
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
				
				if(measure.getAttribute("conceptRef").equalsIgnoreCase("obs_value"))
					obsValue = "obsValue";
				else if(!measure.getAttribute("conceptRef").equalsIgnoreCase("time_format"))
				{
					Measure obj = new Measure(measure.getAttribute("conceptRef"),measure.getAttribute("conceptSchemeRef"),measure.getAttribute("codelist"),getType(measure));
					lstMeasures.add(obj);
				}
			}
		}

		// Attribute
		NodeList attribute = comp.getElementsByTagName("structure:Attribute");
		if(attribute != null && attribute.getLength() > 0)
		{
			for(int i = 0 ; i < attribute.getLength();i++)
			{
				Element att = (Element)attribute.item(i);
				
				if(att.getAttribute("conceptRef").equalsIgnoreCase("obs_value"))
					obsValue = "obsValue";
				else if(!att.getAttribute("conceptRef").equalsIgnoreCase("time_format"))
				{
					Attribute obj = new Attribute(att.getAttribute("conceptRef"),att.getAttribute("conceptSchemeRef"),att.getAttribute("codelist"),"","", getType(att));
					lstAttributes.add(obj);
				}
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
	
	public void produceRDF()
	{
		//Model model = ModelFactory.createDefaultModel();
		
		Model model = ParserUtil.getModelProperties();
		//Model codelist_Model = ModelFactory.createDefaultModel();
		
		//--//Resource root = model.createResource( baseURI + "dsd#" + fileName.substring(0,fileName.indexOf("_DSD")) );
		Resource root = model.createResource( baseURI + "dsd/" + fileName.substring(0,fileName.indexOf("_DSD")) );
		
		model.add(root,ParserUtil.type,ParserUtil.dsd).add(root,ParserUtil.notation,fileName);
		
		//
		for(Dimension dim:lstDimensions)
		{
			Resource component_1 = model.createResource();
			model.add(root,ParserUtil.component,component_1);
			Property prop = model.createProperty(ParserUtil.property + (dim.getConceptRef().toLowerCase().equals("time_period") ? "time" : dim.getConceptRef().toLowerCase()));
			model.add(component_1,ParserUtil.dimension,prop);
			model.add(prop,ParserUtil.type,ParserUtil.dimensionProperty);
			model.add(prop,ParserUtil.type,ParserUtil.codedProperty);
			model.add(prop,ParserUtil.rdfsDomain,ParserUtil.observation);
			//--//Property cncpt = model.createProperty(ParserUtil.concepts + dim.getConceptRef());
			//--//model.add(prop,ParserUtil.concept,cncpt);
			
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
							Property cList = model.createProperty(ParserUtil.sdmx_code + codeList);
							model.add(prop,ParserUtil.codeList,cList);
							model.add(prop,ParserUtil.rdfsRange,ParserUtil.skosConcept);
							Property cncpt = model.createProperty(ParserUtil.sdmx_concept + codeList);
							model.add(prop,ParserUtil.concept,cncpt);
						}
						else
						{
							Property cList = model.createProperty(ParserUtil.dicURI + obj.getId().substring(obj.getId().indexOf("_")+1).toLowerCase());
							model.add(prop,ParserUtil.codeList,cList);
							model.add(prop,ParserUtil.rdfsRange,ParserUtil.skosConcept);
							//Property cncpt = model.createProperty(ParserUtil.concepts + dim.getConceptRef());
							Property cncpt = model.createProperty(ParserUtil.concepts + (dim.getConceptRef().toLowerCase().equals("time_period") ? "time" : dim.getConceptRef().toLowerCase()));
							model.add(prop,ParserUtil.concept,cncpt);
						}
					}
				}
			}
			else
			{
				Property type = model.createProperty(ParserUtil.xsd + dim.getDataType().toLowerCase());
				model.add(prop,ParserUtil.rdfsRange,type);
			}
		}
		
		if(!obsValue.equals(""))
		{
			Resource component_1 = model.createResource();
			model.add(root,ParserUtil.component,component_1);
			Property prop = model.createProperty(ParserUtil.sdmx_measure + obsValue);
			model.add(component_1,ParserUtil.dimension,prop);
		}
		
		if(!freq.equals(""))
		{
			Resource component_1 = model.createResource();
			model.add(root,ParserUtil.component,component_1);
			Property prop = model.createProperty(ParserUtil.sdmx_dimension + freq);
			model.add(component_1,ParserUtil.dimension,prop);
		}
		
		if(!timePeriod.equals(""))
		{
			Resource component_1 = model.createResource();
			model.add(root,ParserUtil.component,component_1);
			Property prop = model.createProperty(ParserUtil.dcterms + "date");
			model.add(component_1,ParserUtil.dimension,prop);
		}
		//
		for(Dimension dim:lstTimeDimensions)
		{
			Resource component_1 = model.createResource();
			model.add(root,ParserUtil.component,component_1);
			Property prop = model.createProperty(ParserUtil.property + (dim.getConceptRef().toLowerCase().equals("time_period") ? "time" : dim.getConceptRef().toLowerCase()));
			model.add(component_1,ParserUtil.dimension,prop);
			model.add(prop,ParserUtil.type,ParserUtil.dimensionProperty);
			model.add(prop,ParserUtil.rdfsDomain,ParserUtil.observation);
			//-//Property cncpt = model.createProperty(ParserUtil.concepts + dim.getConceptRef());
			//--//model.add(prop,ParserUtil.concept,cncpt);
			
			if(!dim.getCodeList().equals(""))
			{
				for(CodeList obj:lstCodeLists)
				{
					if(obj.getId().toString().equals(dim.getCodeList().toString()))
					{
						if(obj.getAgencyID().equals("SDMX"))
						{
							String codeList = getCodeList(obj.getId());
							Property cList = model.createProperty(ParserUtil.sdmx_code + codeList);
							model.add(prop,ParserUtil.codeList,cList);
							model.add(prop,ParserUtil.rdfsRange,ParserUtil.skosConcept);
							Property cncpt = model.createProperty(ParserUtil.sdmx_concept + codeList);
							model.add(prop,ParserUtil.concept,cncpt);
						}
						else
						{
							Property cList = model.createProperty(ParserUtil.dicURI + obj.getId().substring(obj.getId().indexOf("_")+1).toLowerCase());
							model.add(prop,ParserUtil.codeList,cList);
							model.add(prop,ParserUtil.rdfsRange,ParserUtil.skosConcept);
							Property cncpt = model.createProperty(ParserUtil.concepts + (dim.getConceptRef().toLowerCase().equals("time_period") ? "time" : dim.getConceptRef().toLowerCase()));
							model.add(prop,ParserUtil.concept,cncpt);
						}
					}
				}
			}
			else
			{
				Property type = model.createProperty(ParserUtil.xsd + dim.getDataType().toLowerCase());
				model.add(prop,ParserUtil.rdfsRange,type);
			}
		}
		
		//
		for(Measure measure:lstMeasures)
		{
			Resource component_1 = model.createResource();
			model.add(root,ParserUtil.component,component_1);
			Property prop = model.createProperty(ParserUtil.property + (measure.getConceptRef().toLowerCase().equals("time_period") ? "time" : measure.getConceptRef().toLowerCase()));
			model.add(component_1,ParserUtil.measure,prop);
			model.add(prop,ParserUtil.type,ParserUtil.measureProperty);
			model.add(prop,ParserUtil.type,ParserUtil.codedProperty);
			model.add(prop,ParserUtil.rdfsDomain,ParserUtil.observation);
			//--//Property cncpt = model.createProperty(ParserUtil.concepts + measure.getConceptRef());
			//--//model.add(prop,ParserUtil.concept,cncpt);
			
			if(!measure.getCodeList().equals(""))
			{
				for(CodeList obj:lstCodeLists)
				{
					if(obj.getId().toString().equals(measure.getCodeList().toString()))
					{
						if(obj.getAgencyID().equals("SDMX"))
						{
							String codeList = getCodeList(obj.getId());
							Property cList = model.createProperty(ParserUtil.sdmx_code + codeList);
							model.add(prop,ParserUtil.codeList,cList);
							model.add(prop,ParserUtil.rdfsRange,ParserUtil.skosConcept);
							Property cncpt = model.createProperty(ParserUtil.sdmx_concept + codeList);
							model.add(prop,ParserUtil.concept,cncpt);
						}
						else
						{
							Property cList = model.createProperty(ParserUtil.dicURI + obj.getId().substring(obj.getId().indexOf("_")+1).toLowerCase());
							model.add(prop,ParserUtil.codeList,cList);
							model.add(prop,ParserUtil.rdfsRange,ParserUtil.skosConcept);
							Property cncpt = model.createProperty(ParserUtil.concepts + (measure.getConceptRef().toLowerCase().equals("time_period") ? "time" : measure.getConceptRef().toLowerCase()));
							model.add(prop,ParserUtil.concept,cncpt);
						}
					}
				}
			}
			else
			{
				Property type = model.createProperty(ParserUtil.xsd + measure.getDataType().toLowerCase());
				model.add(prop,ParserUtil.rdfsRange,type);
			}
		}
		
		// 
		for(Attribute att:lstAttributes)
		{
			Resource component_1 = model.createResource();
			model.add(root,ParserUtil.component,component_1);
			Property prop = model.createProperty(ParserUtil.property + (att.getConceptRef().toLowerCase().equals("time_period") ? "time" : att.getConceptRef().toLowerCase()));
			model.add(component_1,ParserUtil.attribute,prop);
			model.add(prop,ParserUtil.type,ParserUtil.attributeProperty);
			model.add(prop,ParserUtil.type,ParserUtil.codedProperty);
			model.add(prop,ParserUtil.rdfsDomain,ParserUtil.observation);
			//--//Property cncpt = model.createProperty(ParserUtil.concepts + att.getConceptRef());
			//--//model.add(prop,ParserUtil.concept,cncpt);
			
			for(CodeList obj:lstCodeLists)
			{
				if(obj.getId().toString().equals(att.getCodeList().toString()))
				{
					if(obj.getAgencyID().equals("SDMX"))
					{
						// re-use the URI from sdmx-code.ttl file
						String codeList = getCodeList(obj.getId());
						Property cList = model.createProperty(ParserUtil.sdmx_code + codeList);
						model.add(prop,ParserUtil.codeList,cList);
						model.add(prop,ParserUtil.rdfsRange,ParserUtil.skosConcept);
						Property cncpt = model.createProperty(ParserUtil.sdmx_concept + codeList);
						model.add(prop,ParserUtil.concept,cncpt);
					}
					else
					{
						Property cList = model.createProperty(ParserUtil.dicURI + obj.getId().substring(obj.getId().indexOf("_")+1).toLowerCase());
						model.add(prop,ParserUtil.codeList,cList);
						model.add(prop,ParserUtil.rdfsRange,ParserUtil.skosConcept);
						Property cncpt = model.createProperty(ParserUtil.concepts + (att.getConceptRef().toLowerCase().equals("time_period") ? "time" : att.getConceptRef().toLowerCase()));
						model.add(prop,ParserUtil.concept,cncpt);
					}
				}
			}
		}

		// translates code lists
		String codeListID = "";
		String name;
		HashMap<String, String> hshName = new HashMap<String, String>();
		
		for(CodeList obj:lstCodeLists)
		{
			if(!obj.getAgencyID().equals("SDMX"))
			{
				//codelist_Model = ParserUtil.getModelProperties();
				
				codeListID = obj.getId().substring(obj.getId().indexOf("_")+1);
				Resource codeLists = model.createResource(baseURI + "dic/" + codeListID.toLowerCase());
				//Resource codelist_Lists = codelist_Model.createResource(baseURI + "dic/" + codeListID.toLowerCase());
				
				model.add(codeLists,ParserUtil.type,ParserUtil.conceptScheme);
				//codelist_Model.add(codelist_Lists,ParserUtil.type,ParserUtil.conceptScheme);
				
				model.add(codeLists,ParserUtil.notation,obj.getId().toLowerCase());
				//codelist_Model.add(codelist_Lists,ParserUtil.notation,obj.getId().toLowerCase());
				
				// print multilingual labels
				hshName = obj.gethshName();
				Iterator entrySetIterator = hshName.entrySet().iterator();
				while (entrySetIterator.hasNext())
				{
					Map.Entry entry = (Map.Entry) entrySetIterator.next();
		            String key = (String) entry.getKey();
		            name = hshName.get(key);
		            model.add(codeLists,ParserUtil.rdfsLabel,model.createLiteral(name,key));
		            //codelist_Model.add(codelist_Lists,ParserUtil.rdfsLabel,model.createLiteral(name,key));
				}
				
				ArrayList<Code> arrCode = obj.getCode();
				for(Code code:arrCode)
				{
					//writeLinetoFile("		skos:hasTopConcept <" + codeListURL + "CodeList/" + codeListID + "#" + code.getValue() + ">;");
					String str = baseURI + "dic/" + codeListID.toLowerCase() + "#" + code.getValue();
					Resource res = model.createResource(str);
					//Resource codelist_res = codelist_Model.createResource(str);
					
					model.add(codeLists,ParserUtil.topConcept,res);
					//codelist_Model.add(codelist_Lists,ParserUtil.topConcept,codelist_res);
					
					model.add(res,ParserUtil.type,ParserUtil.skosConcept);
					//codelist_Model.add(codelist_res,ParserUtil.type,ParserUtil.skosConcept);
					
					// print multilingual labels
					hshName = code.gethshDescription();
					Iterator entryIterator = hshName.entrySet().iterator();
					while (entryIterator.hasNext())
					{
						Map.Entry entry = (Map.Entry) entryIterator.next();
			            String key = (String) entry.getKey();
			            name = hshName.get(key);
			            
			            model.add(res,ParserUtil.skosLabel, model.createLiteral(name,key));
			            //codelist_Model.add(codelist_res,ParserUtil.skosLabel, model.createLiteral(name,key));
					}
					
					str = str.substring(0,str.indexOf("#"));
					Resource resource = model.createResource(str);
					//Resource codelist_resource = codelist_Model.createResource(str);
					
					model.add(res,ParserUtil.skosScheme,resource);
					//codelist_Model.add(codelist_res,ParserUtil.skosScheme,codelist_resource);
					
					model.add(res,ParserUtil.notation,code.getValue());
					//codelist_Model.add(codelist_res,ParserUtil.notation,code.getValue());
				}
				
				//codelist_Model.write(System.out,serialization);
			}
		}

		for(Concept concept:lstConcepts)
		{
			Resource con = model.createResource(ParserUtil.concepts + (concept.getId().toLowerCase().equals("time_period") ? "time" : concept.getId().toLowerCase()));
			model.add(con,ParserUtil.type,ParserUtil.sdmx);
			model.add(con,ParserUtil.notation,concept.getId().toLowerCase().equals("time_period") ? "time" : concept.getId().toLowerCase());
			
			//print multilingual labels
			hshName = concept.gethshName();
			Iterator entrySetIterator = hshName.entrySet().iterator();
			while (entrySetIterator.hasNext())
			{
				Map.Entry entry = (Map.Entry) entrySetIterator.next();
	            String key = (String) entry.getKey();
	            name = hshName.get(key);
	            //writeLinetoFile("		skos:prefLabel \"" + name + "\"@" + key + ";");
	            model.add(con,ParserUtil.skosLabel,model.createLiteral(name,key));
			}
			
			Resource res = model.createResource(ParserUtil.concepts);
			model.add(con,ParserUtil.skosScheme,res);
			
		}
		
		writeRDFToFile(fileName,model);
	}
	
	public void writeRDFToFile(String fileName, Model model)
	{
		if(serialization.equalsIgnoreCase("RDF/XML"))
			fileExt = ".rdf";
		else if(serialization.equalsIgnoreCase("TURTLE"))
			fileExt = ".ttl";
		else if(serialization.equalsIgnoreCase("N-TRIPLES"))
			fileExt = ".nt";
		try
	   	{
			OutputStream output = new FileOutputStream(outputFilePath + fileName.substring(0,fileName.indexOf("_DSD")) + fileExt,false);
			model.write(output,serialization.toUpperCase());
			
	   	}catch(Exception e)
	   	{
	   		DiffToC.writeLog("Error while creating dsd RDF file ... " + e.getMessage());
	   	}
	}	
	
	private static void usage()
	{
		System.out.println("usage: DSDParser [parameters]");
		System.out.println();
		System.out.println("	-i inputFilePath	Data Structure Definition (DSD) in XML format as input.");
		System.out.println("	-o outputFilePath	Output directory path to generate DataCube representation of DSD.");
		System.out.println("	-a sdmx ttl file	Path where the sdmx ttl is located.");
		System.out.println("	(optional)-f format	RDF format for serialization (RDF/XML, TURTLE, N-TRIPLES).");
	}
	
	public static void main(String[] args) throws Exception
	{
		DSDParser obj = new DSDParser();
			
		CommandLineParser parser = new BasicParser( );
		Options options = new Options( );
		options.addOption("h", "help", false, "Print this usage information");
		options.addOption("i", "inputFilepath", true, "Data Structure Definition (DSD) in XML format as input.");
		options.addOption("o", "outputFilePath", true, "Output directory path to generate DataCube representation of DSD.");
		options.addOption("f", "format", true, "RDF format for serialization (RDF/XML, TURTLE, N-TRIPLES).");
		options.addOption("a", "sdmx ttl file", true, "Path where the sdmx ttl is located.");
		CommandLine commandLine = parser.parse( options, args );
		
		if( commandLine.hasOption('h') ) {
		    usage();
		    return;
		 }
		
		if(commandLine.hasOption('i'))
			xmlFilePath = commandLine.getOptionValue('i');
		if(commandLine.hasOption('o'))
			outputFilePath = commandLine.getOptionValue('o');
		if(commandLine.hasOption('f'))
			serialization = commandLine.getOptionValue('f');
		
		if(commandLine.hasOption('a'))
			sdmx_codeFilePath = commandLine.getOptionValue('a');
		
		if(xmlFilePath.equals("") || outputFilePath.equals("") || serialization.equals("") || sdmx_codeFilePath.equals(""))
		{
			usage();
			return;
		}
		else
		{
			obj.initObjects();
			obj.parseFile();
		}
	}
}
