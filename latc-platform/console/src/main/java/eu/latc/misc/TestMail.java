/**
 * 
 */
package eu.latc.misc;

/**
 * @author Christophe Guéret <christophe.gueret@gmail.com>
 *
 */
public class TestMail {

	/**
	 * @param args
	 * @throws Exception 
	 */
	public static void main(String[] args) throws Exception {
		MailSender sender = new MailSender();
		sender.send();

		MailSender sender2 = new MailSender();
		sender2.send();
	}

}
