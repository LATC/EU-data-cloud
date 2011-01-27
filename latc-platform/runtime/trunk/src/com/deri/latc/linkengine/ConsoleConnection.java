/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
package com.deri.latc.linkengine;

import org.apache.commons.httpclient.*;
import org.apache.commons.httpclient.methods.*;
import org.apache.commons.httpclient.params.HttpMethodParams;

import com.deri.latc.dto.VoidInfoDto;
import java.io.*;

/**
 *
 * @author jamnas
 * @author nurainir
 */
public class ConsoleConnection {

	 private final String consolehost;
	 private String message=null;
	 
	 public ConsoleConnection(final String console){
		this.consolehost = console;
		
	}

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

   public String getMessage()
   {
	   return this.message;
	   
   }
    
   public boolean postReport(String id, VoidInfoDto vi) throws Exception {
       final String url = consolehost + "/configuration/" + id + "/reports";
       NameValuePair[] data = {
           new NameValuePair("status", vi.getRemarks()),
           new NameValuePair("location", vi.getDataDump()),
           new NameValuePair("size", vi.getStatItem())
       };
       return this.postData(url, data);   
   }
   
   
   public boolean getQueue()
   {
	   final String url = consolehost + "/queue";
	   return this.getData(url);	   
   }
   
   public boolean getSpec(String id)
   {
	   final String url = consolehost + "/configuration/" + id + "/specification";
	   return this.getData(url);	 
   }
   
   public boolean getReport(String id)
   {
	   final String url = consolehost + "/configuration/" + id + "/reports";
	   return this.getData(url);	 
   }
}
