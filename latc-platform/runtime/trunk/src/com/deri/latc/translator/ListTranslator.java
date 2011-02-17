/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
package com.deri.latc.translator;

import java.util.ArrayList;
import java.util.TreeMap;
import java.util.Map;
import java.io.*;

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

    public void translateMember(final String request) {
    	final ArrayList <String> blacklist = new ArrayList<String>();
    	
		try {
			final BufferedReader in= new BufferedReader(new FileReader("blacklist"));
			
		 String readLine;
		 while ((readLine = in.readLine()) != null) {
			 blacklist.add(readLine);
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
            JSONArray suggestions = json.getJSONArray("queue");
            for (int i = 0; i < suggestions.size(); i++) {
                JSONObject item = (JSONObject) JSONSerializer.toJSON(suggestions.getString(i));
                final String title = item.getString("title").substring(0, item.getString("title").length()-4);
                if(!blacklist.contains(title))
                	LinkingConfigs.put(title,item.getString("identifier"));  
            }

        } catch (Exception e) {
            e.printStackTrace();
        }
       
    }
    
    public Map <String,String> getLinkingConfigs()
    {
    	return this.LinkingConfigs;    	
    }
}
