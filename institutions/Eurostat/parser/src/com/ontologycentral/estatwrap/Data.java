package com.ontologycentral.estatwrap;

import java.io.BufferedReader;
import java.io.BufferedWriter;
import java.io.FileWriter;
import java.io.IOException;
import java.io.Reader;
import java.text.DecimalFormat;
import java.util.Date;
import java.util.List;

import javax.xml.stream.XMLStreamException;
import javax.xml.stream.XMLStreamWriter;

public class Data {

public static String PREFIX = "http://ontologycentral.com/2009/01/eurostat/ns#";
	public static String xsd = "http://www.w3.org/2001/XMLSchema#";
	BufferedReader _in;

	static BufferedWriter write = null;
	static FileWriter fstream = null;
	int timePosition = 0;
	public static String type = "";
	
	public Data(Reader sr) throws IOException, XMLStreamException {
		_in = new BufferedReader(sr);
	}
	
	public void getObservationType(Reader reader) throws IOException, XMLStreamException 
	{
		
		BufferedReader in = new BufferedReader(reader);
		String line = null;

		Header h = null;
		Line l = null;
		
		if ((line = in.readLine()) != null) {
			line = line.trim();
			if (line.length() <= 0) {
				throw new IOException("could not read header!");
			}

			h = new Header(line);
		} 
		
		while ((line = in.readLine()) != null) {
			line = line.trim();
			if (line.length() <= 0) {
				continue;
			}

			l = new Line(line);

			getType(h, l);
			
		}
	}
	
	public void convert(XMLStreamWriter out, String id, String freq, String datasetID, String logPath) throws IOException, XMLStreamException {
		String line = null;

		int rows = 0;
		Header h = null;
		Line l = null;

		if ((line = _in.readLine()) != null) {
			++rows;
			
			line = line.trim();
			if (line.length() <= 0) {
				throw new IOException("could not read header!");
			}

			h = new Header(line);
		}
		
		if(type.equals("non-numeric value"))
		{
			createLogFile(logPath);
			writeDataToFile( new Date() + " : Non-numeric vlaues in the Dataset : " + datasetID);
			
			try{
	        	write.flush();  
	        	write.close();
			}catch(IOException e){
				System.out.println("Error while closing the file...");
			}
		}
		
		List<String> hd1 = h.getDim1();
		for (int j = 0; j < hd1.size(); ++j) {
       	 if(hd1.get(j).equals("time"))
        	 timePosition = j;
        }
		
		while ((line = _in.readLine()) != null) {
			++rows;
			line = line.trim();
			if (line.length() <= 0) {
				continue;
			}

			l = new Line(line);

			printTriple(h, l, out, rows, id, freq);
		}

		_in.close();
	}

	public void getType(Header h, Line l)
	{
        List<String> hd1 = h.getDim1();
        List<String> ld1 = l.getDim1();
        
        if (hd1.size() != ld1.size()) {
                System.err.println("header dimensions and line dimensions don't match!");
        }

        List<String> hcol = h.getCols();
        List<String> lcol = l.getCols();

        if (hcol.size() != lcol.size()) {
                System.err.println("header columns and line columns don't match!");
        }
        
        int start = 0;
        int end = hcol.size();
        
        for (int i = start; i < end; ++i)
        {
        	if (((String)lcol.get(i)).equals(":") || ((String)lcol.get(i)).contains(":")) {
        		continue;
        	}
        	String val = (String)lcol.get(i);
        	returnType(val);
        }
	}
	
	 public void printTriple(Header h, Line l, XMLStreamWriter out, int bnodeid, String id, String freq) throws XMLStreamException {
         List<String> hd1 = h.getDim1();
         List<String> ld1 = l.getDim1();
         DecimalFormat df = new DecimalFormat ("0.00");
         
         String obs_URI = "";
         if (hd1.size() != ld1.size()) {
                 System.err.println("header dimensions and line dimensions don't match!");
         }

         List<String> hcol = h.getCols();
         List<String> lcol = l.getCols();

         if (hcol.size() != lcol.size()) {
                 System.err.println("header columns and line columns don't match!");
         }
         
         int start = 0;
         // rdfize all columns data
         int end = hcol.size();
         
         for (int i = start; i < end; ++i)
         {
                 if (((String)lcol.get(i)).equals(":") || ((String)lcol.get(i)).contains(":")) {
                         continue;
                 }
                 
         out.writeStartElement("qb:Observation");
         
         // generate the unique URI for each observation.
         if(!freq.equals(""))
        	 obs_URI = freq + ",";
         
         for (int j = 0; j < hd1.size(); ++j) {
        	 obs_URI += (String)ld1.get(j) + ",";
         }
         obs_URI += (String)hcol.get(i);
         out.writeAttribute("rdf:about", "/data/" + id + "#" + obs_URI);
         obs_URI = "";
         
         out.writeStartElement("qb:dataSet");
         out.writeAttribute("rdf:resource", "/data/" + id);
         out.writeEndElement();

         // add sdmx-dimension:freq
         if(!freq.equals(""))
         {
        	 out.writeStartElement("sdmx-dimension:freq");
             out.writeAttribute("rdf:resource", "http://purl.org/linked-data/sdmx/2009/code#freq-" + freq);
             out.writeEndElement();
         }
         
         for (int j = 0; j < hd1.size(); ++j) {
        	 if(!hd1.get(j).equals("time"))
        	 {
        		 out.writeStartElement("property:" + (String)hd1.get(j));
        		 out.writeAttribute("rdf:resource", Dictionary.PREFIX + (String)hd1.get(j) + "#" + (String)ld1.get(j));
        		 out.writeEndElement();
        	 }
        	 else
        	 {
                 String timeperiod = time.convertTimeSereis((String)ld1.get(j));
                 out.writeStartElement("sdmx-dimension:timePeriod");
                 
                 if(timeperiod.equals("LTAA"))
                 {
                	 out.writeAttribute("rdf:resource", "http://eurostat.linked-statistics.org/misc");
                 }
                 else
                 {
                	 out.writeAttribute("rdf:datatype", xsd + "date");
                	 out.writeCharacters(timeperiod);
                 }
                 out.writeEndElement();
        	 }
         }

         if(h.getDim2().equalsIgnoreCase("time"))
         {
             String timeperiod = time.convertTimeSereis((String)hcol.get(i));
             out.writeStartElement("sdmx-dimension:timePeriod");
             
             if(timeperiod.equals("LTAA"))
             {
            	 out.writeAttribute("rdf:resource", "http://eurostat.linked-statistics.org/misc");
             }
             else
             {
            	 out.writeAttribute("rdf:datatype", xsd + "date");
            	 out.writeCharacters(timeperiod);
             }
             out.writeEndElement();
         }
         else
         {
        	 out.writeStartElement("property:" + (String)h.getDim2());
             out.writeAttribute("rdf:resource", Dictionary.PREFIX + (String)h.getDim2() + "#" + (String)hcol.get(i));
             out.writeEndElement();
         }
         
         // exclude entries like ': c' which exists in the dataset
         if(!lcol.get(i).toString().contains(":"))
         {
             out.writeStartElement("sdmx-measure:obsValue");
             String val = (String)lcol.get(i);
             
             String status = null;
             if (val.indexOf(' ') > 0 ) {
                     status = val.substring(val.indexOf(' ')+1);
                     val = val.substring(0, val.indexOf(' '));
             }
     
             // certain observation values are represented by '-', we consider them to be 0.
             if(val.equals("-"))
             {
            	 if(type.equals("decimal"))
            		 val = "0.00";
            	 else if(type.equals("integer"))
            		 val = "0";
             }

             if(type.equals("decimal"))
             {
            	 out.writeAttribute("rdf:datatype", xsd + "decimal");
            	 if(!val.contains("."))
            		 out.writeCharacters(df.format(Double.valueOf(val).doubleValue()));
            	 else
            		 out.writeCharacters(val);
             }
             else if(type.equals("integer"))
             {
            	 out.writeAttribute("rdf:datatype", xsd + "integer");
            	 out.writeCharacters(val);
             }
             else
            	 out.writeCharacters(val);
             
             out.writeEndElement();
             
             if(status != null)
             {
            	 out.writeStartElement("sdmx-attribute:obsStatus");
                 out.writeAttribute("rdf:resource", "/dic/obs_status#" + status);
                 out.writeEndElement();
             }
             out.writeEndElement();
         }
         
         }
 }

	 public void returnType(String str)
	 {
		 if (str.indexOf(' ') > 0) {
			 str = str.substring(0, str.indexOf(' '));
		 }
		 
		 if( str.indexOf(".") > 0 )
         	{
			 	try
			 	{
			 		Double.parseDouble(str);
			 		
			 		if(!type.equals("non-numeric value"))
			 			type = "decimal";
			 		
			 	}
			 	catch(NumberFormatException nme)
			 	{
			 		type = "non-numeric value";
			 	}
			 	
         	}
		 else
		 {
			 // some datasets has observation values like '-'. We will ignore such values in order to determine the correct data type for the dataset.
			 if(!str.equals("-"))
			 {
				 try
				 {
					 Integer.parseInt(str);
					 if(!type.equals("decimal") & !type.equals("non-numeric value"))
						 type = "integer";
					 
				 }
				 catch(NumberFormatException nme)
				 {
					 type = "non-numeric value";
				 }
			 }
		}
		
	 }
	 
	 public void createLogFile(String filePath)
	 {
		 
		 try
		 {
			 fstream = new FileWriter(filePath + "dataset-typecheck_log.txt",true);
			 write = new BufferedWriter(fstream);
		 }catch(Exception e)
		 {
			 System.err.println("Error in opening the file : " + e.getMessage());
		 }
	}	 
	 
	public void writeDataToFile(String line)
	{
		try{
			write.newLine();
			write.write(line);
		}
		catch (Exception e){//Catch exception if any
			System.err.println("Error while writing data to file : " + e.getMessage());
		}
	}	 
}
