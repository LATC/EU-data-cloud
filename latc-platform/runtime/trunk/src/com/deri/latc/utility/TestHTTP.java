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
	
	/**
	 * Getting error message 
	 * @return
	 */
	public static String getMessage()
	{
		return message;
	}
	
	/**
	 * Testing the availability of URL  
	 * @param URLName	the URL path
	 * @return
	 */
	public static boolean test(final String URLName){
	    boolean result =false;		  
		  try {
	      HttpURLConnection con =
	         (HttpURLConnection) new URL(URLName).openConnection();
	      con.setRequestMethod("GET");
	      
	 	     
	     if(con.getResponseCode() == HttpURLConnection.HTTP_OK || con.getResponseCode() == HttpURLConnection.HTTP_BAD_REQUEST 
	    		 || con.getResponseCode() == HttpURLConnection.HTTP_INTERNAL_ERROR)
	      result = true;
	    }
	    catch (Exception e) {
	      message = e.getMessage();	       
	    }
	    return result;
	  }

	  public static void main(String[] args) {
		  System.out.print(TestHTTP.test("http://semanticweb.cs.vu.nl/europeana/sparql/"));
		  System.out.print(TestHTTP.getMessage());
	  }
	  

}
