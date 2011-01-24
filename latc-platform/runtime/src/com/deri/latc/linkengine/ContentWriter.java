/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
package com.deri.latc.linkengine;

import java.io.*;
import com.deri.latc.dto.VoidInfoDto;

/**
 *
 * @author jamnas
 */
public class ContentWriter {

    public boolean writeIt(String filePath, String fileName, String content) {
        try {
            boolean success = (new File(filePath)).mkdirs();
            FileWriter fstream = new FileWriter(filePath + fileName);
            BufferedWriter out = new BufferedWriter(fstream);
            out.write(content);
            out.flush();
            //Close the output stream
            out.close();
            return true;
        } catch (Exception e) {//Catch exception if any
            System.err.println("Error: " + e.getMessage());
        }
        return false;
    }



    public boolean writeIt(String filePath, String fileName, VoidInfoDto vi) {
        try {
            String content = "";

            content += vi.getGlobalPrefixes();

            content += vi.getThirdPartyInterlinking();

            System.out.println(content);


            boolean success = (new File(filePath)).mkdirs();
            if (success)
            {
            FileWriter fstream = new FileWriter(filePath + fileName);
            BufferedWriter out = new BufferedWriter(fstream);

            out.write(content);
            out.flush();
            //Close the output stream
            out.close();
            }

            return true;
        } catch (Exception e) {//Catch exception if any
            System.err.println("Error: " + e.getMessage());
        }
        return false;
    }
}
