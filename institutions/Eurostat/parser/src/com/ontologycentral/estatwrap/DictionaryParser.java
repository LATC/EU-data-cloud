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

public class DictionaryParser {

	//public static String[] LANG = { "en", "de", "fr" } ;
	public static String[] LANG = { "en" } ;
	private static String outputFilePath = "C:/dic/";
	private static String dictionaryPath = "all_dic/";
	
	public void loadDictionaries() throws Exception
	{
		 File dir = new File(dictionaryPath);
		 
		 File[] files = dir.listFiles();
		 
		 for(File dic:files)
			 downloadDictionary(dic.getName());
	}
	
	public void downloadDictionary(String id) throws Exception
	{
		OutputStream os = new FileOutputStream(outputFilePath + id + ".rdf");
		XMLStreamWriter ch = null;
		List<Reader> rli = new ArrayList<Reader>();
		try {

			XMLOutputFactory factory = XMLOutputFactory.newInstance();
			ch = factory.createXMLStreamWriter(os, "utf-8");
			
			for (String lang : LANG) {
				StringReader sr = null;

				URL url = new URL("http://epp.eurostat.ec.europa.eu/NavTree_prod/everybody/BulkDownloadListing?file=dic/" + lang + "/" + id);
				//URL url = new URL("http://europa.eu/estatref/download/everybody/dic/en/" + id + ".dic");

				System.out.println("looking up " + url);

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
	
	public static void main(String[] args) throws Exception
	{
		DictionaryParser obj = new DictionaryParser();
		//obj.downloadDictionary("sex");
		obj.loadDictionaries();
	}
	
}
