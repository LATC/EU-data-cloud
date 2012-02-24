package org.deri.eurostat;

import java.io.BufferedWriter;
import java.io.FileWriter;
import org.apache.commons.cli.BasicParser;
import org.apache.commons.cli.CommandLine;
import org.apache.commons.cli.CommandLineParser;
import org.apache.commons.cli.Options;
import com.ontologycentral.estatwrap.SDMXParser;

/**
 * 
 * @author Aftab Iqbal
 *
 */

public class Main {

	public static String dsdDirPath = "C:/test/dsd/";
	public static String sdmxDirPath = "C:/test/data/";
	public static String zipDirPath = "C:/test/data/";
	static BufferedWriter write = null;
	static FileWriter fstream = null;
	
	public static void main(String[] args) throws Exception
	{
		
		String sdmxFilePath = "";
		String logFilePath = "";
		CommandLineParser parser = new BasicParser( );
		Options options = new Options( );
		
		options.addOption("i", "file path", true, "sdmx file path.");
		options.addOption("l", "log file path", true, "File path where the logs will be generated");
		
		CommandLine commandLine = parser.parse( options, args );
		
		if(commandLine.hasOption('i'))
			sdmxFilePath = commandLine.getOptionValue('i');
				
		if(commandLine.hasOption('l'))
			logFilePath = commandLine.getOptionValue('l');
		
		if(sdmxFilePath.equals("") || logFilePath.equals(""))
		{
			
			return;
		}
		else
		{
			Main obj = new Main();
			obj.extractTimeFormat(logFilePath, sdmxFilePath);
		}
		
	}

	public void extractTimeFormat(String logFilePath, String sdmxFilePath) throws Exception
	{
		SDMXParser obj = new SDMXParser();
		
		createLogFile(logFilePath);
		
		String time_format = obj.get_FREQ_fromSDMX(sdmxFilePath);
		
		if(time_format.equals("PT1M"))
			writeDataToFile("Minutely data found in : " + sdmxFilePath.substring(sdmxFilePath.lastIndexOf("/")+1));
		else if(time_format.equals("P7D"))
			writeDataToFile("Weekly data found in : " + sdmxFilePath.substring(sdmxFilePath.lastIndexOf("/")+1));
		else if(time_format.equals("P6M"))
			writeDataToFile("Semi-annual data found in : " + sdmxFilePath.substring(sdmxFilePath.lastIndexOf("/")+1));
		
		write.flush();
		write.close();
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
		catch (Exception e){
			System.err.println("Error while writing data to file : " + e.getMessage());
		}
	}	 
}