/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
package com.deri.latc.linkengine;

import java.sql.Timestamp;
import java.io.*;
import com.deri.latc.dto.VoidInfoDto;
import com.deri.latc.utility.Constants;

/**
 *
 * @author jamnas
 */
public class RunHadoopJob {

    public boolean runThis( String fileName, VoidInfoDto vi) {
        ContentWriter cw = new ContentWriter();
        /*
         * Step 1: Copy file into DFS
         * Step 2: Run load command
         * Step 3: Run match command
         * Step 4: Get results in local file sys
         * Step 5: Merge the Results
         * Step 6: TTL file (in future)
         *
         */
        try {
            java.util.Date date = new java.util.Date();
            fileName = fileName;

            String command = "";
            Process process;
            int returnCode = 0;

            command = "hadoop-0.20.2/bin/hadoop dfs -rmr /user/nurgun/" + fileName + "/ /user/nurgun/r" + fileName + "/ ";
            process = Runtime.getRuntime().exec(command);
            returnCode = process.waitFor();
            System.out.println(command + " Return code = " + returnCode);

            command = "hadoop-0.20.2/bin/hadoop fs -put jamal/r1/" + fileName + "/" + "spec.xml " + fileName;
            process = Runtime.getRuntime().exec(command);
            returnCode = process.waitFor();
            System.out.println(command + " Return code = " + returnCode + "::" + new Timestamp(date.getTime()));


            command = "hadoop-0.20.2/bin/hadoop jar silkmr.jar load " + fileName + " ./cache";
            System.out.println(command);
            process = Runtime.getRuntime().exec(command);
            returnCode = process.waitFor();
            date = new java.util.Date();
            // cw.writeLog(process);
            System.out.println(command + " Return code = " + returnCode + "::" + new Timestamp(date.getTime()));

            if (returnCode == 0) {
                date = new java.util.Date();

                command = "hadoop-0.20.2/bin/hadoop jar silkmr.jar match ./cache ./r" + fileName + " ";
                process = Runtime.getRuntime().exec(command);
                returnCode = process.waitFor();
                //cw.writeLog(process);
                System.out.println(command + " Return code = " + returnCode + "::" + new Timestamp(date.getTime()));
                date = new java.util.Date();
//hadoop fs -cat src/* | hadoop fs -put - dest_file
                command = "hadoop-0.20.2/bin/hadoop dfs -mkdir /user/nurgun/r" + fileName + "/re";
                process = Runtime.getRuntime().exec(command);
                returnCode = process.waitFor();
                System.out.println(command + " Return code = " + returnCode + "::" + new Timestamp(date.getTime()));

                command = "hadoop-0.20.2/bin/hadoop dfs -mv /user/nurgun/r" + fileName + "/*.nt /user/nurgun/r" + fileName + "/re";
                process = Runtime.getRuntime().exec(command);
                returnCode = process.waitFor();
                System.out.println(command + " Return code = " + returnCode + "::" + new Timestamp(date.getTime()));

                command = "hadoop-0.20.2/bin/hadoop dfs -getmerge /user/nurgun/r" + fileName + "/re/ jamal/r1/" + fileName + "/" + Constants.LINKS_FILE_NAME;
                process = Runtime.getRuntime().exec(command);
                returnCode = process.waitFor();
                System.out.println(command + " Return code = " + returnCode + "::" + new Timestamp(date.getTime()));


                command = "wc -l jamal/r1/" + fileName + "/links.nt";
                process = Runtime.getRuntime().exec(command);
                returnCode = process.waitFor();
                BufferedReader buf = new BufferedReader(new InputStreamReader(process.getInputStream()));
                String line = "";
                String stat = "";
                int k = 0;
                while ((line = buf.readLine()) != null) {
                    System.out.println((++k) + " " + line);
                    stat = line;
                }
                //String line = buf.readLine();

                stat = stat.substring(0, stat.indexOf(" "));
                System.out.println("::LINE::::::::" + stat);
                vi.setStatItem(stat);


                System.out.println(command + " Return code = " + returnCode + "::" + new Timestamp(date.getTime()));



                return true;
            } else {
                vi.setRemarks("Job Failed: Error in Loading Data");
                return false;
            }
        } catch (Exception e) {
            System.out.println(e);
            return false;
        }

    }
}
