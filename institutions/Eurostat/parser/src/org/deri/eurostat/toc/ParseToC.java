package org.deri.eurostat.toc;

import java.io.IOException;
import java.util.ArrayList;

import javax.xml.parsers.DocumentBuilderFactory;
import javax.xml.parsers.ParserConfigurationException;


import org.w3c.dom.Document;
import org.w3c.dom.Element;
import org.w3c.dom.NodeList;
import org.xml.sax.SAXException;

public class ParseToC {

	
	private static String xmlFilePath = "E:/EU Projects/EuroStat/ToC/table_of_contents.xml";
	private Document xmlDocument;
	private ArrayList<String> lstDatasets = new ArrayList<String>();
	private ArrayList<String> lstDatasetURLs = new ArrayList<String>();
	private ArrayList<String> lstDuplicateDatasets = new ArrayList<String>();
	int sumObeservation = 0;

	
	public void initObjects(String filePath){        
		  
		try {
            xmlDocument = DocumentBuilderFactory.
			newInstance().newDocumentBuilder().
			parse(filePath);            
        } catch (IOException ex) {
            ex.printStackTrace();
        } catch (SAXException ex) {
            ex.printStackTrace();
        } catch (ParserConfigurationException ex) {
            ex.printStackTrace();
        }       
	}
	
	
	public void parseDataSets()
	{
		Element element = xmlDocument.getDocumentElement();
		
		NodeList nl = element.getElementsByTagName("nt:leaf");
		
		if(nl != null && nl.getLength() > 0)
		{
			for(int i = 0 ; i < nl.getLength();i++)
			{
				Element ele = (Element)nl.item(i);
				if(ele.getAttribute("type").equals("dataset"))
				{
					//getObservations(ele);
					getDatasetURLs(ele);
				}
				else if(ele.getAttribute("type").equals("table"))
				{
					//getObservations(ele);
					getDatasetURLs(ele);
				}
			}
		}
		
		for(String str:lstDatasetURLs)
			System.out.println(str);
		
	}
	
	// get the URLs of datasets which have format SDMX
	public void getDatasetURLs(Element element)
	{
		NodeList nl = element.getElementsByTagName("nt:downloadLink");
		if(nl != null && nl.getLength() > 0)
		{
			for(int i = 0 ; i < nl.getLength();i++)
			{
				Element ele = (Element)nl.item(i);
				if(ele.getAttribute("format").equals("sdmx"))
				{
					if(!lstDatasetURLs.contains(ele.getTextContent()))
						lstDatasetURLs.add(ele.getTextContent());
				}
			}
		}
		
	}
	
	// get the total number of observations which exists in the datasets
	public void getObservations(Element element)
	{
		String code = getTextValue(element, "nt:code");
		
		if(!lstDatasets.contains(code))
		{
			lstDatasets.add(code);
			if(!(getTextValue(element, "nt:values").equals("")))
				sumObeservation += Integer.parseInt(getTextValue(element, "nt:values"));
		}
		else
			lstDuplicateDatasets.add(code);	
		
	}

	private String getTextValue(Element ele, String tagName) {
		String textVal = "";
		NodeList nl = ele.getElementsByTagName(tagName);
		
		if(nl != null && nl.getLength() > 0) {
			Element el = (Element)nl.item(0);
			if(el.getFirstChild() != null)
				textVal = el.getFirstChild().getNodeValue();
			else
				textVal = "";
		}
		
		
		return textVal;
	}
	
	public static void main(String[] args)
	{
		ParseToC obj = new ParseToC();
		obj.initObjects(xmlFilePath);
    	obj.parseDataSets();
	}
	
}
