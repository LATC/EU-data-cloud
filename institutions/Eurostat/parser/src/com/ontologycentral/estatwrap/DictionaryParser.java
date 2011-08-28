package com.ontologycentral.estatwrap;

import java.io.BufferedReader;
import java.io.File;
import java.io.FileNotFoundException;
import java.io.FileOutputStream;
import java.io.IOException;
import java.io.InputStream;
import java.io.InputStreamReader;
import java.io.OutputStream;
import java.io.Reader;
import java.io.StringReader;
import java.net.HttpURLConnection;
import java.net.URL;
import java.util.ArrayList;
import java.util.List;

import javax.xml.stream.XMLOutputFactory;
import javax.xml.stream.XMLStreamException;
import javax.xml.stream.XMLStreamWriter;

import org.apache.commons.cli.BasicParser;
import org.apache.commons.cli.CommandLine;
import org.apache.commons.cli.CommandLineParser;
import org.apache.commons.cli.Options;

public class DictionaryParser {

	//public static String[] LANG = { "en", "de", "fr" } ;
	public static String[] LANG = { "en" } ;
	private static String outputFilePath = "";
	private static String dictionaryPath = "";
	
	public void loadDictionaries() throws Exception
	{
		 File dir = new File(dictionaryPath);
		 
		 File[] files = dir.listFiles();
		 
		 for(File dic:files)
			 downloadDictionary(dic.getName());
	}
	
	public void downloadDictionary(String id) throws Exception
	{
		
		OutputStream os = new FileOutputStream(outputFilePath + id.substring(0,id.indexOf(".dic")) + ".rdf");
		XMLStreamWriter ch = null;
		List<Reader> rli = new ArrayList<Reader>();
		try {

			XMLOutputFactory factory = XMLOutputFactory.newInstance();
			ch = factory.createXMLStreamWriter(os, "utf-8");
			
			for (String lang : LANG) {
				StringReader sr = null;

				URL url = new URL("http://epp.eurostat.ec.europa.eu/NavTree_prod/everybody/BulkDownloadListing?file=dic/" + lang + "/" + id);
				//URL url = new URL("http://europa.eu/estatref/download/everybody/dic/en/" + id + ".dic");

				System.out.println("RDFizing : " + url);

//				if (cache.containsKey(url)) {
//					sr = new StringReader((String)cache.get(url));
//				}

				if (sr == null) {
					HttpURLConnection conn = (HttpURLConnection)url.openConnection();
					InputStream is = conn.getInputStream();

//					if (conn.getResponseCode() != 200) {
//						resp.sendError(conn.getResponseCode());
//						return;
//					}

					String encoding = conn.getContentEncoding();
					if (encoding == null) {
						encoding = "ISO-8859-1";
					}

					BufferedReader in = new BufferedReader(new InputStreamReader(is, encoding));
					String l;
					StringBuilder sb = new StringBuilder();

					while ((l = in.readLine()) != null) {
						sb.append(l);
						sb.append('\n');
					}
					in.close();

					String str = sb.toString();
					sr = new StringReader(str);

//					try {
//						cache.put(url, str);
//					} catch (RuntimeException e) {
//						_log.info(e.getMessage());
//					}
				}

				rli.add(sr);
			}
			
//			resp.setHeader("Cache-Control", "public");
//			Calendar c = Calendar.getInstance();
//			c.add(Calendar.DATE, 1);
//			resp.setHeader("Expires", Listener.RFC822.format(c.getTime()));

			DictionaryPage.convert(ch, id, rli, LANG);
		} catch (XMLStreamException e) {
			e.printStackTrace();
			//resp.sendError(500, e.getMessage());
			return;
		} catch (IOException e) {
			e.printStackTrace();
			//resp.sendError(500, e.getMessage());
			return;
		} finally {
			if (ch != null) {
				try {
					ch.close();
				} catch (XMLStreamException e) {
					e.printStackTrace();
					return;
				}
			}
		}
		
		os.close();

	}
	
	private static void usage()
	{
		System.out.println("usage: UnCompressFile [parameters]");
		System.out.println();
		System.out.println("	-i Dictionary Path	Directory path where the dictionary files are stored.");
		System.out.println("	-o Output Path		Output directory path to generate the RDF.");
	}
	
	public static void main(String[] args) throws Exception
	{
		CommandLineParser parser = new BasicParser( );
		Options options = new Options( );
		options.addOption("h", "help", false, "Print this usage information");
		options.addOption("i", "dictionaryPath", true, "Directory path where the dictionary files are stored.");
		options.addOption("o", "outputPath", true, "Output directory path to generate the RDF.");
		CommandLine commandLine = parser.parse( options, args );

		if( commandLine.hasOption('h') ) {
		    usage();
		    return;
		 }
		
		if(commandLine.hasOption('i'))
			dictionaryPath = commandLine.getOptionValue('i');
		
		if(commandLine.hasOption('o'))
			outputFilePath = commandLine.getOptionValue('o');
		
		if(dictionaryPath.equals("") || outputFilePath.equals(""))
		{
			usage();
			return;
		}
		else
		{
			DictionaryParser obj = new DictionaryParser();
			obj.loadDictionaries();
		}
		
	}
	
}
