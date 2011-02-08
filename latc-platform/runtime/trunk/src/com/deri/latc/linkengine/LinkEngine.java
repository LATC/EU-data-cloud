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
import java.util.Date;
import java.text.DateFormat;


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
		 final Date now = new java.util.Date();
		 RESULTDIR = parameters.RESULT_LOCAL_DIR+'/'+DateFormat.getDateInstance().format(now).substring(0, 11);
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
		 if(TestHTTP.test(parameters.LATC_CONSOLE_HOST))
	        	logfile.info(parameters.LATC_CONSOLE_HOST+" OK");
	        else 
	        {
	        	logfile.severe(parameters.LATC_CONSOLE_HOST+" DOWN");
	        	System.exit(0);
	        	}
            		
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
        toDoList = lt.getLinkingConfigs();
             
        for (final String id : toDoList.keySet()) {
        	logfile.info( "start processing id "+id+" title "+toDoList.get(id));
            
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
                
//	         	6- data dump
                Date now = new java.util.Date();
                Void.setDataDump(parameters.RESULTS_HOST + '/' +DateFormat.getDateInstance().format(now).substring(0, 11)+'/'+ id + "/" + parameters.LINKS_FILE_STORE);
                
	        	//testing endpoint
	            if(Void.getSourceSparqlEndpoint()!=null && !this.testConn(Void.getSourceSparqlEndpoint()))
	            	{
	            		Void.setRemarks(Void.getSourceSparqlEndpoint()+" DOWN");
	            		client.postReport(id, Void);
	            		System.exit(0);
	            	}
	            if(Void.getTargetSparqlEndpoint()!=null && !this.testConn(Void.getTargetSparqlEndpoint()))
	            	{
	            		Void.setRemarks(Void.getTargetSparqlEndpoint()+" DOWN");
	            		client.postReport(id, Void);
	            		System.exit(0);
	            	}
	            if(Void.getSourceUriLookupEndpoint()!=null && !this.testConn(Void.getSourceUriLookupEndpoint()))
	            	{
	            		Void.setRemarks(Void.getSourceUriLookupEndpoint()+" DOWN");
	            		client.postReport(id, Void);
	            		System.exit(0);
	            	}
	            if(Void.getTargetUriLookupEndpoint()!=null && !this.testConn(Void.getTargetUriLookupEndpoint()))
	            	{
	            		Void.setRemarks(Void.getTargetUriLookupEndpoint()+" DOWN");
	            		client.postReport(id, Void);
	            		System.exit(0);
	            	}
            
	                       
	            
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
	
	                
	
	                cw.writeIt(RESULTDIR +'/'+ id + '/'+ parameters.VOID_FILE, Void);
	
	                // 2-e
	                Void.setRemarks(Void.getStatItem()+" Links generated succesfully");
	                logfile.info( "Processing id "+id+" title "+toDoList.get(id)+ " success");
	
	            } // if hadoop
	            else {
	            	logfile.severe( "Processing id "+id+" title "+toDoList.get(id)+ " failed");
         	 
	            }

	            client.postReport(id, Void);
	            }
        } // for loop
            
    }

    private boolean runHadoop(String id, VoidInfoDto vi,String resultdir) {

    	
    	Logger loghadoop;
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
    	        	vi.setRemarks("HADOOP DOWN "+HC.getMessage());
    	        	fh.close();
    	        	return false;
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
             
              // SILK LOAD success
              if (returnCode == 0) {
                  command = hadoop+ " jar silkmr.jar match ./cache ./r" + id + " ";
                  loghadoop.info(command);
                  process = Runtime.getRuntime().exec(command);
                  returnCode = process.waitFor();
                  if (returnCode != 0)
                  {
                	  loghadoop.severe(this.readProcess(process,returnCode));
                	  vi.setRemarks("Job Failed: Error in Matching Data");
                      logfile.severe("Job Failed: Error in Matching Data");
                      fh.close();
                      return false;
                  }
                  
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
                  fh.close();
                  return true;
              } 
              // SILK load failed
              else {
            	  loghadoop.severe(this.readProcess(process,returnCode));
            	  vi.setRemarks("Job Failed: Error in Loading Data");
                  logfile.severe("Job Failed: Error in Loading Data");
                  fh.close();
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

  
    
    private boolean testConn(String URL)
    {

        if(TestHTTP.test(URL))
        	return true;
        else 
        {
        	logfile.severe(URL+" DOWN");
        	return false;
        }
    }
    
    public static void main(String[] args) throws Exception {

        LinkEngine le;
       	le = new LinkEngine(args[0]);
        le.execute();

    }
}
