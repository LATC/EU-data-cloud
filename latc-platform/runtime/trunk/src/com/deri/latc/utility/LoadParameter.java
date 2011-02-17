package com.deri.latc.utility;

import java.io.BufferedReader;
import java.io.FileNotFoundException;
import java.io.FileReader;
import java.io.IOException;


/**
*  Loading parameter for running runtime
* @author Nur Aini Rakhmawati 
* @since February 2011
*/
public class LoadParameter {
	
	public String HADOOP_PATH = "hadoop-0.20.2";
	public String HDFS_USER = System.getProperty("user.name");
	public String LATC_CONSOLE_HOST = "http://fspc409.few.vu.nl/LATC_Console";
	public String LINKS_FILE_STORE  = "links.nt";
	public String RESULTS_HOST = "http://demo.sindice.net/latctemp";
	public String RESULT_LOCAL_DIR = "results";
	public String SPEC_FILE = "spec.xml";
	public String VOID_FILE = "void.ttl";
	
	public LoadParameter()
	{}
	
	public LoadParameter(String pathconfigfile) throws IOException
	{
		try {
			BufferedReader in = new BufferedReader(new FileReader(pathconfigfile));
			 String readLine;
			 while ((readLine = in.readLine()) != null) {
				    final String [] split = readLine.split("=");
			     // wrong format
				    if (split.length<2  || split.length>2)
			        {
			        	continue;
			        }
			        // commented line with hash, ignore it
			        if(split[1].trim().startsWith("#"))
			        	continue;
			        if(split[0].trim().contentEquals("HADOOP_PATH"))
			        	HADOOP_PATH=this.removeslash(split[1].trim());
			        else if (split[0].trim().contentEquals("HDFS_USER"))
			        	HDFS_USER=split[1].trim();
			        else if (split[0].trim().contentEquals("LATC_CONSOLE_HOST"))
			        	LATC_CONSOLE_HOST=this.removeslash(split[1].trim());
			        else if (split[0].trim().contentEquals("LINKS_FILE_STORE"))
			        	LINKS_FILE_STORE=split[1].trim();
			        else if (split[0].trim().contentEquals("RESULTS_HOST"))
			        	RESULTS_HOST=this.removeslash(split[1].trim());
			        else if (split[0].trim().contentEquals("RESULT_LOCAL_DIR"))
			        	RESULT_LOCAL_DIR=this.removeslash(split[1].trim());
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
	
	private String removeslash(String word)
	{
		if(word.endsWith("/"))
			return word.substring(0, word.length()-1);
		else
			return word;
	}
	
	   public static void main(String[] args) throws Exception {

	        LoadParameter lp = new LoadParameter(args[0]);
	        System.out.println(lp.LATC_CONSOLE_HOST);
	        System.out.println(lp.HADOOP_PATH);

	    }

}
