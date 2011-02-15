package eu.latc.console.resource;

import org.json.JSONArray;
import org.json.JSONObject;

import org.restlet.data.Form;
import org.restlet.data.MediaType;
import org.restlet.data.Status;
import org.restlet.ext.json.JsonConverter;
import org.restlet.representation.Representation;
import org.restlet.representation.StringRepresentation;
import org.restlet.resource.Get;
import org.restlet.resource.Post;
import org.restlet.resource.ServerResource;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import eu.latc.console.MainApplication;
import eu.latc.console.ObjectManager;
import eu.latc.console.objects.Notification;

/**
 * @author cgueret
 * 
 */
public class NotificationsResource extends ServerResource {
	// Logger instance
	protected final Logger logger = LoggerFactory.getLogger(NotificationsResource.class);

	/**
	 * Return the notifications about the task
	 * 
	 */
	@Get
	public Representation get() {
		try {
			logger.info("[GET] Asked for notifications");
			
			JSONArray array = new JSONArray();
			ObjectManager manager = ((MainApplication) getApplication()).getObjectManager();
			for (Notification report : manager.getReports()) 
				array.put(report.toJSON());
			
			JSONObject json = new JSONObject();
			json.put("notification", array);
			JsonConverter conv = new JsonConverter();
			logger.info(json.toString());
			return conv.toRepresentation(json, null, null);
		} catch (Exception e) {
			e.printStackTrace();

			// If anything goes wrong, just report back on an internal error
			setStatus(Status.SERVER_ERROR_INTERNAL);
			return null;
		}
	}
}
