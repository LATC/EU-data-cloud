package com.deri.latc.dto;

import java.io.BufferedReader;
import java.io.FileNotFoundException;
import java.io.FileReader;
import java.io.IOException;
import java.util.HashMap;
import java.util.Map;
import java.util.regex.Matcher;
import java.util.regex.Pattern;

public class VoidPrefix {
	
	private final static Map <String,String> prefix=new HashMap<String, String>();
	
	public static void loadPrefix()
	{
		BufferedReader in;
		try {
			in = new BufferedReader(new FileReader("voidtmpl"));
			String patternStr = "[ \t]*@prefix[ \t]*(.*):[ \t]*<(.*)>";
			Pattern pattern = Pattern.compile(patternStr);
			String readLine;
			 while ((readLine = in.readLine()) != null) {
				/* if(!readLine.startsWith("@prefix"))
					 break;
				 final String [] split = readLine.split("[\t ]");
			     // wrong format
				    if (split.length<2  || split.length>2)
			        {
			        	continue;
			        }*/
				 Matcher matcher = pattern.matcher(readLine);
				 if(matcher.find())
				    prefix.put(matcher.group(1),matcher.group(2));
				 if(readLine.startsWith("**newprefix**"))
					 break;
				    
			 }
		} catch (FileNotFoundException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		} catch (IOException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}
		 
	}
	
	public static Map <String,String> getPrefix()
	{
		if(prefix.size()==0)
		{
			System.err.print("run loadPrefix first or please make sure file voidprefix is exist");
			System.exit(0);
			return null;
		}
		else
			return prefix;
				
	}
	/**
	 * @param args
	 */
	public static void main(String[] args) {
		
		VoidPrefix.loadPrefix();
		Map <String,String> prefixes = VoidPrefix.getPrefix();
		if(prefixes.size()>0)
		{
			for (final String ns : prefixes.keySet() )
				System.out.print("@prefix "+ns+": <"+prefixes.get(ns)+">. \n");
		}

	}

}
