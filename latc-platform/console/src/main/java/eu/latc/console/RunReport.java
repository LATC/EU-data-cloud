package eu.latc_project.console;

import java.io.Serializable;
import java.util.Date;

import javax.jdo.annotations.Column;
import javax.jdo.annotations.DatastoreIdentity;
import javax.jdo.annotations.IdGeneratorStrategy;
import javax.jdo.annotations.IdentityType;
import javax.jdo.annotations.PersistenceCapable;
import javax.jdo.annotations.Persistent;
import javax.jdo.annotations.PrimaryKey;

import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

/**
 * @author cgueret
 * 
 */
@PersistenceCapable(detachable = "true", identityType = IdentityType.APPLICATION)
@DatastoreIdentity(strategy = IdGeneratorStrategy.UUIDHEX)
@PrimaryKey(name = "identifier")
public class RunReport implements Serializable {
	// Serial
	private static final long serialVersionUID = -8373420245200091065L;

	// Logger instance
	protected static final Logger logger = LoggerFactory
			.getLogger(RunReport.class);

	// The identifier for this report
	@PrimaryKey
	@Persistent(valueStrategy = IdGeneratorStrategy.UUIDHEX)
	@Column(jdbcType = "VARCHAR", length = 32)
	private String identifier = null;

	// The URI for the mappings generated
	@Persistent
	private String resultsLocation = null;

	// The number of the mappings generated
	@Persistent
	private long resultsSize = 0;

	// The status message
	@Persistent
	private String statusMessage = null;

	// Date/time when that report was reported
	@Persistent
	private Date reportDate = null;
	
	// The linking configuration this report is about
	@Persistent
	private LinkingConfiguration linkingConfiguration;

	/**
	 * @return
	 */
	public String getIdentifier() {
		return identifier;
	}

	/**
	 * @param resultsLocation
	 */
	public void setResultsLocation(String resultsLocation) {
		this.resultsLocation = resultsLocation;
	}

	/**
	 * @return
	 */
	public String getResultsLocation() {
		return resultsLocation;
	}

	/**
	 * @param statusMessage
	 */
	public void setStatusMessage(String statusMessage) {
		this.statusMessage = statusMessage;
	}

	/**
	 * @return
	 */
	public String getStatusMessage() {
		return statusMessage;
	}

	/**
	 * @param reportDate
	 */
	public void setReportDate(Date reportDate) {
		this.reportDate = reportDate;
	}

	/**
	 * @return
	 */
	public Date getReportDate() {
		return reportDate;
	}

	/**
	 * @param resultsSize
	 */
	public void setResultsSize(long resultsSize) {
		this.resultsSize = resultsSize;
	}

	/**
	 * @return
	 */
	public long getResultsSize() {
		return resultsSize;
	}

	public void setLinkingConfiguration(LinkingConfiguration linkingConfiguration) {
		this.linkingConfiguration = linkingConfiguration;
	}

	public LinkingConfiguration getLinkingConfiguration() {
		return linkingConfiguration;
	}
}
