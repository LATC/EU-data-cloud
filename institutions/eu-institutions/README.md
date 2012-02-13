# Institutions and Bodies of the European Union

This is a LOD dataset about 'Institutions and Bodies of the European Union'. Michael and Anja from the [LATC Support Action](http://latc-project.eu/) are developing and maintaining it.

## Using

### SPARQL Query Example

Go to [sparql.org](http://sparql.org/sparql.html) and paste in the following query that list all organisations, their names and links to Wikipedia:

	PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
	PREFIX owl: <http://www.w3.org/2002/07/owl#>
	PREFIX org: <http://www.w3.org/ns/org#>
	PREFIX dct: <http://purl.org/dc/terms/> 

	SELECT ?org ?name ?alias WHERE {

	 ?euib rdfs:subClassOf org:Organization .
	 ?org a ?euib ;
	      dct:title ?name ;
	      owl:sameAs ?alias .
	}

### Using the JSON data from within a Web page

The site is [CORS-enabled](http://enable-cors.org/), so you can directly use the data in your Web page. For a simple example how to use the JSON data from within a Web page, see the '[standalone json](https://github.com/mhausenblas/eu-institutions/blob/master/usage/standalone-json.html)' HTML file.

### Using the CSV data in a Google spreadsheet

Import the [CSV data](http://eu-institutions.appspot.com/format/csv) in a Google spreadsheet.

### Dataset metadata

The dataset is described using [VoID](http://www.w3.org/TR/void/), see '[.well-known/void](http://institutions.publicdata.eu/.well-known/void)' on the site itself.

## License

The dataset is available under the Public Domain Dedication and License (PDDL).