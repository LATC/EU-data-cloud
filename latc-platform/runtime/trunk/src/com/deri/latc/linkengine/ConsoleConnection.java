/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
package com.deri.latc.linkengine;

import org.apache.commons.httpclient.*;
import org.apache.commons.httpclient.methods.*;
import org.apache.commons.httpclient.params.HttpMethodParams;
import net.sf.json.JSONObject;

import com.deri.latc.dto.VoidInfoDto;
import java.io.*;

/**
 * Class Console client handles REST connection
 * @author jamnas
 * @author Nur Aini Rakhmawati
 */
public class ConsoleConnection {

	 private final String consolehost;
	 private String message=null;

	 /**
	  * Constructor of ConsoleConnection
	  * @param console	String URL of console server
	  */
	 public ConsoleConnection(final String console){
		this.consolehost = console+"/api";
		
	}

	 /**
	  * POST Method
	  * @param url	The URL of posting data
	  * @param data	The data that puts on body
	  * @return	true if post data successfully
	  */
    private boolean postData(String url, NameValuePair [] data) 
    {
    	boolean status = false; 
    	HttpClient client = new HttpClient();
         PostMethod post = new PostMethod();
         try {
			post.setURI(new URI(url, false));
	
         post.setRequestBody(data);
         
         if (client.executeMethod(post) != HttpStatus.SC_OK) {
             this.message = post.getStatusText();
         }
         else 
        	 status =true;
     	} catch (URIException e) {
     		this.message = e.getMessage();
			
		} catch (NullPointerException e) {
			this.message = e.getMessage();
		} catch (HttpException e) {
			this.message = e.getMessage();
		} catch (IOException e) {
			this.message = e.getMessage();
		}
		finally {
           post.releaseConnection();
        }
         return status;
    }
    
    
    /**
     * GET method
     * @param url	The URL of get data
     * @return	true if GET data successfully
     */
    private boolean getData(String url) {
       
    	boolean status = false; 
        HttpClient client = new HttpClient();
        GetMethod get = new GetMethod(url);
       
        get.getParams().setParameter(HttpMethodParams.RETRY_HANDLER,
                new DefaultHttpMethodRetryHandler(3, false));
        try {
        	if (client.executeMethod(get) != HttpStatus.SC_OK) {
                this.message = get.getStatusText();
            }
        
            else
            {
            	status =true; 
            	StringBuffer responseBuffer = new StringBuffer();
                InputStreamReader stream = new InputStreamReader(get.getResponseBodyAsStream(), "UTF-8");
                int data = stream.read();
                 while (data!=-1)
                 {
                		 responseBuffer.append((char)data);
                 		  data = stream.read();
                 }
                 this.message = responseBuffer.toString();
                 stream.close();
	           
            }

        } catch (HttpException e) {
        	this.message = e.getMessage();
        } catch (IOException e) {
        	this.message = e.getMessage();
        } finally {
            // Release the connection.
            get.releaseConnection();
        }
        return status;
    }

    /**
     * Getting error message
     * @return	Error message, if there is no error it return null
     */
   public String getMessage()
   {
	   return this.message;
	   
   }
    
   /**
    * Post report after executing joB <br/> severity value is <i>info</i> if the job success and <i>warn</i> if job is failed 
    * @param id	ID of job (random)
    * @param vi	Void handler
    * @return	true if posting report successfully
    * @throws Exception
    */
   public boolean postReport(String id, VoidInfoDto vi, String apikey) throws Exception {
       final String url = consolehost + "/task/" + id + "/notifications";
       JSONObject data = new JSONObject();
       data.put("size", vi.getStatItem());
       data.put("location", vi.getDataDump());
       String severity = "info";
       
       if (vi.getStatItem() < 0)
    	   severity ="warn";
       
       NameValuePair[] request = {
           new NameValuePair("message", vi.getRemarks()),
           new NameValuePair("severity", severity),
           new NameValuePair("data", data.toString()),
           new NameValuePair("api_key", apikey)
       };
       return this.postData(url, request);   
   }
   
     
   /**
    * Get list of task from console
    * @return true if get list successfully
    */
   
   public boolean getTasks()
   {
	   final String url = consolehost + "/tasks";
	   return this.getData(url);	   
   }
   
   /**
    * Get specification file, given specified ID
    * @param id	ID of task
    * @return	true if get specification successfully	
    */
   public boolean getSpec(String id)
   {
	   final String url = consolehost + "/task/" + id + "/configuration";
	   return this.getData(url);	 
   }
   
   /**
    * Get information of Job ID, given specified ID
    * @param id	ID of task
    * @return	true if get information successfully
    */
   public boolean getReport(String id)
   {
	   final String url = consolehost + "/task/" + id + "/notifications";
	   return this.getData(url);	 
   }
}
