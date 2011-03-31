package com.deri.latc.utility;

import java.io.BufferedWriter;
import java.io.FileWriter;
import java.io.IOException;
import java.util.Date;


public class ReportCSV {

	
	public static enum status {sucesss, failed, ongoing}; 
	final BufferedWriter out; 
	
	public ReportCSV(String path) throws IOException {
	
		out = new BufferedWriter(new FileWriter(path));
		out.write("ID, Title, Executing Time, Status, LinksGenerated, Reason, URL Specification,Owner");
		
		
	}
	
	public void putData(String ID, String Title, String LinkSpec,long ExecuteDate, status st, String reason, int links, String author )
	{
		String STATUS;
		if(st==status.failed)
			STATUS = "FAILED";
		else if(st==status.sucesss)
			STATUS = "SUCCESS";
		else
			STATUS = "ONGOING";
		String timeEx=this.LongToStringDate(ExecuteDate);
		try {
			out.newLine();
			out.append(ID+','+Title.replace("To", "->")+','+timeEx+','+STATUS+','+links+','+reason+','+LinkSpec+','+author);
			out.flush();
		} catch (IOException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}
	}
	
	private String LongToStringDate(long ExecuteDate)
	{
		final long [] divisor ={24*60*60 // days
								,60*60 //hour
								,60 // minutes
								,1}; // second
		String timeEx="";

		int d=0;
		while(d<4)
		{
			long divison = ExecuteDate / divisor [d]/1000;
			
			if(divison > 0)
			{
				if(divison<10)
					timeEx =timeEx+'0'+ Integer.toString((int)divison)+':';
				else
				timeEx = timeEx+Integer.toString((int)divison)+':';
				ExecuteDate =  ExecuteDate % (divisor [d]*1000);
			}
			else
				timeEx = timeEx+"00:";
			d++;
		}
		return timeEx;
	}
	
	public void close()
	{
		try {
			out.close();
		} catch (IOException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}
	}

	/**
	 * @param args
	 * @throws IOException 
	 */
	public static void main(String[] args) throws IOException {
		ReportCSV rc = new ReportCSV("myreport.csv");
		@SuppressWarnings("deprecation")
		Date dt1 = new Date(2011, 3, 11, 02, 23,20);
		@SuppressWarnings("deprecation")
		Date dt2 = new Date(2011, 3, 11, 14, 30);
		rc.putData("ff8081812cac8e41012cac8e41f50000", "DBPediaToDrugBankdrugs","http://", dt2.getTime()-dt1.getTime(), status.ongoing	, "nothing",12, "myauthor");
		rc.close();
		

	}

	

}
