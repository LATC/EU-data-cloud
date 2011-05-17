/**
 * 
 */
package eu.latc.misc;

/* Copyright 2010 Google Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *    http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

import com.sun.mail.smtp.SMTPTransport;
import com.sun.mail.util.BASE64EncoderStream;

import net.oauth.OAuth;
import net.oauth.OAuthAccessor;
import net.oauth.OAuthConsumer;
import net.oauth.OAuthException;
import net.oauth.OAuthMessage;

import java.io.IOException;
import java.net.URISyntaxException;
import java.security.Provider;
import java.security.Security;
import java.util.HashMap;
import java.util.Map;
import java.util.Properties;

import javax.mail.Session;
import javax.mail.URLName;

/**
 * Performs XOAUTH authentication.
 * 
 * <p>
 * Before using this class, you must call {@code initialize} to install the
 * XOAUTH SASL provider.
 */
public class XoauthAuthenticator {
	public static final class XoauthProvider extends Provider {
		private static final long serialVersionUID = -4148877413151741891L;

		public XoauthProvider() {
			super("Google Xoauth Provider", 1.0, "Provides the Xoauth experimental SASL Mechanism");
			put("SaslClientFactory.XOAUTH", "com.google.code.samples.xoauth.XoauthSaslClientFactory");
		}
	}

	/**
	 * Installs the XOAUTH SASL provider. This must be called exactly once
	 * before calling other methods on this class.
	 */
	public static void initialize() {
		Security.addProvider(new XoauthProvider());
	}

	/**
	 * Connects and authenticates to an SMTP server with XOAUTH. You must have
	 * called {@code initialize}.
	 * 
	 * @param userEmail
	 *            Email address of the user to authenticate, for example
	 *            {@code xoauth@gmail.com}.
	 * @param oauthToken
	 *            The user's OAuth token.
	 * @param oauthTokenSecret
	 *            The user's OAuth token secret.
	 * @param consumer
	 *            The application's OAuthConsumer. For testing, use
	 *            {@code getAnonymousConsumer()}.
	 * @param debug
	 *            Whether to enable debug logging on the connection.
	 * 
	 * @return An authenticated SMTPTransport that can be used for SMTP
	 *         operations.
	 */
	public static SMTPTransport connectToGMail(String userEmail, String oauthToken, String oauthTokenSecret,
			boolean debug) throws Exception {
		Properties props = new Properties();
		props.put("mail.smtp.ehlo", "true");
		props.put("mail.smtp.auth", "false");
		props.put("mail.smtp.starttls.enable", "true");
		props.put("mail.smtp.starttls.required", "true");
		props.put("mail.smtp.sasl.enable", "false");
		Session session = Session.getInstance(props);
		session.setDebug(debug);

		final URLName unusedUrlName = null;
		SMTPTransport transport = new SMTPTransport(session, unusedUrlName);

		// If the password is non-null, SMTP tries to do AUTH LOGIN.
		final String emptyPassword = null;
		transport.connect("smtp.googlemail.com", 587, userEmail, emptyPassword);

		/*
		 * I couldn't get the SASL infrastructure to work with JavaMail 1.4.3; I
		 * don't think it was ready yet in that release. So we'll construct the
		 * AUTH command manually.
		 */
		OAuthConsumer consumer = new OAuthConsumer(null, "anonymous", "anonymous", null);
		byte[] saslResponse = buildResponse(userEmail, oauthToken, oauthTokenSecret, consumer);
		saslResponse = BASE64EncoderStream.encode(saslResponse);
		transport.issueCommand("AUTH XOAUTH " + new String(saslResponse), 235);
		return transport;
	}

	/**
	 * Builds an XOAUTH SASL client response.
	 * 
	 * @param userEmail
	 *            The email address of the user, for example "xoauth@gmail.com".
	 * @param protocol
	 *            The XoauthProtocol for which to generate an authentication
	 *            string.
	 * @param tokenAndTokenSecret
	 *            The OAuth token and token_secret.
	 * @param consumer
	 *            The OAuth consumer that is trying to authenticate.
	 * 
	 * @return A byte array containing the auth string suitable for being
	 *         returned from {@code SaslClient.evaluateChallenge}. It needs to
	 *         be base64-encoded before actually being sent over the network.
	 */
	private static byte[] buildResponse(String userEmail, String oauthToken, String oauthTokenSecret, OAuthConsumer consumer)
			throws IOException, OAuthException, URISyntaxException {
		OAuthAccessor accessor = new OAuthAccessor(consumer);
		accessor.tokenSecret = oauthTokenSecret;

		Map<String, String> parameters = new HashMap<String, String>();
		parameters.put(OAuth.OAUTH_SIGNATURE_METHOD, "HMAC-SHA1");
		parameters.put(OAuth.OAUTH_TOKEN, oauthToken);

		String url = String.format("https://mail.google.com/mail/b/%s/%s/", userEmail, "smtp");

		OAuthMessage message = new OAuthMessage("GET", url, parameters.entrySet());
		message.addRequiredParameters(accessor);

		StringBuilder authString = new StringBuilder();
		authString.append("GET ");
		authString.append(url);
		authString.append(" ");
		int i = 0;
		for (Map.Entry<String, String> entry : message.getParameters()) {
			if (i++ > 0) {
				authString.append(",");
			}
			authString.append(OAuth.percentEncode(entry.getKey()));
			authString.append("=\"");
			authString.append(OAuth.percentEncode(entry.getValue()));
			authString.append("\"");
		}
		return authString.toString().getBytes();
	}

}