package eu.latc.console.resource;

import org.json.JSONArray;
import org.json.JSONObject;

import org.restlet.data.Form;
import org.restlet.data.MediaType;
import org.restlet.data.Status;
import org.restlet.ext.atom.Entry;
import org.restlet.ext.atom.Feed;
import org.restlet.ext.atom.Text;
import org.restlet.ext.json.JsonConverter;
import org.restlet.representation.Representation;
import org.restlet.representation.StringRepresentation;
import org.restlet.resource.Get;
import org.restlet.resource.Post;
import org.restlet.resource.ResourceException;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import eu.latc.console.MainApplication;
import eu.latc.console.ObjectManager;
import eu.latc.console.objects.Notification;

/**
 * @author cgueret
 * 
 */
public class TaskNotificationsResource extends TaskResource {
	// Logger instance
	protected final Logger logger = LoggerFactory.getLogger(TaskNotificationsResource.class);

	/**
	 * Return the notifications about the task
	 * 
	 */
	@Get("json")
	public Representation toJSON() {
		try {
			logger.info("[GET-JSON] Asked notifications for " + taskID);
			
			JSONArray array = new JSONArray();
			ObjectManager manager = ((MainApplication) getApplication()).getObjectManager();
			for (Notification report : manager.getReportsFor(taskID))
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

			for (Notification report : manager.getReportsFor(taskID)) {
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
	
	/**
	 * Adds a new report for this linking configuration
	 * 
	 */
	@Post
	public Representation add(Form form) {
		try {
			logger.info("[POST] Add notification " + form.toString() + " for " + taskID);

			// Add a report for the insertion
			Notification notification = new Notification();
			notification.setMessage(form.getFirstValue("message", true));
			if (notification.getMessage() == null) {
				setStatus(Status.CLIENT_ERROR_BAD_REQUEST);
				return null;
			}
			notification.setData(form.getFirstValue("data", true));
			if (notification.getData() == null) {
				setStatus(Status.CLIENT_ERROR_BAD_REQUEST);
				return null;
			}
			notification.setSeverity(form.getFirstValue("severity", true));
			if (notification.getSeverity() == null)
				notification.setSeverity("info");

			ObjectManager manager = ((MainApplication) getApplication()).getObjectManager();
			manager.addNotification(taskID, notification);

			setStatus(Status.SUCCESS_OK);
			return new StringRepresentation("Report added", MediaType.TEXT_HTML);
		} catch (Exception e) {
			e.printStackTrace();

			// If anything goes wrong, just report back on an internal error
			setStatus(Status.SERVER_ERROR_INTERNAL);
			return null;
		}
	}
}
