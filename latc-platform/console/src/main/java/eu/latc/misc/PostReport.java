package eu.latc.misc;

import org.apache.commons.httpclient.HttpClient;
import org.apache.commons.httpclient.HttpStatus;
import org.apache.commons.httpclient.NameValuePair;
import org.apache.commons.httpclient.URI;
import org.apache.commons.httpclient.methods.PostMethod;
import org.json.JSONObject;

public class PostReport {
	// Where the application is deployed
	static String HOST = "http://127.0.0.1:8080/console";

	// The identifier of the configuration to send a report about
	static String ID = "ff8080812e2e35a3012e2e35a3c80000";

	/**
	 * @param args
	 * @throws Exception
	 */
	public static void main(String[] args) throws Exception {
		// The data is a JSONObject with a free content
		JSONObject data = new JSONObject();
		data.put("size", 100);
		data.put("location", "http://demo.sindice.net/latctemp/2011-02-14/climb_silk_link_spec/");

		// Prepare the message
		// "message" is mandatory
		// "severity" defaults to 'info' if not precised
		// "data" is optional
		// (note: The date/time is automatically set to the POST date/time)
		NameValuePair[] request = { 
				new NameValuePair("message", "Generated 100 triples"),
				new NameValuePair("severity", "info"), 
				new NameValuePair("api_key", "aa4967eb8b7a5ccab7dbb57aa2368c7f"), 
				new NameValuePair("data", data.toString()) };

		// Diplay the result
		System.out.println("Message to send -> " + request.toString());

		// Prepare the query
		String URI = HOST + "/api/task/" + ID + "/notifications";
		PostMethod post = new PostMethod();
		post.setURI(new URI(URI, false));
		post.setRequestBody(request);

		// Issue the POST
		HttpClient clientService = new HttpClient();
		int status = clientService.executeMethod(post);

		// Check response code
		if (status != HttpStatus.SC_OK) {
			throw new Exception("Received error status " + status);
		}
	}
}
