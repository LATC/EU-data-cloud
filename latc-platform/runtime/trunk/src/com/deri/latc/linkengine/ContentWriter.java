/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
package com.deri.latc.linkengine;

import java.io.*;
import com.deri.latc.dto.VoidInfoDto;

/**
 * Storing String or VOID to file
 * @author jamnas
 * @author nurainir
 */
public class ContentWriter {

    public void writeIt(String fileName, String content) {
        try {
                FileWriter fstream = new FileWriter(fileName);
	            BufferedWriter out = new BufferedWriter(fstream);
	            out.write(content);
	            out.flush();
	            //Close the output stream
	            out.close();
           
            
        } catch (Exception e) {//Catch exception if any
            System.err.println("Error: " + e.getMessage());
        }
     
    }



    public void writeIt(String fileName, VoidInfoDto vi) {
        try {
            String content = "";

            content += vi.getGlobalPrefixes();

            content += vi.getThirdPartyInterlinking();

            System.out.println(content);
         
            FileWriter fstream = new FileWriter(fileName);
            BufferedWriter out = new BufferedWriter(fstream);

            out.write(content);
            out.flush();
            //Close the output stream
            out.close();
        } catch (Exception e) {//Catch exception if any
            System.err.println("Error: " + e.getMessage());
        }
       
    }
}
