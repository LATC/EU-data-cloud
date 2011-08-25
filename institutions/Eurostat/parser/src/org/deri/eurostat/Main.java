package org.deri.eurostat;

import org.deri.eurostat.toc.ParseToC;

public class Main {

	public static String dsdDirPath = "C:/test/dsd/";
	public static String sdmxDirPath = "C:/test/data/";
	public static String zipDirPath = "C:/test/data/";
	
	public static void main(String[] args)
	{
		dsdDirPath = args[0];
		sdmxDirPath = args[1];
		
		ParseToC obj = new ParseToC();
		obj.RDFize(zipDirPath);
	}

}