package com.deri.latc.utility;

import java.io.BufferedReader;
import java.io.FileNotFoundException;
import java.io.FileReader;
import java.io.IOException;
import java.util.regex.Matcher;
import java.util.regex.Pattern;


/**
*  Loading parameter for running runtime
* @author Nur Aini Rakhmawati 
* @since February 2011
*/
public class Parameters {
	
	public static String HADOOP_PATH = "";
	public static String HDFS_USER = System.getProperty("user.name");
	public static String LATC_CONSOLE_HOST = "";
	public static String LINKS_FILE_STORE  = "links.nt";
	public static String RESULTS_HOST = "";
	public static String RESULT_LOCAL_DIR = "results";
	public static String SPEC_FILE = "spec.xml";
	public static String VOID_FILE = "void.ttl";
	public static String API_KEY ="";
	
	/**
	 * 
	 * @param pathconfigfile	path of configuration file
	 * @throws IOException
	 */
	public static void load(String pathconfigfile) throws IOException
	{
		int requiredparam =0;
		try {
			BufferedReader in = new BufferedReader(new FileReader(pathconfigfile));
			String readLine;
			String patternStr = "[ \t]*(.+)=[ \t]*(.+)";
			Pattern pattern = Pattern.compile(patternStr);
			
			 while ((readLine = in.readLine()) != null) {
				 Matcher matcher = pattern.matcher(readLine);
				 if(matcher.find())
				 {
					  // commented line with hash, ignore it
					 if(matcher.group(1).startsWith("#"))
						 continue;
					 else
					 {
					     String key = matcher.group(1).trim();
					     String value = matcher.group(2).trim();
						 if(key.contentEquals("HADOOP_PATH"))
						 {
					        	HADOOP_PATH=removeslash(value);
					        	requiredparam++;
						 }
					     else if (key.contentEquals("HDFS_USER"))
					        	HDFS_USER=value;
					     else if (key.contentEquals("LATC_CONSOLE_HOST"))
					     {
					        	LATC_CONSOLE_HOST=removeslash(value);
					        	requiredparam++;
						 }
					     else if (key.contentEquals("LINKS_FILE_STORE"))
					        	LINKS_FILE_STORE=value;
					     else if (key.contentEquals("RESULTS_HOST"))
					     {
					        	RESULTS_HOST=removeslash(value);
					        	requiredparam++;
						 }
					     else if (key.contentEquals("RESULT_LOCAL_DIR"))
					        	RESULT_LOCAL_DIR=removeslash(value);
					     else if (key.contentEquals("SPEC_FILE"))
					        	SPEC_FILE=value;
					     else if (key.contentEquals("VOID_FILE"))
					        	VOID_FILE=value;
					     else if (key.contentEquals("API_KEY"))
					     {
					    	 API_KEY=value;
					     	requiredparam++;
						 }
					 }
				 }
				 
				 
			      
			      
			   
			 }
			
		} catch (FileNotFoundException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}
		
		if(requiredparam < 4)
		{
			System.err.print("more parameter required please check your configuration file");
			System.exit(0);
		}
		
	}  
	
	/**
	 * Remove / 
	 * @param word	the URL 
	 * @return
	 */
	private static String removeslash(String word)
	{
		if(word.endsWith("/"))
			return word.substring(0, word.length()-1);
		else
			return word;
	}
	
	   public static void main(String[] args) throws Exception {

	        Parameters.load(args[0]);
	        System.out.println(Parameters.LATC_CONSOLE_HOST);
	        System.out.println(Parameters.HADOOP_PATH);

	    }

}
