package eu.latc.servlet;

import org.json.JSONObject;

import org.restlet.data.Status;
import org.restlet.ext.json.JsonConverter;
import org.restlet.representation.Representation;
import org.restlet.resource.Get;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import eu.latc.console.LinkingConfiguration;

/**
 * @author cgueret
 * 
 */
public class AboutHandler extends BaseHandler {
	// Logger instance
	protected final Logger logger = LoggerFactory.getLogger(AboutHandler.class);

	/**
	 * Return information about the linking specification
	 * 
	 */
	@Get
	public Representation getInformation() {
		try {
			logger.info("[GET] Asked about " + configurationID);
			LinkingConfiguration conf = manager.getConfiguration(configurationID);
			JSONObject entry = new JSONObject();
			entry.put("title", conf.getTitle());
			entry.put("description", conf.getDescription());
			entry.put("identifier", conf.getIdentifier());
			entry.put("position", conf.getPosition());
			JsonConverter conv = new JsonConverter();
			logger.info(entry.toString());
			return conv.toRepresentation(entry, null, null);
		} catch (Exception e) {
			e.printStackTrace();

			// If anything goes wrong, just report back on an internal error
			setStatus(Status.SERVER_ERROR_INTERNAL);
			return null;
		}
	}
}
