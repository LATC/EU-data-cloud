package eu.latc_project.console;

import org.json.JSONObject;
import org.restlet.data.Form;
import org.restlet.data.MediaType;
import org.restlet.data.Status;
import org.restlet.ext.json.JsonConverter;
import org.restlet.ext.xml.DomRepresentation;
import org.restlet.representation.FileRepresentation;
import org.restlet.representation.Representation;
import org.restlet.representation.StringRepresentation;
import org.restlet.resource.Delete;
import org.restlet.resource.Get;
import org.restlet.resource.Post;
import org.restlet.resource.Put;
import org.restlet.resource.ResourceException;
import org.restlet.resource.ServerResource;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import eu.latc_project.servlet.MainApplication;

// http://www.2048bits.com/2008/06/creating-simple-web-service-with.html

public class CallsHandler extends ServerResource {
	// Logger instance
	protected final Logger logger = LoggerFactory.getLogger(CallsHandler.class);

	// Reserved IDs
	private final static String ID_NEW = "new";
	private final static String ID_LIST = "list";

	// The UUID of the configuration file
	private String configurationID;

	// The entity manager used to deal with the configuration files
	private Manager manager;

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

		// If no ID has been given, return a 404
		if (configurationID == null) {
			setStatus(Status.CLIENT_ERROR_NOT_FOUND);
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
	public Representation add(Representation content) {
		// If that has been send to something different than ID_NEW, it's an
		// error
		if (!configurationID.equals(ID_NEW)) {
			setStatus(Status.CLIENT_ERROR_BAD_REQUEST);
			return new FileRepresentation("404.html", MediaType.TEXT_HTML);
		}

		// Get the configuration file to save
		Form parameters = new Form(content);
		String configuration = parameters.getFirstValue("configuration");
		if (configuration == null) {
			setStatus(Status.CLIENT_ERROR_BAD_REQUEST);
			return new FileRepresentation("404.html", MediaType.TEXT_HTML);
		}

		logger.info("Received POST");

		try {
			// Store the configuration file
			long id = manager.addConfiguration(configuration);

			// Return the identifier
			setStatus(Status.SUCCESS_CREATED);
			return new StringRepresentation(Long.toString(id),
					MediaType.TEXT_HTML);
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
	 * @param content
	 *            the configuration file content to put under the identifier
	 */
	@Put
	public Representation update(Form parameters) {
		// Not allowed on new
		if (configurationID.equals(ID_NEW)) {
			setStatus(Status.CLIENT_ERROR_BAD_REQUEST);
			return new FileRepresentation("404.html", MediaType.TEXT_HTML);
		}

		try {
			// The list of configuration files is updated
			if (configurationID.equals(ID_LIST)) {
				logger.info("Received a PUT on the list");
				logger.info(parameters.toString());
				String ordering = parameters.getValues("item[]");
				manager.setQueue(Manager.ACTIVE, ordering);

				setStatus(Status.SUCCESS_OK);
				return new StringRepresentation("updated", MediaType.TEXT_HTML);
			}

			// A configuration file is updated
			else {
				// Parse the identifier
				int id = Integer.parseInt(configurationID);
				logger.info("Received a PUT for configuration file " + id);

				// Get the configuration file to assign
				String configuration = parameters
						.getFirstValue("configuration");
				String description = parameters.getFirstValue("description");
				if (configuration == null || description == null) {
					setStatus(Status.CLIENT_ERROR_BAD_REQUEST);
					return new FileRepresentation("404.html",
							MediaType.TEXT_HTML);
				}

				// Assign the new configuration file
				manager.updateConfiguration(id, "", configuration);

				setStatus(Status.SUCCESS_OK);
				return new StringRepresentation("update", MediaType.TEXT_HTML);
			}
		} catch (Exception e) {
			e.printStackTrace();
			// If anything goes wrong, just report back on an internal error
			setStatus(Status.SERVER_ERROR_INTERNAL);
			return new FileRepresentation("404.html", MediaType.TEXT_HTML);
		}
	}

	/**
	 * Return a configuration file or the list of configurations
	 * 
	 */
	@Get
	public Representation getResource() {
		try {
			// The object requested is the list of configuration files
			if (configurationID.equals(ID_LIST)) {
				logger.info("Received a GET on " + ID_LIST);
				JSONObject json = new JSONObject();
				for (LinkingConfiguration conf : manager
						.getQueue(Manager.ACTIVE)) {
					JSONObject entry = new JSONObject();
					entry.put("identifier", conf.getIdentifier());
					entry.put("description", conf.getDescription());
					json.accumulate("queue", entry);
				}
				JsonConverter conv = new JsonConverter();
				logger.info(json.toString());
				return conv.toRepresentation(json, null, null);
			}

			// A specific configuration file has been asked
			else {
				int id = Integer.parseInt(configurationID);

				LinkingConfiguration conf = manager.getConfiguration(id);
				if (conf != null) {
					return new DomRepresentation(MediaType.TEXT_XML,
							conf.getDocument());
				} else {
					setStatus(Status.CLIENT_ERROR_NOT_FOUND);
					// return new FileRepresentation("404.html",
					// MediaType.TEXT_HTML);
					return new FileRepresentation("404.html",
							MediaType.TEXT_HTML);
				}
			}
		} catch (Exception e) {
			e.printStackTrace();
			// If anything goes wrong, just report back on an internal error
			setStatus(Status.SERVER_ERROR_INTERNAL);
			return null;
		}
	}

	/**
	 * Delete a configuration file
	 */
	@Delete
	public Representation remove() {
		// Can not delete any of the reserved configurationID
		if (configurationID.equals(ID_NEW) || configurationID.equals(ID_LIST)) {
			setStatus(Status.CLIENT_ERROR_BAD_REQUEST);
			return new FileRepresentation("404.html", MediaType.TEXT_HTML);
		}

		try {
			// Try to get the configuration file associated to the ID
			long id = Integer.parseInt(configurationID);
			return new StringRepresentation("delete configuration file",
					MediaType.TEXT_HTML);

		} catch (Exception e) {
			// If anything goes wrong, just report back on an internal error
			setStatus(Status.SERVER_ERROR_INTERNAL);
			return new FileRepresentation("404.html", MediaType.TEXT_HTML);
		}
	}
}
