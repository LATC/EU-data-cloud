package eu.latc_project.servlet;

import org.restlet.Application;
import org.restlet.Restlet;
import org.restlet.routing.Router;

import eu.latc_project.console.Manager;

public class MainApplication extends Application {
	// Instance of the manager for configuration files
	private Manager manager = new Manager();

	/**
	 * Creates a root Restlet that will receive all incoming calls.
	 */
	@Override
	public Restlet createInboundRoot() {
		// Create a router
		Router router = new Router(getContext());

		// Handler for the processing queue
		// GET returns the ordered list of configuration IDs
		router.attach("/queue", QueueHandler.class);

		// Submit a new configuration
		// POST on this address an XML silk linking configuration
		router.attach("/configuration/new", UploadHandler.class);

		// Handler for the raw linking specification file
		// GET to get the raw XML linking configuration
		router.attach("/configuration/{ID}/specification",
				SpecificationHandler.class);

		// Handler for the reports
		// GET to get a sorted list of reports
		// POST to this address to save a new report
		router.attach("/configuration/{ID}/reports", ReportsHandler.class);

		// Handler for all the meta information
		// GET to get some information
		router.attach("/configuration/{ID}/about", AboutHandler.class);

		// Default handler
		// DELETE to remove a configuration file
		router.attach("/configuration/{ID}", DefaultHandler.class);

		return router;
	}

	/**
	 * @return
	 */
	public Manager getManager() {
		return manager;
	}
}
