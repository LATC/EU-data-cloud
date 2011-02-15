package eu.latc.misc;

import org.apache.commons.httpclient.HttpClient;
import org.apache.commons.httpclient.HttpStatus;
import org.apache.commons.httpclient.NameValuePair;
import org.apache.commons.httpclient.URI;
import org.apache.commons.httpclient.methods.PostMethod;

public class PostReport {
	// Where the application is deployed
	//static String HOST = "http://127.0.0.1:8080/console";
	// The identifier of the configuration to send a report about
	//static String ID = "ff8080812e14f2fa012e14f2fa210000";
	// The full URI to POST the report to
	static String HOST = "http://fspc409.few.vu.nl/LATC_Console";
	static String ID = "ff8081812e15060a012e15060af50000";
	static String API_URI = HOST + "/api/configuration/" + ID + "/reports";

	/**
	 * @param args
	 * @throws Exception
	 */
	public static void main(String[] args) throws Exception {
		// Prepare the message
		// NOTE: The date/time is automatically set to the POST date/time
		NameValuePair[] data = { 
				new NameValuePair("status", "Test notification"),
				new NameValuePair("severity", "info"),
				new NameValuePair("data", "") 
		};
		System.out.println("Message to be sent -> " + data.toString());

		// Issue the POST 
		HttpClient clientService = new HttpClient();
		PostMethod post = new PostMethod();
		post.setURI(new URI(API_URI, false));
		post.setRequestBody(data);
		int status = clientService.executeMethod(post);

		// Check response code
		if (status != HttpStatus.SC_OK) {
			throw new Exception("Received error status " + status);
		}
	}
}
