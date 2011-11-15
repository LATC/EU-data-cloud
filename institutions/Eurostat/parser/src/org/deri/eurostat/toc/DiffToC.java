package org.deri.eurostat.toc;

import java.io.IOException;
import java.io.InputStream;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.Map;

import javax.xml.parsers.DocumentBuilderFactory;
import javax.xml.parsers.ParserConfigurationException;

import org.apache.commons.cli.BasicParser;
import org.apache.commons.cli.CommandLine;
import org.apache.commons.cli.CommandLineParser;
import org.apache.commons.cli.Options;
import org.w3c.dom.Document;
import org.w3c.dom.Element;
import org.w3c.dom.NodeList;
import org.xml.sax.SAXException;


/**
 * This class will download the ToC from the Eurostat site and lists down the datasets which has
 * been updated by comparing the modified date of each dataset 
 *
 * @author Aftab Iqbal
 * 
 */

public class DiffToC {

	ParseToC obj;
	private Document xmlDocument;
	HashMap<String, String> hshMap_New = new HashMap<String, String>();
	HashMap<String, String> hshMap_Old = new HashMap<String, String>();
	static ArrayList<String> arrDatasets = new ArrayList<String>();
	
	public void runComparison(String inputFilePath, String outputFilePath)
	{
		download_New_TOC();
		readTOC(inputFilePath);
		System.out.println(hshMap_New.size());
		System.out.println(hshMap_Old.size());
		
		for (Map.Entry<String, String> entry : hshMap_New.entrySet())
		{
			String code = entry.getKey();
			String modifiedDate = entry.getValue();
			String oldDate = "";

			//System.out.println(code + " : " + modifiedDate);
			oldDate = hshMap_Old.get(code);
			System.out.println(oldDate);
			if(oldDate == null || !oldDate.equals(modifiedDate))
				arrDatasets.add(code + " : " + oldDate + " : " + modifiedDate);
		}
	}
	
	public void initObjects(InputStream in){        
        try {
            xmlDocument = DocumentBuilderFactory.
			newInstance().newDocumentBuilder().
			parse(in);            
        } catch (IOException ex) {
            ex.printStackTrace();
        } catch (SAXException ex) {
            ex.printStackTrace();
        } catch (ParserConfigurationException ex) {
            ex.printStackTrace();
        }       
    }

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

	/**
	 * Read all the datasets exists in the TableOfContents.xml
	 * @param flag
	 */
	public void readDataSetEntries(boolean flag)
	{
		Element element = xmlDocument.getDocumentElement();
		
		NodeList nl = element.getElementsByTagName("nt:leaf");
		if(nl != null && nl.getLength() > 0)
		{
			for(int i = 0 ; i < nl.getLength();i++)
			{
				Element ele = (Element)nl.item(i);
				if(ele.getAttribute("type").equals("dataset") || ele.getAttribute("type").equals("table"))
				{
					getDatasetModificationDate(ele,flag);
				}
			}
		}
	}
	
	/**
	 * Get the modification date of the dataset.
	 * @param ele
	 * @param flag
	 */
	public void getDatasetModificationDate(Element ele, boolean flag)
	{
		String modificationDate = "";
		String datasetCode = "";
		
		NodeList nl = ele.getElementsByTagName("nt:code");
		datasetCode = nl.item(0).getTextContent(); 
		
		nl = ele.getElementsByTagName("nt:lastModified");
		modificationDate = nl.item(0).getTextContent();
	
		if(flag)
			hshMap_New.put(datasetCode, modificationDate);
		else
			hshMap_Old.put(datasetCode, modificationDate);
		
	}
	
	/**
	 *  Download the new TableOfContent.xml from the site for comparison. 
	 */
	public void download_New_TOC()
	{
		obj = new ParseToC();
		
		InputStream is = obj.get_ToC_XMLStream();
		initObjects(is);
		readDataSetEntries(true);
	}
	
	/**
	 * Load the last TableOfContents.xml from the directory.
	 * @param filePath
	 */
	public void readTOC(String filePath)
	{
		initObjects(filePath);
		readDataSetEntries(false);
	}
	
	private static void usage()
	{
		System.out.println("usage: DiffToC [parameters]");
		System.out.println();
		System.out.println("	-i input filepath	Input file path of the TableOfContents.xml file.");
		System.out.println("	-o output filepath	Output directory path where the new TableOfContents.xml file will be saved.");
	}

	public static void main(String[] args) throws Exception
	{
		String inputFilePath = "";
		String outputFilePath = "";
		
		CommandLineParser parser = new BasicParser( );
		Options options = new Options( );
		options.addOption("h", "help", false, "Print this usage information");
		options.addOption("i", "file path", true, "Input file path of the TableOfContents.xml file.");
		options.addOption("o", "outputFilePath", true, "Output directory path where the new TableOfContents.xml file will be saved.");
		
		CommandLine commandLine = parser.parse( options, args );
		
		if( commandLine.hasOption('h') ) {
		    usage();
		    return;
		 }

		if(commandLine.hasOption('i'))
			inputFilePath = commandLine.getOptionValue('i');
		
		if(commandLine.hasOption('o'))
			outputFilePath = commandLine.getOptionValue('o');
		
		if(inputFilePath.equals("") || outputFilePath.equals(""))
		{
			usage();
			return;
		}
		else
		{
			DiffToC obj = new DiffToC();
			obj.runComparison(inputFilePath,outputFilePath);
			
			for(String str:arrDatasets)
				System.out.println(str);
			
			System.out.println("total changes ... " + arrDatasets.size());
		}
			
	}
}
