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
		out.write("ID, Title, Executing Time, Status, Reason, URL Specification,Owner");
		
	}
	
	public void putData(String ID, String Title, String LinkSpec,long ExecuteDate, status st, String reason )
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
			out.append(ID+','+Title.replace("To", "->")+','+timeEx+','+STATUS+','+reason+','+LinkSpec+", unknown");
		} catch (IOException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}
	}
	
	private String LongToStringDate(long ExecuteDate)
	{
		String timeEx=null;
		if((ExecuteDate/86400000) > 0)
		{
			if((ExecuteDate/86400000)<10)
				timeEx ='0'+ Integer.toString((int)(ExecuteDate/86400000))+':';
			else
			timeEx = Integer.toString((int)(ExecuteDate/86400000))+':';
			ExecuteDate = ExecuteDate % 86400000;
		}
		else
			timeEx = timeEx+"00:";
		if((ExecuteDate/3600000) > 0)
		{
			if((ExecuteDate/3600000)<10)
				timeEx =timeEx+'0'+ Integer.toString((int)(ExecuteDate/3600000))+':';
			else
			timeEx = timeEx+Integer.toString((int)(ExecuteDate/3600000))+':';
			ExecuteDate = ExecuteDate % 3600000;
		}
		else
			timeEx = timeEx+"00:";
		if((ExecuteDate/60000) > 0)
		{
			if((ExecuteDate/60000) <10 )
				timeEx = timeEx+'0'+Integer.toString((int)(ExecuteDate/60000))+':';
			else
			timeEx = timeEx+Integer.toString((int)(ExecuteDate/60000))+':';
			ExecuteDate = ExecuteDate % 60000;
		}
		else
			timeEx = timeEx+"00:";
		if((ExecuteDate/1000) > 0)
		{
			if(ExecuteDate < 10)
				timeEx = timeEx+'0'+Integer.toString((int)(ExecuteDate/1000));
			else
				timeEx = timeEx+Integer.toString((int)(ExecuteDate/1000));
		}
		else
			timeEx = timeEx+"00";
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
		Date dt1 = new Date(2011, 3, 9, 02, 23,20);
		Date dt2 = new Date(2011, 3, 11, 14, 30);
		rc.putData("ff8081812cac8e41012cac8e41f50000", "DBPediaToDrugBankdrugs","http://", dt2.getTime()-dt1.getTime(), status.ongoing	, "nothing");
		rc.close();
		

	}

	

}
