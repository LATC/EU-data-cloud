package eu.latc.console.resource;

import org.json.JSONObject;
import org.restlet.data.Form;
import org.restlet.data.MediaType;
import org.restlet.data.Status;
import org.restlet.ext.json.JsonConverter;
import org.restlet.representation.FileRepresentation;
import org.restlet.representation.Representation;
import org.restlet.representation.StringRepresentation;
import org.restlet.resource.Delete;
import org.restlet.resource.Get;
import org.restlet.resource.Put;
import org.restlet.resource.ResourceException;
import org.restlet.resource.ServerResource;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import eu.latc.console.MainApplication;
import eu.latc.console.ObjectManager;
import eu.latc.console.objects.Task;

/**
 * The default handler is attached to <api_path>/<id> If the id is "new" and the
 * request a POST, then a new configuration file is created Otherwise, the call
 * is re-directed to <api_path>/<id>/about
 * 
 * @author cgueret
 * 
 */
public class TaskResource extends ServerResource {
	// Logger instance
	protected final Logger logger = LoggerFactory.getLogger(TaskResource.class);

	// The ID of the task
	protected String taskID;

	// The task requested
	protected Task task;

	/*
	 * (non-Javadoc)
	 * 
	 * @see org.restlet.resource.UniformResource#doInit()
	 */
	@Override
	protected void doInit() throws ResourceException {
		// Get the "ID" attribute value taken from the URI template /{ID}.
		taskID = (String) getRequest().getAttributes().get("ID");

		// If no ID has been given, return a 404
		if (taskID == null) {
			setStatus(Status.CLIENT_ERROR_NOT_FOUND);
			setExisting(false);
		}

		// Try to get the task
		ObjectManager manager = ((MainApplication) getApplication()).getObjectManager();
		task = manager.getTask(taskID);
		if (task == null) {
			setStatus(Status.CLIENT_ERROR_NOT_FOUND);
			setExisting(false);
		}

		// TODO See how to implement content negotiation
		// setNegotiated(true);
		// getVariants().add(new Variant(MediaType.APPLICATION_XML));
		// getVariants().add(new Variant(MediaType.TEXT_XML));
		// getVariants().add(new Variant(MediaType.TEXT_HTML));
	}

	/**
	 * Delete a configuration file
	 */
	@Delete
	public Representation remove(Form parameters) {
		// Check credentials
		if (parameters.getFirstValue("api_key", true) == null || !parameters.getFirstValue("api_key", true).equals(APIKeyResource.KEY)) {
			setStatus(Status.CLIENT_ERROR_FORBIDDEN);
			return null;
		}

		try {
			ObjectManager manager = ((MainApplication) getApplication()).getObjectManager();

			// Try to get the configuration file associated to the ID
			manager.eraseTask(taskID);

			return new StringRepresentation("deleted configuration file", MediaType.TEXT_HTML);

		} catch (Exception e) {
			e.printStackTrace();

			// If anything goes wrong, just report back on an internal error
			setStatus(Status.SERVER_ERROR_INTERNAL);
			return new FileRepresentation("404.html", MediaType.TEXT_HTML);
		}
	}

	/**
	 * Return information about the linking specification
	 * 
	 */
	@Get
	public Representation getInformation() {
		try {
			logger.info("[GET] Asked about " + taskID);

			// Get an object manager
			ObjectManager manager = ((MainApplication) getApplication()).getObjectManager();

			// Get the task
			Task task = manager.getTask(taskID);

			// Prepare the answer
			JSONObject entry = new JSONObject();
			entry.put("title", task.getTitle());
			entry.put("description", task.getDescription());
			entry.put("author", task.getAuthor());
			entry.put("identifier", task.getIdentifier());
			JsonConverter conv = new JsonConverter();
			logger.info("Answer " + entry.toString());

			return conv.toRepresentation(entry, null, null);
		} catch (Exception e) {
			e.printStackTrace();

			// If anything goes wrong, just report back on an internal error
			setStatus(Status.SERVER_ERROR_INTERNAL);
			return null;
		}
	}

	/**
	 * @return
	 */
	@Put
	public Representation updateInformation(Form parameters) {
		logger.info("[PUT] Details for " + taskID + " with " + parameters);
		
		// Check credentials
		if (parameters.getFirstValue("api_key", true) == null || !parameters.getFirstValue("api_key", true).equals(APIKeyResource.KEY)) {
			setStatus(Status.CLIENT_ERROR_FORBIDDEN);
			return null;
		}

		// Update
		task.setTitle(parameters.getFirstValue("title"));
		task.setDescription(parameters.getFirstValue("description"));
		task.setAuthor(parameters.getFirstValue("author"));
		ObjectManager manager = ((MainApplication) getApplication()).getObjectManager();
		manager.saveTask(task);
		
		setStatus(Status.SUCCESS_OK);
		return null;
	}
}
