package eu.latc.console.objects;

import java.io.Serializable;
import java.util.Date;

import javax.jdo.annotations.Column;
import javax.jdo.annotations.DatastoreIdentity;
import javax.jdo.annotations.IdGeneratorStrategy;
import javax.jdo.annotations.IdentityType;
import javax.jdo.annotations.NotPersistent;
import javax.jdo.annotations.PersistenceCapable;
import javax.jdo.annotations.Persistent;
import javax.jdo.annotations.PrimaryKey;

import org.json.JSONException;
import org.json.JSONObject;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import eu.latc.misc.DateToXSDateTime;

/**
 * @author cgueret
 * 
 */
@PersistenceCapable(detachable = "true", identityType = IdentityType.APPLICATION)
@DatastoreIdentity(strategy = IdGeneratorStrategy.UUIDHEX)
@PrimaryKey(name = "identifier")
public class Notification implements Serializable {
	// Serial
	private static final long serialVersionUID = -8373420245200091065L;

	// Logger instance
	protected static final Logger logger = LoggerFactory.getLogger(Notification.class);

	// The identifier for this report
	@PrimaryKey
	@Persistent(valueStrategy = IdGeneratorStrategy.UUIDHEX)
	@Column(jdbcType = "VARCHAR", length = 32)
	private String identifier = null;

	// The status message
	@Persistent
	private String message = null;

	// Severity of the log
	@Persistent
	private String severity = null;

	// Date and time
	@Persistent
	private Date date = null;

	// The task this notification relates to
	@Persistent
	private Task task;

	// Data (extra information related to this notification)
	@Persistent
	private String data = null;

	@NotPersistent
	private String taskTitle = null;

	/**
	 * @param taskTitle
	 */
	public void setTaskTitle(String taskTitle) {
		this.taskTitle = taskTitle;
	}

	/**
	 * @return
	 */
	public String getTaskTitle() {
		return taskTitle;
	}

	/**
	 * @return
	 */
	public String getIdentifier() {
		return identifier;
	}

	/**
	 * @param message
	 */
	public void setMessage(String message) {
		this.message = message;
	}

	/**
	 * @return
	 */
	public String getMessage() {
		return message;
	}

	/**
	 * @param date
	 */
	public void setDate(Date date) {
		this.date = date;
	}

	/**
	 * @return
	 */
	public Date getDate() {
		return date;
	}

	/**
	 * @param task
	 */
	public void setTask(Task task) {
		this.task = task;
	}

	/**
	 * @return
	 */
	public Task getTask() {
		return task;
	}

	/**
	 * @param data
	 */
	public void setData(String data) {
		this.data = data;
	}

	/**
	 * @return
	 */
	public String getData() {
		if (data == null)
			data = (new JSONObject()).toString();
		return data;
	}

	/**
	 * @param severity
	 */
	public void setSeverity(String severity) {
		this.severity = severity;
	}

	/**
	 * @return
	 */
	public String getSeverity() {
		if (severity == null)
			severity = "info";
		return severity;
	}

	/**
	 * @return
	 * @throws JSONException
	 */
	public JSONObject toJSON() throws JSONException {
		JSONObject entry = new JSONObject();
		entry.put("identifier", identifier);
		// FIXME hack
		entry.put("severity", (severity == null ? "info" : severity));
		entry.put("message", message);
		entry.put("date", DateToXSDateTime.format(date));
		entry.put("data", data);
		return entry;
	}
}
