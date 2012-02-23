package org.deri.eurostat.zip;

import java.io.FileOutputStream;
import java.io.IOException;
import java.io.InputStream;
import java.io.OutputStream;
import java.net.HttpURLConnection;
import java.net.URL;

import org.apache.commons.cli.BasicParser;
import org.apache.commons.cli.CommandLine;
import org.apache.commons.cli.CommandLineParser;
import org.apache.commons.cli.Options;

/**
 * 
 * @author Aftab Iqbal
 *
 */

public class DownloadZip {

	public static String tmpZipPath = "";
	public static String tmpTsvPath = "";
	int count = 0;
	
	public void zipURL(String fileURL, String tempZipPath, String tempTsvPath)
	{
		tmpZipPath = tempZipPath;
		
		System.out.println("Downloading file No# " + ++count + " from " + fileURL);
		try {
			
			URL url = new URL(fileURL);
			HttpURLConnection conn = (HttpURLConnection)url.openConnection();
			InputStream is = conn.getInputStream();

			if (conn.getResponseCode() != 200) {
				System.err.println(conn.getResponseCode());
			}

			// download zip file to a tmp directory
			String fileName = fileURL.substring(fileURL.lastIndexOf("/")+1);
			downloadZip(is, fileName);
			
			is.close();
			
			//download TSV file
			tsvURL(fileURL.substring(0,fileURL.indexOf(".sdmx.zip")) + ".tsv.gz", tempTsvPath);
			
		} catch (IOException e) {
			e.printStackTrace();
			return;
		}
		catch(Exception e) {
			e.printStackTrace();
		}
	}
	
	public void tsvURL(String fileURL, String downloadPath)
	{
		tmpTsvPath = downloadPath;
		
		System.out.println("Downloading tsv file No# " + count + " from " + fileURL);
		try {
			
			URL url = new URL(fileURL);
			HttpURLConnection conn = (HttpURLConnection)url.openConnection();
			InputStream is = conn.getInputStream();

			if (conn.getResponseCode() != 200) {
				System.err.println(conn.getResponseCode());
			}

			// download zip file to a tmp directory
			String fileName = fileURL.substring(fileURL.lastIndexOf("/")+1);
			downloadTSV(is, fileName);
			
			is.close();
		} catch (IOException e) {
			e.printStackTrace();
			return;
		}
		catch(Exception e) {
			e.printStackTrace();
		}
	}
	
	public void downloadZip(InputStream is, String file) throws IOException
	{
		int length = 0;
		byte[] buffer = new byte[1024];
		OutputStream os = new FileOutputStream(tmpZipPath + file);
		
		while( (length = is.read(buffer)) > 0)
			os.write(buffer, 0, length);
		
		os.close();
		is.close();
	}

	public void downloadTSV(InputStream is, String file) throws IOException
	{
		int length = 0;
		byte[] buffer = new byte[1024];
		OutputStream os = new FileOutputStream(tmpTsvPath + file);
		
		while( (length = is.read(buffer)) > 0)
			os.write(buffer, 0, length);
		
		os.close();
		is.close();
	}
	
	private static void usage()
	{
		System.out.println("usage: UnCompressFile [parameters]");
		System.out.println();
		System.out.println("	-p path		Directory path for downloading the compressed zip files.");
		System.out.println("	-u zip		URL of the compressed zip file.");
		System.out.println("	-t tsv		Directory path for downloading the compressed tsv files.");
	}

	
	public static void main(String[] args) throws Exception
	{
		String url = "";
		
		CommandLineParser parser = new BasicParser( );
		Options options = new Options( );
		options.addOption("h", "help", false, "Print this usage information");
		options.addOption("p", "path", true, "Directory path for downloading the compressed zip files.");
		options.addOption("u", "zip", true, "URL of the compressed file.");
		options.addOption("t", "tsv", true, "Directory path for downloading the compressed tsv files.");
		
		CommandLine commandLine = parser.parse( options, args );

		if( commandLine.hasOption('h') ) {
		    usage();
		    return;
		 }
		
		if(commandLine.hasOption('p'))
			tmpZipPath = commandLine.getOptionValue('p');
		
		if(commandLine.hasOption('u'))
			url = commandLine.getOptionValue('u');
		
		if(commandLine.hasOption('t'))
			tmpTsvPath = commandLine.getOptionValue('t');
		
		if(tmpZipPath.equals("") || url.equals("") || tmpTsvPath.equals(""))
		{
			usage();
			return;
		}
		else
		{
			DownloadZip obj = new DownloadZip();
			obj.zipURL(url, tmpZipPath, tmpTsvPath);
		}
	}
	
}
