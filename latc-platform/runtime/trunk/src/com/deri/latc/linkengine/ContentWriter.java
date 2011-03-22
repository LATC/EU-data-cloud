/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
package com.deri.latc.linkengine;

import java.io.BufferedReader;
import java.io.BufferedWriter;
import java.io.FileNotFoundException;
import java.io.FileReader;
import java.io.FileWriter;
import java.io.IOException;

import com.deri.latc.dto.VoidInfoDto;
import com.deri.latc.utility.Parameters;

/**
 * Storing String or VOID to file
 * @author jamnas
 * @author Nur Aini Rakhmawati
 */
public class ContentWriter {

	/**
	 * Storing string to file
	 * @param fileName	path of file
	 * @param content	string to be stored
	 */
    public void writeIt(String fileName, String content) {
        try {
                FileWriter fstream = new FileWriter(fileName);
	            BufferedWriter out = new BufferedWriter(fstream);
	            out.write(content);
	            out.flush();
	            //Close the output stream
	            out.close();
           
            
        } catch (Exception e) {//Catch exception if any
            System.err.println("Error: " + e.getMessage());
        }
     
    }

/**
 * Storing Void to file
 * @param fileName	path of file
 * @param vi	Void handler value
 */

    public void writeIt(String fileName, VoidInfoDto vi) {
        try {
                             
           //read void template file and write void
           BufferedReader in = new BufferedReader(new FileReader("voidtmpl"));
           FileWriter fstream = new FileWriter(fileName);
           BufferedWriter out = new BufferedWriter(fstream);
   			
   			String readLine;
   			while ((readLine = in.readLine()) != null) {
   					readLine = this.replaceTemplate(readLine, vi);
   					if(!readLine.matches("(.*)\\*\\*(.*)\\*\\*(.*)"))
   					{
   					out.write(readLine);
   					out.write('\n');
   					}
   				}
   				in.close();
   				out.flush();
   	            out.close();
			} catch (FileNotFoundException e) {
				System.err.println("Error: " + e.getMessage());
			} catch (IOException e) {
				System.err.println("Error: " + e.getMessage());
			}		         
    }
        
    /**
     * 
     * @param input
     * @param vi
     * @return
     */
    private String replaceTemplate(String input, VoidInfoDto vi)
    {
    	if (input.contains("**newprefix**") )
    		input = input.replace("**newprefix**", vi.getGlobalPrefixes());
    	else if(input.contains("**source**"))
    		input =input.replace("**source**", vi.getSourceDatasetName());
    	else if(input.contains("**target**"))
    		input =input.replace("**target**", vi.getTargetDatasetName());
    	else if(input.contains("**sparqlsource**") && vi.getSourceSparqlEndpoint()!=null)
    		input =input.replace("**sparqlsource**", vi.getSourceSparqlEndpoint());
    	else if(input.contains("**sparqltarget**") && vi.getTargetSparqlEndpoint()!=null)
    		input =input.replace("**sparqltarget**", vi.getTargetSparqlEndpoint());
    	else if(input.contains("**uriLookupsource**") && vi.getSourceUriLookupEndpoint()!=null )
    		input =input.replace("**uriLookupsource**", vi.getSourceUriLookupEndpoint());
    	else if(input.contains("**uriLookuptarget**") && vi.getTargetUriLookupEndpoint()!=null )
    		input =input.replace("**uriLookuptarget**", vi.getTargetUriLookupEndpoint());
    	else if(input.contains("**linksetname**"))
    		input =input.replace("**linksetname**", vi.getSourceDatasetName()+'2'+vi.getTargetDatasetName());
    	else if(input.contains("**linktype**"))
    		input =input.replace("**linktype**", vi.getLinkPredicate());
    	else if(input.contains("**triples**"))
    		input =input.replace("**triples**", Integer.toString(vi.getStatItem()));
    	else if(input.contains("**datadump**"))
    		input =input.replace("**datadump**", vi.getDataDump());
    	else if(input.contains("**linksetcreatedtime**"))
    		input =input.replace("**linksetcreatedtime**", vi.getLinkSetCreatedTime());
       	else if(input.contains("**speccreatedtime**"))
       		input =input.replace("**speccreatedtime**", vi.getSpecCreatedTime());
       	else if(input.contains("**specauthor**"))
       		input =input.replace("**specauthor**", vi.getSpecAuthor());
       	else if(input.contains("**specretrievedtime**"))
       		input =input.replace("**specretrievedtime**", vi.getSpecRetrievedTime());
    	else if(input.contains("**specURL**"))
    		input =input.replace("**specURL**", vi.getSpec());
    	else if(input.contains("**consolehost**"))
    		input =input.replace("**consolehost**", Parameters.LATC_CONSOLE_HOST);
     	else if(input.contains("**SilkSpecAPIResource**"))
    		input =input.replace("**SilkSpecAPIResource**", vi.getSilkSpecAPIResource());
     	else if(input.contains("**specmodifiedtime**"))
    		input =input.replace("**specmodifiedtime**", vi.getSpecModifiedTime());
     	else if(input.contains("**SilkSpecID**"))
    		input =input.replace("**SilkSpecID**", vi.getID());
      	else if(input.contains("**SilkSpecTitle**"))
    		input =input.replace("**SilkSpecTitle**", vi.getTitle());
    	return input;
    }
}
