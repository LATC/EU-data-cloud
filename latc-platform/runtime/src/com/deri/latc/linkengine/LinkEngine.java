/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
package com.deri.latc.linkengine;


import com.deri.latc.translator.ListTranslator;
import com.deri.latc.dto.*;
import com.deri.latc.utility.Constants;
import java.util.*;

/**
 *
 * @author jamnas
 */
public class LinkEngine {

    public void execute() throws Exception {
        // Create an instance of LinkEngine.
        HttpRequestHandler client = new HttpRequestHandler();
        ListTranslator lt = new ListTranslator();
        ContentWriter cw = new ContentWriter();
        RunHadoopJob rh = new RunHadoopJob();
        Vector toDoList = null;
        //ConfigFilesListDto dt;
/*
         * Step1: get list from panel
         * Step2: get config file
         *  a: put on demo server
         *  b: run it on hadoop server
         *  c: put results in public place
         *  d: create VoiD file
         *  e: Put result status back to LATC console
         * Repeat step-2 till end
         *
         */
        //Step 1
        toDoList = lt.translateMember(client.getData(Constants.LATC_CONSOLE_HOST + "/queue"));
        System.out.println("*******result  host "+Constants.RESULTS_HOST);
        
        //Step 2

        for (int i = 0; i < toDoList.size(); i++) {
            String id = toDoList.get(i).toString();
            VoidInfoDto vi = new VoidInfoDto();

            String fileContent = lt.getConfigFile(client.getData(Constants.LATC_CONSOLE_HOST + "/configuration/" + id + "/specification"));

//step 2-a
            cw.writeIt("jamal/r1/" + id + "/", "spec.xml", fileContent);
//step 2-b,c
            if (rh.runThis(id + "", vi)) {
                // step 2-d
            	
                // 1-Namespaces
                vi.setGlobalPrefixes("@prefix rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#> . \n"
                        + "@prefix rdfs: <http://www.w3.org/2000/01/rdf-schema#> . \n"
                        + "@prefix owl: <http://www.w3.org/2002/07/owl#> . \n"
                        + "@prefix owl: <http://rdfs.org/ns/void#> . \n"
                        + "@prefix : <#> . \n");

                // 2- dataset descriptions
                String ds1 = fileContent.substring(fileContent.indexOf("id=", fileContent.indexOf("DataSource ")) + 4, fileContent.indexOf("type") - 1);
                vi.setSourceDatasetName(ds1.substring(0, ds1.indexOf("\"")));

                String ds2 = fileContent.substring(fileContent.indexOf("id=", fileContent.lastIndexOf("DataSource ")) + 4, fileContent.lastIndexOf("type") - 1);
                vi.setTargetDatasetName(ds2.substring(0, ds2.indexOf("\"")));




// 3- Sparql Endpoints
                String e1 = fileContent.substring(fileContent.indexOf("value=\"", fileContent.indexOf("endpointURI")) + 7, fileContent.indexOf("ql\"/>") + 2);
                vi.setSourceSparqlEndpoint(e1);

                String e2 = fileContent.substring(fileContent.indexOf("value=\"", fileContent.lastIndexOf("endpointURI")) + 7, fileContent.lastIndexOf("ql\"/>") + 2);
                vi.setTargetSparqlEndpoint(e2);

// 4- Vocab

// 5- 3rd party Interlinking
                String linktype = fileContent.substring(fileContent.indexOf("<LinkType>") + 10, fileContent.indexOf("</LinkType>"));

                vi.setLinkPredicate("         void:linkPredicate " + linktype + ";\n");

                vi.setThirdPartyInterlinking(":" + vi.getSourceDatasetName() + "2" + vi.getTargetDatasetName() + " a void:Linkset ; \n "
                        + vi.getLinkPredicate()
                        + "          void:target :" + vi.getSourceDatasetName() + ";\n  "
                        + "        void:target :" + vi.getTargetDatasetName() + " ;\n"
                        + "          void:triples  " + vi.getStatItem() + ";\n          .\n");

// 6- data dump
                vi.setDataDump(Constants.RESULTS_HOST + "/" + id + "/" + Constants.LINKS_FILE_NAME);

                cw.writeIt("jamal/r1/" + id + "/", "void.ttl", vi);

                // 2-e
                vi.setRemarks("Job Executed");

            } // if hadoop
            else {
                // 2-e
                //  vi.setRemarks("Job Failed");
            }
// 2-e

            client.postLCReport(id + "", vi);

        } // for loop

    }

    public static void main(String[] args) throws Exception {

        LinkEngine le = new LinkEngine();
        le.execute();


    }
}
