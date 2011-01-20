package eu.latc.servlet;

import java.io.File;
import java.util.Iterator;
import java.util.List;

import org.apache.commons.fileupload.FileItem;
import org.apache.commons.fileupload.disk.DiskFileItemFactory;
import org.restlet.data.MediaType;
import org.restlet.data.Status;
import org.restlet.ext.fileupload.RestletFileUpload;
import org.restlet.representation.Representation;
import org.restlet.representation.StringRepresentation;
import org.restlet.resource.Get;
import org.restlet.resource.Post;
import org.restlet.resource.ResourceException;
import org.restlet.resource.ServerResource;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import eu.latc.console.LinkingConfiguration;
import eu.latc.console.Manager;
import eu.latc.console.RunReport;

/**
 * @author cgueret
 * @see http://wiki.restlet.org/docs_2.0/42-restlet.html
 */
public class UploadHandler extends ServerResource {
	// Logger instance
	final Logger logger = LoggerFactory.getLogger(UploadHandler.class);

	/*
	 * (non-Javadoc)
	 * 
	 * @see org.restlet.resource.UniformResource#doInit()
	 */
	@Override
	protected void doInit() throws ResourceException {
		getMetadataService().addExtension("multipart",
				MediaType.MULTIPART_FORM_DATA, true);
	}

	/**
	 * @return
	 */
	@Get("html")
	public String browse() {
		StringBuilder sb = new StringBuilder("<html><body>");
		sb.append("<form method=\"post\" ");
		sb.append("action=\"");
		sb.append(getReference());
		sb.append("\" ");
		sb.append(" enctype=\"multipart/form-data\">");
		sb.append("<input name=\"fileToUpload\" type=\"file\"/>");
		sb.append("<input type=\"submit\"/>");
		sb.append("</form>");
		sb.append("</body></html>");
		return sb.toString();
	}

	/**
	 * @param data
	 * @return
	 * @throws Exception
	 */
	@Post("multipart")
	public Representation add(Representation entity) throws Exception {
		if ((entity == null)
				|| (!MediaType.MULTIPART_FORM_DATA.equals(
						entity.getMediaType(), true))) {
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
		for (final Iterator<FileItem> it = items.iterator(); it.hasNext()
				&& fileItem == null;) {
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
		Manager manager = ((MainApplication) getApplication()).getManager();

		// Save the configuration file
		String configurationID = manager.addConfiguration(fileItem.getString());

		// Set the title
		LinkingConfiguration linkingConfiguration = manager
				.getConfiguration(configurationID);
		linkingConfiguration.setTitle(fileItem.getName());
		linkingConfiguration.setDescription("No description");
		manager.saveConfiguration(linkingConfiguration);

		// Add an initial upload report
		RunReport report = new RunReport();
		report.setStatusMessage("File uploaded");
		report.setResultsLocation("http://latc-project.eu");
		manager.addRunReport(configurationID, report);

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
		sb.append("<script>document.location=\"" + getReference()
				+ "/../../../\"</script>");
		sb.append("</body></html>");
		return new StringRepresentation(sb.toString(), MediaType.TEXT_HTML);
	}
}
