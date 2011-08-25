package org.deri.eurostat.mirror;

import org.apache.commons.cli.BasicParser;
import org.apache.commons.cli.CommandLine;
import org.apache.commons.cli.CommandLineParser;
import org.apache.commons.cli.Options;
import org.deri.eurostat.toc.ParseToC;

public class EuroStatMirror {

	public static String filePath = "";
	private static void usage()
	{
		System.out.println("usage: UnCompressFile [parameters]");
		System.out.println();
		System.out.println("	-p path		Directory path for downloading the compressed files.");
		
	}
	
	public static void main(String[] args) throws Exception
	{
		/*
		CommandLineParser parser = new BasicParser( );
		Options options = new Options( );
		options.addOption("h", "help", false, "Print this usage information");
		options.addOption("p", "path", true, "Directory path for downloading the compressed files.");
		CommandLine commandLine = parser.parse( options, args );

		if( commandLine.hasOption('h') ) {
		    usage();
		    return;
		 }
		
		if(commandLine.hasOption('p'))
			filePath = commandLine.getOptionValue('p');
		
		if(filePath.equals(""))
		{
			usage();
			return;
		}
		else
		{
			ParseToC obj = new ParseToC();
			obj.downloadZip(filePath);
		}
		*/
		
		filePath = args[0];
		ParseToC obj = new ParseToC();
		obj.downloadZip(filePath);
	}
}
