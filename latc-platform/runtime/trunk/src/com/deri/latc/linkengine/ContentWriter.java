/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
package com.deri.latc.linkengine;

import java.io.*;
import com.deri.latc.dto.VoidInfoDto;

/**
 * Storing String or VOID to file
 * @author jamnas
 * @author nurainir
 */
public class ContentWriter {

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



    public void writeIt(String fileName, VoidInfoDto vi) {
        try {
           
            
           vi.setGlobalPrefixes("@prefix rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#> . \n"
                    + "@prefix rdfs: <http://www.w3.org/2000/01/rdf-schema#> . \n"
                    + "@prefix owl: <http://www.w3.org/2002/07/owl#> . \n"
                    + "@prefix owl: <http://rdfs.org/ns/void#> . \n"
                    + "@prefix : <#> . \n");

        	vi.setLinkPredicate("\tvi:linkPredicate " +vi.getLinkPredicate() + ";\n");

        	vi.setThirdPartyInterlinking(":" +vi.getSourceDatasetName() + "2" +vi.getTargetDatasetName() + " a void:Linkset ; \n "
                    +vi.getLinkPredicate()
                    + "\tvi:target :" +vi.getSourceDatasetName() + ";\n  "
                    + "\tvi:target :" +vi.getTargetDatasetName() + " ;\n"
                    + "\tvi:triples  " +vi.getStatItem() + ";\n\t.\n");

                      
         
            FileWriter fstream = new FileWriter(fileName);
            BufferedWriter out = new BufferedWriter(fstream);

            out.write(vi.getGlobalPrefixes());
            out.write(':'+vi.getSourceDatasetName()+" a void Dataset;\n");
            if(vi.getSourceSparqlEndpoint()!=null)
            	out.write("\tvoid:sparqlEndpoint <"+vi.getSourceSparqlEndpoint()+">;\n\t.\n");
            else
            	out.write("\tvoid:uriLookupEndpoint <"+vi.getSourceUriLookupEndpoint()+">;\n\t.\n");
            out.write(':'+vi.getTargetDatasetName()+" a void Dataset;\n");
            if(vi.getTargetSparqlEndpoint()!=null)
            	out.write("\tvoid:sparqlEndpoint <"+vi.getTargetSparqlEndpoint()+">;\n\t.\n");
            else
            	out.write("\tvoid:uriLookupEndpoint <"+vi.getTargetUriLookupEndpoint()+">;\n\t.\n");
            out.write(vi.getThirdPartyInterlinking());
            out.flush();
            //Close the output stream
            out.close();
        } catch (Exception e) {//Catch exception if any
            System.err.println("Error: " + e.getMessage());
        }
       
    }
}
