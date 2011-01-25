/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
package com.deri.latc.linkengine;


import com.deri.latc.translator.ListTranslator;
import com.deri.latc.dto.VoidInfoDto;
import com.deri.latc.utility.LoadParameter;
import com.deri.latc.utility.LogFormatter;

import java.io.BufferedReader;
import java.io.File;
import java.io.IOException;
import java.io.InputStreamReader;
import java.util.HashMap;
import java.util.Map;
import java.util.logging.FileHandler;
import java.util.logging.Logger;


/**
 *
 * @author jamnas,nurainir
 */
public class LinkEngine {


	private final LoadParameter parameters;
	static Logger logfile;
	final String RESULTDIR;

	@SuppressWarnings("static-access")
	public LinkEngine (String ConfigFile) throws IOException
	{
		parameters = new LoadParameter(ConfigFile);
	 	 final java.util.Date now = new java.util.Date();
		 RESULTDIR = parameters.RESULT_LOCAL_DIR+'/'+now.toLocaleString().substring(0, 11);
		 boolean exists = (new File(parameters.RESULT_LOCAL_DIR)).exists();
		 if (!exists)
			 (new File(parameters.RESULT_LOCAL_DIR)).mkdirs();
		 exists = (new File(RESULTDIR)).exists();
		 if (!exists)
			 (new File(RESULTDIR)).mkdirs();
		 boolean append = true;
	      FileHandler fh = new FileHandler(RESULTDIR+"/log", append);
	      fh.setFormatter(new LogFormatter());
	      logfile = Logger.getLogger("RuntimeLog");
	      logfile.addHandler(fh);

	}




    
	public void execute() throws Exception {

        ListTranslator lt = new ListTranslator();
        ContentWriter cw = new ContentWriter();
        Map <String,String> toDoList = new HashMap<String, String>();
        HttpRequestHandler client = new HttpRequestHandler(parameters.LATC_CONSOLE_HOST,RESULTDIR);


/*
         * Step1: get list from panel
         * Step2: get spec file
         *  a: put on demo server
         *  b: run it on hadoop server
         *  c: put results in public place
         *  d: create VoiD file
         *  e: Put result status back to LATC console
         * Repeat step-2 till end
         *
         */
        //Step 1

        /*
         * Getting list of link configuration from LATC_CONSOLE
         * JSON Format :  title, identifier
         */
        lt.translateMember(client.getData(parameters.LATC_CONSOLE_HOST + "/queue"));
        toDoList = lt.getLinkingConfigs();
        
        //Step 2
        for (final String id : toDoList.keySet()) {
        	logfile.info( "Processing id "+id+" title "+toDoList.get(id));
            VoidInfoDto vi = new VoidInfoDto();
            /*
             * Writing specification linking from LATC_CONSOLE_HOST/configuration/ID/specification
             */
            String specContent = client.getData(parameters.LATC_CONSOLE_HOST + "/configuration/" + id + "/specification");
            cw.writeIt(RESULTDIR +'/'+ id + '/', parameters.SPEC_FILE, specContent);

            /*
             * Running hadoop for silk Map reduce
             */
            if (this.runHadoop(id, vi,RESULTDIR)) {
                // step 2-d

                // 1-Namespaces
                vi.setGlobalPrefixes("@prefix rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#> . \n"
                        + "@prefix rdfs: <http://www.w3.org/2000/01/rdf-schema#> . \n"
                        + "@prefix owl: <http://www.w3.org/2002/07/owl#> . \n"
                        + "@prefix owl: <http://rdfs.org/ns/void#> . \n"
                        + "@prefix : <#> . \n");

                // 2- dataset descriptions
                String ds1 = specContent.substring(specContent.indexOf("id=", specContent.indexOf("DataSource ")) + 4, specContent.indexOf("type") - 1);
                vi.setSourceDatasetName(ds1.substring(0, ds1.indexOf("\"")));

                String ds2 = specContent.substring(specContent.indexOf("id=", specContent.lastIndexOf("DataSource ")) + 4, specContent.lastIndexOf("type") - 1);
                vi.setTargetDatasetName(ds2.substring(0, ds2.indexOf("\"")));

                // 3- Sparql Endpoints
                String e1 = specContent.substring(specContent.indexOf("value=\"", specContent.indexOf("endpointURI")) + 7, specContent.indexOf("ql\"/>") + 2);
                vi.setSourceSparqlEndpoint(e1);

                String e2 = specContent.substring(specContent.indexOf("value=\"", specContent.lastIndexOf("endpointURI")) + 7, specContent.lastIndexOf("ql\"/>") + 2);
                vi.setTargetSparqlEndpoint(e2);

                // 4- Vocab

                // 5- 3rd party Interlinking
                String linktype = specContent.substring(specContent.indexOf("<LinkType>") + 10, specContent.indexOf("</LinkType>"));

                vi.setLinkPredicate("         void:linkPredicate " + linktype + ";\n");

                vi.setThirdPartyInterlinking(":" + vi.getSourceDatasetName() + "2" + vi.getTargetDatasetName() + " a void:Linkset ; \n "
                        + vi.getLinkPredicate()
                        + "          void:target :" + vi.getSourceDatasetName() + ";\n  "
                        + "        void:target :" + vi.getTargetDatasetName() + " ;\n"
                        + "          void:triples  " + vi.getStatItem() + ";\n          .\n");

                // 	6- data dump
                vi.setDataDump(parameters.RESULTS_HOST + "/" + id + "/" + parameters.LINKS_FILE_STORE);

                cw.writeIt(RESULTDIR +'/'+ id + '/', parameters.VOID_FILE, vi);

                // 2-e
                vi.setRemarks("Job Executed");
                logfile.info( "Processing id "+id+" title "+toDoList.get(id)+ " success");

            } // if hadoop
            else {
            	logfile.warning( "Processing id "+id+" title "+toDoList.get(id)+ " failed");
            }
// 2-e

            client.postLCReport(id + "", vi);

        } // for loop
            
    }

    private boolean runHadoop(String id, VoidInfoDto vi,String resultdir) {
    	  /*
           * Step 1: Copy file into DFS
           * Step 2: Run load command
           * Step 3: Run match command
           * Step 4: Get results in local file sys
           * Step 5: Merge the Results
           * Step 6: TTL file (in future)
           *
           */
    	
    	final Logger loghadoop;
    	
    	
          try {
              java.util.Date date = new java.util.Date();
              FileHandler fh = new FileHandler(RESULTDIR+'/'+id+"/log", true);
    	      fh.setFormatter(new LogFormatter());
    	      loghadoop = Logger.getLogger("HadoopLog");
    	      loghadoop.addHandler(fh);
    	

              final String hadoop = parameters.HADOOP_PATH+"/bin/hadoop";
              String command ="";
              Process process;
              int returnCode = 0;

              command = hadoop+" dfs -rmr "+parameters.HADOOP_USER+'/' + id + " "+parameters.HADOOP_USER+"/r" + id + "/ ";
              process = Runtime.getRuntime().exec(command);
              returnCode = process.waitFor();
              logfile.info( command + " Return code = " + returnCode);
             
              
              command = hadoop+" fs -put "+resultdir+'/' +id + "/" + parameters.SPEC_FILE +' ' + id;
              process = Runtime.getRuntime().exec(command);
              returnCode = process.waitFor();
              logfile.info( command + " Return code = " + returnCode);
              
              
              command = hadoop+ " jar silkmr.jar load " + id + " ./cache";
              System.out.println(command);
              process = Runtime.getRuntime().exec(command);
              returnCode = process.waitFor();
              logfile.info( command + " Return code = " + returnCode);
              loghadoop.info(this.readProcess(process,returnCode));

              if (returnCode == 0) {
                  command = hadoop+ " jar silkmr.jar match ./cache ./r" + id + " ";
                  process = Runtime.getRuntime().exec(command);
                  returnCode = process.waitFor();

                  logfile.info( command + " Return code = " + returnCode);
                  loghadoop.info(this.readProcess(process,returnCode));
                  
                  command =hadoop+ " dfs -mkdir "+parameters.HADOOP_USER +"/r" + id + "/re";
                  process = Runtime.getRuntime().exec(command);
                  returnCode = process.waitFor();
                  logfile.info( command + " Return code = " + returnCode);
                  
                  
                  command = hadoop+ " dfs -mv "+parameters.HADOOP_USER +"/r" + id + "/*.nt "+parameters.HADOOP_USER +"/r" + id + "/re";
                  process = Runtime.getRuntime().exec(command);
                  returnCode = process.waitFor();
                  logfile.info( command + " Return code = " + returnCode);

                  command =hadoop+ " dfs -getmerge "+parameters.HADOOP_USER +"/r"+ id + "/re/ "+resultdir+'/'+ id + '/' + parameters.LINKS_FILE_STORE;
                  process = Runtime.getRuntime().exec(command);
                  returnCode = process.waitFor();
                  logfile.info( command + " Return code = " + returnCode);

                  command = "wc -l "+resultdir+'/' + id + '/'+parameters.LINKS_FILE_STORE;
                  process = Runtime.getRuntime().exec(command);
                  returnCode = process.waitFor();

                  BufferedReader buf = new BufferedReader(new InputStreamReader(process.getInputStream()));
                  String line = "";
                  String stat = "";
                  int k = 0;
                  while ((line = buf.readLine()) != null) {
                      System.out.println((++k) + " " + line);
                      stat = line;
                  }

                  stat = stat.substring(0, stat.indexOf(" "));
                  System.out.println("::LINE::" + stat);
                  vi.setStatItem(stat);
                  if(Integer.parseInt(stat) >0)
                	  logfile.info( "storing result at "+resultdir+'/' + id + '/'+parameters.LINKS_FILE_STORE);

                  return true;
              } else {
                  vi.setRemarks("Job Failed: Error in Loading Data");
                  logfile.severe("Job Failed: Error in Loading Data");
                  return false;
              }
          } catch (Exception e) {
        	  logfile.severe(e.getMessage());
              return false;
          }
          

    }
    
    
    private String readProcess (final Process p, int returncode) {
    	
    	
    	final BufferedReader in;
    	if(returncode ==0)
    		in =new BufferedReader ( new InputStreamReader(p.getInputStream()));
    	else
    		in =new BufferedReader ( new InputStreamReader(p.getErrorStream()));
    	final StringBuffer result = new StringBuffer();
        String line;

    	try {
			while ((line = in.readLine()) != null) {
			    result.append(line);
			  }
		} catch (IOException e) {
			logfile.severe(e.getMessage());
		}
		return result.toString();
    }

    public static void main(String[] args) throws Exception {

        LinkEngine le;
       	le = new LinkEngine(args[0]);
        le.execute();

    }
}
