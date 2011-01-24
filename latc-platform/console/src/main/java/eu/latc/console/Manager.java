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

import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

/**
 * The manager interfaces with all the modifications performed to the
 * configuration files
 * 
 * @author cgueret
 * 
 * 
 */
public class Manager {
	// Logger instance
	protected final Logger logger = LoggerFactory
			.getLogger("LATC.Console.Manager");

	// Factory used to create the entity manager instances
	protected final PersistenceManagerFactory pmf = JDOHelper
			.getPersistenceManagerFactory("datanucleus.properties");

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
			Extent<LinkingConfiguration> e2 = pm.getExtent(
					LinkingConfiguration.class, true);
			Query q2 = pm.newQuery(e2, "");
			Collection<LinkingConfiguration> c2 = (Collection<LinkingConfiguration>) q2
					.execute();
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
	public LinkingConfiguration getConfiguration(String configurationID) {
		PersistenceManager pm = pmf.getPersistenceManager();
		Transaction tx = pm.currentTransaction();

		try {
			tx.begin();

			StringIdentity id = new StringIdentity(LinkingConfiguration.class,
					configurationID);
			LinkingConfiguration conf = (LinkingConfiguration) pm
					.getObjectById(id);
			LinkingConfiguration copy = (LinkingConfiguration) pm
					.detachCopy(conf);

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
	public String addConfiguration(String configuration) throws Exception {
		PersistenceManager pm = pmf.getPersistenceManager();
		Transaction tx = pm.currentTransaction();

		try {
			tx.begin();

			// Create the LinkingConfiguration and persist it
			LinkingConfiguration linkingConfiguration = new LinkingConfiguration();
			linkingConfiguration.setConfiguration(configuration);
			linkingConfiguration.setDescription("no description");
			linkingConfiguration.setTitle("no title");
			pm.makePersistent(linkingConfiguration);
			logger.info("Persisted configuration "
					+ linkingConfiguration.getIdentifier());

			// Append it to the processing queue
			Collection<LinkingConfiguration> queue = getProcessingQueue();
			queue.add(linkingConfiguration);
			setProcessingQueue(queue);

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
	public String addRunReport(String configurationID, RunReport report)
			throws Exception {
		PersistenceManager pm = pmf.getPersistenceManager();
		Transaction tx = pm.currentTransaction();

		try {
			tx.begin();

			// Check if the report is valid
			if (report == null)
				return null;

			// Get the configuration
			StringIdentity id = new StringIdentity(LinkingConfiguration.class,
					configurationID);
			LinkingConfiguration configuration = (LinkingConfiguration) pm
					.getObjectById(id);

			// Set the date to the report and save it
			Date now = new Date();
			report.setReportDate(now);
			report.setLinkingConfiguration(configuration);
			configuration.addReport(report);
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
	 * @return a sorted collection of LinkingConfiguration
	 */
	@SuppressWarnings("unchecked")
	public Collection<LinkingConfiguration> getProcessingQueue() {
		PersistenceManager pm = pmf.getPersistenceManager();
		Transaction tx = pm.currentTransaction();

		try {
			tx.begin();

			// Query for all the LinkingConfiguration files, sorted by position
			Query query = pm.newQuery(LinkingConfiguration.class);
			query.setOrdering("position ascending");

			// Create collection of detached instances of the
			// linkingconfiguration
			Collection<LinkingConfiguration> res = new ArrayList<LinkingConfiguration>();
			for (LinkingConfiguration conf : (Collection<LinkingConfiguration>) query
					.execute())
				res.add((LinkingConfiguration) pm.detachCopy(conf));

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
	@SuppressWarnings("unchecked")
	public Collection<RunReport> getReportsFor(String configurationID) {
		PersistenceManager pm = pmf.getPersistenceManager();
		Transaction tx = pm.currentTransaction();

		try {
			tx.begin();

			// Query for all the reports
			Query query = pm.newQuery(RunReport.class);
			query.setOrdering("reportDate ascending");
			Collection<RunReport> res = new ArrayList<RunReport>();
			for (RunReport report : (Collection<RunReport>) query.execute()) {
				if (report.getLinkingConfiguration().getIdentifier()
						.equals(configurationID)) {
					res.add((RunReport) pm.detachCopy(report));
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
	 * Set a new ordering for the processing queue. The queue indicated as a
	 * parameter is processed and all the positions of the LinkingConfiguration
	 * are updated accordingly
	 * 
	 * @param queue
	 *            An ordered list of LinkingConfiguration objects
	 */
	public void setProcessingQueue(Collection<LinkingConfiguration> queue) {
		PersistenceManager pm = pmf.getPersistenceManager();
		Transaction tx = pm.currentTransaction();

		try {
			tx.begin();

			// Update the positions
			long position = 1;
			for (LinkingConfiguration config : queue) {
				config.setPosition(position++);
				saveConfiguration(config);
			}
		} finally {
			if (tx.isActive())
				tx.rollback();
			pm.close();
		}

	}

	/**
	 * Erase a configuration file from the data base
	 * 
	 * @param configurationID
	 *            the identifier of the LinkingConfiguration to delete
	 */
	public void eraseConfiguration(String configurationID) {
		PersistenceManager pm = pmf.getPersistenceManager();
		Transaction tx = pm.currentTransaction();

		try {
			tx.begin();

			// Request the deletion
			StringIdentity id = new StringIdentity(LinkingConfiguration.class,
					configurationID);
			LinkingConfiguration conf = (LinkingConfiguration) pm
					.getObjectById(id);
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
	public void saveConfiguration(LinkingConfiguration config) {
		PersistenceManager pm = pmf.getPersistenceManager();
		Transaction tx = pm.currentTransaction();

		try {
			tx.begin();

			// Apply the changes
			pm.makePersistent(config);

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
