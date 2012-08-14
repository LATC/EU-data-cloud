package org.deri.eurostat.dss;

import java.io.IOException;
import java.io.FileOutputStream;
import java.io.InputStream;
import java.io.OutputStream;
import java.net.HttpURLConnection;
import java.net.URL;

import org.apache.commons.cli.BasicParser;
import org.apache.commons.cli.CommandLine;
import org.apache.commons.cli.CommandLineParser;
import org.apache.commons.cli.Options;
import org.deri.eurostat.dsdparser.ParserUtil;
import org.deri.eurostat.toc.*;

import com.hp.hpl.jena.rdf.model.Model;
import com.hp.hpl.jena.rdf.model.Resource;

import javax.xml.parsers.DocumentBuilderFactory;
import javax.xml.parsers.ParserConfigurationException;
import javax.xml.xpath.XPathFactory;

import org.w3c.dom.Document;
import org.w3c.dom.Element;
import org.w3c.dom.NodeList;
import org.xml.sax.SAXException;

import java.util.Date;
import java.text.SimpleDateFormat;

/**
 * 
 * @author Aftab Iqbal
 * @author Sarven Capadisli http://csarven.ca/#i
 *
 */

public class Metadata {
    private Document xmlDocument;
    private static String outputFilePath = "";
    private static String inputFilePath = "";
    private static String serialization = "TURTLE";
    private static String fileExt = ".ttl";

    public void generateMetadataFiles()
    {
        if(inputFilePath.equals("")) {
            System.out.println("GETing ToC from XMLStream.");
            InputStream is = get_ToC_XMLStream();
            initObjects(is);
        }
        else {
            System.out.println("Using file: " + inputFilePath);
            initObjects(inputFilePath);
        }

        if(serialization.equalsIgnoreCase("RDF/XML"))
            fileExt = ".rdf";
        else if(serialization.equalsIgnoreCase("TURTLE"))
            fileExt = ".ttl";
        else if(serialization.equalsIgnoreCase("N-TRIPLES"))
            fileExt = ".nt";

        createCatalog();
        createVoID();
    }

	public InputStream get_ToC_XMLStream()
	{
		InputStream is = null;
		try {
			URL url = new URL("http://epp.eurostat.ec.europa.eu/NavTree_prod/everybody/BulkDownloadListing?sort=1&file=table_of_contents.xml");
			HttpURLConnection conn = (HttpURLConnection)url.openConnection();
			is = conn.getInputStream();

			if (conn.getResponseCode() != 200) {
				System.err.println(conn.getResponseCode());
			}
		} catch (IOException e) {
			e.printStackTrace();
			return null;
		}

		return is;
	}

	public void initObjects(InputStream in){
        try {
            xmlDocument = DocumentBuilderFactory.
			newInstance().newDocumentBuilder().
			parse(in);
        } catch (IOException ex) {
            ex.printStackTrace();
        } catch (SAXException ex) {
            ex.printStackTrace();
        } catch (ParserConfigurationException ex) {
            ex.printStackTrace();
        }
    }

    public void initObjects(String filePath){
        try {
        	xmlDocument = DocumentBuilderFactory.
			newInstance().newDocumentBuilder().
			parse(filePath);
        } catch (IOException ex) {
            ex.printStackTrace();
        } catch (SAXException ex) {
            ex.printStackTrace();
        } catch (ParserConfigurationException ex) {
            ex.printStackTrace();
        }
    }


    public void createCatalog()
    {
        Model model = ParserUtil.getModelProperties();

        Resource eurostatURI = model.createResource(ParserUtil.baseURI + "Eurostat");
        String datatypeDate = ParserUtil.xsd + "date";

        Element element = xmlDocument.getDocumentElement();
        NodeList leafs = element.getElementsByTagName("nt:leaf");
        if(leafs != null && leafs.getLength() > 0)
        {
            for(int i=0; i < leafs.getLength(); i++)
            {
                Element leaf = (Element)leafs.item(i);
                if(leaf.getAttribute("type").equals("dataset") || leaf.getAttribute("type").equals("table"))
                {
                    NodeList leafCodes = leaf.getElementsByTagName("nt:code");
                    String code = leafCodes.item(0).getTextContent().trim();

                    Resource dss = model.createResource(ParserUtil.dssURI + code);
                    Resource dsd = model.createProperty(ParserUtil.dsdURI + code);

                    //datasetURI a qb:DataSet
                    model.add(dss, ParserUtil.type, ParserUtil.qbDataset);

                    //datasetURI qb:structure dsdURI
                    model.add(dss, ParserUtil.qb_structure, dsd);
                    //dsdURI a qb:DataStructureDefinition
                    model.add(dsd, ParserUtil.type, ParserUtil.dsd);

                    //datasetURI cc:license CC0
                    model.add(dss, ParserUtil.ccLicense, model.createResource("http://creativecommons.org/publicdomain/zero/1.0/"));

                    //datasetURI dcterms:identifier code
                    model.add(dss, model.createProperty(ParserUtil.dcterms + "identifier"), code);

                    //dcterms:title
                    NodeList leafTitles = leaf.getElementsByTagName("nt:title");
                    if(leafTitles != null && leafTitles.getLength() > 0)
                    {
                        for(int j=0; j < leafTitles.getLength(); j++)
                        {
                            Element leafTitle = (Element)leafTitles.item(j);
                            String leafTitleTextContent = leafTitle.getTextContent().trim();

                            if(leafTitleTextContent.length() > 0)
                            {
                                //datasetURI dcterms:title title@lang
                                if(leafTitle.getAttribute("language") == null)
                                {
                                    model.add(dss, ParserUtil.dcTitle, leafTitleTextContent);
                                }
                                else
                                {
                                    model.add(dss, ParserUtil.dcTitle, model.createLiteral(leafTitleTextContent, leafTitle.getAttribute("language").trim()));
                                }
                            }
                        }
                    }

                    //dcterms:description
                    NodeList leafDescriptions = leaf.getElementsByTagName("nt:shortDescription");
                    if(leafDescriptions != null && leafDescriptions.getLength() > 0)
                    {
                        for(int j=0; j < leafDescriptions.getLength(); j++)
                        {
                            Element leafDescription = (Element)leafDescriptions.item(j);
                            String leafDescriptionTextContent = leafDescription.getTextContent().trim();

                            if(leafDescriptionTextContent.length() > 0)
                            {
                                //datasetURI dcterms:description description@lang
                                if(leafDescription.getAttribute("language") == null)
                                {
                                    model.add(dss, model.createProperty(ParserUtil.dcterms + "description"), leafDescriptionTextContent);
                                }
                                else
                                {
                                    model.add(dss, model.createProperty(ParserUtil.dcterms + "description"), model.createLiteral(leafDescriptionTextContent, leafDescription.getAttribute("language").trim()));
                                }
                            }
                        }
                    }


                    //datasetURI dcterms:source sdmxSourceURI , tsvSourceURI
                    NodeList leafDownloadLinks = leaf.getElementsByTagName("nt:downloadLink");
                    if(leafDownloadLinks != null && leafDownloadLinks.getLength() > 0)
                    {
                        for(int j=0; j < leafDownloadLinks.getLength(); j++)
                        {
                            Element leafDownloadLink = (Element)leafDownloadLinks.item(j);

                            if(leafDownloadLink.getAttribute("format").equals("tsv") || leafDownloadLink.getAttribute("format").equals("sdmx"))
                            {
                                model.add(dss, model.createProperty(ParserUtil.dcterms + "source"), model.createResource(leafDownloadLink.getTextContent().trim()));
                            }
                        }
                    }

                    //datasetURI dcterms:created created^^xsd:date
                    NodeList leafLastUpdates = leaf.getElementsByTagName("nt:lastUpdate");
                    String leafLastUpdate = leafLastUpdates.item(0).getTextContent().trim();
                    if (leafLastUpdate.length() > 0)
                    {
                        model.add(dss, model.createProperty(ParserUtil.dcterms + "created"), model.createTypedLiteral(convertDateToXSDDate(leafLastUpdate), datatypeDate));
                    }

                    //datasetURI dcterms:modified modified^^xsd:date
                    NodeList leafLastModifieds = leaf.getElementsByTagName("nt:lastModified");
                    String leafLastModified = leafLastModifieds.item(0).getTextContent().trim();

                    if (leafLastModified.length() > 0)
                    {
                        model.add(dss, model.createProperty(ParserUtil.dcterms + "modified"), model.createTypedLiteral(convertDateToXSDDate(leafLastModified), datatypeDate));
                    }
                }
            }
        }

        writeRDFToFile("catalog", model);
        System.out.println("Created Catalog.");
    }


    public void createVoID()
    {
        Date dateNow = new Date ();
        SimpleDateFormat dateformatYYYYMMDD = new SimpleDateFormat("yyyy-MM-dd");
        StringBuilder nowYYYYMMDD = new StringBuilder( dateformatYYYYMMDD.format( dateNow ) );
        String datatypeDate = ParserUtil.xsd + "date";

        Model model = ParserUtil.getModelProperties();

        Resource eurostatURI = model.createResource(ParserUtil.baseURI);
        Resource eurostatVoIDURI = model.createResource(ParserUtil.baseURI + "void.ttl");
        Resource eurostatDatasetURI = model.createResource(ParserUtil.baseURI + "void.ttl#eurostat");

        model.add(eurostatVoIDURI, ParserUtil.type, model.createResource(ParserUtil.voidURI + "DatasetDescription"));
        model.add(eurostatVoIDURI, model.createProperty(ParserUtil.dcterms + "title"), model.createLiteral("A VoID Description of the eurostat.linked-statistics.org Dataset", "en"));
        model.add(eurostatVoIDURI, model.createProperty(ParserUtil.dcterms + "creator"), model.createResource("http://csarven.ca/#i"));
        model.add(eurostatVoIDURI, model.createProperty(ParserUtil.foaf + "primaryTopic"), eurostatDatasetURI);

        model.add(eurostatDatasetURI, ParserUtil.type, ParserUtil.voidDataset);
        model.add(eurostatDatasetURI, model.createProperty(ParserUtil.foaf + "homepage"), eurostatURI);
        model.add(eurostatDatasetURI, model.createProperty(ParserUtil.dcterms + "title"), model.createLiteral("Eurostat Linked Data", "en"));

        model.add(eurostatDatasetURI, model.createProperty(ParserUtil.dcterms + "modified"), model.createTypedLiteral(nowYYYYMMDD, datatypeDate));
/*
TODO: Add
dcterms:source [ foaf:homepage <http://eurostat.linked-statistics.org/> ] ;
dcterms:publisher [ foaf:homepage <http://deri.ie/> ] ;

Add
        model.add(dss, model.createProperty(ParserUtil.dcterms + "created"), model.createTypedLiteral("????-??-??", datatypeDate));

*/


        Element element = xmlDocument.getDocumentElement();
        NodeList leafs = element.getElementsByTagName("nt:leaf");
        if(leafs != null && leafs.getLength() > 0)
        {
            for(int i=0; i < leafs.getLength(); i++)
            {
                Element leaf = (Element)leafs.item(i);
                if(leaf.getAttribute("type").equals("dataset") || leaf.getAttribute("type").equals("table"))
                {
                    NodeList leafCodes = leaf.getElementsByTagName("nt:code");
                    String code = leafCodes.item(0).getTextContent().trim();

                    Resource dss = model.createResource(ParserUtil.dssURI + code);

                    model.add(eurostatDatasetURI, ParserUtil.voidSubset, dss);
                    model.add(dss, ParserUtil.type, ParserUtil.voidDataset);
                    model.add(dss, model.createProperty(ParserUtil.voidURI + "dataDump"), model.createResource("http://eurostat.linked-statistics.org/data/" + code + ".rdf"));
                    model.add(dss, model.createProperty(ParserUtil.voidURI + "dataDump"), model.createResource("http://eurostat.linked-statistics.org/dsd/" + code + ".rdf"));
                }
            }
        }

        writeRDFToFile("void", model);
        System.out.println("Created VoID.");
    }


    public String convertDateToXSDDate(String s)
    {
        return s.substring(6,10) + "-" + s.substring(3,5) + "-" + s.substring(0,2);
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
        System.out.println("usage: Metadata [parameters]");
        System.out.println();
        System.out.println("    (optional) -i inputFilePath    Use local table_of_contents.xml file to generate metadata files rather than downloading from BulkDownload facility.");
        System.out.println("    -o outputFilePath    Output directory path to generate the metadata files.");
        System.out.println("    (optional) -f format    RDF format for serialization (RDF/XML, TURTLE, N-TRIPLES).");
    }

    public static void main(String[] args) throws Exception
    {

        CommandLineParser parser = new BasicParser( );
        Options options = new Options( );
        options.addOption("h", "help", false, "Print this usage information");
        options.addOption("i", "inputFilepath", true, "Local ToC file.");
        options.addOption("o", "outputFilepath", true, "Output directory path to generate the metadata files.");
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
            Metadata obj = new Metadata();
            obj.generateMetadataFiles();
        }
    }
}
