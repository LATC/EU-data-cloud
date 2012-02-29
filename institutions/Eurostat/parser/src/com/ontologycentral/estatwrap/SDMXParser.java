package com.ontologycentral.estatwrap;

import java.io.BufferedReader;
import java.io.FileInputStream;
import java.io.FileNotFoundException;
import java.io.FileOutputStream;
import java.io.IOException;
import java.io.InputStream;
import java.io.InputStreamReader;
import java.io.OutputStream;
import java.util.Iterator;
import java.util.zip.GZIPInputStream;
import javax.xml.stream.XMLEventReader;
import javax.xml.stream.XMLInputFactory;
import javax.xml.stream.XMLOutputFactory;
import javax.xml.stream.XMLStreamException;
import javax.xml.stream.XMLStreamWriter;
import javax.xml.stream.events.Attribute;
import javax.xml.stream.events.StartElement;
import javax.xml.stream.events.XMLEvent;
import org.apache.commons.cli.BasicParser;
import org.apache.commons.cli.CommandLine;
import org.apache.commons.cli.CommandLineParser;
import org.apache.commons.cli.Options;
import org.deri.eurostat.toc.DiffToC;


public class SDMXParser {

	public static String outputFilePath = "";
	public static String logFilePath = "";
	
	public SDMXParser(String outPath)
	{
		outputFilePath = outPath;
	}
	
	public SDMXParser(){}

	public void downLoadTSV(String id, String sdmxFilePath, String tsvFilePath) throws Exception
	{
		OutputStream os = new FileOutputStream(outputFilePath + id + ".rdf");

		try {

			// instead of keep the URL connection alive, we also download *.tsv and work on it locally
			InputStream is = new GZIPInputStream(new FileInputStream(tsvFilePath));
			InputStream is_1 = new GZIPInputStream(new FileInputStream(tsvFilePath));
						
			String encoding = "ISO-8859-1";
			BufferedReader in = new BufferedReader(new InputStreamReader(is, encoding));
			BufferedReader in_1 = new BufferedReader(new InputStreamReader(is_1, encoding));
			
			XMLOutputFactory factory = XMLOutputFactory.newInstance();
			XMLStreamWriter ch = factory.createXMLStreamWriter(os, "utf-8");

			String freq = get_FREQ_fromSDMX(sdmxFilePath);
			
			DataPage.convert(ch, id, in, in_1, freq, id,logFilePath);
			
			ch.close();
			is.close();
			is_1.close();
			
		} catch (IOException e) {
			DiffToC.writeLog( "IOException in SDMXParser : " + e.getMessage());
			return;
		} catch (XMLStreamException e) {
			DiffToC.writeLog("XMLStreamException in SDMXParser : " + e.getMessage());
			return;
		} catch (RuntimeException e) {
			DiffToC.writeLog("RuntimeException in SDMXParser : " + e.getMessage());
			return;			
		}
		
		os.close();

	}

	public String get_FREQ_fromSDMX(String sdmxFilePath)
	{
		String freq = "";
		int counter = 0;
		try {
			XMLInputFactory inputFactory = XMLInputFactory.newInstance();
			InputStream in = new FileInputStream(sdmxFilePath);
			XMLEventReader eventReader = inputFactory.createXMLEventReader(in);
			
			while (eventReader.hasNext()) 
			{
				XMLEvent event = eventReader.nextEvent();
				if (event.isStartElement()) 
				{
					StartElement startElement = event.asStartElement();
					// if the element is starting with <data:Series
					if (startElement.getName().getLocalPart() == "Series") 
					{
						counter += 1;
						Iterator<Attribute> attributes = startElement.getAttributes();
						while (attributes.hasNext()) 
						{
							Attribute attribute = attributes.next();
							// if it has a FREQ attribute
							if (attribute.getName().toString().equals("FREQ")) 
							{
								freq = attribute.getValue();
								break;
							}
						}
					}
					// if freq is found or in 10 observations we failed to find the FREQ attribute than 
					// break the loop in order to avoid reading whole XML file.
					if(!freq.equals("") || counter >= 10)
						break;
				}
			}
			
		}catch (FileNotFoundException e) {
			e.printStackTrace();
			DiffToC.writeLog("Error while reading the sdmx XML file. FileNotFoundException : " + e.getMessage());
		} catch (XMLStreamException e) {
			e.printStackTrace();
			DiffToC.writeLog("Error while reading the sdmx XML file. XMLStreamException : " + e.getMessage());
		}
		
		return freq;
	}
	
	private static void usage()
	{
		System.out.println("usage: SDMXParser [parameters]");
		System.out.println();
		System.out.println("	-f filename		Name of the file.");
		System.out.println("	-i file path	File path of the SDMX xml file.");
		System.out.println("	-t tsv file path	File path of the SDMX tsv file.");
		System.out.println("	-o output file path	Output directory path to generate DataCube representation of observations.");
		System.out.println("	-l log file path	File path where the logs will be generated.");
		
	}
	
	public static void main(String[] args) throws Exception
	{
		String fileName = "";
		String sdmxFilePath = "";
		String tsvFilePath = "";
		
		CommandLineParser parser = new BasicParser( );
		Options options = new Options( );
		options.addOption("h", "help", false, "Print this usage information");
		options.addOption("f", "filename", true, "Name of the file.");
		options.addOption("i", "file path", true, "File path of the SDMX xml file.");
		options.addOption("t", "tsv file path", true, "File path of the SDMX tsv file.");
		options.addOption("o", "output file path", true, "Output directory path to generate DataCube representation of observations");
		options.addOption("l", "log file path", true, "File path where the logs will be written.");
		
		CommandLine commandLine = parser.parse( options, args );
		
		if( commandLine.hasOption('h') ) {
		    usage();
		    return;
		 }
		
		if(commandLine.hasOption('f'))
			fileName = commandLine.getOptionValue('f');
		
		if(commandLine.hasOption('i'))
			sdmxFilePath = commandLine.getOptionValue('i');
		
		if(commandLine.hasOption('t'))
			tsvFilePath = commandLine.getOptionValue('t');
		
		if(commandLine.hasOption('o'))
			outputFilePath = commandLine.getOptionValue('o');
		
		if(commandLine.hasOption('l'))
			logFilePath = commandLine.getOptionValue('l');
		
		
		if(tsvFilePath.equals("") || fileName.equals("") || sdmxFilePath.equals("") || outputFilePath.equals("") || logFilePath.equals(""))
		{
			usage();
			return;
		}
		else
		{
			SDMXParser obj = new SDMXParser();
			obj.downLoadTSV(fileName, sdmxFilePath, tsvFilePath);
		}
	}
}
