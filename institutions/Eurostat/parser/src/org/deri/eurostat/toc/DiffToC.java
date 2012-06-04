package org.deri.eurostat.toc;

import java.io.BufferedWriter;
import java.io.File;
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
import java.util.logging.FileHandler;
import java.util.logging.Level;
import java.util.logging.Logger;
import java.util.logging.SimpleFormatter;

import javax.xml.parsers.DocumentBuilderFactory;
import javax.xml.parsers.ParserConfigurationException;

import org.apache.commons.cli.BasicParser;
import org.apache.commons.cli.CommandLine;
import org.apache.commons.cli.CommandLineParser;
import org.apache.commons.cli.Options;
import org.apache.commons.io.FileUtils;
import org.deri.eurostat.dsdparser.DSDParser;
import org.deri.eurostat.zip.DownloadZip;
import org.deri.eurostat.zip.UnCompressXML;
import org.w3c.dom.Document;
import org.w3c.dom.Element;
import org.w3c.dom.NodeList;
import org.xml.sax.SAXException;

import com.ontologycentral.estatwrap.SDMXParser;


/**
 * This class will download the ToC from the Eurostat site and lists down the datasets which has
 * been updated by comparing the modified date of each dataset 
 *
 * @author Aftab Iqbal
 * 
 */

public class DiffToC {

	private static Logger theLogger = Logger.getLogger(DiffToC.class.getName());
	static private FileHandler txtFile;
	static private SimpleFormatter formatterTxt;
	
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
	static StringBuffer emailBody = new StringBuffer();
	public static String url = "http://eurostat.linked-statistics.org/data/";
	
	
	
	static public void logger() {
		// Create Logger
		try{
		Logger logger = Logger.getLogger("");
		logger.setLevel(Level.INFO);
		txtFile = new FileHandler("Logging.txt",true);
		
		// Create txt Formatter
		formatterTxt = new SimpleFormatter();
		txtFile.setFormatter(formatterTxt);
		logger.addHandler(txtFile);
		
		}catch(IOException ex)
		{
			System.out.println("Error while creating Log file to capture logging." + ex.getMessage());
		}
	}
	
	public static void writeLog(String msg)
	{
		theLogger.setLevel(Level.INFO);
		theLogger.info(msg);
	}
	
	public void runComparison(String inputFilePath, String outputFilePath, String logFilePath, String tempZipPath, String tempTsvPath, String tempDataPath, String dsdPath, String dataPath, String dataLogPath, String originalDataPath, String rawDataPath, String originalTsvPath, String sdmxTTLFile)
	{
		logger();
		
		read_New_TOC();
		readTOC(inputFilePath);
		
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

		try{
        	write.flush();  
        	write.close();
		}catch(IOException e){
			writeLog("Error while closing the file...");
		}
		
		// write logs as email body
		createEmailBody();
		
		//replace the old toc with new toc
		download_New_TOC(outputFilePath);
		
		
		// download updated and new dataset sdmx zip files
		downloadZipFiles(tempZipPath, tempTsvPath);
		
		// uncompress the new zip files
		unCompressZipFiles(tempZipPath, tempDataPath);
		
		// rdfize the newly downloaded files
		rdfize(dsdPath,dataPath, tempDataPath, dataLogPath, tempTsvPath, sdmxTTLFile);
		
		// move the newly downloaded zip, tsv and uncompressed files to their respective directories
		moveFiles(tempZipPath, tempDataPath, originalDataPath, rawDataPath, tempTsvPath, originalTsvPath);
		
		// delete the files from temp folders 
		deleteFiles(tempZipPath,tempDataPath, tempTsvPath);
		
		// send email
		Email obj = new Email();
		obj.sendEmail("eurostat updates", emailBody);

		writeLog("Updates has been done and email sent with details.");

	}
	
	public void deleteFiles(String tempZipPath, String tempDataPath, String tempTsvPath)
	{
		writeLog("Deleting files from tempData, tempTSV and tempZip directories...");
		// delete from tempZip folder
		File dir = new File(tempZipPath);
		File[] files = dir.listFiles();
		for(File f:files)
		   f.delete();
		
		// delete from tempData folder
		dir = new File(tempDataPath);
		files = dir.listFiles();
		for(File f:files)
		   f.delete();
		
		// delete from tempTsv folder
		dir = new File(tempTsvPath);
		files = dir.listFiles();
		for(File f:files)
		   f.delete();
		
	}
	
	public void moveFiles(String tempZipPath, String tempDataPath, String originalDataPath, String rawDataPath, String tempTsvPath, String originalTsvPath)
	{
		// move files to the original-data directory
		writeLog("Copying files from temp directory...");
		File sourceLocation = new File(tempZipPath);
		File targetLocation = new File(originalDataPath);
		
		try{
			FileUtils.copyDirectory(sourceLocation, targetLocation);
		}catch(Exception ex){writeLog("Zip file transfer failed...");}
		
		// move files to the raw-data directory
		sourceLocation = new File(tempDataPath);
		targetLocation = new File(rawDataPath);
		
		try{
			FileUtils.copyDirectory(sourceLocation, targetLocation);
		}catch(Exception ex){writeLog("Raw data file transfer failed...");}
		
		// move files to the TSV directory
		sourceLocation = new File(tempTsvPath);
		targetLocation = new File(originalTsvPath);
		
		try{
			FileUtils.copyDirectory(sourceLocation, targetLocation);
		}catch(Exception ex){writeLog("Tsv file transfer failed...");}
		
	}
	
	public void rdfize(String dsdPath, String dataPath, String tempDataPath, String dataLogPath, String tempTsvPath, String sdmxTTLFile)
	{
		writeLog("RDFizing updated datasets...");

		DSDParser dsd = new DSDParser();
		SDMXParser sdmx = new SDMXParser();
		
		File dir = new File(tempDataPath);
		
		File[] files = dir.listFiles();
		
		for(File f:files)
		{
			
			if(f.getName().contains(".dsd.xml"))
			{
				writeLog("Processing :" + f.getAbsolutePath());
				dsd = new DSDParser();
				dsd.xmlFilePath = f.getAbsolutePath();
				dsd.outputFilePath = dsdPath;
				dsd.serialization = "turtle";
				dsd.sdmx_codeFilePath = sdmxTTLFile;
				dsd.initObjects();
				dsd.parseFile();
			}
			else if(f.getName().contains(".sdmx.xml"))
			{
				writeLog("Processing :" + f.getAbsolutePath());
				
				sdmx = new SDMXParser();
				sdmx.outputFilePath = dataPath;
				sdmx.logFilePath = dataLogPath;
				try{
					String fileName = f.getName().substring(0,f.getName().indexOf("."));
					sdmx.downLoadTSV(fileName, f.getAbsolutePath(), tempTsvPath + fileName + ".tsv.gz");
				
				}catch(Exception ex)
				{
					writeLog("Error while processing dataset : " + ex.getMessage());
				}
			}
		}
	}
	
	public void downloadZipFiles(String tempZipPath, String tempTsvPath)
	{
		writeLog("Downloading compressed files.");
		DownloadZip obj = new DownloadZip();
		if(dsUpdates.size() > 0 )
		{
			for(String str:dsUpdates)
			{
				if(str.contains("http://"))
					obj.zipURL(str.substring(str.lastIndexOf("[")+1,str.lastIndexOf("]")),tempZipPath, tempTsvPath);
			}
		}
		
		if(newDatasets.size() > 0)
		{
			for(String str:newDatasets)
			{
				if(str.contains("http://"))
					obj.zipURL(str.substring(str.lastIndexOf("[")+1,str.lastIndexOf("]")),tempZipPath, tempTsvPath);
			}
		}
	}
	
	public void unCompressZipFiles(String tempZipPath, String tempDataPath)
	{
		writeLog("UnCompressing files...");
		UnCompressXML obj = new UnCompressXML();
		
		File dir = new File(tempZipPath);
		
		File[] files = dir.listFiles();
		
		for(File f:files)
			obj.parseZipFile(f.getAbsolutePath(), tempDataPath);
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
			writeLog("Error while parsing the date format :" + originalDate + " : " + modifiedDate);
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

		/*
		 * LastModified refers to the 'last table structure change' where as
		 * LastUpdate refers to the 'last update of data'. So we won't compare
		 * LastModified and LastUpdate dates instead we use LastUpdate value of 
		 * the dataset
		 */
		
//		if(!lastModified.equals("") && !lastUpdate.equals(""))
//		{
//			if(isGreater(lastUpdate,lastModified))
//				date = lastUpdate;
//			else
//				date = lastModified;
//		}
//		else if(lastModified.equals(""))
//			date = lastUpdate;
//		else if(lastUpdate.equals(""))
//			date = lastModified;
		
		// incase, lastUpdate value is empty then use the lastModified value
		if(lastUpdate.equals(""))
			date = lastModified;
		else
			date = lastUpdate;
		
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
		writeLog("Downloading new ToC.xml");
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
			writeLog("Error while downloading the table_of_contents.xml");
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
		writeLog("Writing update logs to the file.");
		
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
	
	public void createEmailBody()
	{
		DateFormat dateFormat = new SimpleDateFormat("yyyy/MM/dd HH:mm:ss");
		Date date = new Date();
		addtoEmailBody("");
		addtoEmailBody("Script was run at : " + dateFormat.format(date));
		addtoEmailBody("");
		addtoEmailBody("Total number of datasets that has been changed since last update : " + dsUpdates.size());
		addtoEmailBody("");
		addtoEmailBody("New datasets found are : " + newDatasets.size());
		addtoEmailBody("");
		addtoEmailBody("Datasets which has been removed : " + missingDatasets.size());
		addtoEmailBody("");
		addtoEmailBody("---------------------------------------------------------");
		if(dsUpdates.size() > 0 )
		{
			addtoEmailBody("");
			addtoEmailBody("Updated datasets are :");
			addtoEmailBody("======================");
			for(String str:dsUpdates)
			{
				if(str.contains("http://"))
				{
					addtoEmailBody("");
					addtoEmailBody("-> " + str.substring(1,str.indexOf("]")) + " (" + url + str.substring(str.lastIndexOf("/")+1,str.lastIndexOf(".sdmx.zip")) + ".rdf" + ")");
				}
			}
		}

		if(newDatasets.size() > 0 )
		{
			addtoEmailBody("");
			addtoEmailBody("New datasets are :");
			addtoEmailBody("==================");
			for(String str:newDatasets)
			{
				if(str.contains("http://"))
				{
					addtoEmailBody("");
					addtoEmailBody("-> " + str.substring(1,str.indexOf("]")) + " (" + url + str.substring(str.lastIndexOf("/")+1,str.lastIndexOf(".sdmx.zip")) + ".rdf" + ")");
				}
				
			}
		}		

		if(missingDatasets.size() > 0 )
		{
			addtoEmailBody("");
			addtoEmailBody("Removed datasets are :");
			addtoEmailBody("======================");
			for(String str:missingDatasets)
			{
				if(str.contains("http://"))
				{
					addtoEmailBody("");
					addtoEmailBody("-> " + str.substring(1,str.indexOf("]")) + " (" + url + str.substring(str.lastIndexOf("/")+1,str.lastIndexOf(".sdmx.zip")) + ".rdf" + ")");
				}
			}
		}		
	}
	
	public void createLogFile(String filePath)
	{
		writeLog("Creating log file for writing updates.");
		try
	   	{
			fstream = new FileWriter(filePath + "weekly-updates_log.txt",true);
			write = new BufferedWriter(fstream);
	   	}catch(Exception e)
	   	{
	   		writeLog("Error in opening the file : " + e.getMessage());
	   	}
	}
	
	public void addtoEmailBody(String str)
	{
		emailBody.append("\n");
		emailBody.append(str);
	}
	
	public void writeDataToFile(String line)
	{
		try{
	       	write.newLine();
	       	write.write(line);
	    }
	    catch (Exception e){//Catch exception if any
	       	      writeLog("Error while writing data to file : " + e.getMessage());
	    }
	}
	
	private static void usage()
	{
		System.out.println("usage: DiffToC [parameters]");
		System.out.println();
		System.out.println("	-i input filepath	Input file path of the TableOfContents.xml file.");
		System.out.println("	-o output filepath	Output directory path where the new TableOfContents.xml file will be saved.");
		System.out.println("	-f log filepath		Output directory path where the log of updates will be stored.");
		System.out.println("	-z temp zip path	Directory path where zip files will be temporarily stored.");
		System.out.println("	-v temp tsv path	Directory path where tsv files will be temporarily stored.");
		System.out.println("	-t temp data path	Directory path where the sdmx and dsd files will be temporarily stored.");
		System.out.println("	-s sdmx file path	Output directory path to generate DataCube representation of observations.");
		System.out.println("	-d dsd file path	Output directory path to generate DataCube representation of DSD.");
		System.out.println("	-l data log path	File path where the logs will be written.");
		System.out.println("	-p original data path	Path where zip files will be stored.");
		System.out.println("	-b original tsv path	Path where tsv files will be stored.");
		System.out.println("	-r raw data path	Path where the uncompressed files will be stored.");
		System.out.println("	-a sdmx ttl file	Path where the sdmx ttl is located.");
		
	}
	
	public static void main(String[] args) throws Exception
	{
		String inputFilePath = "";
		String outputFilePath = "";
		String logFilePath = "";
		String tempZipPath = "";
		String tempDataPath = "";
		String dsdPath = "";
		String dataPath = "";
		String dataLogPath = "";
		String originalDataPath = "";
		String rawDataPath = "";
		String tempTsvPath = "";
		String originalTSVPath = "";
		String sdmxTTLFile = "";
		
		CommandLineParser parser = new BasicParser( );
		Options options = new Options( );
		options.addOption("h", "help", false, "Print this usage information");
		options.addOption("i", "file path", true, "Input file path of the TableOfContents.xml file.");
		options.addOption("o", "output file path", true, "Output directory path where the new TableOfContents.xml file will be saved.");
		options.addOption("f", "log file path", true, "Output directory path where the log of updates will be stored.");
		options.addOption("z", "temp zip path", true, "Directory path where zip files will be temporarily stored.");
		options.addOption("v", "temp tsv path", true, "Directory path where tsv files will be temporarily stored.");
		options.addOption("t", "temp data path", true, "Directory path where the sdmx and dsd files will be temporarily stored.");
		options.addOption("s", "sdmx file path", true, "Output directory path to generate DataCube representation of observations.");
		options.addOption("d", "dsd file path", true, "Output directory path to generate DataCube representation of DSD.");
		options.addOption("l", "data log path", true, "File path where the logs will be written.");
		options.addOption("p", "original data path", true, "Path where zip files will be stored.");
		options.addOption("b", "original tsv path", true, "Path where tsv files will be stored.");
		options.addOption("r", "raw data path", true, "Path where the uncompressed files will be stored.");
		options.addOption("a", "sdmx ttl file", true, "Path where the sdmx ttl is located.");
		CommandLine commandLine = parser.parse( options, args );
		
		if( commandLine.hasOption('h') ) {
		    usage();
		    return;
		 }

		if(commandLine.hasOption('a'))
			sdmxTTLFile = commandLine.getOptionValue('a');
		
		if(commandLine.hasOption('i'))
			inputFilePath = commandLine.getOptionValue('i');
		
		if(commandLine.hasOption('o'))
			outputFilePath = commandLine.getOptionValue('o');
		
		if(commandLine.hasOption('f'))
			logFilePath = commandLine.getOptionValue('f');
		
		if(commandLine.hasOption('z'))
			tempZipPath = commandLine.getOptionValue('z');
		
		if(commandLine.hasOption('t'))
			tempDataPath = commandLine.getOptionValue('t');
		
		if(commandLine.hasOption('s'))
			dataPath = commandLine.getOptionValue('s');
		
		if(commandLine.hasOption('d'))
			dsdPath = commandLine.getOptionValue('d');
		
		if(commandLine.hasOption('l'))
			dataLogPath = commandLine.getOptionValue('l');
		
		if(commandLine.hasOption('p'))
			originalDataPath = commandLine.getOptionValue('p');
		
		if(commandLine.hasOption('r'))
			rawDataPath = commandLine.getOptionValue('r');
		
		if(commandLine.hasOption('b'))
			originalTSVPath = commandLine.getOptionValue('b');
		
		if(commandLine.hasOption('v'))
			tempTsvPath = commandLine.getOptionValue('v');
		
		if(tempTsvPath.equals("") || originalTSVPath.equals("") || inputFilePath.equals("") || outputFilePath.equals("") || logFilePath.equals("") || tempZipPath.equals("") || tempDataPath.equals("") || dsdPath.equals("") || dataPath.equals("") || dataLogPath.equals("") || originalDataPath.equals("") || rawDataPath.equals("") || sdmxTTLFile.equals(""))
		{
			usage();
			return;
		}
		else
		{
			DiffToC obj = new DiffToC();
			obj.runComparison(inputFilePath,outputFilePath,logFilePath, tempZipPath, tempTsvPath, tempDataPath, dsdPath, dataPath, dataLogPath, originalDataPath, rawDataPath, originalTSVPath, sdmxTTLFile);
		}
		
	}
}
