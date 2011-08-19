package com.ontologycentral.estatwrap;

import java.io.BufferedReader;
import java.io.FileOutputStream;
import java.io.IOException;
import java.io.InputStream;
import java.io.InputStreamReader;
import java.io.OutputStream;
import java.net.HttpURLConnection;
import java.net.URI;
import java.net.URISyntaxException;
import java.net.URL;
import java.util.Calendar;
import java.util.Map;
import java.util.logging.Logger;
import java.util.zip.GZIPInputStream;

import javax.xml.stream.XMLOutputFactory;
import javax.xml.stream.XMLStreamException;
import javax.xml.stream.XMLStreamWriter;

import org.apache.commons.cli.BasicParser;
import org.apache.commons.cli.CommandLine;
import org.apache.commons.cli.CommandLineParser;
import org.apache.commons.cli.Options;


public class SDMXParser {

	private static String outputFilePath = "";
	
	public void downLoadTSV(String id) throws Exception
	{
		OutputStream os = new FileOutputStream(outputFilePath + id + ".rdf");
		URL url = new URL("http://epp.eurostat.ec.europa.eu/NavTree_prod/everybody/BulkDownloadListing?file=data/" + id + ".tsv.gz");

		try {
			HttpURLConnection conn = (HttpURLConnection)url.openConnection();
			InputStream is = new GZIPInputStream(conn.getInputStream());

			if (conn.getResponseCode() != 200) {
				//resp.sendError(conn.getResponseCode());
			}

			String encoding = conn.getContentEncoding();
			if (encoding == null) {
				encoding = "ISO-8859-1";
			}

			BufferedReader in = new BufferedReader(new InputStreamReader(is, encoding));

			//resp.setHeader("Cache-Control", "public");
			Calendar c = Calendar.getInstance();
			c.add(Calendar.HOUR, 1);
			//resp.setHeader("Expires", Listener.RFC822.format(c.getTime()));
			//resp.setHeader("Content-Encoding", "gzip");

			//--//XMLOutputFactory factory = (XMLOutputFactory)ctx.getAttribute(Listener.FACTORY);
			XMLOutputFactory factory = XMLOutputFactory.newInstance();
			
			//-//Map<String, String> toc = (Map<String, String>)ctx.getAttribute(Listener.TOC);
			
			
			XMLStreamWriter ch = factory.createXMLStreamWriter(os, "utf-8");

			DataPage.convert(ch, id, in);

			ch.close();
		} catch (IOException e) {
			//resp.sendError(500, url + ": " + e.getMessage());
			System.out.println(e.getMessage());
			return;
		} catch (XMLStreamException e) {
			//resp.sendError(500, url + ": " + e.getMessage());
			System.out.println(e.getMessage());
			return;
		} catch (RuntimeException e) {
			//resp.sendError(500, url + ": " + e.getMessage());
			System.out.println(e.getMessage());
			return;			
		}

		os.close();

	}
	
	private static void usage()
	{
		System.out.println("usage: SDMXParser [parameters]");
		System.out.println();
		System.out.println("	-f filename		Name of the file.");
		System.out.println("	-o outputFilePath		Output directory path to generate DataCube representation of observations.");
		
	}
	
	public static void main(String[] args) throws Exception
	{
		String fileName = "";
		CommandLineParser parser = new BasicParser( );
		Options options = new Options( );
		options.addOption("h", "help", false, "Print this usage information");
		options.addOption("f", "filename", true, "Name of the file.");
		options.addOption("o", "filename", true, "Output directory path to generate DataCube representation of observations");
		
		CommandLine commandLine = parser.parse( options, args );
		
		if( commandLine.hasOption('h') ) {
		    usage();
		    return;
		 }
		
		if(commandLine.hasOption('f'))
			fileName = commandLine.getOptionValue('f');
		
		if(commandLine.hasOption('o'))
			outputFilePath = commandLine.getOptionValue('o');
		
		if(fileName.equals("") || outputFilePath.equals(""))
		{
			usage();
			return;
		}
		else
		{
			SDMXParser obj = new SDMXParser();
			obj.downLoadTSV(fileName);
		}
	}
}
