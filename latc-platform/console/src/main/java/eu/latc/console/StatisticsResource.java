/**
 * 
 */
package eu.latc.console;

import java.text.SimpleDateFormat;
import java.util.Calendar;
import java.util.Date;

import org.json.JSONArray;
import org.json.JSONObject;
import org.restlet.data.Form;
import org.restlet.data.Status;
import org.restlet.ext.json.JsonConverter;
import org.restlet.representation.Representation;
import org.restlet.resource.Get;
import org.restlet.resource.ServerResource;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import eu.latc.console.objects.Notification;
import eu.latc.console.objects.Task;
import eu.latc.console.resource.TasksResource;

/**
 * @author Christophe Guéret <christophe.gueret@gmail.com>
 * 
 */
public class StatisticsResource extends ServerResource {
	// Logger instance
	protected final Logger logger = LoggerFactory.getLogger(StatisticsResource.class);

	/**
	 * Return the list of tasks
	 */
	@Get("json")
	public Representation toJSON() {
		logger.info("[GET-JSON] Return statistics");

		try {
			// Get access to the entity manager stored in the app
			ObjectManager manager = ((MainApplication) getApplication()).getObjectManager();

			// Go through all the notifications to get stats for the latest run
			int lastYear = 1900;
			int lastDay = 1;
			String runDate = "";
			long links = 0;
			SimpleDateFormat sdf = new SimpleDateFormat("dd/MM/yyyy");
			for (Notification notification : manager.getNotifications(0)) {
				Calendar cal = Calendar.getInstance();
				cal.setTime(notification.getDate());
				
				// If we find a more recent report, reset the counters
				if (cal.get(Calendar.DAY_OF_YEAR) > lastDay && cal.get(Calendar.YEAR) > lastYear) {
					lastDay = cal.get(Calendar.DAY_OF_YEAR);
					lastYear = cal.get(Calendar.YEAR);
					runDate = sdf.format(notification.getDate());
					links = 0;
				}
				
				// If that notification corresponds to our current aggregator, count it
				if (cal.get(Calendar.DAY_OF_YEAR) == lastDay && cal.get(Calendar.YEAR) == lastYear) {
					if (!notification.getData().equals("")) {
						JSONObject data = new JSONObject(notification.getData());
						if (data.has("size") && data.getLong("size") > 0)
							links += data.getLong("size");
						
						if (data.has("executetime"))
							links += data.getLong("size");
					}
				}
				
			}

			// The object requested is the list of configuration files
			JSONObject json = new JSONObject();
			JSONArray array = new JSONArray();
			for (Task task : manager.getTasks(limit, filter))
				array.put(task.toJSON());
			json.put("task", array);

			JsonConverter conv = new JsonConverter();
			return conv.toRepresentation(json, null, null);
		} catch (Exception e) {
			e.printStackTrace();

			// If anything goes wrong, just report back on an internal error
			setStatus(Status.SERVER_ERROR_INTERNAL);
			return null;
		}
	}

}
