/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
package com.deri.latc.linkengine;


import com.deri.latc.translator.ListTranslator;
import com.deri.latc.dto.VoidInfoDto;
import com.deri.latc.utility.LoadParameter;
import com.deri.latc.utility.LogFormatter;
import com.deri.latc.utility.SpecParser;
import com.deri.latc.utility.TestHTTP;
import com.deri.latc.utility.HadoopClient;

import java.io.BufferedReader;
import java.io.File;
import java.io.FileReader;
import java.io.IOException;
import java.io.InputStreamReader;
import java.util.HashMap;
import java.util.Map;
import java.util.logging.FileHandler;
import java.util.logging.Logger;

import org.xml.sax.InputSource;
import org.xml.sax.SAXException;
import org.xml.sax.XMLReader;
import org.xml.sax.helpers.XMLReaderFactory;


/**
 *
 * @author jamnas
 * @author Nur Aini Rakhmawati 
 */
public class LinkEngine {


	private final LoadParameter parameters;
	private static final Logger logfile = Logger.getLogger("RuntimeLog");
	String RESULTDIR;


	public LinkEngine (String ConfigFile) throws IOException
	{
		parameters = new LoadParameter(ConfigFile);
	 	this.createresultDir();

	}

	public LinkEngine (LoadParameter p)throws IOException
	{
		this.parameters =p;
		this.createresultDir();
	}

	private void createresultDir() throws  IOException
	{
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
	      logfile.addHandler(fh);
	}
    
	public void execute() throws Exception {

		//testing console
       this.testConn(parameters.LATC_CONSOLE_HOST);
      		
		ListTranslator lt = new ListTranslator();
        ContentWriter cw = new ContentWriter();
        Map <String,String> toDoList = new HashMap<String, String>();
        ConsoleConnection client = new ConsoleConnection(parameters.LATC_CONSOLE_HOST);
        
        /*
         * Getting list of link configuration from LATC_CONSOLE
         * JSON Format :  title, identifier
         */
        
        if(!client.getQueue())
        {
        	logfile.severe("Error during get Queue"+client.getMessage());
        	System.exit(0);
        }
        lt.translateMember(client.getMessage());
      //  toDoList = lt.getLinkingConfigs();
        toDoList.put("ff8081812cac8e41012cac8f20700016", "openlibrary-dbpedia.xml");
        toDoList.put("ff8081812cac8e41012cac8f43f4001a", "sider_drugbank_drugs.xml");
        toDoList.put("ff8081812cac8e41012cac8f33fa0018", "sider_dailymed_drugs.xml");
        toDoList.put("ff8081812cac8e41012cf0357d490028", "sider_diseasome_diseases.xml");
        toDoList.put("ff8081812cac8e41012cbbc81dad001e", "climb_silk_link_spec.xml");
        toDoList.put("ff8081812cac8e41012cac8e76c20006", "dbpedia-lgd_lake.xml");
        toDoList.put("ff8081812cac8e41012cac8ed5aa000e", "dbpedia-lgd_university.xml");
        toDoList.put("ff8081812cac8e41012cac8ec3ea000c", "dbpedia-lgd_stadium.xml");
        toDoList.put("ff8081812cac8e41012cac8ea77a000a", "dbpedia-lgd_school.xml");
        //toDoList.put("ff8081812cac8e41012cac8e95820008", "dbpedia-lgd_mountain.xml");
        
 
        
        for (final String id : toDoList.keySet()) {
        	logfile.info( "Processing id "+id+" title "+toDoList.get(id));
            
            //create id directory
            boolean exists = (new File(RESULTDIR +'/'+ id)).exists();
            if (!exists)
   			 (new File(RESULTDIR +'/'+ id )).mkdirs();
            
            
            /*
             * Writing specification linking from LATC_CONSOLE_HOST/configuration/ID/specification
             */
            if(!client.getSpec(id))
            {
            	logfile.severe("Error during get Specification id "+id+" "+client.getMessage());
            }
            else
            {
	            String specContent = client.getMessage();
	            cw.writeIt(RESULTDIR +'/'+ id + '/'+ parameters.SPEC_FILE, specContent);
	            VoidInfoDto Void=this.parseSpec(RESULTDIR +'/'+ id + '/'+ parameters.SPEC_FILE);
                
	        	//testing endpoint
	            if(Void.getSourceSparqlEndpoint()!=null)
	            	this.testConn(Void.getSourceSparqlEndpoint());
	            if(Void.getTargetSparqlEndpoint()!=null)
	            	this.testConn(Void.getTargetSparqlEndpoint());
	            if(Void.getSourceUriLookupEndpoint()!=null)
	            	this.testConn(Void.getSourceUriLookupEndpoint());
	            if(Void.getTargetUriLookupEndpoint()!=null)
	            	this.testConn(Void.getTargetUriLookupEndpoint());
            
	                       
	            
	            /*
	             * Running hadoop for silk Map reduce
	             */
	            if (this.runHadoop(id, Void,RESULTDIR)) {
	                
	
	                // 1-Namespaces
	            	Void.setGlobalPrefixes("@prefix rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#> . \n"
	                        + "@prefix rdfs: <http://www.w3.org/2000/01/rdf-schema#> . \n"
	                        + "@prefix owl: <http://www.w3.org/2002/07/owl#> . \n"
	                        + "@prefix owl: <http://rdfs.org/ns/void#> . \n"
	                        + "@prefix : <#> . \n");
	
	            	Void.setLinkPredicate("         void:linkPredicate " + Void.getLinkPredicate() + ";\n");
	
	            	Void.setThirdPartyInterlinking(":" + Void.getSourceDatasetName() + "2" + Void.getTargetDatasetName() + " a void:Linkset ; \n "
	                        + Void.getLinkPredicate()
	                        + "          void:target :" + Void.getSourceDatasetName() + ";\n  "
	                        + "        void:target :" + Void.getTargetDatasetName() + " ;\n"
	                        + "          void:triples  " + Void.getStatItem() + ";\n          .\n");
	
	                // 	6- data dump
	                java.util.Date now = new java.util.Date();
	                Void.setDataDump(parameters.RESULTS_HOST + '/' +now.toLocaleString().substring(0, 11)+'/'+ id + "/" + parameters.LINKS_FILE_STORE);
	
	                cw.writeIt(RESULTDIR +'/'+ id + '/'+ parameters.VOID_FILE, Void);
	
	                // 2-e
	                Void.setRemarks(Void.getStatItem()+" Links generated succesfully");
	                logfile.info( "Processing id "+id+" title "+toDoList.get(id)+ " success");
	
	            } // if hadoop
	            else {
	            	logfile.warning( "Processing id "+id+" title "+toDoList.get(id)+ " failed");
	            	Void.setRemarks ("Linkset generation failed");  
	            }
	// 2-e
	
	            client.postReport(id, Void);
	            }
        } // for loop
            
    }

    private boolean runHadoop(String id, VoidInfoDto vi,String resultdir) {

    	
    	final Logger loghadoop;
    	HadoopClient HC = new HadoopClient(parameters.HADOOP_PATH,parameters.HADOOP_USER);
    	
    	
    	
          try {
             
              FileHandler fh = new FileHandler(RESULTDIR+'/'+id+"/log", true);
    	      fh.setFormatter(new LogFormatter());
    	      loghadoop = Logger.getLogger("HadoopLog");
    	      loghadoop.addHandler(fh);
    	
    	    //testing hadoop
    	    	if(HC.test())
    	    		loghadoop.info("HADOOP OK");
    	        else 
    	        {
    	        	loghadoop.severe("HADOOP DOWN "+HC.getMessage());
    	        	System.exit(0);
    	        }
    	      
              final String hadoop = parameters.HADOOP_PATH+"/bin/hadoop";
              String command ="";
              Process process;
              int returnCode = 0;

              HC.deleteFile(id);          
              if(HC.deleteDir("r"+id))
            	  System.out.println("delete r"+id);
      		   else
      			System.out.println(HC.getMessage());
              
              HC.copyFromLocalFile(resultdir+'/' +id + "/" + parameters.SPEC_FILE, id);
              
              // running SILK
              
              command = hadoop+ " jar silkmr.jar load " + id + " ./cache";
              loghadoop.info(command);
              process = Runtime.getRuntime().exec(command);
              returnCode = process.waitFor();
             
              loghadoop.info(this.readProcess(process,returnCode));
              // SILK LOAD success
              if (returnCode == 0) {
                  command = hadoop+ " jar silkmr.jar match ./cache ./r" + id + " ";
                  loghadoop.info(command);
                  process = Runtime.getRuntime().exec(command);
                  returnCode = process.waitFor();

                  loghadoop.info(this.readProcess(process,returnCode));
                  
                  HC.copyMergeToLocal("/r"+id+"/*.nt", resultdir+'/'+ id + '/' + parameters.LINKS_FILE_STORE, false);
              
                  BufferedReader buf = new BufferedReader(new FileReader(resultdir+'/' + id + '/'+parameters.LINKS_FILE_STORE));
                  int numbLine=0;
                  while ( buf.readLine() != null)
                	  numbLine++;
                  buf.close();
                
                  loghadoop.info(numbLine+" links Generated");
                  vi.setStatItem(numbLine);
                  if(numbLine >0)
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
		if(result.toString()=="")
			result.append("Success");
	
		return result.toString();
    }
    
    private VoidInfoDto parseSpec(String specPath)
    {
    	VoidInfoDto Vi = new VoidInfoDto();
    	 try {
    			
    			XMLReader xr = XMLReaderFactory.createXMLReader();
    			SpecParser handler = new SpecParser();
    			xr.setContentHandler(handler);
    			xr.setErrorHandler(handler);
    	    	FileReader r = new FileReader(specPath);
    	    	xr.parse(new InputSource(r));
    			Vi=handler.getVoid();
    			} catch (IOException e) {
    				logfile.severe(e.getMessage());
    					e.printStackTrace();
    				} catch (SAXException e) {
    					logfile.severe(e.getMessage());
    				}
    	return Vi;    	
    }

    private void testConn(String URL)
    {

        if(TestHTTP.test(URL))
        	logfile.info(URL+" OK");
        else 
        {
        	logfile.severe(URL+" DOWN");
        	System.exit(0);
        }
    }
    public static void main(String[] args) throws Exception {

        LinkEngine le;
       	le = new LinkEngine(args[0]);
        le.execute();

    }
}
