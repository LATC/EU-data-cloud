package eu.latc.servlet;

import org.restlet.data.MediaType;
import org.restlet.data.Status;
import org.restlet.representation.FileRepresentation;
import org.restlet.representation.Representation;
import org.restlet.representation.StringRepresentation;
import org.restlet.resource.Delete;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

/**
 * The default handler is attached to <api_path>/<id> If the id is "new" and the
 * request a POST, then a new configuration file is created Otherwise, the call
 * is re-directed to <api_path>/<id>/about
 * 
 * @author cgueret
 * 
 */
public class DefaultHandler extends BaseHandler {
	// Logger instance
	protected final Logger logger = LoggerFactory
			.getLogger(DefaultHandler.class);

	/**
	 * Delete a configuration file
	 */
	@Delete
	public Representation remove() {
		// Can not delete any of the reserved configurationID
		if (configurationID.equals(ID_NEW)) {
			setStatus(Status.CLIENT_ERROR_BAD_REQUEST);
			return null;
		}

		try {
			// Try to get the configuration file associated to the ID
			manager.eraseConfiguration(configurationID);

			return new StringRepresentation("deleted configuration file",
					MediaType.TEXT_HTML);

		} catch (Exception e) {
			e.printStackTrace();

			// If anything goes wrong, just report back on an internal error
			setStatus(Status.SERVER_ERROR_INTERNAL);
			return new FileRepresentation("404.html", MediaType.TEXT_HTML);
		}
	}

}
