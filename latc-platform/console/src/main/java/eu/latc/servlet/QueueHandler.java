package eu.latc.servlet;

import java.util.ArrayList;
import java.util.Collection;

import org.json.JSONArray;
import org.json.JSONObject;
import org.restlet.data.Form;
import org.restlet.data.MediaType;
import org.restlet.data.Status;
import org.restlet.ext.json.JsonConverter;
import org.restlet.representation.Representation;
import org.restlet.representation.StringRepresentation;
import org.restlet.resource.Get;
import org.restlet.resource.Put;
import org.restlet.resource.ServerResource;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import eu.latc.console.LinkingConfiguration;
import eu.latc.console.Manager;

public class QueueHandler extends ServerResource {
	// Logger instance
	protected final Logger logger = LoggerFactory
			.getLogger(SpecificationHandler.class);

	/**
	 * Update the processing queue
	 * 
	 * @param parameters
	 *            the new processing queue, as a collection of identifiers
	 */
	@Put
	public Representation update(Form parameters) {
		// Get access to the entity manager stored in the app
		Manager manager = ((MainApplication) getApplication()).getManager();

		if (parameters == null) {
			setStatus(Status.SERVER_ERROR_INTERNAL);
			return null;
		}

		try {
			logger.info("[PUT] Update the processing queue with "
					+ parameters.getValues("item[]"));

			// Generate the new queue and set it
			Collection<LinkingConfiguration> queue = new ArrayList<LinkingConfiguration>();
			for (String id : parameters.getValues("item[]").split(","))
				queue.add(manager.getConfiguration(id));
			manager.setProcessingQueue(queue);

			setStatus(Status.SUCCESS_OK);
			return new StringRepresentation("Queue updated",
					MediaType.TEXT_HTML);

		} catch (Exception e) {
			e.printStackTrace();

			// If anything goes wrong, just report back on an internal error
			setStatus(Status.SERVER_ERROR_INTERNAL);
			return null;
		}
	}

	/**
	 * Return an ordered list of configuration files identifiers
	 * 
	 */
	@Get
	public Representation get() {
		// Get access to the entity manager stored in the app
		Manager manager = ((MainApplication) getApplication()).getManager();

		try {
			// The object requested is the list of configuration files
			logger.info("[GET] Return the processing queue");
			JSONObject json = new JSONObject();
			JSONArray array = new JSONArray();
			for (LinkingConfiguration conf : manager.getProcessingQueue()) {
				JSONObject entry = new JSONObject();
				logger.info(conf.getIdentifier() + " / " + conf.getTitle());
				entry.put("identifier", conf.getIdentifier());
				entry.put("title", conf.getTitle());
				array.put(entry);
			}
			json.put("queue", array);
			
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
