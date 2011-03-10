/**
 * 
 */
package eu.latc.console.resource;

import java.security.NoSuchAlgorithmException;

import org.json.JSONException;
import org.json.JSONObject;
import org.restlet.data.Form;
import org.restlet.data.Status;
import org.restlet.ext.json.JsonConverter;
import org.restlet.representation.Representation;
import org.restlet.resource.Post;
import org.restlet.resource.ServerResource;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

/**
 * @author Christophe Guéret <christophe.gueret@gmail.com>
 * 
 */
public class APIKeyResource extends ServerResource {
	// Logger instance
	protected final Logger logger = LoggerFactory.getLogger(APIKeyResource.class);
	
	private final static String LOGIN = "admin";
	private final static String PASSWORD = "LATCPassw0rD";
	public final static String KEY = "aa4967eb8b7a5ccab7dbb57aa2368c7f";

	/**
	 * Return the API Key
	 * 
	 * @param form
	 * @return
	 * @throws NoSuchAlgorithmException
	 * @throws JSONException
	 */
	@Post
	public Representation get_key(Form form) throws NoSuchAlgorithmException, JSONException {
		// Get login and password or fail
		String user = form.getFirstValue("username", true);
		String password = form.getFirstValue("password", true);
		if (user == null || password == null) {
			setStatus(Status.CLIENT_ERROR_BAD_REQUEST);
			return null;
		}

		// Check if the login is ok
		if (!user.equals(LOGIN)) {
			setStatus(Status.CLIENT_ERROR_UNAUTHORIZED);
			return null;
		}

		// Check if the password is ok
		//MessageDigest md = MessageDigest.getInstance("MD5");
		logger.info(password);
		if (!password.equals(PASSWORD)) {
			setStatus(Status.CLIENT_ERROR_UNAUTHORIZED);
			return null;
		}

		// Return the key
		JSONObject entry = new JSONObject();
		entry.put("api_key", KEY);
		JsonConverter conv = new JsonConverter();
		return conv.toRepresentation(entry, null, null);
	}
}
