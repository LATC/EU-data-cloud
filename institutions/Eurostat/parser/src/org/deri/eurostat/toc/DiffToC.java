package org.deri.eurostat.toc;

import java.io.BufferedWriter;
import java.io.FileInputStream;
import java.io.FileNotFoundException;
import java.io.FileOutputStream;
import java.io.FileWriter;
import java.io.IOException;
import java.io.InputStream;
import java.io.OutputStream;
import java.text.DateFormat;
import java.text.ParseException;
import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.Date;
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
	HashMap<String, String> hshMap_URLs = new HashMap<String, String>();
	HashMap<String, String> hshMap_Titles = new HashMap<String, String>();
	static ArrayList<String> dsUpdates = new ArrayList<String>();
	static ArrayList<String> newDatasets = new ArrayList<String>();
	static ArrayList<String> missingDatasets = new ArrayList<String>();
	static BufferedWriter write = null;
	static FileWriter fstream = null;
	
	public void runComparison(String inputFilePath, String outputFilePath, String logFilePath)
	{
		read_New_TOC();
		readTOC(inputFilePath);
		
		// for testing
		//readTOC_1(inputFilePath + "table_of_contents_1.xml");
		//readTOC(inputFilePath + "table_of_contents.xml");
		
		//System.out.println(hshMap_New.size());
		//System.out.println(hshMap_Old.size());
		
		for (Map.Entry<String, String> entry : hshMap_New.entrySet())
		{
			String code = entry.getKey();
			String newDate = entry.getValue();
			String oldDate = "";

			oldDate = hshMap_Old.get(code);
			if(oldDate == null)
				newDatasets.add("[" + hshMap_Titles.get(code) + "] [" + hshMap_URLs.get(code) + "]");
			else if(!oldDate.equals("") && !newDate.equals(""))
			{
				if(!isGreater(oldDate,newDate) && !oldDate.equals(newDate))
					dsUpdates.add("[" + hshMap_Titles.get(code) + "] [" + hshMap_URLs.get(code) + "]");
					//arrDatasets.add(code + " # " + oldDate + " # " + newDate + " # " + hshMap_Titles.get(code) + " # " + hshMap_URLs.get(code));
			}
		}
		
		for (Map.Entry<String, String> entry : hshMap_Old.entrySet())
		{
			String code = entry.getKey();
			if(hshMap_New.get(code) == null)
				missingDatasets.add("[" + hshMap_Titles.get(code) + "] [" + hshMap_URLs.get(code) + "]");
		}
		
		createLogFile(logFilePath);
		printLogs();
		download_New_TOC(outputFilePath);

		try{
        	write.flush();  
        	write.close();
		}catch(IOException e){
			System.out.println("Error while closing the file...");
		}
		System.out.println("Comparison has been completed. Please see the Logs.");
		
	}
	
	public boolean isGreater(String originalDate, String modifiedDate)
	{
		
		SimpleDateFormat sdfSource = new SimpleDateFormat("dd.MM.yyyy");
		
		try {
			
		Date date1 = sdfSource.parse(originalDate);
		Date date2 = sdfSource.parse(modifiedDate);
		
		if(date1.after(date2))
			return true;
		else
			return false;
		
		} catch(ParseException ex){
			System.out.println("Error while parsing the date format :" + originalDate + " : " + modifiedDate);
		}
		
		return false;
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
	 * Read all the datasets that are in the TableOfContents.xml
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
		String lastModified = "";
		String datasetCode = "";
		String date = "";
		String lastUpdate = "";
		
		NodeList nl = ele.getElementsByTagName("nt:code");
		datasetCode = nl.item(0).getTextContent(); 
		
		nl = ele.getElementsByTagName("nt:lastModified");
		lastModified = nl.item(0).getTextContent();
		
		nl = ele.getElementsByTagName("nt:lastUpdate");
		lastUpdate = nl.item(0).getTextContent();
		
		getDatasetURL(ele, datasetCode);
		
		getDatasetTitle(ele, datasetCode);
		
		if(!lastModified.equals("") && !lastUpdate.equals(""))
		{
			if(isGreater(lastUpdate,lastModified))
				date = lastUpdate;
			else
				date = lastModified;
		}
		else if(lastModified.equals(""))
			date = lastUpdate;
		else if(lastUpdate.equals(""))
			date = lastModified;
		
		if(flag)
			hshMap_New.put(datasetCode, date);
		else
			hshMap_Old.put(datasetCode, date);
		
	}
	
	public void getDatasetURL(Element element, String datasetCode)
	{
		
		NodeList nl = element.getElementsByTagName("nt:downloadLink");
		if(nl != null && nl.getLength() > 0)
		{
			for(int i = 0 ; i < nl.getLength();i++)
			{
				Element ele = (Element)nl.item(i);
				if(ele.getAttribute("format").equals("sdmx"))
				{
					hshMap_URLs.put(datasetCode, ele.getTextContent());
				}
			}
		}
		
	}

	public void getDatasetTitle(Element element, String datasetCode)
	{
		
		NodeList nl = element.getElementsByTagName("nt:title");
		if(nl != null && nl.getLength() > 0)
		{
			for(int i = 0 ; i < nl.getLength();i++)
			{
				Element ele = (Element)nl.item(i);
				if(ele.getAttribute("language").equals("en"))
				{
					hshMap_Titles.put(datasetCode, ele.getTextContent());
				}
			}
		}
		
	}	

	/**
	 *  Download the new TableOfContent.xml from the site for comparison. 
	 */
	public void read_New_TOC()
	{
		obj = new ParseToC();
		
		InputStream is = obj.get_ToC_XMLStream();
		initObjects(is);
		readDataSetEntries(true);
	}
	
	public void download_New_TOC(String outputFilePath)
	{
		try{
			InputStream is = obj.get_ToC_XMLStream();
		OutputStream os = new FileOutputStream(outputFilePath + "table_of_contents.xml");
		byte[] buffer = new byte[4096];  
		int bytesRead;  
		while ((bytesRead = is.read(buffer)) != -1) {  
		  os.write(buffer, 0, bytesRead);  
		}  
		is.close();
		os.close();
		}catch(Exception ex){
			System.out.println("Error while downloading the table_of_contents.xml");
		}
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
	
	public void readTOC_1(String filePath)
	{
		initObjects(filePath);
		readDataSetEntries(true);
	}
	
	public void printLogs()
	{
		DateFormat dateFormat = new SimpleDateFormat("yyyy/MM/dd HH:mm:ss");
		Date date = new Date();
		
		writeDataToFile("");
		writeDataToFile("===================================================================================================================================================================================================");
		writeDataToFile("***************************************************************************************************************************************************************************************************");
		writeDataToFile("===================================================================================================================================================================================================");
		writeDataToFile("");
		writeDataToFile("The time when script was run : " + dateFormat.format(date));
		writeDataToFile("Total number of datasets that has been changed since last update : " + dsUpdates.size());
		writeDataToFile("New datasets found are : " + newDatasets.size());
		writeDataToFile("Datasets which has been removed : " + missingDatasets.size());
		
		if(dsUpdates.size() > 0 )
		{
			writeDataToFile("");
			writeDataToFile("Updated datasets are :");
			for(String str:dsUpdates)
				writeDataToFile(str);
		}

		if(newDatasets.size() > 0 )
		{
			writeDataToFile("");
			writeDataToFile("New datasets are :");
			for(String str:newDatasets)
				writeDataToFile(str);
		}		

		if(missingDatasets.size() > 0 )
		{
			writeDataToFile("");
			writeDataToFile("Removed datasets are :");
			for(String str:missingDatasets)
				writeDataToFile(str);
		}		

		
	}
	
	public void createLogFile(String filePath)
	{
		try
	   	{
			fstream = new FileWriter(filePath + "log.txt",true);
			write = new BufferedWriter(fstream);
	   	}catch(Exception e)
	   	{
	   		System.err.println("Error in opening the file : " + e.getMessage());
	   	}
	}
	
	public void writeDataToFile(String line)
	{
		try{
	       	write.newLine();
	       	write.write(line);
	    }
	    catch (Exception e){//Catch exception if any
	       	      System.err.println("Error while writing data to file : " + e.getMessage());
	    }
	}
	
	private static void usage()
	{
		System.out.println("usage: DiffToC [parameters]");
		System.out.println();
		System.out.println("	-i input filepath	Input file path of the TableOfContents.xml file.");
		System.out.println("	-o output filepath	Output directory path where the new TableOfContents.xml file will be saved.");
		System.out.println("	-f log filepath		Output directory path where the log file will be created.");
	}
	
	public static void main(String[] args) throws Exception
	{
		String inputFilePath = "";
		String outputFilePath = "";
		String logFilePath = "";
		
		CommandLineParser parser = new BasicParser( );
		Options options = new Options( );
		options.addOption("h", "help", false, "Print this usage information");
		options.addOption("i", "file path", true, "Input file path of the TableOfContents.xml file.");
		options.addOption("o", "outputFilePath", true, "Output directory path where the new TableOfContents.xml file will be saved.");
		options.addOption("f", "logFilePath", true, "Output directory path where the log file will be created.");
		CommandLine commandLine = parser.parse( options, args );
		
		if( commandLine.hasOption('h') ) {
		    usage();
		    return;
		 }

		if(commandLine.hasOption('i'))
			inputFilePath = commandLine.getOptionValue('i');
		
		if(commandLine.hasOption('o'))
			outputFilePath = commandLine.getOptionValue('o');
		
		if(commandLine.hasOption('f'))
			logFilePath = commandLine.getOptionValue('f');
		
		if(inputFilePath.equals("") || outputFilePath.equals("") || logFilePath.equals(""))
		{
			usage();
			return;
		}
		else
		{
			DiffToC obj = new DiffToC();
			obj.runComparison(inputFilePath,outputFilePath,logFilePath);
		}
		
	}
}
