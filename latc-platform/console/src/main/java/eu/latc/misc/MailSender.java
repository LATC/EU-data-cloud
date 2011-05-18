/**
 * 
 */
package eu.latc.misc;

import java.util.ArrayList;
import java.util.List;
import java.util.Properties;

import javax.mail.Message;
import javax.mail.Session;
import javax.mail.internet.AddressException;
import javax.mail.internet.InternetAddress;
import javax.mail.internet.MimeMessage;

import com.sun.mail.smtp.SMTPTransport;

/**
 * @author Christophe Guéret <christophe.gueret@gmail.com>
 * 
 * @see http://code.google.com/apis/gmail/oauth/code.html
 */
public class MailSender {
	/** Recipients list */
	private List<InternetAddress> recipients = new ArrayList<InternetAddress>();
	private String text;
	private String topic;
	private final static String EMAIL = "latc.platform@gmail.com";
	private final static String OAUTH_TOKEN = "1/fl26DxUbpeg_BS3OGJSw-R4Ra45aTu7Ery6kR4X_ur0";
	private final static String OAUTH_TOKEN_SECRET = "otTXDWPFLO-c-jU9V20ev1na";

	/**
	 * 
	 */
	public MailSender() {
		XoauthAuthenticator.initialize();
	}

	/**
	 * @param recipientName
	 * @throws AddressException
	 */
	public void addRecepient(String recipientName) throws AddressException {
		recipients.add(new InternetAddress(recipientName));
	}

	/**
	 * @param message
	 */
	public void setMessage(String text) {
		this.text = text;
	}

	/**
	 * @param topic
	 */
	public void setTopic(String topic) {
		this.topic = topic;
	}

	/**
	 * @throws Exception
	 */
	public void send() throws Exception {
		// Apparently, we need that
		Properties props = new Properties();
		props.put("mail.transport.protocol", "smtps");
		props.put("mail.smtps.host", "smtp.gmail.com");
		props.put("mail.smtps.auth", "true");
		props.put("mail.smtps.quitwait", "false");
		Session mailSession = Session.getDefaultInstance(props);
		mailSession.setDebug(false);

		// Compose the message
		MimeMessage message = new MimeMessage(mailSession);
		message.setSubject(topic);
		message.setContent(text, "text/plain");
		for (InternetAddress recipient : recipients)
			message.addRecipient(Message.RecipientType.TO, recipient);

		// Send to GMail
		SMTPTransport smtpTransport = XoauthAuthenticator.connectToGMail(EMAIL, OAUTH_TOKEN, OAUTH_TOKEN_SECRET, true);
		smtpTransport.sendMessage(message, message.getRecipients(Message.RecipientType.TO));
		smtpTransport.close();
	}

}
