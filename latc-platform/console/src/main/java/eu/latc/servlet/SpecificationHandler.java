package eu.latc.servlet;


import org.restlet.data.Form;
import org.restlet.data.MediaType;
import org.restlet.data.Status;
import org.restlet.ext.xml.DomRepresentation;
import org.restlet.representation.FileRepresentation;
import org.restlet.representation.Representation;
import org.restlet.representation.StringRepresentation;
import org.restlet.resource.Get;
import org.restlet.resource.Post;
import org.restlet.resource.Put;
import org.restlet.resource.ResourceException;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import eu.latc.console.LinkingConfiguration;

/**
 * @author cgueret
 * 
 */
public class SpecificationHandler extends BaseHandler {
	// Logger instance
	protected final Logger logger = LoggerFactory
			.getLogger(SpecificationHandler.class);

	/*
	 * (non-Javadoc)
	 * 
	 * @see eu.latc_project.servlet.BaseHandler#doInit()
	 */
	@Override
	protected void doInit() throws ResourceException {
		super.doInit();

		// This handler can not be used on "new"
		if (configurationID.equals(ID_NEW)) {
			setStatus(Status.CLIENT_ERROR_BAD_REQUEST);
			setExisting(false);
		}
	}

	/**
	 * Create a new configuration file
	 * 
	 * @param content
	 *            the configuration file content to put under the identifier
	 */
	@Post
	public Representation add(Form parameters) {
		// If that has been send to something different than ID_NEW, it's an
		// error
		if (!configurationID.equals(ID_NEW)) {
			setStatus(Status.CLIENT_ERROR_BAD_REQUEST);
			return new FileRepresentation("404.html", MediaType.TEXT_HTML);
		}

		logger.info("Received POST");
		logger.info(parameters.toString());

		// Get the configuration file to save
		String configuration = parameters.getFirstValue("configuration");
		String description = parameters.getFirstValue("description");
		if (configuration == null || description == null) {
			setStatus(Status.CLIENT_ERROR_BAD_REQUEST);
			return new FileRepresentation("404.html", MediaType.TEXT_HTML);
		}

		try {
			// Store the configuration file
			String confId = manager.addConfiguration(configuration);

			// Return the identifier
			setStatus(Status.SUCCESS_CREATED);
			return new StringRepresentation(confId, MediaType.TEXT_HTML);
		} catch (Exception e) {
			e.printStackTrace();

			// Something prevented the configuration file from being stored
			setStatus(Status.SERVER_ERROR_INTERNAL);
			return new FileRepresentation("404.html", MediaType.TEXT_HTML);
		}
	}

	/**
	 * Update an existing configuration file
	 * 
	 * @param parameters
	 *            the configuration file content to put under the identifier
	 */
	@Put
	public Representation update(Form parameters) {

		try {
			// Parse the identifier
			logger.info("[PUT] Update configuration file " + configurationID
					+ " with " + parameters.toString());

			// Get the configuration file to assign
			String text = parameters.getFirstValue("configuration");
			if (text == null) {
				setStatus(Status.CLIENT_ERROR_BAD_REQUEST);
				return null;
			}

			// Assign the new configuration and save it
			LinkingConfiguration config = manager.getConfiguration(configurationID);
			config.setConfiguration(text);
			manager.saveConfiguration(config);

			setStatus(Status.SUCCESS_OK);
			return new StringRepresentation("updated", MediaType.TEXT_HTML);
		} catch (Exception e) {
			e.printStackTrace();
			
			// If anything goes wrong, just report back on an internal error
			setStatus(Status.SERVER_ERROR_INTERNAL);
			return null;
		}
	}

	/**
	 * Return a the XML linking specification associated to the identifier
	 * 
	 */
	@Get
	public Representation get() {
		try {
			// A specific configuration file has been asked
			logger.info("[GET] Return the XML linking configuration of "
					+ configurationID);

			LinkingConfiguration conf = manager
					.getConfiguration(configurationID);
			if (conf == null) {
				setStatus(Status.CLIENT_ERROR_NOT_FOUND);
				return new FileRepresentation("404.html", MediaType.TEXT_HTML);
			}

			return new DomRepresentation(MediaType.TEXT_XML, conf.getDocument());
		} catch (Exception e) {
			e.printStackTrace();

			// If anything goes wrong, just report back on an internal error
			setStatus(Status.SERVER_ERROR_INTERNAL);
			return null;
		}
	}

}
