/**
 * 
 */
package eu.latc.console.resource;

import java.io.PrintWriter;
import java.io.StringWriter;

import org.restlet.data.Status;
import org.restlet.representation.StringRepresentation;
import org.restlet.resource.ServerResource;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import eu.latc.misc.MailSender;

/**
 * @author Christophe Guéret <christophe.gueret@gmail.com>
 * 
 */
public class ConsoleResource extends ServerResource {
	// Logger instance
	protected final Logger logger = LoggerFactory.getLogger(ConsoleResource.class);

	/*
	 * (non-Javadoc)
	 * 
	 * @see org.restlet.resource.UniformResource#doCatch(java.lang.Throwable)
	 */
	@Override
	protected void doCatch(Throwable throwable) {
		// If anything goes wrong, just report back on an internal error
		getResponse().setStatus(Status.SERVER_ERROR_INTERNAL);

		// Get the text from the exception
		StringWriter sw = new StringWriter();
		PrintWriter pw = new PrintWriter(sw);
		throwable.printStackTrace(pw);

		// Log the error
		logger.error(sw.getBuffer().toString());

		// Prepare a detailed log
		StringBuffer buffer = new StringBuffer();
		buffer.append("Request: ").append(getRequest()).append("\n");
		buffer.append("Request entity:\n").append(getRequestEntity()).append("\n");
		buffer.append("Query: ").append(getQuery()).append("\n");
		buffer.append("Client agent: ").append(getClientInfo().getAgent()).append("\n");
		buffer.append("Client IP: ").append(getClientInfo().getAddress()).append("\n");
		buffer.append("Full stacktrace:\n");
		buffer.append(sw.getBuffer());

		// Adjust the response if there was one in preparation
		if (getResponse() != null) {
			getResponse().setEntity(new StringRepresentation(buffer.toString()));
		}

		try {
			logger.info("Send mail");
			// Send a mail
			MailSender mailSender = new MailSender();
			mailSender.setTopic("[LATC] Exception in Console");
			mailSender.addRecepient("christophe.gueret@gmail.com");
			mailSender.addRecepient("anja@anjeve.de");
			mailSender.setMessage(buffer.toString());
			mailSender.send();
		} catch (Exception e) {
			// Ignore the exception if we fail sending the mail
			e.printStackTrace();
		}
	}

}
