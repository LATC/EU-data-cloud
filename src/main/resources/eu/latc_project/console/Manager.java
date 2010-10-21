package eu.latc_project.console;

import java.util.ArrayList;
import java.util.Collection;

import javax.jdo.Extent;
import javax.jdo.JDOHelper;
import javax.jdo.PersistenceManager;
import javax.jdo.PersistenceManagerFactory;
import javax.jdo.Query;
import javax.jdo.Transaction;

import org.datanucleus.identity.OID;
import org.datanucleus.identity.OIDImpl;
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
	// Reserved identifier to distinguish that list
	protected static final String ACTIVE = "active";
	protected static final String DISABLED = "disabled";

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

			// Erase all running queue
			Extent<RunningQueue> e = pm.getExtent(RunningQueue.class, true);
			Query q = pm.newQuery(e, "");
			Collection<RunningQueue> c = (Collection<RunningQueue>) q.execute();
			pm.deletePersistentAll(c);

			// Erase all linking configurations
			Extent<LinkingConfiguration> e2 = pm.getExtent(
					LinkingConfiguration.class, true);
			Query q2 = pm.newQuery(e2, "");
			Collection<LinkingConfiguration> c2 = (Collection<LinkingConfiguration>) q2
					.execute();
			pm.deletePersistentAll(c2);

			// Create a new empty ACTIVE and DISABLED configuration file sets
			RunningQueue queue = new RunningQueue();
			queue.setName(ACTIVE);
			pm.makePersistent(queue);
			logger.info("Created queue: " + queue.getName());
			RunningQueue queue2 = new RunningQueue();
			queue2.setName(DISABLED);
			pm.makePersistent(queue2);
			logger.info("Created queue: " + queue2.getName());

			tx.commit();
		} finally {
			if (tx.isActive()) {
				tx.rollback();
			}
			pm.close();
		}
	}

	/**
	 * @param configurationID
	 *            the identifier of the linking configuration file
	 * @return the linking configuration object associated to that identifier or
	 *         null if there is no matching object
	 */
	public LinkingConfiguration getConfiguration(long configurationID) {
		PersistenceManager pm = pmf.getPersistenceManager();
		Transaction tx = pm.currentTransaction();
		try {
			tx.begin();
			OID id = new OIDImpl(
					"eu.latc_project.console.LinkingConfiguration", new Long(
							configurationID));
			LinkingConfiguration conf = (LinkingConfiguration) pm
					.getObjectById(id);
			LinkingConfiguration copy = (LinkingConfiguration) pm
					.detachCopy(conf);
			copy.setIdentifier(configurationID);
			return copy;
		} catch (Exception e) {
			return null;
		} finally {
			if (tx.isActive()) {
				tx.rollback();
			}
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
	@SuppressWarnings("unchecked")
	public long addConfiguration(String configuration) throws Exception {
		PersistenceManager pm = pmf.getPersistenceManager();
		Transaction tx = pm.currentTransaction();
		try {
			tx.begin();

			// Create the configuration file LinkingConfiguration
			LinkingConfiguration linkingConfiguration = new LinkingConfiguration();
			linkingConfiguration.setConfiguration(configuration);

			// Add it to the database (equivalent to an INSERT)
			pm.makePersistent(linkingConfiguration);
			OID id = (OID) pm.getObjectId(linkingConfiguration);
			long key = (Long) id.getKeyValue();
			logger.info("Persisted " + key);

			// Append it to the end of the ACTIVE batch queue
			// RunningQueue c = (RunningQueue) pm.getObjectById(ACTIVE);
			Extent<RunningQueue> e = pm.getExtent(RunningQueue.class, true);
			Query q = pm.newQuery(e, "name == '" + ACTIVE + "'");
			Collection<RunningQueue> c = (Collection<RunningQueue>) q.execute();
			RunningQueue queue = (RunningQueue) c.toArray()[0];
			logger.info("fdfsd " + queue);
			queue.appendLinkingConfiguration(key);
			logger.info("Updated " + queue.getName());
			tx.commit();

			return key;
		} finally {
			if (tx.isActive()) {
				tx.rollback();
			}
			pm.close();
		}
	}

	/**
	 * Modify the description and the linking configuration of a stored
	 * configuration
	 * 
	 * @param id
	 * @param configuration
	 * @return true If the configuration has been updated
	 */
	public boolean updateConfiguration(long id, String description,
			String configuration) {
		PersistenceManager pm = pmf.getPersistenceManager();
		Transaction tx = pm.currentTransaction();
		try {
			tx.begin();
			OID oid = new OIDImpl(
					"eu.latc_project.console.LinkingConfiguration",
					new Long(id));
			LinkingConfiguration conf = (LinkingConfiguration) pm
					.getObjectById(oid);
			if (conf != null) {
				conf.setDescription(description);
				conf.setConfiguration(configuration);
			}
			tx.commit();
			return true;
		} catch (Exception e) {
			e.printStackTrace();
			return false;
		} finally {
			if (tx.isActive()) {
				tx.rollback();
			}
			pm.close();
		}
	}

	/**
	 * Test the existence of a particular identifier in the base of linking
	 * configurations
	 * 
	 * @param id
	 *            the identifier to look for
	 * @return true only if a configuration file is associated to the id
	 *         <code>id</code>
	 */
	public boolean contains(int id) {
		/*
		 * EntityManager em = null; try { em = emf.createEntityManager();
		 * 
		 * // Find the linking configuration concerned LinkingConfiguration
		 * linkingConfiguration = em.find( LinkingConfiguration.class, id);
		 * return (linkingConfiguration != null); } catch (Exception e) {
		 * e.printStackTrace(); if (em != null &&
		 * em.getTransaction().isActive()) em.getTransaction().rollback();
		 * return false; } finally { if (em != null) em.close(); }
		 */
		return false;
	}

	/**
	 * Return a safe copy of the running queue
	 * 
	 * @param name
	 *            the name of the queue
	 * @return a collection of LinkingConfiguration instances
	 */
	@SuppressWarnings("unchecked")
	public Collection<LinkingConfiguration> getQueue(String name) {
		PersistenceManager pm = pmf.getPersistenceManager();
		Transaction tx = pm.currentTransaction();

		try {
			tx.begin();

			// Get the content of the running queue
			Extent<RunningQueue> e = pm.getExtent(RunningQueue.class, true);
			Query q = pm.newQuery(e, "name == '" + name + "'");
			Collection<RunningQueue> c = (Collection<RunningQueue>) q.execute();
			RunningQueue queue = (RunningQueue) c.toArray()[0];

			// Populate and return a list of linking configuration instances
			Collection<LinkingConfiguration> result = new ArrayList<LinkingConfiguration>();
			for (Long configurationID : queue.getLinkingConfigurations())
				result.add(getConfiguration(configurationID));
			return result;
		} finally {
			if (tx.isActive()) {
				tx.rollback();
			}
			pm.close();
		}
	}

	/**
	 * @param name
	 * @param ordering
	 */
	@SuppressWarnings("unchecked")
	public void setQueue(String name, String ordering) {
		PersistenceManager pm = pmf.getPersistenceManager();
		Transaction tx = pm.currentTransaction();
		try {
			tx.begin();
			Extent<RunningQueue> e = pm.getExtent(RunningQueue.class, true);
			Query q = pm.newQuery(e, "name == '" + name + "'");
			Collection<RunningQueue> c = (Collection<RunningQueue>) q.execute();
			RunningQueue queue = (RunningQueue) c.toArray()[0];
			queue.setLinkingConfigurations(ordering);
			tx.commit();
		} finally {
			if (tx.isActive()) {
				tx.rollback();
			}
			pm.close();
		}

	}
}
