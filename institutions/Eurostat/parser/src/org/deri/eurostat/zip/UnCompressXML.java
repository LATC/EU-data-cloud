package org.deri.eurostat.zip;

import java.net.*;
import java.util.*;
import java.util.zip.*;
import java.io.*;

import org.deri.eurostat.dsdParser.dsdParser_Jena;

public class UnCompressXML {

	
	public static String tmpZipPath = "C:/tempZip/";
	
	public void parseZipFile(String id)
	{
		
		try {
			
			URL url = new URL("http://epp.eurostat.ec.europa.eu/NavTree_prod/everybody/BulkDownloadListing?file=data/" + id + ".sdmx.zip");
			HttpURLConnection conn = (HttpURLConnection)url.openConnection();
			InputStream is = conn.getInputStream();

			if (conn.getResponseCode() != 200) {
				System.err.println(conn.getResponseCode());
			}

			// download zip file to a tmp directory
			downloadZip(is, id);

			readZipFile(id);
			
		} catch (IOException e) {
			e.printStackTrace();
			return;
		}
	}
	
	// download Zip file to a tmp directory
	public void downloadZip(InputStream is, String id) throws IOException
	{
		int length = 0;
		byte[] buffer = new byte[1024];
		OutputStream os = new FileOutputStream(tmpZipPath + id + ".sdmx.zip");
		
		while( (length = is.read(buffer)) > 0)
			os.write(buffer, 0, length);
		
		os.close();
		is.close();
	}
	
	// this function will read the contents of the zip file and call appropriate functions to parse the DSD and SDMX files
	public void readZipFile(String id) throws IOException
	{
		ZipFile zipFile = new ZipFile(tmpZipPath + id + ".sdmx.zip");
		Enumeration e = zipFile.entries();
		
		 while(e.hasMoreElements())
	        {
	        	ZipEntry entry = (ZipEntry)e.nextElement();
	        	
	        	InputStream is = new BufferedInputStream(zipFile.getInputStream(entry));
	        	
	        	if(entry.getName().contains(".dsd.xml"))
	        	{
	        		//createXML(is, id, ".dsd.xml");
	        		parseDSD(is);
	        	}
	        	else if(entry.getName().contains(".sdmx.xml"))
	        	{
	        		createXML(is, id, ".sdmx.xml");
	        		//parseSDMX(is);
	        	}
	        }
	}
	
	public void createXML(InputStream in, String id, String fileType) throws IOException
	{
		String outFileName = "";
		if(fileType.equals("dsd.xml"))
			outFileName = fileType;
		else
			outFileName = fileType;
		
		try {
			 
		     OutputStream out = new FileOutputStream(tmpZipPath + id + outFileName);
		    
		     // Transfer bytes from the ZIP file to the output file
		     byte[] buf = new byte[1024];
		     int len;
		     while ((len = in.read(buf)) > 0) {
		    	 out.write(buf, 0, len);
		     }

		     // Close the streams
		     out.close();
		     in.close();
		    } catch (IOException e) {
				System.out.println("Error : " + e.getMessage());
		    }
		    
	}
	
	public void parseDSD(InputStream is) throws IOException
	{
		dsdParser_Jena obj = new dsdParser_Jena();
		obj.initObjects(is);
		obj.parseFile();
	}
	
	public static void main(String[] args)
	{
		UnCompressXML obj = new UnCompressXML();
		obj.parseZipFile("tsieb010");
	}
}
