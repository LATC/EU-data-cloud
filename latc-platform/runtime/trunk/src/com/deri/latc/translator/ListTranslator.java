/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
package com.deri.latc.translator;

import java.util.ArrayList;
import java.util.StringTokenizer;
import java.util.TreeMap;
import java.util.Map;
import java.io.BufferedReader;
import java.io.FileReader;
import java.io.IOException;
import java.io.FileNotFoundException;
import net.sf.json.JSONObject;
import net.sf.json.JSONSerializer;
import net.sf.json.JSONArray;
 
/**
 * Request to LATC console in JSON format
 * @author jamnas
 * @author Nur Aini Rakhmawati
 */
public class ListTranslator {
	
	private final Map <String,String> LinkingConfigs;
	
	public ListTranslator()
	{
		this.LinkingConfigs = new TreeMap<String, String>();		
	}

    /**
     * Translate JSON list of tasks to TreeMap. <br/>
     * filtering the task regarding the blacklist file
     * @param request	JSON list of task
     */
	public void translateMember(final String request) {
    	final ArrayList <String> blacklist = new ArrayList<String>();
    	
		try {
			final BufferedReader in= new BufferedReader(new FileReader("blacklist"));
			
		 String readLine;
		 while ((readLine = in.readLine()) != null) {
			 readLine = readLine.replace("->", "To");
			 blacklist.add(readLine.toLowerCase());
			  }
		 
		} catch (FileNotFoundException e1) {
			// TODO Auto-generated catch block
			e1.printStackTrace();
		} catch (IOException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}
    	
        try {
          
            JSONObject json = (JSONObject) JSONSerializer.toJSON(request);
            JSONArray suggestions = json.getJSONArray("task");
            for (int i = 0; i < suggestions.size(); i++) {
                JSONObject item = (JSONObject) JSONSerializer.toJSON(suggestions.getString(i));
                StringTokenizer st = new StringTokenizer(item.getString("title")," ",false);
                String title ="";
                while (st.hasMoreElements()) title += st.nextElement();
                title = title.replace("->", "To");
                if(blacklist.contains(title.toLowerCase()))
                	title = "**"+title;
                	LinkingConfigs.put(title,item.getString("identifier")+'#'+item.getString("created")+'#'+item.getString("modified")+'#'+item.getString("author"));  
            }

        } catch (Exception e) {
            e.printStackTrace();
        }
       
    }
	
	
    /**
     * Get List of task title-identifier
     * @return	list of task in map
     */
    public Map <String,String> getLinkingConfigs()
    {
    	return this.LinkingConfigs;    	
    }
}
