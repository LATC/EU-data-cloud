package com.deri.latc.utility;

import org.xml.sax.SAXException;
import org.xml.sax.XMLReader;
import org.xml.sax.Attributes;
import org.xml.sax.InputSource;
import org.xml.sax.helpers.XMLReaderFactory;
import org.xml.sax.helpers.DefaultHandler;

import com.deri.latc.dto.VoidInfoDto;

import java.io.FileReader;
import java.io.IOException;

/**
* Parsing SILK Specification XML file
* @author Nur Aini Rakhmawati 
* @since February 2011
*/


public class SpecParser extends DefaultHandler {
	
	private VoidInfoDto Void;
	private String [] datasource = new String[2]; //name-URI-0 OR name-URI-1 
	private int i=0; 
	private boolean linktype =false;
	
	public VoidInfoDto getVoid()
	{
		return this.Void;
	}

    public void startDocument ()
    {
    	this.Void = new VoidInfoDto();
    	
    }


    public void endDocument ()
    {
	
    	for(final String ds : this.datasource)
    	{
    		String [] dsSplit = ds.split("-",3);
    		if(dsSplit[0].equalsIgnoreCase(Void.getSourceDatasetName()))
    			if(dsSplit[1].startsWith("0"))
    				Void.setSourceSparqlEndpoint(dsSplit[2]);
    			else 
    				Void.setSourceUriLookupEndpoint(dsSplit[2]);
    		else
    			if(dsSplit[1].startsWith("0"))
    				Void.setTargetSparqlEndpoint(dsSplit[2]);
    			else 
    				Void.setTargetUriLookupEndpoint(dsSplit[2]);
    	}
    	
    }


    public void startElement (String uri, String name,
			      String qName, Attributes atts)
    {
    	//DataSource
    	if(qName.equalsIgnoreCase("DataSource"))
    	{
    		datasource[i]=atts.getValue("id");
    		if(atts.getValue("type").equalsIgnoreCase("sparqlEndpoint"))
    			datasource[i]+="-0";	
    		else
    			datasource[i]+="-1";	
    		
    	}
    	//sparqlEndpoint OR URILookUp
    	if(qName.equalsIgnoreCase("param") && atts.getValue("name").equalsIgnoreCase("endpointURI"))
    	{
    		datasource[i]+='-'+atts.getValue("value");	
    	}
    	    	
    	//LinkType
    	if(qName.equalsIgnoreCase("LinkType"))
    		linktype=true;
    	
    	if(qName.equalsIgnoreCase("SourceDataset"))
    		Void.setSourceDatasetName(atts.getValue("dataSource"));
    	if(qName.equalsIgnoreCase("TargetDataset"))
    		Void.setTargetDatasetName(atts.getValue("dataSource"));
	
		
    }


    public void endElement (String uri, String name, String qName)
    {
    	if(qName.equalsIgnoreCase("DataSource"))
    	{
    		i++;
    	}
    	if(qName.equalsIgnoreCase("LinkType"))
    		linktype=false;
    }

    public void characters (char ch[], int start, int length)
    {
    	
    	if(linktype)
    	{
    		Void.setLinkPredicate(new String(ch,start,length));
    	}
    }
	
	
	/**
	 * @param args
	 */
	public static void main(String[] args) {
		   try {
		
		XMLReader xr = XMLReaderFactory.createXMLReader();
		SpecParser handler = new SpecParser();
		xr.setContentHandler(handler);
		xr.setErrorHandler(handler);
    	 FileReader r = new FileReader(args[0]);
				xr.parse(new InputSource(r));
				VoidInfoDto Vi=handler.getVoid();
				System.out.println(Vi.getSourceDatasetName());
				System.out.println(Vi.getLinkPredicate());
				System.out.println(Vi.getTargetDatasetName());
				System.out.println(Vi.getSourceSparqlEndpoint());
				System.out.println(Vi.getTargetSparqlEndpoint());
				System.out.println(Vi.getTargetUriLookupEndpoint());
			} catch (IOException e) {
				// TODO Auto-generated catch block
				e.printStackTrace();
			} catch (SAXException e) {
				// TODO Auto-generated catch block
				e.printStackTrace();
			}



	}

}
