package org.deri.eurostat.zip;

import java.net.*;
import java.util.*;
import java.util.zip.*;
import java.io.*;

import org.deri.eurostat.Main;
import org.deri.eurostat.dsdparser.DSDParser;
import com.ontologycentral.estatwrap.SDMXParser;
import org.apache.commons.cli.*;

/**
 * Download the compressed sdmx file from Eurostat download page, uncompress the files
 * i.e. SDMX and DSD files and call the appropriate parsers to RDFize DSD and SDMX
 * observations using RDF DataCube vocabulary.
 *  
 * @author Aftab Iqbal
 *
 */
public class UnCompressXML {

	//public static String tmpZipPath = "C:/tempZip/";
	public static String tmpZipPath = "/home/romulus/EuroStat/zip/";
	
	public void parseZipFile(String fileName, String downLoadPath)
	{
		tmpZipPath = downLoadPath;
		//System.out.println("tmpZipPath" + tmpZipPath);
		try {
			
//			URL url = new URL(fileURL);
//			HttpURLConnection conn = (HttpURLConnection)url.openConnection();
//			InputStream is = conn.getInputStream();
//
//			if (conn.getResponseCode() != 200) {
//				System.err.println(conn.getResponseCode());
//			}

			// download zip file to a tmp directory
			//String fileName = fileURL.substring(fileURL.lastIndexOf("/")+1);
			//downLoadZip(is,fileName);
			//fileName = fileName.substring(fileName.lastIndexOf("/")+1);
			readZipFile(fileName);
			
//		} catch (IOException e) {
//			e.printStackTrace();
//			return;
//		}
		}catch(Exception e) {
			e.printStackTrace();
		}
	}
/*	
	// download compressed file to a temp directory
	public void downloadZip(InputStream is, String file) throws IOException
	{
		//System.out.println("Download Path --> " + tmpZipPath + file);
		int length = 0;
		byte[] buffer = new byte[1024];
		OutputStream os = new FileOutputStream(tmpZipPath + file);
		
		while( (length = is.read(buffer)) > 0)
			os.write(buffer, 0, length);
		
		os.close();
		is.close();
	}
*/	
	// Read the contents of the compressed file and call appropriate functions to parse the DSD and SDMX files
	public void readZipFile(String file)
	{
		//System.out.println("Reading Path --> " + tmpZipPath + file);
		try {
			//System.out.println("file : " + file);
			ZipFile zipFile = new ZipFile(file);
			Enumeration e = zipFile.entries();
			
			while(e.hasMoreElements())
	        	{
	        		ZipEntry entry = (ZipEntry)e.nextElement();
	        		InputStream is = new BufferedInputStream(zipFile.getInputStream(entry));
	        		String id = entry.getName().substring(0,entry.getName().indexOf("."));
	        		
	        		if(entry.getName().contains(".dsd.xml"))
	        		{
	        			createXML(is, id, ".dsd.xml");
	        			
	        			// to parse the DSD file
	        			//parseDSD(is);
	        		}
	        		else if(entry.getName().contains(".sdmx.xml"))
	        		{
	        			createXML(is, id, ".sdmx.xml");
	        			
	        			// to parse the SDMX file
	        			//parseSDMX(entry.getName().substring(0,entry.getName().indexOf(".")));
	        		}
	        	}
		}catch(Exception e) {
				e.printStackTrace();
		}
	}
	
	public void createXML(InputStream in, String id, String fileType) throws IOException
	{
		String outFileName = fileType;
		
		try {
			 //System.out.println("id : " + id);
		     OutputStream out = new FileOutputStream(tmpZipPath + id + outFileName);
		    
		     // Transfer bytes from the compressed file to the output file
		     byte[] buf = new byte[1024];
		     int len;
		     while ((len = in.read(buf)) > 0) {
		    	 out.write(buf, 0, len);
		     }

		     // Close the streams
		     out.close();
		     in.close();
		    } catch (IOException e) {
				System.err.println("Error : " + e.getMessage());
		    }
		    
	}
	
	public void parseDSD(InputStream is) throws IOException
	{
		DSDParser obj = new DSDParser();
		obj.initObjects(is);
		obj.parseFile();
	}
	
	public void parseSDMX(String sdmxFile, String sdmxFilePath) throws Exception
	{
		System.out.println("Parsing SDMX file : " + sdmxFile);
		SDMXParser obj = new SDMXParser(Main.sdmxDirPath);
		obj.downLoadTSV(sdmxFile, sdmxFilePath);
	}
	
	private static void usage()
	{
		System.out.println("usage: UnCompressFile [parameters]");
		System.out.println();
		System.out.println("	-i file path		Compressed file path.");
		System.out.println("	-o output path		Directory path for uncompressing the contents of the file.");
	}

	public static void main(String[] args) throws Exception
	{
		String filepath = "";
		String outputpath = "";
		CommandLineParser parser = new BasicParser( );
		Options options = new Options( );
		options.addOption("h", "help", false, "Print this usage information");
		options.addOption("i", "file path", true, "Compressed file path.");
		options.addOption("o", "output path", true, "Directory path for uncompressing the contents of the file.");
		CommandLine commandLine = parser.parse( options, args );
		
		if( commandLine.hasOption('h') ) {
		    usage();
		    return;
		 }
		
		if(commandLine.hasOption('i'))
			filepath = commandLine.getOptionValue('i');
		
		if(commandLine.hasOption('o'))
			outputpath = commandLine.getOptionValue('o');
		
		if(outputpath.equals("") || filepath.equals(""))
		{
			usage();
			return;
		}
		else
		{
			tmpZipPath = outputpath;
			UnCompressXML obj = new UnCompressXML();
			obj.parseZipFile(filepath,tmpZipPath);
		}
		
	
	}
	
}
