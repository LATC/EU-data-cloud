/**
 * 
 */
package eu.latc.console;

import java.text.SimpleDateFormat;
import java.util.Calendar;

import org.json.JSONObject;
import org.restlet.data.Status;
import org.restlet.ext.json.JsonConverter;
import org.restlet.representation.Representation;
import org.restlet.resource.Get;
import org.restlet.resource.ServerResource;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import eu.latc.console.objects.Notification;

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
			long runtime = 0;
			int executed = 0;
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
					executed = 0;
					runtime = 0;
				}

				// If that notification corresponds to our current aggregator,
				// count it
				if (cal.get(Calendar.DAY_OF_YEAR) == lastDay && cal.get(Calendar.YEAR) == lastYear) {
					if (!notification.getData().equals("")) {
						JSONObject data = new JSONObject(notification.getData());
						if (data.has("size") && data.has("executetime")) {
							links += data.getLong("size");
							executed++;
							String[] s = data.getString("executetime").split(":");
							runtime += Integer.parseInt(s[0]) * 24 * 60 * 60;
							runtime += Integer.parseInt(s[1]) * 60 * 60;
							runtime += Integer.parseInt(s[2]) * 60;
							runtime += Integer.parseInt(s[3]);
						}
					}
				}
			}

			// The object requested is the list of configuration files
			JSONObject json = new JSONObject();
			json.put("queue_size", manager.getTasks(0, true).size());
			json.put("tasks_size", manager.getTasks(0, false).size());
			json.put("last_run_size", links);
			json.put("last_run_time", runtime);
			json.put("last_run_date", runDate);
			json.put("last_executed", executed);
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
