package eu.latc.console;

import java.io.IOException;
import java.io.Serializable;
import java.io.StringReader;
import java.util.ArrayList;
import java.util.Collection;

import javax.jdo.annotations.Column;
import javax.jdo.annotations.DatastoreIdentity;
import javax.jdo.annotations.Element;
import javax.jdo.annotations.IdGeneratorStrategy;
import javax.jdo.annotations.IdentityType;
import javax.jdo.annotations.NotPersistent;
import javax.jdo.annotations.PersistenceCapable;
import javax.jdo.annotations.Persistent;
import javax.jdo.annotations.PrimaryKey;
import javax.xml.parsers.DocumentBuilder;
import javax.xml.parsers.DocumentBuilderFactory;
import javax.xml.parsers.ParserConfigurationException;

import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.w3c.dom.Document;
import org.xml.sax.InputSource;
import org.xml.sax.SAXException;

/**
 * @author cgueret
 * 
 */
@PersistenceCapable(detachable = "true", identityType = IdentityType.APPLICATION)
@DatastoreIdentity(strategy = IdGeneratorStrategy.UUIDHEX)
@PrimaryKey(name = "identifier")
public class LinkingConfiguration implements Serializable {
	// Serialization ID
	private static final long serialVersionUID = -8292316878407319874L;

	// Logger instance
	protected static final Logger logger = LoggerFactory
			.getLogger(LinkingConfiguration.class);

	// The identifier for this configuration file
	@PrimaryKey
	@Persistent(valueStrategy = IdGeneratorStrategy.UUIDHEX)
	@Column(name="CONFIGURATION_ID",jdbcType = "VARCHAR", length = 32)
	private String identifier;

	// A title
	@Persistent
	@Column(jdbcType = "VARCHAR", length = 125)
	private String title = "";

	// A short description of what this configuration does
	@Persistent
	@Column(jdbcType = "VARCHAR", length = 1000)
	private String description = "";

	// The configuration file in its text serialized form
	@Persistent
	@Column(jdbcType = "VARCHAR", length = 20000)
	private String configuration = "";

	// The position in the processing queue
	@Persistent
	private long position = 0;

	// Collection of reports
	@Persistent
	@Element(types=RunReport.class, column="CONFIGURATION_ID", dependent="true", mappedBy="linkingConfiguration")
	private Collection<RunReport> reports = new ArrayList<RunReport>();

	// The configuration file, as an XML document
	@NotPersistent
	private Document document = null;

	/**
	 * Get the description of the configuration file
	 * 
	 * @return
	 */
	public String getDescription() {
		if (description == null)
			return "No description";
		return description;
	}

	/**
	 * Set the description of the configuration file
	 * 
	 * @param description
	 * @return
	 */
	public void setDescription(String description) {
		this.description = description;
	}

	/**
	 * @return
	 */
	public String getConfiguration() {
		return configuration;
	}

	/**
	 * Assign a new configuration file to the LinkingConfiguration object
	 * 
	 * @param configuration
	 *            The configuration file expressed in the XML format used by
	 *            SiLK
	 * @throws Exception
	 *             If <code>configuration</code> is null of if it is not a
	 *             proper XML file
	 * 
	 */
	public void setConfiguration(String configuration) throws Exception {
		// Die if the parameter is equal to null
		if (configuration == null)
			throw new Exception();

		// Try to parse the new document to see if it's valid
		Document d = parseLinkingConfiguration(configuration);
		if (d == null)
			throw new Exception();

		// Set the new configuration file
		this.configuration = configuration;
	}

	/**
	 * Return the XML document of the configuration file stored for this object
	 * 
	 * @return an XML document or <code>null</code> of the current configuration
	 *         file is not valid (should never happen, this is checked at
	 *         assignment time)
	 */
	public Document getDocument() {
		// If the document has not been parsed yet, do it now
		if (this.document == null) {
			try {
				this.document = parseLinkingConfiguration(this.configuration);
			} catch (Exception e) {
				e.printStackTrace();
			}
		}

		return this.document;
	}

	/**
	 * @param linkingConfiguration
	 * @return
	 * @throws Exception
	 */
	private Document parseLinkingConfiguration(String linkingConfiguration) {
		Document doc = null;
		try {
			DocumentBuilderFactory factory = DocumentBuilderFactory
					.newInstance();
			DocumentBuilder builder = factory.newDocumentBuilder();
			StringReader reader = new StringReader(linkingConfiguration);
			InputSource inputSource = new InputSource(reader);
			doc = builder.parse(inputSource);
			reader.close();
		} catch (ParserConfigurationException e) {
			e.printStackTrace();
		} catch (SAXException e) {
			e.printStackTrace();
		} catch (IOException e) {
			e.printStackTrace();
		}
		return doc;
	}

	/**
	 * @return
	 */
	public String getIdentifier() {
		return identifier;
	}

	/**
	 * @param title
	 */
	public void setTitle(String title) {
		this.title = title;
	}

	/**
	 * @return
	 */
	public String getTitle() {
		return title;
	}

	/**
	 * @param position
	 */
	public void setPosition(long position) {
		this.position = position;
	}

	/**
	 * @return
	 */
	public long getPosition() {
		return position;
	}

	/**
	 * @param report
	 */
	public void addReport(RunReport report) {
		reports.add(report);
	}

	/**
	 * @return
	 */
	public Collection<RunReport> getReports() {
		return reports;
	}
}

// Serialize the document object into a string
/*
 * try { TransformerFactory transfac = TransformerFactory.newInstance();
 * transfac.setAttribute("indent-number", 4); Transformer t =
 * transfac.newTransformer();
 * t.setOutputProperty(OutputKeys.OMIT_XML_DECLARATION, "no");
 * t.setOutputProperty(OutputKeys.INDENT, "yes");
 * t.setOutputProperty(OutputKeys.METHOD, "xml");
 * 
 * // create string from xml tree StringWriter sw = new StringWriter();
 * t.transform(new DOMSource(configuration), new StreamResult(sw));
 * this.configuration = sw.getBuffer().toString();
 * System.out.println(this.configuration); } catch (TransformerException e) {
 * this.configuration = ""; e.printStackTrace(); }
 */
