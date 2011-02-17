package com.deri.latc.utility;

import java.net.HttpURLConnection;
import java.net.URL;


/**
* Testing HTTP Connection
* @author Nur Aini Rakhmawati 
* @since February 2011
*/

public class TestHTTP {

	private static String message;;
	
	public static String getMessage()
	{
		return message;
	}
	
	  public static boolean test(final String URLName){
	    boolean result =false;		  
		  try {
	      HttpURLConnection con =
	         (HttpURLConnection) new URL(URLName).openConnection();
	      con.setRequestMethod("HEAD");
	      //con.setRequestMethod(method)
	      System.out.println(con.getResponseMessage());
	      if(con.getResponseCode() == HttpURLConnection.HTTP_OK || con.getResponseMessage().equalsIgnoreCase("No_query_string"))
	      result = true;
	    }
	    catch (Exception e) {
	      message = e.getMessage();	       
	    }
	    return result;
	  }

	  public static void main(String[] args) {
		  System.out.print(TestHTTP.test("http://data.linkedmdb.org/sparql"));
		  System.out.print(TestHTTP.getMessage());
	  }
	  

}
