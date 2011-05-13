package eu.latc.console;

import org.restlet.Application;
import org.restlet.Restlet;
import org.restlet.routing.Router;

import eu.latc.console.resource.APIKeyResource;
import eu.latc.console.resource.NotificationsResource;
import eu.latc.console.resource.TaskResource;
import eu.latc.console.resource.TasksResource;
import eu.latc.console.resource.TaskNotificationsResource;
import eu.latc.console.resource.TaskConfigurationResource;

public class MainApplication extends Application {
	// Instance of the manager for configuration files
	private ObjectManager manager = new ObjectManager();

	/**
	 * Creates a root Restlet that will receive all incoming calls.
	 */
	@Override
	public Restlet createInboundRoot() {
		// Create a router
		Router router = new Router(getContext());

		// Handler for login
		// GET returns an API key matching a given login/password combination
		router.attach("/api_key", APIKeyResource.class);
		
		// Handler for the processing queue
		// GET returns the list of tasks
		// POST to create a new task
		router.attach("/tasks", TasksResource.class);

		// GET returns the list of all notifications
		router.attach("/notifications", NotificationsResource.class);

		// GET returns a bunch of statistics
		router.attach("/statistics", StatisticsResource.class);
		
		// Handler for the raw linking specification file
		// GET to get the raw XML linking configuration
		router.attach("/task/{ID}/configuration", TaskConfigurationResource.class);

		// Handler for the reports
		// GET to get a sorted list of reports
		// POST to this address to save a new report
		router.attach("/task/{ID}/notifications", TaskNotificationsResource.class);

		// Task resource
		// GET to get the description of the task
		// PUT to update the description of the task
		// DELETE to delete the task
		router.attach("/task/{ID}", TaskResource.class);

		// Activate content filtering based on extensions
		getTunnelService().setExtensionsTunnel(true);

		return router;
	}

	/**
	 * @return
	 */
	public ObjectManager getObjectManager() {
		return manager;
	}
}
