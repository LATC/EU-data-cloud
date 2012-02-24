package org.deri.eurostat.toc;

import java.util.Properties;

import javax.mail.Message;
import javax.mail.MessagingException;
import javax.mail.Session;
import javax.mail.Transport;
import javax.mail.internet.InternetAddress;
import javax.mail.internet.MimeMessage;

/**
 * 
 * @author Aftab Iqbal
 *
 */

public class Email {

	static String host = "smtp.gmail.com";
    static String from = "eurostat.updates@gmail.com";
    static String pass = "eurostatpassword";
    Properties props;
    
    public Email()
    {
    	setProperties();
    }
    
	public void setProperties()
	{
		props = System.getProperties();
		props.put("mail.smtp.starttls.enable", "true"); // added this line
	    props.put("mail.smtp.host", host);
	    props.put("mail.smtp.user", from);
	    props.put("mail.smtp.password", pass);
	    props.put("mail.smtp.port", "587");
	    props.put("mail.smtp.auth", "true");
	}
	
	public void sendEmail(String subject, StringBuffer emailBody)
	{
		String[] to = {"eurostat-updates@lists.deri.org"};
		
		Session session = Session.getDefaultInstance(props, null);
	    MimeMessage message = new MimeMessage(session);
	   
	    try{
		    message.setFrom(new InternetAddress(from));
		 
		    InternetAddress[] toAddress = new InternetAddress[to.length];
		    for( int i=0; i < to.length; i++ ) { 
		        toAddress[i] = new InternetAddress(to[i]);
		    }
		    
		    for( int i=0; i < toAddress.length; i++) {
		        message.addRecipient(Message.RecipientType.TO, toAddress[i]);
		    }
		    
		    message.setSubject(subject);
		    message.setText(emailBody.toString());
		    Transport transport = session.getTransport("smtp");
		    transport.connect(host, from, pass);
		    transport.sendMessage(message, message.getAllRecipients());
		    transport.close();
	    
	   }catch(MessagingException ex)
	   {
		   System.out.println(ex.getMessage());
	   }
	   
	}
	
}
