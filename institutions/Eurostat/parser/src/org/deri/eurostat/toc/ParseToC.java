package org.deri.eurostat.toc;

import java.io.IOException;
import java.io.InputStream;
import java.net.HttpURLConnection;
import java.net.URL;
import java.util.ArrayList;
import java.util.HashMap;

import javax.xml.parsers.DocumentBuilderFactory;
import javax.xml.parsers.ParserConfigurationException;
import javax.xml.xpath.XPathFactory;

import org.apache.commons.cli.BasicParser;
import org.apache.commons.cli.CommandLine;
import org.apache.commons.cli.CommandLineParser;
import org.apache.commons.cli.Options;
import org.deri.eurostat.zip.DownloadZip;
import org.deri.eurostat.zip.UnCompressXML;
import org.w3c.dom.Document;
import org.w3c.dom.Element;
import org.w3c.dom.NodeList;
import org.xml.sax.SAXException;

/**
 * Downloads the ToC.XML from EuroStat and extracts the Dataset URLs from it. Each of
 * the parsed URL is than sent to UnCompressXML class to uncompress the file and
 * RDFize the DSD and SDMX observations.
 * 
 * @author Aftab Iqbal
 *
 */
public class ParseToC {

	private Document xmlDocument;
	public ArrayList<String> lstDatasetURLs = new ArrayList<String>();
	public HashMap<String, HashMap<String,String>> toc = new HashMap<String, HashMap<String,String>>(); 
	private static int printDatasets = 10;
	UnCompressXML obj = new UnCompressXML();
	DownloadZip zip = new DownloadZip();
	
	public InputStream get_ToC_XMLStream()
	{
		InputStream is = null;
		try {
			URL url = new URL("http://epp.eurostat.ec.europa.eu/NavTree_prod/everybody/BulkDownloadListing?sort=1&file=table_of_contents.xml");
			HttpURLConnection conn = (HttpURLConnection)url.openConnection();
			is = conn.getInputStream();

			if (conn.getResponseCode() != 200) {
				System.err.println(conn.getResponseCode());
			}
		} catch (IOException e) {
			e.printStackTrace();
			return null;
		}
		
		return is;
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
	
	public void parseDataSets()
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
					getDatasetURLs(ele);
				}
			}
		}
		
	}

	public void printResults()
	{
		int count = 0;
		
		System.out.println("Total Datasets found in the ToC are : " + lstDatasetURLs.size());
		for(String str:lstDatasetURLs)
		{
			System.out.println(str);
			if(++count == printDatasets)
				break;
		}
	}
	
	// This piece of code will parse the compressed file URLs sequentially.
	public void parseXMLFiles(String downLoadPath)
	{
		for(String str:lstDatasetURLs)
		{
			obj.parseZipFile(str, downLoadPath);
		}
	}

	public void downloadXMLFiles(String tempZipPath, String tempTsvPath)
	{
		for(String str:lstDatasetURLs)
		{
			zip.zipURL(str, tempZipPath, tempTsvPath);
		}
	}

	// get the URLs of datasets having SDMX format
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
	
	public void extractDatasetTitles()
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
					storeDatasetTitles(ele);
				}
			}
		}

	}
	
	public void storeDatasetTitles(Element element)
	{
		HashMap<String, String> hsh = new HashMap<String, String>();
		String code = "";
		
		NodeList nl = element.getElementsByTagName("nt:code");
		
		code = nl.item(0).getTextContent();
		
		nl = element.getElementsByTagName("nt:title");
		if(nl != null && nl.getLength() > 0)
		{
			for(int i = 0 ; i < nl.getLength();i++)
			{
				Element ele = (Element)nl.item(i);
				hsh.put(ele.getAttribute("language"), ele.getTextContent());
			}
		}

		toc.put(code, hsh);
	}
	
	private static void usage()
	{
		System.out.println("usage: ParseToC [parameters]");
		System.out.println();
		System.out.println("	-n num		No. of Dataset URLs to print. Default sets to 10.");
	}
	
	public void parseToC()
	{
		InputStream is = get_ToC_XMLStream();
		initObjects(is);
		parseDataSets();
		printResults();
	}
	
	public void RDFize(String downLoadPath)
	{
		InputStream is = get_ToC_XMLStream();
		initObjects(is);
		parseDataSets();
	}

	public void getDatasetTitles()
	{
		InputStream is = get_ToC_XMLStream();
		initObjects(is);
		extractDatasetTitles();
	}

	public void downloadZip(String tempZipPath, String tempTsvPath)
	{
		InputStream is = get_ToC_XMLStream();
		initObjects(is);
		parseDataSets();
		downloadXMLFiles(tempZipPath, tempTsvPath);
	}
	
	public static void main(String[] args) throws Exception
	{
		ParseToC obj = new ParseToC();
		
		CommandLineParser parser = new BasicParser( );
		Options options = new Options( );
		options.addOption("h", "help", false, "Print this usage information");
		options.addOption("n", "num", true, "No. of Dataset URLs to print. Default sets to 10.");

		CommandLine commandLine = parser.parse( options, args );
		
		if( commandLine.hasOption('h') ) {
		    usage();
		    return;
		 }
		
		if(commandLine.hasOption('n'))
			printDatasets = Integer.parseInt(commandLine.getOptionValue('n'));
		
		obj.parseToC();
	}
	
}
