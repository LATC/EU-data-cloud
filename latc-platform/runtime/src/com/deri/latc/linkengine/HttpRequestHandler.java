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
public class HttpRequestHandler {

	 final String consolehost;
	 final String resultdir;
	
	 
	 public HttpRequestHandler(final String console,  final String result){
		this.consolehost = console;
		this.resultdir = result;
		
	}
	
    public boolean postLCReport(String id, VoidInfoDto vi) throws Exception {
        String url = consolehost + "/configuration/" + id + "/reports";
        System.out.println(url);
        NameValuePair[] data = {
            new NameValuePair("status", vi.getRemarks()),
            new NameValuePair("location", resultdir + "/" + id),
            new NameValuePair("size", vi.getStatItem())
        };

        HttpClient clientService = new HttpClient();
        PostMethod post = new PostMethod();
        post.setURI(new org.apache.commons.httpclient.URI(url, false));
        post.setRequestBody(data);
        int status = clientService.executeMethod(post);

        if (status != HttpStatus.SC_OK) {
            throw new Exception("Received error status " + status);
        }

        return true;
    }

    public String getData(String url) {
        String page = "";
        // Create an instance of HttpClientDemo.
        HttpClient client = new HttpClient();

        // Create a method instance.
        GetMethod method = new GetMethod(url);

        // Provide custom retry handler is necessary
        method.getParams().setParameter(HttpMethodParams.RETRY_HANDLER,
                new DefaultHttpMethodRetryHandler(3, false));

        try {
            // Execute the method.
            int statusCode = client.executeMethod(method);

            if (statusCode != HttpStatus.SC_OK) {
                System.err.println("Method failed: " + method.getStatusLine());
            }
            else
            {
            	 StringBuffer responseBuffer = new StringBuffer();
                 InputStreamReader stream = new InputStreamReader(method.getResponseBodyAsStream(), "UTF-8");
           
                 int data = stream.read();
                 while (data!=-1)
                 {
                		 responseBuffer.append((char)data);
                 		  data = stream.read();
                 }
                 page = responseBuffer.toString();
                 stream.close();
	            
            }

        } catch (HttpException e) {
            System.err.println("Fatal protocol violation: " + e.getMessage());
            e.printStackTrace();
        } catch (IOException e) {
            System.err.println("Fatal transport error: " + e.getMessage());
            e.printStackTrace();
        } finally {
            // Release the connection.
            method.releaseConnection();
        }
        return page;
    }

   
}
