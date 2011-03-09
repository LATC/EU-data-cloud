package eu.latc.misc;

import java.text.ParseException;
import java.text.SimpleDateFormat;
import java.util.Date;

/**
 * @author Christophe Guéret <christophe.gueret@gmail.com>
 * 
 */
public class DateToXSDateTime {
	/**
	 * Format a date object into a string as defined in
	 * http://www.w3schools.com/Schema/schema_dtypes_date.asp
	 * 
	 * @param date
	 * @return
	 */
	public static String format(Date date) {
		SimpleDateFormat sdf = new SimpleDateFormat("yyyy-MM-dd'T'hh:mm:ss");
		return sdf.format(date);
	}

	/**
	 * @param datetime
	 * @return
	 * @throws ParseException
	 */
	public static Date parse(String datetime) throws ParseException {
		SimpleDateFormat sdf = new SimpleDateFormat("yyyy-mm-dd hh:mm:ss");
		return sdf.parse(datetime);
	}
}
