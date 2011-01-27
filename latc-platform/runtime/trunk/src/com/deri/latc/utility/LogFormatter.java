package com.deri.latc.utility;
import java.util.logging.Formatter;
import java.util.logging.LogRecord;

public class LogFormatter extends Formatter {

	@Override
	public String format(LogRecord record) {
		 StringBuffer buffer = new StringBuffer(200);
         buffer.append(new java.util.Date());
         buffer.append(' ');
         buffer.append(record.getLevel());
         buffer.append(' ');
         buffer.append(formatMessage(record));
         buffer.append('\n');
         return buffer.toString();

	}

}
