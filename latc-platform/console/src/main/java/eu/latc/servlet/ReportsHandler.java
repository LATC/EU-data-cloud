package eu.latc_project.servlet;

import org.json.JSONArray;
import org.json.JSONObject;

import org.restlet.data.Form;
import org.restlet.data.MediaType;
import org.restlet.data.Status;
import org.restlet.ext.json.JsonConverter;
import org.restlet.representation.Representation;
import org.restlet.representation.StringRepresentation;
import org.restlet.resource.Get;
import org.restlet.resource.Post;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import eu.latc_project.console.RunReport;

/**
 * @author cgueret
 * 
 */
public class ReportsHandler extends BaseHandler {
	// Logger instance
	protected final Logger logger = LoggerFactory.getLogger(ReportsHandler.class);

	/**
	 * Return information about the linking specification
	 * 
	 */
	@Get
	public Representation get() {
		try {
			logger.info("[GET] Asked reports for " + configurationID);
			JSONObject json = new JSONObject();
			JSONArray array = new JSONArray();
			for (RunReport report: manager.getReportsFor(configurationID)) {
				JSONObject entry = new JSONObject();
				entry.put("identifier", report.getIdentifier());
				entry.put("status", report.getStatusMessage());
				entry.put("date", report.getReportDate());
				entry.put("size", report.getResultsSize());
				entry.put("location", report.getResultsLocation());
				array.put(entry);
			}
			json.put("report", array);
			
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
	 * Adds a new report for this linking configuration
	 * 
	 */
	@Post
	public Representation add(Form form) {
		try {
			logger.info("[POST] Add report " + form.toString() + " for " + configurationID);
			
			// Add a report for the insertion
			RunReport report = new RunReport();
			report.setStatusMessage(form.getFirstValue("status", true));
			report.setResultsLocation(form.getFirstValue("location", true));
			report.setResultsSize(Long.parseLong(form.getFirstValue("size", true)));
			manager.addRunReport(configurationID, report);
			
			setStatus(Status.SUCCESS_OK);
			return new StringRepresentation("Report added",
					MediaType.TEXT_HTML);
		} catch (Exception e) {
			e.printStackTrace();

			// If anything goes wrong, just report back on an internal error
			setStatus(Status.SERVER_ERROR_INTERNAL);
			return null;
		}
	}
}
