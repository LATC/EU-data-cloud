package eu.latc_project.console;

import java.io.Serializable;
import java.util.ArrayList;
import java.util.Collection;

import org.datanucleus.util.StringUtils;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

public class RunningQueue implements Serializable {
	// Serialization ID
	protected static final long serialVersionUID = -2596631579122818480L;

	// Logger instance
	protected static final Logger logger = LoggerFactory
			.getLogger(RunningQueue.class);

	// The name of this running queue
	protected String name = "";

	// Comma separated list of LinkingConfiguration files identifiers
	protected String linkingConfigurations = "";

	/**
	 * @param active
	 */
	public void setName(String name) {
		this.name = name;
	}

	/**
	 * @return
	 */
	public String getName() {
		return name;
	}

	/**
	 * @param linkingConfiguration
	 */
	public Collection<Long> getLinkingConfigurations() {
		Collection<Long> list = new ArrayList<Long>();
		for (String id : StringUtils.split(linkingConfigurations, ","))
			list.add(Long.decode(id));
		return list;
	}

	/**
	 * @param linkingConfigurationsIds
	 */
	public void setLinkingConfigurations(String ordering) {
		this.linkingConfigurations = ordering;
	}

	/**
	 * @param linkingConfiguration
	 */
	public void appendLinkingConfiguration(long key) {
		if (linkingConfigurations.length() > 0)
			linkingConfigurations += ",";
		linkingConfigurations += key;
		logger.info("New set : " + linkingConfigurations);
	}
}
