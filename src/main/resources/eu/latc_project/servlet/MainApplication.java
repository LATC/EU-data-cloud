package eu.latc_project.servlet;

import org.restlet.Application;
import org.restlet.Restlet;
import org.restlet.routing.Router;

import eu.latc_project.console.CallsHandler;
import eu.latc_project.console.Manager;

public class MainApplication extends Application {
	// Instance of the manager for configuration files
	private Manager manager = new Manager();

	/**
	 * Creates a root Restlet that will receive all incoming calls.
	 */
	@Override
	public Restlet createInboundRoot() {
		// Create a router and attach the handlers
		Router router = new Router(getContext());
		router.attach("/configuration/{ID}", CallsHandler.class);
		return router;
	}

	/**
	 * @return
	 */
	public Manager getManager() {
		return manager;
	}
}
