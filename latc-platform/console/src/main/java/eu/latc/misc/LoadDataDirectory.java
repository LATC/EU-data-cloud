package eu.latc_project.misc;

import java.io.BufferedInputStream;
import java.io.File;
import java.io.FileInputStream;
import java.io.IOException;

import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import eu.latc_project.console.LinkingConfiguration;
import eu.latc_project.console.Manager;
import eu.latc_project.console.RunReport;

/**
 * @author cgueret
 * 
 */
public class LoadDataDirectory {
	// Logger instance
	protected final static Logger logger = LoggerFactory
			.getLogger("LATC.App.LoadDataDirectory");

	/**
	 * @param args
	 * @throws Exception
	 */
	public static void main(String[] args) throws Exception {
		// Create a new manager
		Manager manager = new Manager();

		// Get access to the directory where the configuration files are saved
		File dir = new File("data/");
		if (!dir.exists() || !dir.isDirectory())
			throw new Exception("Wrong directory");

		// Clean the content of the persistence layer
		manager.eraseAll();

		// Load all the content of the directory
		for (File file : dir.listFiles()) {
			if (!file.getName().endsWith("xml"))
				continue;
			logger.info("Load " + file.getAbsolutePath());

			// Read the file
			String configuration = readFileAsString(file);

			// Propose the document for addition
			String configurationID = manager.addConfiguration(configuration);

			// Update its name
			if (configurationID != null) {
				LinkingConfiguration linkingConfiguration = manager
						.getConfiguration(configurationID);
				linkingConfiguration.setTitle(file.getName());
				linkingConfiguration
						.setDescription("Maps entities as indicated in "
								+ file.getAbsolutePath());
				manager.saveConfiguration(linkingConfiguration);

				// Add a report for the insertion
				RunReport report = new RunReport();
				report.setStatusMessage("File uploaded");
				report.setResultsLocation("http://latc-project.eu");
				manager.addRunReport(linkingConfiguration.getIdentifier(), report);

			}
		}
	}

	/**
	 * From: http://snippets.dzone.com/posts/show/1335
	 * 
	 * @param filePath
	 * @return
	 * @throws java.io.IOException
	 */
	private static String readFileAsString(File file)
			throws java.io.IOException {
		byte[] buffer = new byte[(int) file.length()];
		BufferedInputStream f = null;
		try {
			f = new BufferedInputStream(new FileInputStream(file));
			f.read(buffer);
		} finally {
			if (f != null)
				try {
					f.close();
				} catch (IOException ignored) {
				}
		}
		return new String(buffer);

	}
}
