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
		//logger.info(this.getRequest().toString());
		//logger.info(this.getQuery().toString());
		//logger.info(this.getRequestEntity().toString());
	//logger.info(this.getMethod().toString());
		// logger.info(this.getRequestAttributes().toString());
		getVariants().add(new Variant(MediaType.MULTIPART_FORM_DATA));
		getVariants().add(new Variant(MediaType.MULTIPART_ALL));
		// logger.info(this.getVariants().toString());
	}

	/**
	 * @param data
	 * @return
	 * @throws Exception
	 */
	@Post("multipart/form-data")
	public Representation add(Representation entity) throws Exception {
		logger.info("[POST] Received a new task " + entity);

		if ((entity == null) || (!MediaType.MULTIPART_FORM_DATA.equals(entity.getMediaType(), true))) {
			setStatus(Status.CLIENT_ERROR_BAD_REQUEST);
			logger.info("got " + entity.getMediaType());
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
		Task task = manager.getTask(taskID);
		task.setTitle(fileItem.getName());
		task.setDescription("No description");
		task.setCreationDate(new Date());
		manager.saveTask(task);

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
		// Handle the limit parameter
		int limit = 0;
		Form params = getReference().getQueryAsForm();
		if (params.getFirstValue("limit",true) != null)
			limit = Integer.parseInt(params.getFirstValue("limit",true));
		
		logger.info("[GET-JSON] Return a list of tasks "+ limit);
		
		try {
			// Get access to the entity manager stored in the app
			ObjectManager manager = ((MainApplication) getApplication()).getObjectManager();

			// The object requested is the list of configuration files
			JSONObject json = new JSONObject();
			JSONArray array = new JSONArray();
			for (Task task : manager.getTasks(limit)) {
				JSONObject entry = new JSONObject();
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

			for (Task task : manager.getTasks(5)) {
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
