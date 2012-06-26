package org.deri.eurostat.dss;

import java.io.FileOutputStream;
import java.io.InputStream;
import java.io.OutputStream;

import org.apache.commons.cli.BasicParser;
import org.apache.commons.cli.CommandLine;
import org.apache.commons.cli.CommandLineParser;
import org.apache.commons.cli.Options;
import org.deri.eurostat.dsdparser.ParserUtil;
import org.deri.eurostat.toc.*;

import com.hp.hpl.jena.rdf.model.Model;
import com.hp.hpl.jena.rdf.model.Resource;

/**
 * 
 * @author Aftab Iqbal
 *
 */

public class Catalog {

	private static String outputFilePath = "";
	private static String inputFilePath = "";
	private static String serialization = "TURTLE";
	private static String fileExt = ".ttl";
	ParseToC obj;
	
	public void generate_VoidFiles()
	{
		obj = new ParseToC();
		if(inputFilePath.equals("")) {
		InputStream is = obj.get_ToC_XMLStream();
		obj.initObjects(is);
		obj.parseDataSets();
		}
		else {
			obj.initObjects(inputFilePath);
			obj.parseDataSets();
		}
			
		if(serialization.equalsIgnoreCase("RDF/XML"))
			fileExt = ".rdf";
		else if(serialization.equalsIgnoreCase("TURTLE"))
			fileExt = ".ttl";
		else if(serialization.equalsIgnoreCase("N-TRIPLES"))
			fileExt = ".nt";
		
		createCatalog();
		createInventory();
	}

	public void createCatalog()
	{
		Model model = ParserUtil.getModelProperties();
		
		Resource main = model.createResource(ParserUtil.baseURI + "Eurostat");
		model.add(main,ParserUtil.type,ParserUtil.voidDataset);
		
		for(String str:obj.lstDatasetURLs)
		{
			str = str.substring(str.lastIndexOf("/")+1, str.indexOf(".sdmx.zip"));
			Resource dss = model.createResource(ParserUtil.dssURI + str);
			model.add(dss,ParserUtil.type,ParserUtil.qbDataset);
			model.add(dss,ParserUtil.type,ParserUtil.voidDataset);
			
			model.add(dss,ParserUtil.qb_structure,model.createProperty(ParserUtil.dsdURI + str));
			model.add(dss,ParserUtil.dataDump,model.createProperty(ParserUtil.dataURI + str + ".rdf"));
			
			model.add(main,ParserUtil.subset,model.createProperty(ParserUtil.dssURI + str));
		}
		
		writeRDFToFile("catalog", model);
	}

	public void createInventory()
	{
		Model model = ParserUtil.getModelProperties();
				
		for(String str:obj.lstDatasetURLs)
		{
			str = str.substring(str.lastIndexOf("/")+1, str.indexOf(".sdmx.zip"));
			Resource dsd = model.createResource(ParserUtil.dsdURI + str);
			model.add(dsd,ParserUtil.type,ParserUtil.dsd);
			model.add(dsd,ParserUtil.type,ParserUtil.voidDataset);
			model.add(dsd,ParserUtil.dataDump,model.createProperty(ParserUtil.dsdURI + str + fileExt));
		}
		
		Resource catalog = model.createResource(ParserUtil.baseURI + "catalog");
		model.add(catalog,ParserUtil.type,ParserUtil.voidDataset);
		model.add(catalog,ParserUtil.dataDump,model.createProperty(ParserUtil.baseURI + "catalog" + fileExt));

		Resource inventory = model.createResource(ParserUtil.baseURI + "inventory");
		model.add(inventory,ParserUtil.type,ParserUtil.voidDataset);
		model.add(inventory,ParserUtil.dataDump,model.createProperty(ParserUtil.baseURI + "inventory" + fileExt));

		writeRDFToFile("inventory", model);
	}

	public void writeRDFToFile(String fileName, Model model)
	{

		try
	   	{
			OutputStream output = new FileOutputStream(outputFilePath + fileName + fileExt,false);
			model.write(output,serialization.toUpperCase());
			
	   	}catch(Exception e)
	   	{
	   		System.out.println("Error while creating file ..." + e.getMessage());
	   	}
	}
	
	private static void usage()
	{
		System.out.println("usage: Catalog [parameters]");
		System.out.println();
		System.out.println("	(optional) -i inputFilePath	Use local ToC.xml file to generate catalog rather than downloading from BulkDownload facility.");
		System.out.println("	-o outputFilePath	Output directory path to generate the Catalog and Inventory files.");
		System.out.println("	(optional) -f format	RDF format for serialization (RDF/XML, TURTLE, N-TRIPLES).");
	}
	
	public static void main(String[] args) throws Exception
	{
		
		CommandLineParser parser = new BasicParser( );
		Options options = new Options( );
		options.addOption("h", "help", false, "Print this usage information");
		options.addOption("i", "inputFilepath", true, "Local ToC file.");
		options.addOption("o", "outputFilepath", true, "Output directory path to generate the Catalog and Inventory files.");
		options.addOption("f", "format", true, "RDF format for serialization (RDF/XML, TURTLE, N-TRIPLES).");
		CommandLine commandLine = parser.parse( options, args );
		
		if( commandLine.hasOption('h') ) {
		    usage();
		    return;
		 }
		
		if(commandLine.hasOption('i'))
			inputFilePath = commandLine.getOptionValue('i');
		if(commandLine.hasOption('o'))
			outputFilePath = commandLine.getOptionValue('o');
		if(commandLine.hasOption('f'))
			serialization = commandLine.getOptionValue('f');
		
		if(outputFilePath.equals("") || serialization.equals(""))
		{
			usage();
			return;
		}
		else
		{
			Catalog obj = new Catalog();
			obj.generate_VoidFiles();
		}
	}
}
