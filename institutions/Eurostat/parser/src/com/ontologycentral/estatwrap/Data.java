package com.ontologycentral.estatwrap;

import java.io.BufferedReader;
import java.io.BufferedWriter;
import java.io.FileWriter;
import java.io.IOException;
import java.io.RandomAccessFile;
import java.io.Reader;
import java.text.DecimalFormat;
import java.util.List;
import java.util.logging.Logger;

import javax.xml.stream.XMLStreamException;
import javax.xml.stream.XMLStreamWriter;

public class Data {

public static String PREFIX = "http://ontologycentral.com/2009/01/eurostat/ns#";
	public static String xsd = "http://www.w3.org/2001/XMLSchema#";
	BufferedReader _in;

	static BufferedWriter write = null;
	static FileWriter fstream = null;
	int timePosition = 0;
	// here, use a threshold to limit the amount of data converted (GAE limitations)
	public static int MAX_COLS = 8;
	public static int MAX_ROWS = 1024;
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
		
//		if ((line = _inputStream.readLine()) != null) {
//			++rows;
//			line = line.trim();
//			if (line.length() <= 0) {
//				throw new IOException("could not read header!");
//			}
//
//			h = new Header(line);
//		}
		
		
		
		//System.out.println("Type is :" + type);
		
		if(type.equals("non-numeric value"))
		{
			createLogFile(logPath);
			writeDataToFile("Non-numeric vlaues in the Dataset : " + datasetID);
			
			try{
	        	write.flush();  
	        	write.close();
			}catch(IOException e){
				System.out.println("Error while closing the file...");
			}
		}
		
		List hd1 = h.getDim1();
		for (int j = 0; j < hd1.size(); ++j) {
       	 //System.out.println("hd1 --> " + hd1.get(j));
         if(hd1.get(j).equals("time"))
        	 timePosition = j;
        }
		
		while ((line = _in.readLine()) != null) {
			//System.out.println("in print truple");
			++rows;
			line = line.trim();
			if (line.length() <= 0) {
				continue;
			}

			l = new Line(line);

			printTriple(h, l, out, rows, id, freq);

			// this code restricts from converting more data.
//			if (rows > MAX_ROWS) {
//				break;
//			}
		}

		
		_in.close();
	}

/*	
	public void printTriple(Header h, Line l, XMLStreamWriter out, int bnodeid, String id) throws XMLStreamException {
		List hd1 = h.getDim1();
		List ld1 = l.getDim1();

		if (hd1.size() != ld1.size()) {
			System.err.println("header dimensions and line dimensions don't match!");
		}

		List hcol = h.getCols();
		List lcol = l.getCols();

		if (hcol.size() != lcol.size()) {
			System.err.println("header columns and line columns don't match!");
		}
		
		int start = 0;
		int end = Math.min(hcol.size(), MAX_COLS);
		
		// hack - some stats are sorted from oldest to newest, some the other way round
		// check if the last entry contains year 200x or 201x
		String last = (String)hcol.get(hcol.size()-1);
		//System.out.println(last);
		if (last.contains("200") || last.contains("201")) {
			start = hcol.size()-MAX_COLS;
			if (start < 0) {
				start = 0;
			}
			end = hcol.size();
		}

		for (int i = start; i < end; ++i)
		{
			if (((String)lcol.get(i)).equals(":")) {
				continue;
			}
			
    		out.writeStartElement("qb:Observation");
    		
    		out.writeStartElement("qb:dataset");
    		out.writeAttribute("rdf:resource", baseURI + "/id/" + id + "#ds");
    		// @@@ workaround to get query processor to function
    		//out.writeAttribute("rdf:resource", id + "#ds");

    		out.writeEndElement();

			for (int j = 0; j < hd1.size(); ++j) {
	    		out.writeStartElement((String)hd1.get(j));
	    		//--//out.writeAttribute("rdf:resource", Dictionary.PREFIX + (String)hd1.get(j) + "#" + (String)ld1.get(j));
	    		out.writeAttribute("rdf:resource", baseURI + "/dic/" + (String)hd1.get(j) + "#" + (String)ld1.get(j));
	    		out.writeEndElement();
			}

    		out.writeStartElement((String)h.getDim2());
    		//--//out.writeAttribute("rdf:resource", Dictionary.PREFIX + (String)h.getDim2() + "#" + (String)hcol.get(i));
    		out.writeAttribute("rdf:resource", baseURI + "/dic/" + (String)h.getDim2() + "#" + (String)hcol.get(i));
    		out.writeEndElement();

    		//http://purl.org/linked-data/sdmx/2009/measure#obsValue
    		out.writeStartElement("sdmx-measure:obsValue");
    		String val = (String)lcol.get(i);
    		String note = null;
    		if (val.indexOf(' ') > 0) {
    			note = val.substring(val.indexOf(' ')+1);
    			val = val.substring(0, val.indexOf(' '));
    			//-//out.writeAttribute("rdf:datatype", Dictionary.PREFIX + "note#" + note);
    			out.writeAttribute("rdf:datatype", baseURI + "/dic/" + "note#" + note);
    		}
    		out.writeCharacters(val);
    		out.writeEndElement();

    		out.writeEndElement();
		}
	}
*/	

	public void getType(Header h, Line l)
	{
        List hd1 = h.getDim1();
        List ld1 = l.getDim1();
        String obs_URI = "";
        if (hd1.size() != ld1.size()) {
                System.err.println("header dimensions and line dimensions don't match!");
        }

        List hcol = h.getCols();
        List lcol = l.getCols();

        if (hcol.size() != lcol.size()) {
                System.err.println("header columns and line columns don't match!");
        }
        
        int start = 0;
        
        // displays only 8 columns data per dataset. But we need to dump all the data.
        //int end = Math.min(hcol.size(), MAX_COLS);
        int end = hcol.size();
        
        // hack - some stats are sorted from oldest to newest, some the other way round
        // check if the last entry contains year 200x or 201x
        String last = (String)hcol.get(hcol.size()-1);

      for (int i = start; i < end; ++i)
      {
     	 if (((String)lcol.get(i)).equals(":") || ((String)lcol.get(i)).contains(":")) {
              continue;
     	 }
     	 String val = (String)lcol.get(i);
     	 //System.out.println(val);
     	 returnType(val);
      }
      
      //System.out.println("Type is : " + type);
	}
	
	 public void printTriple(Header h, Line l, XMLStreamWriter out, int bnodeid, String id, String freq) throws XMLStreamException {
         List hd1 = h.getDim1();
         List ld1 = l.getDim1();
         DecimalFormat df = new DecimalFormat ("0.00");
         
         String obs_URI = "";
         if (hd1.size() != ld1.size()) {
                 System.err.println("header dimensions and line dimensions don't match!");
         }

         List hcol = h.getCols();
         List lcol = l.getCols();

         if (hcol.size() != lcol.size()) {
                 System.err.println("header columns and line columns don't match!");
         }
         
         int start = 0;
         
         // displays only 8 columns data per dataset. But we need to dump all the data.
         //int end = Math.min(hcol.size(), MAX_COLS);
         int end = hcol.size();
         
         // hack - some stats are sorted from oldest to newest, some the other way round
         // check if the last entry contains year 200x or 201x
         String last = (String)hcol.get(hcol.size()-1);
         //System.out.println(last);
         
         // This piece of code restricts the number of records to display only the last 8 columns if
         // last entry contains year 200x or 201x. We dont need it in our case as we are dumping all data.
//         if (last.contains("200") || last.contains("201")) {
//                 start = hcol.size()-MAX_COLS;
//                 if (start < 0) {
//                         start = 0;
//                 }
//                 end = hcol.size();
//         }


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
         // @@@ workaround to get query processor to function
         //out.writeAttribute("rdf:resource", id + "#ds");

         out.writeEndElement();

         // new code for adding FREQ
         if(!freq.equals(""))
         {
        	 out.writeStartElement("sdmx-dimension:freq");
             out.writeAttribute("rdf:resource", "http://purl.org/linked-data/sdmx/2009/code#freq-" + freq);
             out.writeEndElement();
         }
         
         for (int j = 0; j < hd1.size(); ++j) {
        	 //System.out.println("hd1 --> " + hd1.get(j));
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

         //System.out.println(hcol.get(i));
// new code
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
// old code
//         out.writeStartElement("property:" + (String)h.getDim2());
//         out.writeAttribute("rdf:resource", Dictionary.PREFIX + (String)h.getDim2() + "#" + (String)hcol.get(i));
//         out.writeEndElement();
         
         //http://purl.org/linked-data/sdmx/2009/measure#obsValue
         
         // exclude entries like ': c' which exists in the dataset
         if(!lcol.get(i).toString().contains(":"))
         {
             out.writeStartElement("sdmx-measure:obsValue");
             String val = (String)lcol.get(i);
             
             //System.out.println(val);
             //String datatype = "";
             //if(type.equals("decimal"))
            	// datatype = "";
             
             String status = null;
             if (val.indexOf(' ') > 0 ) {
                     status = val.substring(val.indexOf(' ')+1);
                     val = val.substring(0, val.indexOf(' '));
                     //out.writeAttribute("rdf:resource", "/dic/obs_status#" + status);
                     //out.writeAttribute("rdf:datatype", Dictionary.PREFIX + "obs_status#" + status);
             }
     
    // new code         

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
         
// old code         
//         out.writeCharacters(val);
//         out.writeEndElement();
//
//         out.writeEndElement();
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
			 		//System.out.println(str + " is a valid decimal number");
			 	}
			 	catch(NumberFormatException nme)
			 	{
			 		//System.out.println(str + " is not a valid decimal number");
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
					 //System.out.println(str + " is valid integer number");
				 }
				 catch(NumberFormatException nme)
				 {
					 //System.out.println(str + " is not a valid integer number");
					 type = "non-numeric value";
				 }
			 }
		}
		 
		 //return type;
	 }
	 
	 public void createLogFile(String filePath)
	 {
		 
		 try
		 {
			 fstream = new FileWriter(filePath + "log.txt",true);
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
