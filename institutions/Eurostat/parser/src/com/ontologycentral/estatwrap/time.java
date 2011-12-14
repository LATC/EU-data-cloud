package com.ontologycentral.estatwrap;


// this class transforms the different time representations 
public class time {

	public enum Quarterly {Q1("-01-01"), Q2("-04-01"), Q3("-07-01"), Q4("-10-01");
		
		private String value;

		private Quarterly(String value) {
            this.value = value;
		}
		
		public String getValue()
		{
			return value;
		}
	};

	public enum Semester {S1("-01-01"), S2("-07-01");
	
	private String value;

	private Semester(String value) {
        this.value = value;
	}
	
	public String getValue()
	{
		return value;
	}
};

	public enum Monthly {M1("-01-01"), M2("-02-01"), M3("-03-01"), M4("-04-01"), M5("-05-01"), M6("-06-01"), M7("-07-01"), M8("-08-01"), M9("-09-01"), M10("-10-01"), M11("-11-01"), M12("-12-01");
	
		private String value;

		private Monthly(String value) {
			this.value = value;
		}
		
		public String getValue()
		{
			return value;
		}

	};
	
	
	public static String convertTimeSereis(String time)
	{
		String year = "";
		String freq = "";
		
		//for time period like : (Daily) D - 2011M12D05
		if(time.contains("M") & time.contains("D") & !time.contains("T"))
		{
			return daily(time);
		}
		// for time period like : (Half yearly, Semester) S - 2011S2
		else if(time.contains("S"))
		{
			freq = time.substring(time.indexOf("S"));
			year = time.substring(0,time.indexOf("S"));
			
			return year + semesterly(freq);
		}
		// for time period like : LTAA
		else if(time.contains("LTAA"))
		{
			// we will handle this special case while writing triples for this observation value
			return "LTAA";
		}
		// for time period like : 1989_1993
		else if(time.contains("_"))
		{
			return multiYear(time);
		}
		// for time period like : 2011Q2
		else if(time.contains("Q") & time.length() == 6)
		{
			freq = time.substring(time.indexOf("Q"));
			year = time.substring(0,time.indexOf("Q"));
			
			return year + quarterly(freq);
		}
		// for time period like : 2011M
		else if(time.contains("M") & time.length() >=6 & time.length() <=7)
		{
			freq = time.substring(time.indexOf("M"));
			year = time.substring(0,time.indexOf("M"));
			
			return year + monthly(freq);
		}
		else 
			return time;
	}
	
	// for example : 2011Q4
	public static String quarterly(String value)
	{
		
		if(value.equals("Q1"))
			return Quarterly.Q1.getValue();
		else if(value.equals("Q2"))
			return Quarterly.Q2.getValue();
		else if(value.equals("Q3"))
			return Quarterly.Q3.getValue();
		else if(value.equals("Q4"))
			return Quarterly.Q4.getValue();
		
		return value;
	}
	
	//for example : 2011M3
	public static String monthly(String value)
	{
		if(value.equals("M1"))
			return Monthly.M1.getValue();
		else if(value.equals("M2"))
			return Monthly.M2.getValue();
		else if(value.equals("M3"))
			return Monthly.M3.getValue();
		else if(value.equals("M4"))
			return Monthly.M4.getValue();
		else if(value.equals("M5"))
			return Monthly.M5.getValue();
		else if(value.equals("M6"))
			return Monthly.M6.getValue();
		else if(value.equals("M7"))
			return Monthly.M7.getValue();
		else if(value.equals("M8"))
			return Monthly.M8.getValue();
		else if(value.equals("M9"))
			return Monthly.M9.getValue();
		else if(value.equals("M10"))
			return Monthly.M10.getValue();
		else if(value.equals("M11"))
			return Monthly.M11.getValue();
		else if(value.equals("M12"))
			return Monthly.M12.getValue();
		
		return value;
	}
	
	public static String daily(String value)
	{
		int month;
		int year;
		int day;
		
		year = Integer.parseInt(value.substring(0,value.indexOf("M")));
		month = Integer.parseInt(value.substring(value.indexOf("M")+1,value.indexOf("D")));
		day = Integer.parseInt(value.substring(value.indexOf("D")+1));
				
		if(month < 10)
			value = String.valueOf(year) + "-" + String.valueOf("0" + month);
		else
			value = String.valueOf(year) + "-" + String.valueOf(month);
		
		if(day < 10)
			value = value  + ("-" + String.valueOf("0" + day));
		else
			value = value + ("-" + String.valueOf(day));
		
		return value;
	}
	
	public static String semesterly(String value)
	{
		if(value.equals("S1"))
			return Semester.S1.getValue();
		else if(value.equals("S2"))
			return Semester.S2.getValue();
		
		return value;
	}
	
	public static String multiYear(String value)
	{
		return value.substring(0,value.indexOf("_")) + "-01-01";
	}
	
	public static void main(String[] args)
	{
		System.out.println("1999Q2 --> " + convertTimeSereis("1992M12D2"));
	}

}
