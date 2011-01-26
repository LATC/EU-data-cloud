package com.deri.latc.utility;

import java.io.BufferedReader;
import java.io.FileNotFoundException;
import java.io.FileReader;
import java.io.IOException;

/*
 * Loading parameter for running runtime
 * @author : nurainir
 */

public class LoadParameter {
	
	public static String HADOOP_PATH = "hadoop-0.20.2";
	public static String HADOOP_USER = "/user/nurgun";
	public static String LATC_CONSOLE_HOST = "http://fspc409.few.vu.nl/LATC_Console/api";
	public static String LINKS_FILE_STORE  = "links.nt";
	public static String RESULTS_HOST = "http://demo.sindice.net/latctemp";
	public static String RESULT_LOCAL_DIR = "results";
	public static String SPEC_FILE = "spec.xml";
	public static String VOID_FILE = "void.ttl";
	
	public LoadParameter()
	{}
	
	public LoadParameter(String pathconfigfile) throws IOException
	{
		try {
			BufferedReader in = new BufferedReader(new FileReader(pathconfigfile));
			 String readLine;
			 while ((readLine = in.readLine()) != null) {
				   final String [] split = readLine.split("=");
			        if (split.length<2  || split.length>2)
			        {
			        	continue;
			        }
			        if(split[0].trim().contentEquals("HADOOP_PATH"))
			        	HADOOP_PATH=split[1].trim();
			        else if (split[0].trim().contentEquals("HADOOP_USER"))
			        	HADOOP_USER=split[1].trim();
			        else if (split[0].trim().contentEquals("LATC_CONSOLE_HOST"))
			        	LATC_CONSOLE_HOST=split[1].trim();
			        else if (split[0].trim().contentEquals("LINKS_FILE_STORE"))
			        	LINKS_FILE_STORE=split[1].trim();
			        else if (split[0].trim().contentEquals("RESULTS_HOST"))
			        	RESULTS_HOST=split[1].trim();
			        else if (split[0].trim().contentEquals("RESULT_LOCAL_DIR"))
			        	RESULT_LOCAL_DIR=split[1].trim();
			        else if (split[0].trim().contentEquals("SPEC_FILE"))
			        	SPEC_FILE=split[1].trim();
			        else if (split[0].trim().contentEquals("VOID_FILE"))
			        	VOID_FILE=split[1].trim();
			 }
			
		} catch (FileNotFoundException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}
		
		
	}      

}
