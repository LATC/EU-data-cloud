package org.deri.eurostat.mirror;

import org.apache.commons.cli.BasicParser;
import org.apache.commons.cli.CommandLine;
import org.apache.commons.cli.CommandLineParser;
import org.apache.commons.cli.Options;
import org.deri.eurostat.toc.ParseToC;

/**
 * 
 * @author Aftab Iqbal
 *
 */

public class EuroStatMirror {

	public static String filePath = "";
	public static String tsvFilePath = "";
	private static void usage()
	{
		System.out.println("usage: UnCompressFile [parameters]");
		System.out.println();
		System.out.println("	-p path		Directory path for downloading the zip files.");
		System.out.println("	-t tsv path		Directory path for downloading the compressed tsv files.");
	}
	
	public static void main(String[] args) throws Exception
	{
		
		CommandLineParser parser = new BasicParser( );
		Options options = new Options( );
		options.addOption("h", "help", false, "Print this usage information");
		options.addOption("p", "path", true, "Directory path for downloading the zip files.");
		options.addOption("t", "path", true, "Directory path for downloading the compressed tsv files.");
		
		CommandLine commandLine = parser.parse( options, args );

		if( commandLine.hasOption('h') ) {
		    usage();
		    return;
		 }
		
		if(commandLine.hasOption('p'))
			filePath = commandLine.getOptionValue('p');
		
		if(commandLine.hasOption('t'))
			tsvFilePath = commandLine.getOptionValue('t');
		
		if(filePath.equals("") || tsvFilePath.equals(""))
		{
			usage();
			return;
		}
		else
		{
			ParseToC obj = new ParseToC();
			obj.downloadZip(filePath, tsvFilePath);
		}
		
	}
}
