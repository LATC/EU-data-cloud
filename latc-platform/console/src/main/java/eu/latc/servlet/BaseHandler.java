package eu.latc.servlet;

import org.restlet.data.Status;
import org.restlet.resource.ResourceException;
import org.restlet.resource.ServerResource;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import eu.latc.console.Manager;

public class BaseHandler extends ServerResource {
	
	// Logger instance
	protected final Logger logger = LoggerFactory.getLogger(BaseHandler.class);

	// Reserved IDs
	protected final static String ID_NEW = "new";

	// The entity manager used to deal with the configuration files
	protected Manager manager;
	
	// The ID of the configuration file
	protected String configurationID;

	/*
	 * (non-Javadoc)
	 * 
	 * @see org.restlet.resource.UniformResource#doInit()
	 */
	@Override
	protected void doInit() throws ResourceException {
		// Get access to the entity manager stored in the app
		manager = ((MainApplication) getApplication()).getManager();

		// Get the "ID" attribute value taken from the URI template /{ID}.
		configurationID = (String) getRequest().getAttributes().get("ID");

		logger.info(""+getAllowedMethods());

		// If no ID has been given, return a 404
		if (configurationID == null) {
			setStatus(Status.CLIENT_ERROR_NOT_FOUND);
			setExisting(false);
		}

		// TODO Check if the ID is valid, otherwise return a 404
		
		// TODO See how to implement content negotiation 	
		//setNegotiated(true);
		//getVariants().add(new Variant(MediaType.APPLICATION_XML));
		//getVariants().add(new Variant(MediaType.TEXT_XML));
		//getVariants().add(new Variant(MediaType.TEXT_HTML));
	}

}
