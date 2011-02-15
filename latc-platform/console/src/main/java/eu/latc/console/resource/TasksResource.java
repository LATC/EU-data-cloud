package eu.latc.console.resource;

import java.io.File;
import java.util.Iterator;
import java.util.List;

import org.apache.commons.fileupload.FileItem;
import org.apache.commons.fileupload.disk.DiskFileItemFactory;
import org.json.JSONArray;
import org.json.JSONObject;
import org.restlet.data.MediaType;
import org.restlet.data.Status;
import org.restlet.ext.fileupload.RestletFileUpload;
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
import eu.latc.console.objects.Task;

public class TasksResource extends ServerResource {
	// Logger instance
	protected final Logger logger = LoggerFactory.getLogger(TasksResource.class);

	/**
	 * @param data
	 * @return
	 * @throws Exception
	 */
	@Post("multipart")
	public Representation add(Representation entity) throws Exception {
		logger.info("received " + entity);

		if ((entity == null) || (!MediaType.MULTIPART_FORM_DATA.equals(entity.getMediaType(), true))) {
			setStatus(Status.CLIENT_ERROR_BAD_REQUEST);
			return null;
		}

		// Create a tmp file
		String fileName = File.createTempFile("latc", "tmp").getAbsolutePath();

		// Create a factory for disk-based file items
		DiskFileItemFactory factory = new DiskFileItemFactory();
		factory.setSizeThreshold(1000240);

		// Parse the entity elements
		RestletFileUpload upload = new RestletFileUpload(factory);
		List<FileItem> items;
		items = upload.parseRepresentation(entity);

		// Process only the uploaded item called "fileToUpload" and
		// save it on disk
		FileItem fileItem = null;
		for (final Iterator<FileItem> it = items.iterator(); it.hasNext() && fileItem == null;) {
			FileItem fi = it.next();
			if (fi.getFieldName().equals("fileToUpload")) {
				fileItem = fi;
				File file = new File(fileName);
				fi.write(file);
			}
		}

		// Something went wrong
		if (fileItem == null) {
			setStatus(Status.CLIENT_ERROR_BAD_REQUEST);
			return null;
		}

		// Get the entity manager
		ObjectManager manager = ((MainApplication) getApplication()).getObjectManager();

		// Save the configuration file
		String taskID = manager.addConfiguration(fileItem.getString());

		// Set the title
		Task linkingConfiguration = manager.getTask(taskID);
		linkingConfiguration.setTitle(fileItem.getName());
		linkingConfiguration.setDescription("No description");
		manager.saveTask(linkingConfiguration);

		// Add an initial upload report
		Notification report = new Notification();
		report.setMessage("Task created");
		manager.addNotification(taskID, report);

		// Set the return code and return the identifier
		setStatus(Status.SUCCESS_CREATED);
		/*
		 * JSONObject json = new JSONObject(); json.put("id", configurationID);
		 * json.put("href", getReference() + "/../" + configurationID +
		 * "/specification"); JsonConverter conv = new JsonConverter();
		 * logger.info(json.toString()); return conv.toRepresentation(json,
		 * null, null);
		 */
		StringBuilder sb = new StringBuilder("<html><body>");
		sb.append("<script>document.location=\"" + getReference() + "/../../../\"</script>");
		sb.append("</body></html>");
		return new StringRepresentation(sb.toString(), MediaType.TEXT_HTML);
	}
	
	/**
	 * Return the list of tasks
	 */
	@Get
	public Representation get() {
		// Get access to the entity manager stored in the app
		ObjectManager manager = ((MainApplication) getApplication()).getObjectManager();

		try {
			// The object requested is the list of configuration files
			logger.info("[GET] Return a list of tasks");
			JSONObject json = new JSONObject();
			JSONArray array = new JSONArray();
			for (Task task : manager.getTasks()) {
				JSONObject entry = new JSONObject();
				logger.info(task.getIdentifier() + " / " + task.getTitle());
				entry.put("identifier", task.getIdentifier());
				entry.put("title", task.getTitle());
				entry.put("description", task.getDescription());
				array.put(entry);
			}
			json.put("task", array);

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
