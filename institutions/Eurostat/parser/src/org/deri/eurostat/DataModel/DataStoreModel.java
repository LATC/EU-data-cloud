package org.deri.eurostat.DataModel;

import java.io.File;
import java.io.IOException;
import java.util.ArrayList;

import org.openrdf.OpenRDFException;
import org.openrdf.query.BindingSet;
import org.openrdf.query.QueryLanguage;
import org.openrdf.query.TupleQuery;
import org.openrdf.query.TupleQueryResult;
import org.openrdf.repository.Repository;
import org.openrdf.repository.RepositoryConnection;
import org.openrdf.repository.RepositoryException;
import org.openrdf.repository.sail.SailRepository;
import org.openrdf.rio.RDFFormat;
import org.openrdf.sail.inferencer.fc.ForwardChainingRDFSInferencer;
import org.openrdf.sail.memory.MemoryStore;

public class DataStoreModel {

	Repository _repository=null;
	RepositoryConnection con = null;
	
	 public static String SELECT_CodeList_TEMPLATE = "" +
	    "PREFIX skos: <http://www.w3.org/2004/02/skos/core#> " +
	    "PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#> " +
	    "PREFIX sdmx-code: <http://purl.org/linked-data/sdmx/2009/code#> " +
	    "SELECT ?sub " +
	    "WHERE { " +
	    "?sub rdf:type skos:ConceptScheme ." +
	    "?sub skos:notation ?code ." +
	    "FILTER regex(str(?code), \"%s\", \"i\")." +
	    //"?s ?p ?o ." +
	    "}";

    public DataStoreModel()
	{
	
		_repository = new SailRepository(new ForwardChainingRDFSInferencer(new MemoryStore()));
		
        try {
            _repository.initialize();
            con = _repository.getConnection();
        } catch (RepositoryException e) {
            // TODO Auto-generated catch block
        }
        
	}
	public void shutdownRepository()
	{
        try {
        	con.close();
            _repository.shutDown();
            
        } catch (RepositoryException e) {
            // TODO Auto-generated catch block
        }
		
	}
	
	public void addRDFtoDataModel(String filePath, String baseURI, String format)
	{
		File file = new File(filePath);
		
		try {
		
			
			   if(format == "RDFXML")
				   con.add(file, baseURI, RDFFormat.RDFXML);
			   else
				   con.add(file, baseURI, RDFFormat.TURTLE);
		}
		catch (OpenRDFException e) {
			System.out.println("Open RDF Exception :" + filePath );
		   // handle exception
		}
		catch (IOException e) {
			System.out.println("ERRORRR :: File IO Exception");
		   // handle io exception
		}
	}
	
	public String returnCodeListURI(String codeList)
	{
		
		String codeListURI="";
		try {
			   RepositoryConnection con = _repository.getConnection();
			   try {
				   		BindingSet bindingSet;

				   		//Extract Authors
				   		TupleQuery tupleQuery = con.prepareTupleQuery(QueryLanguage.SPARQL, String.format(SELECT_CodeList_TEMPLATE,codeList));
				   		TupleQueryResult result = tupleQuery.evaluate();
			   			
			   			if(result.hasNext())
			   			{			   				
			   				bindingSet = result.next();
			   				codeListURI = bindingSet.getValue("sub").toString();
			   				codeListURI = codeListURI.substring(codeListURI.indexOf("#")+1);
			   				
			   			}
			   			
			   			result.close();				   		
			   }
			   finally {
				   con.close();
			   }
		}
		catch (OpenRDFException e) {
		  // handle exception
		}
		
		return codeListURI;
	}

}
