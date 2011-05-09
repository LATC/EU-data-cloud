package eu.latc.console;

import java.util.ArrayList;
import java.util.Collection;
import java.util.Date;

import javax.jdo.Extent;
import javax.jdo.JDOHelper;
import javax.jdo.PersistenceManager;
import javax.jdo.PersistenceManagerFactory;
import javax.jdo.Query;
import javax.jdo.Transaction;
import javax.jdo.identity.StringIdentity;

import org.json.JSONObject;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import eu.latc.console.objects.Notification;
import eu.latc.console.objects.Task;

/**
 * The manager interfaces with all the modifications performed to the
 * configuration files
 * 
 * @author cgueret
 * 
 * 
 */
public class ObjectManager {
	// Logger instance
	protected final Logger logger = LoggerFactory.getLogger("LATC.Console.Manager");

	// Factory used to create the entity manager instances
	protected final PersistenceManagerFactory pmf = JDOHelper.getPersistenceManagerFactory("datanucleus.properties");

	/*
	 * (non-Javadoc)
	 * 
	 * @see java.lang.Object#finalize()
	 */
	@Override
	public void finalize() {
		// Close the factory
		pmf.close();
	}

	/**
	 * Clear the content of the data base and recreate the necessary elements
	 * 
	 * @throws Exception
	 *             If something nasty happened during the clearing process
	 */
	@SuppressWarnings("unchecked")
	public void eraseAll() throws Exception {
		PersistenceManager pm = pmf.getPersistenceManager();
		Transaction tx = pm.currentTransaction();

		try {
			tx.begin();

			// Erase all linking configurations, by constraint on the foreign
			// key all the report will also go
			Extent<Task> e2 = pm.getExtent(Task.class, true);
			Query q2 = pm.newQuery(e2, "");
			Collection<Task> c2 = (Collection<Task>) q2.execute();
			pm.deletePersistentAll(c2);

			tx.commit();
		} finally {
			if (tx.isActive())
				tx.rollback();
			pm.close();
		}
	}

	/**
	 * @param configurationID
	 *            the identifier of the linking configuration file
	 * @return the linking configuration object associated to that identifier or
	 *         null if there is no matching object
	 */
	public Task getTask(String configurationID) {
		PersistenceManager pm = pmf.getPersistenceManager();
		Transaction tx = pm.currentTransaction();

		try {
			tx.begin();

			StringIdentity id = new StringIdentity(Task.class, configurationID);
			Task conf = (Task) pm.getObjectById(id);
			Task copy = (Task) pm.detachCopy(conf);

			return copy;
		} catch (Exception e) {
			e.printStackTrace();
			return null;
		} finally {
			if (tx.isActive())
				tx.rollback();
			pm.close();
		}
	}

	/**
	 * Adds a new configuration file to the base. By default, set it at the end
	 * of the processing queue
	 * 
	 * @param configuration
	 *            an XML configuration file for SILK
	 * @return the identifier associated to this configuration file
	 * @throws Exception
	 *             If it was not possible to add the configuration to the base
	 */
	// TODO Check for duplicates when a new content if proposed
	public String addTask(String configuration) throws Exception {
		PersistenceManager pm = pmf.getPersistenceManager();
		Transaction tx = pm.currentTransaction();

		try {
			tx.begin();

			// Create the LinkingConfiguration and persist it
			Task linkingConfiguration = new Task();
			linkingConfiguration.setConfiguration(configuration);
			linkingConfiguration.setDescription("no description");
			linkingConfiguration.setTitle("no title");
			pm.makePersistent(linkingConfiguration);
			logger.info("Persisted task " + linkingConfiguration.getIdentifier());

			// Apply
			tx.commit();

			return linkingConfiguration.getIdentifier();
		} finally {
			if (tx.isActive())
				tx.rollback();
			pm.close();
		}
	}

	/**
	 * @param configuration
	 * @param report
	 * @return
	 * @throws Exception
	 */
	public String addNotification(String taskID, Notification report) throws Exception {
		// Check if the report is valid
		if (report == null)
			return null;

		PersistenceManager pm = pmf.getPersistenceManager();
		Transaction tx = pm.currentTransaction();

		try {
			// Open a transaction
			tx.begin();

			// Get the configuration
			StringIdentity id = new StringIdentity(Task.class, taskID);
			Task task = (Task) pm.getObjectById(id);

			// Set the date to the report and bind it to the task
			Date now = new Date();
			report.setDate(now);
			report.setTask(task);
			task.addReport(report);

			// Check if the task should stay executable
			if (task.isExecutable()) {
				// If the report is a successful creation of triples, switch
				// off the execution flag
				if (!report.getData().equals("")) {
					JSONObject data = new JSONObject(report.getData());
					if (data.has("size") && data.getLong("size") > 0)
						task.setExecutable(false);
				}
			} else {
				// If the report is an update of the task, switch on the flag
				// FIXME String comparison is not robust
				if (report.getMessage().equals("Configuration modified"))
					task.setExecutable(true);
			}

			// Save the report
			pm.makePersistent(report);
			logger.info("Persisted report " + report.getIdentifier());

			// Apply
			tx.commit();

			return report.getIdentifier();
		} finally {
			if (tx.isActive())
				tx.rollback();
			pm.close();
		}
	}

	/**
	 * Return the processing queue as a list of LinkingConfiguration
	 * 
	 * @param limit
	 * @param filter
	 * 
	 * @return a sorted collection of LinkingConfiguration
	 */
	@SuppressWarnings("unchecked")
	public Collection<Task> getTasks(int limit, boolean filter) {
		PersistenceManager pm = pmf.getPersistenceManager();
		Transaction tx = pm.currentTransaction();

		try {
			tx.begin();

			// Query for all the LinkingConfiguration files, sorted by position
			Query query = pm.newQuery(Task.class);
			query.setOrdering("creationDate descending");

			// Create collection of detached instances of the
			Collection<Task> res = new ArrayList<Task>();
			for (Task task : (Collection<Task>) query.execute())
				if (limit == 0 || res.size() < limit)
					if (task.isExecutable() || !filter)
						res.add((Task) pm.detachCopy(task));

			return res;
		} finally {
			if (tx.isActive())
				tx.rollback();
			pm.close();
		}
	}

	/**
	 * @return
	 */
	// TODO Move this method to the Task object (if possible)
	@SuppressWarnings("unchecked")
	public Collection<Notification> getReportsFor(String configurationID) {
		PersistenceManager pm = pmf.getPersistenceManager();
		Transaction tx = pm.currentTransaction();

		try {
			tx.begin();

			// Query for all the reports
			Query query = pm.newQuery(Notification.class);
			query.setOrdering("date ascending");
			Collection<Notification> res = new ArrayList<Notification>();
			for (Notification report : (Collection<Notification>) query.execute())
				if (report.getTask().getIdentifier().equals(configurationID))
					res.add((Notification) pm.detachCopy(report));

			return res;
		} finally {
			if (tx.isActive())
				tx.rollback();
			pm.close();
		}
	}

	/**
	 * @param limit
	 *            the maximum number of reports to return, set to 0 for all of
	 *            them
	 * @return
	 */
	@SuppressWarnings("unchecked")
	public Collection<Notification> getReports(int limit) {
		PersistenceManager pm = pmf.getPersistenceManager();
		Transaction tx = pm.currentTransaction();

		try {
			tx.begin();

			// Query for all the reports
			Query query = pm.newQuery(Notification.class);
			query.setOrdering("date descending");

			// Compose the result set
			Collection<Notification> res = new ArrayList<Notification>();
			for (Notification report : (Collection<Notification>) query.execute()) {
				if (limit == 0 || res.size() < limit) {
					// FIXME Hack to get the title of the concerned task
					String title = report.getTask().getTitle();
					Notification reportCopy = (Notification) pm.detachCopy(report);
					reportCopy.setTaskTitle(title);
					res.add(reportCopy);
				}
			}

			return res;
		} finally {
			if (tx.isActive())
				tx.rollback();
			pm.close();
		}
	}

	/**
	 * Erase a configuration file from the data base
	 * 
	 * @param taskID
	 *            the identifier of the LinkingConfiguration to delete
	 */
	public void eraseTask(String taskID) {
		PersistenceManager pm = pmf.getPersistenceManager();
		Transaction tx = pm.currentTransaction();

		try {
			tx.begin();

			// Request the deletion
			StringIdentity id = new StringIdentity(Task.class, taskID);
			Task conf = (Task) pm.getObjectById(id);
			if (conf != null)
				pm.deletePersistent(conf);

			// Apply the changes
			tx.commit();
		} catch (Exception e) {
			e.printStackTrace();
		} finally {
			if (tx.isActive())
				tx.rollback();
			pm.close();
		}
	}

	/**
	 * Update a persisted, detached, instance
	 * 
	 * @param config
	 */
	public void saveTask(Task task) {
		// Update the last modification field
		task.setLastModificationDate(new Date());

		PersistenceManager pm = pmf.getPersistenceManager();
		Transaction tx = pm.currentTransaction();

		try {
			tx.begin();

			// Apply the changes
			pm.makePersistent(task);

			tx.commit();
		} catch (Exception e) {
			e.printStackTrace();
		} finally {
			if (tx.isActive())
				tx.rollback();
			pm.close();
		}
	}

}
