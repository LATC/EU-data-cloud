package eu.latc.console.resource;

import org.json.JSONArray;
import org.json.JSONObject;

import org.restlet.data.Form;
import org.restlet.data.Status;
import org.restlet.ext.atom.Entry;
import org.restlet.ext.atom.Feed;
import org.restlet.ext.atom.Text;
import org.restlet.ext.json.JsonConverter;
import org.restlet.representation.Representation;
import org.restlet.resource.Get;
import org.restlet.resource.ResourceException;
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
	@Get("json")
	public Representation toJSON() {
		// Handle the limit parameter
		int limit = 0;
		Form params = getReference().getQueryAsForm();
		if (params.getFirstValue("limit", true) != null)
			limit = Integer.parseInt(params.getFirstValue("limit", true));

		try {
			logger.info("[GET-JSON] Asked for notifications");

			JSONArray array = new JSONArray();
			ObjectManager manager = ((MainApplication) getApplication()).getObjectManager();
			for (Notification report : manager.getNotifications(limit)) {
				JSONObject data = report.toJSON();
				data.put("title", report.getTaskTitle());
				array.put(data);
			}

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

	/**
	 * @return
	 * @throws ResourceException
	 */
	@Get("atom")
	public Feed toAtom() throws ResourceException {
		logger.info("[GET-ATOM] Asked for notifications");

		try {
			// Get access to the entity manager stored in the app
			ObjectManager manager = ((MainApplication) getApplication()).getObjectManager();
			Feed result = new Feed();
			result.setTitle(new Text("LATC latest notifications"));
			Entry entry;

			for (Notification report : manager.getNotifications(5)) {
				entry = new Entry();
				entry.setTitle(new Text("(" + report.getSeverity() + ")" + report.getMessage()));
				StringBuffer summary = new StringBuffer();
				summary.append("Task:" + report.getTaskTitle()).append("\n");
				summary.append("Date:" + report.getDate()).append("\n");
				summary.append("Extra:" + report.getData()).append("\n");
				entry.setSummary(summary.toString());
				result.getEntries().add(entry);
			}
			return result;
		} catch (Exception e) {
			e.printStackTrace();

			// If anything goes wrong, just report back on an internal error
			setStatus(Status.SERVER_ERROR_INTERNAL);
			return null;
		}
	}

}
