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
import java.util.TreeMap;
import java.util.Map;
import java.util.logging.FileHandler;
import java.util.logging.Logger;
import java.util.Date;
import java.text.SimpleDateFormat;


import org.xml.sax.InputSource;
import org.xml.sax.SAXException;
import org.xml.sax.XMLReader;
import org.xml.sax.helpers.XMLReaderFactory;


/**
 * The main class of runtime which collaborate all of the class
 * @author jamnas
 * @author Nur Aini Rakhmawati 
 * @version 2.0 
 */
public class LinkEngine {


	private final LoadParameter parameters;
	private static final Logger logfile = Logger.getLogger("RuntimeLog");
	private String RESULTDIR;
	private Map <String,String> toDoList = new TreeMap<String, String>();


	/**
	 * 
	 * @param ConfigFile	String	the path of configuration file
	 * @throws IOException
	 */
	public LinkEngine (String ConfigFile) throws IOException
	{
		parameters = new LoadParameter(ConfigFile);
	 	this.createresultDir();

	}

	/**
	 * 
	 * @param p	LoadParameter	The variable which is obtained from command line user 
	 * @throws IOException
	 */
	public LinkEngine (LoadParameter p)throws IOException
	{
		this.parameters =p;
		this.createresultDir();
	}

	/**
	 * Create result Directory based on the executing date runtime  
	 * @throws IOException
	 */
	private void createresultDir() throws  IOException
	{
		String datepattern = "yyyy-MM-dd";
		SimpleDateFormat sdf =new SimpleDateFormat(datepattern);
		 RESULTDIR = parameters.RESULT_LOCAL_DIR+'/'+sdf.format(new Date());
		 boolean exists = (new File(parameters.RESULT_LOCAL_DIR)).exists();
		 if (!exists)
			 (new File(parameters.RESULT_LOCAL_DIR)).mkdirs();
		 exists = (new File(RESULTDIR)).exists();
		 if (!exists)
			 (new File(RESULTDIR)).mkdirs();
		 boolean append = true;
	      FileHandler fh = new FileHandler(RESULTDIR+"/report.log", append);
	      fh.setFormatter(new LogFormatter());
	      logfile.addHandler(fh);
	}
    
	/**
	 * Connecting to <b>console</b> for getting all the SILK specification files, Running SILK for each specification in <b>HDFS</b> and posting report back to <b>console</b> 
	 * @throws Exception
	 */
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
       
        ConsoleConnection client = new ConsoleConnection(parameters.LATC_CONSOLE_HOST);
        
        /*
         * Getting list of link configuration from LATC_CONSOLE
         * JSON Format :  title, identifier
         */
        
        if(!client.getTasks())
        {
        	logfile.severe("Error during get Queue "+client.getMessage());
        	System.exit(0);
        }
        lt.translateMember(client.getMessage());
        toDoList = lt.getLinkingConfigs();
               
             
        for (final String title : toDoList.keySet()) {
        	final String id=toDoList.get(title);
        	logfile.info( "start processing id "+id+" title "+title);
            
            //create id directory
            boolean exists = (new File(RESULTDIR +'/'+ title).exists());
            if (!exists)
   			 (new File(RESULTDIR +'/'+ title )).mkdirs();
            
            
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
	            cw.writeIt(RESULTDIR +'/'+ title + '/'+ parameters.SPEC_FILE, specContent);
	            VoidInfoDto Void=this.parseSpec(RESULTDIR +'/'+ title + '/'+ parameters.SPEC_FILE);
                
//	         	6- data dump
	            String datepattern = "yyyy-MM-dd";
	    		SimpleDateFormat sdf =new SimpleDateFormat(datepattern);
                Void.setDataDump(parameters.RESULTS_HOST + '/' +sdf.format(new Date())+'/'+title + "/");
                
	        	//testing endpoint
	            if(Void.getSourceSparqlEndpoint()!=null && !this.testConn(Void.getSourceSparqlEndpoint()))
	            	{
	            		Void.setRemarks(Void.getSourceSparqlEndpoint()+" DOWN");
	            		client.postReport(id, Void);
	            		continue;
	            	}
	            if(Void.getTargetSparqlEndpoint()!=null && !this.testConn(Void.getTargetSparqlEndpoint()))
	            	{
	            		Void.setRemarks(Void.getTargetSparqlEndpoint()+" DOWN");
	            		client.postReport(id, Void);
	            		continue;
	            	}
	            if(Void.getSourceUriLookupEndpoint()!=null && !this.testConn(Void.getSourceUriLookupEndpoint()))
	            	{
	            		Void.setRemarks(Void.getSourceUriLookupEndpoint()+" DOWN");
	            		client.postReport(id, Void);
	            		continue;
	            	}
	            if(Void.getTargetUriLookupEndpoint()!=null && !this.testConn(Void.getTargetUriLookupEndpoint()))
	            	{
	            		Void.setRemarks(Void.getTargetUriLookupEndpoint()+" DOWN");
	            		client.postReport(id, Void);
	            		continue;
	            	}
            
	                       
	            
	            /*
	             * Running hadoop for silk Map reduce
	             */
	            if (this.runHadoop(title, Void,RESULTDIR)) {
	                
	
	               cw.writeIt(RESULTDIR +'/'+ title + '/'+ parameters.VOID_FILE, Void);
	
	                // 2-e
	                Void.setRemarks(Void.getStatItem()+" Links generated succesfully");
	                logfile.info( "Processing id "+id+" title "+title+ " success");
	
	            } // if hadoop
	            else {
	            	logfile.severe( "Processing id "+id+" title "+title+ " failed");
         	 
	            }

	            client.postReport(id, Void);
	            }
        } // for loop
            
    }

	/**
	 * Running SILK and merging the SILK result on Hadoop Distributed Filesystem 
	 * @param title	String	Title of specification 
	 * @param vi	{@link VoidInfoDto}	Void declaration
	 * @param resultdir	String	the path of result directory
	 * @return	<b>true</b> SILK load, match running successfully 
	 */
    private boolean runHadoop(String title, VoidInfoDto vi,String resultdir) {

    	
    	Logger loghadoop;
    	HadoopClient HC = new HadoopClient(parameters.HADOOP_PATH,parameters.HDFS_USER);
    	String err=null;
    	
          try {
             
              FileHandler fh = new FileHandler(RESULTDIR+'/'+title+"/report.log", true);
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

              HC.deleteFile(title);          
              if(HC.deleteDir("r"+title))
            	  System.out.println("delete r"+title);
      		   else
      			System.out.println(HC.getMessage());
              
              HC.copyFromLocalFile(resultdir+'/' +title + "/" + parameters.SPEC_FILE, title);
              
              // running SILK
              
              command = hadoop+ " jar silkmr.jar load " + title + " ./cache";
              loghadoop.info(command);
              process = Runtime.getRuntime().exec(command);
              returnCode = process.waitFor();
             
              // SILK LOAD success
              if (returnCode == 0) {
                  command = hadoop+ " jar silkmr.jar match ./cache ./r" + title + " ";
                  loghadoop.info(command);
                  process = Runtime.getRuntime().exec(command);
                  returnCode = process.waitFor();
                  if (returnCode != 0)
                  {
                	  err=this.readProcess(process);
                	  loghadoop.severe(err);
                	  vi.setRemarks(err);
                      logfile.severe("Job Failed: Error in Matching Data");
                      fh.close();
                      return false;
                  }
                  
                  HC.copyMergeToLocal("/r"+title+"/*.nt", resultdir+'/'+ title + '/' + parameters.LINKS_FILE_STORE, false);
              
                  BufferedReader buf = new BufferedReader(new FileReader(resultdir+'/' + title + '/'+parameters.LINKS_FILE_STORE));
                  int numbLine=0;
                  while ( buf.readLine() != null)
                	  numbLine++;
                  buf.close();
                
                  loghadoop.info(numbLine+" links Generated");
                  vi.setStatItem(numbLine);
                  if(numbLine >0)
                	  logfile.info( "storing result at "+resultdir+'/' + title + '/'+parameters.LINKS_FILE_STORE);
                  fh.close();
                  return true;
              } 
              // SILK load failed
              else {
            	  err=this.readProcess(process);
            	  loghadoop.severe(err);
            	  vi.setRemarks(err);
                  logfile.severe("Job Failed: Error in Loading Data");
                  fh.close();
                  return false;
              }
              
          } catch (Exception e) {
        	  logfile.severe(e.getMessage());        	 
              return false;
          }
      
    }
    
    /**
     * Read the stdout after executing command
     * @param p Process ID
     * @return	the first line error message of process
     */
    private String readProcess (final Process p) {
    	String message=null;
    	final BufferedReader in =new BufferedReader ( new InputStreamReader(p.getErrorStream()));
    	String line;
    	try {
			while((line = in.readLine()) != null)
			{
				message =line;
				if((line = in.readLine()) != null && line.startsWith("\tat"))
					break;
			}
				
				message =message.substring(message.indexOf(':')+2);
				
		} catch (IOException e) {
			logfile.severe(e.getMessage());
		}
		return message;
    }
    
    
    /**
     * Parsing specification file
     * @param specPath	String	path of specification file
     * @return	{@link VoidInfoDto} if the specification have source,target ad prefix will return the void handler
     */
    
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
    				} catch (SAXException e) {
    					logfile.severe(e.getMessage());
    				}
    	return Vi;    	
    }

    /**
     * test http connection, given URL
     * @param URL
     * @return	true if connect successfully
     */
  
    
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
