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

public class Catalog {

	private static String outputFilePath = "";
	private static String serialization = "TURTLE";
	
	ParseToC obj;
	
	public void generate_VoidFiles()
	{
		obj = new ParseToC();
		InputStream is = obj.get_ToC_XMLStream();
		obj.initObjects(is);
		obj.parseDataSets();

		createCatalog();
		createInventory();
	}

	public void createCatalog()
	{
		Model model = ParserUtil.getModelProperties();
				
		for(String str:obj.lstDatasetURLs)
		{
			str = str.substring(str.lastIndexOf("/")+1, str.indexOf(".sdmx.zip"));
			Resource dss = model.createResource(ParserUtil.dssURI + str);
			model.add(dss,ParserUtil.type,ParserUtil.qbDataset);
			model.add(dss,ParserUtil.type,ParserUtil.voidDataset);
			
			model.add(dss,ParserUtil.dsd,model.createProperty(ParserUtil.dsdURI + str));
			model.add(dss,ParserUtil.dataDump,model.createProperty(ParserUtil.dataURI + str + ".rdf"));
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
			model.add(dsd,ParserUtil.dataDump,model.createProperty(ParserUtil.dsdURI + str + ".rdf"));
		}
		
		writeRDFToFile("inventory", model);
	}

	public void writeRDFToFile(String fileName, Model model)
	{
		try
	   	{
			OutputStream output = new FileOutputStream(outputFilePath + fileName + ".rdf",false);
			model.write(output,serialization);
			
	   	}catch(Exception e)
	   	{
	   		System.out.println("Error while creating file ..." + e.getMessage());
	   	}
	}
	
	private static void usage()
	{
		System.out.println("usage: Catalog [parameters]");
		System.out.println();
		System.out.println("	-o outputFilePath	Output directory path to generate the Catalog and Inventory files.");
		System.out.println("	(optional)-f format	RDF format for serialization (RDF/XML, TURTLE, N-TRIPLES).");
	}
	
	public static void main(String[] args) throws Exception
	{
		
		CommandLineParser parser = new BasicParser( );
		Options options = new Options( );
		options.addOption("h", "help", false, "Print this usage information");
		options.addOption("o", "outputFilepath", true, "Output directory path to generate the Catalog and Inventory files.");
		options.addOption("f", "format", true, "RDF format for serialization (RDF/XML, TURTLE, N-TRIPLES).");
		CommandLine commandLine = parser.parse( options, args );
		
		if( commandLine.hasOption('h') ) {
		    usage();
		    return;
		 }
		
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
