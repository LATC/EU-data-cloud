package eu.latc.console.resource;

import java.io.File;
import java.util.Date;
import java.util.HashSet;
import java.util.Iterator;
import java.util.List;
import java.util.Set;

import org.apache.commons.fileupload.FileItem;
import org.apache.commons.fileupload.disk.DiskFileItemFactory;
import org.json.JSONArray;
import org.json.JSONObject;
import org.restlet.data.Form;
import org.restlet.data.MediaType;
import org.restlet.data.Method;
import org.restlet.data.Status;
import org.restlet.ext.atom.Entry;
import org.restlet.ext.atom.Feed;
import org.restlet.ext.atom.Text;
import org.restlet.ext.fileupload.RestletFileUpload;
import org.restlet.ext.json.JsonConverter;
import org.restlet.representation.Representation;
import org.restlet.representation.StringRepresentation;
import org.restlet.representation.Variant;
import org.restlet.resource.Get;
import org.restlet.resource.Post;
import org.restlet.resource.ResourceException;
import org.restlet.resource.ServerResource;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import eu.latc.console.MainApplication;
import eu.latc.console.ObjectManager;
import eu.latc.console.objects.Notification;
import eu.latc.console.objects.Task;

public class TasksResource extends ServerResource {
	// Logger instance
	protected final Logger logger = LoggerFactory.getLogger(TasksResource.class);

	/*
	 * (non-Javadoc)
	 * 
	 * @see org.restlet.resource.UniformResource#doInit()
	 */
	@Override
	protected void doInit() throws ResourceException {
		Set<Method> methods = new HashSet<Method>();
		methods.add(Method.ALL);
		this.setAllowedMethods(methods);
		// logger.info(this.getRequest().toString());
		// logger.info(this.getQuery().toString());
		// logger.info(this.getRequestEntity().toString());
		// logger.info(this.getMethod().toString());
		// logger.info(this.getRequestAttributes().toString());
		getVariants().add(new Variant(MediaType.MULTIPART_FORM_DATA));
		getVariants().add(new Variant(MediaType.MULTIPART_ALL));
		// logger.info(this.getVariants().toString());
		logger.info("" + this.getRequest().getChallengeResponse());
		logger.info("" + this.getRequest().getAttributes());
		logger.info("" + getReference().getQueryAsForm());
	}

	/**
	 * @param data
	 * @return
	 * @throws Exception
	 */
	@Post("multipart/form-data")
	public Representation add(Representation multipartForm) throws Exception {
		logger.info("[POST] Received a new task " + multipartForm);

		// Check if the form is valid
		if ((multipartForm == null) || (!MediaType.MULTIPART_FORM_DATA.equals(multipartForm.getMediaType(), true))) {
			setStatus(Status.CLIENT_ERROR_BAD_REQUEST);
			logger.info("got " + multipartForm.getMediaType());
			return null;
		}

		// Create a factory for disk-based file items
		DiskFileItemFactory factory = new DiskFileItemFactory();
		factory.setSizeThreshold(1000240);

		// Parse the entity elements
		RestletFileUpload upload = new RestletFileUpload(factory);
		List<FileItem> items = upload.parseRepresentation(multipartForm);

		// Process the content of the form
		String specification = null;
		String title = null;
		String description = null;
		String author = null;
		for (FileItem item : items) {
			if (!item.isFormField() && item.getFieldName().equals("specification"))
				specification = item.getString();
			if (item.isFormField() && item.getFieldName().equals("title"))
				title = item.getString();
			if (item.isFormField() && item.getFieldName().equals("description"))
				description = item.getString();
			if (item.isFormField() && item.getFieldName().equals("author"))
				author = item.getString();
		}

		// We need to have at least a specification to save
		if (specification == null) {
			setStatus(Status.CLIENT_ERROR_BAD_REQUEST);
			return null;
		}

		// Get the entity manager
		ObjectManager manager = ((MainApplication) getApplication()).getObjectManager();

		// Save the configuration file
		String taskID = manager.addTask(specification);

		// Set the title
		Task task = manager.getTask(taskID);
		task.setTitle(title == null ? "No title" : title);
		task.setDescription(description == null ? "No description" : description);
		task.setAuthor(author == null ? "Unknown" : author);
		task.setCreationDate(new Date());
		task.setExecutable(true);
		manager.saveTask(task);

		// Add an initial upload report
		Notification report = new Notification();
		report.setMessage("Task created");
		report.setSeverity("info");
		report.setData("");
		manager.addNotification(taskID, report);

		// Set the return code and return the identifier
		setStatus(Status.SUCCESS_CREATED);

		JSONObject json = new JSONObject();
		json.put("id", taskID);
		json.put("href", getReference() + "/" + taskID);
		logger.info("[POST] Reply " + json);
		JsonConverter conv = new JsonConverter();
		return conv.toRepresentation(json, null, null);
	}

	/**
	 * Handler for suffix based content negociation
	 * 
	 * @param variant
	 * @return
	 */
	@Get("json|atom")
	public Representation toSomething(Variant variant) {
		if (variant.getMediaType().equals(MediaType.APPLICATION_ATOM))
			return toAtom();
		return toJSON();
	}

	/**
	 * Return the list of tasks
	 */
	@Get("json")
	public Representation toJSON() {
		Form params = getReference().getQueryAsForm();
		
		// Handle the "limit" parameter
		int limit = 0;
		if (params.getFirstValue("limit", true) != null)
			limit = Integer.parseInt(params.getFirstValue("limit", true));

		// Handle the "all" parameter
		boolean filter = true;
		if (params.getFirstValue("filter", true) != null)
			filter = Boolean.parseBoolean(params.getFirstValue("filter", true));
		
		logger.info("[GET-JSON] Return a list of tasks " + limit);

		try {
			// Get access to the entity manager stored in the app
			ObjectManager manager = ((MainApplication) getApplication()).getObjectManager();

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

	/**
	 * @param tasks
	 * @return
	 * @throws ResourceException
	 */
	@Get("atom")
	public Feed toAtom() throws ResourceException {
		logger.info("[GET-ATOM] Return a list of tasks");

		try {
			// Get access to the entity manager stored in the app
			ObjectManager manager = ((MainApplication) getApplication()).getObjectManager();
			Feed result = new Feed();
			result.setTitle(new Text("Tasks created for LATC"));
			Entry entry;

			for (Task task : manager.getTasks(5, false)) {
				entry = new Entry();
				entry.setTitle(new Text(task.getTitle()));
				StringBuffer summary = new StringBuffer();
				summary.append("Description: " + task.getDescription()).append('\n');
				summary.append("Creation date:" + task.getCreationDate()).append('\n');
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
