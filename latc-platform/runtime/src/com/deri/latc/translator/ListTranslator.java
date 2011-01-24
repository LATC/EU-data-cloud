/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
package com.deri.latc.translator;

import java.util.*;

import net.sf.json.JSONObject;
import net.sf.json.JSONSerializer;
import net.sf.json.JSONArray;
 
/**
 *
 * @author jamnas, nurainir
 * Request to LATC console in JSON format
 */
public class ListTranslator {
	
	private final Map <String,String> LinkingConfigs;
	
	public ListTranslator()
	{
		this.LinkingConfigs = new HashMap<String, String>();		
	}

    public void translateMember(String request) {
        
        try {
          
            JSONObject json = (JSONObject) JSONSerializer.toJSON(request);
            JSONArray suggestions = json.getJSONArray("queue");
            for (int i = 0; i < suggestions.size(); i++) {
                JSONObject item = (JSONObject) JSONSerializer.toJSON(suggestions.getString(i));
                LinkingConfigs.put(item.getString("identifier"), item.getString("title"));  
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
