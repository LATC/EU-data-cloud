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
 * @author jamnas
 * Request to LATC console in JSON format
 */
public class ListTranslator {

    public Vector translateMember(String request) {
        Vector ids = new Vector(0);
        try {
            //  ConfigFilesListDto p = new ConfigFilesListDto();
            System.out.println("............");

            JSONObject json = (JSONObject) JSONSerializer.toJSON(request);
            // String query = json.getString("queue");

            JSONArray suggestions = json.getJSONArray("queue");
            for (int i = 0; i < suggestions.size(); i++) {
//JSONObject pilot = json.getJSONObject();

                JSONObject json1 = (JSONObject) JSONSerializer.toJSON(suggestions.getString(i));
//String firstNSystem.out.println( suggestions.getString(i));ame = pilot.getString("description");
                //   System.out.println( json1.getString("description"));
                ids.add(json1.getString("identifier"));
            }
            //   for(int i=0;i<ids.size();i++)
            //     System.out.println(ids.get(i));


        } catch (Exception e) {
            e.printStackTrace();
        }
        return ids;
    }

    public String getConfigFile(String request) {
        String config = "";
        try {
            // System.out.println(request);
        } //   for(int i=0;i<ids.size();i++)
        //     System.out.println(ids.get(i));
        catch (Exception e) {
            e.printStackTrace();
        }
        return request;
    }
}
