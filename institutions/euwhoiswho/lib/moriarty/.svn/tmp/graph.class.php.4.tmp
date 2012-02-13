<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'moriarty.inc.php';
require_once MORIARTY_ARC_DIR . "ARC2.php";
require_once MORIARTY_DIR . 'httprequest.class.php';
require_once MORIARTY_DIR . 'httprequestfactory.class.php';

/**
 * The base class for graphs in a store.
 */
class Graph {
  /**
   * @access private
   */
  var $uri;
  /**
   * @access private
   */
  var $credentials;
  /**
   * @access private
   */
  var $request_factory;

  /**
   * Create a new instance of this class
   * @param string uri URI of the graph
   * @param Credentials credentials the credentials to use for authenticated requests (optional)
   */
  function __construct($uri, $credentials = null, $request_factory = null)  {
    $this->uri = $uri;
    $this->credentials = $credentials;
    $this->request_factory = $request_factory;
  }

  /**
   * Apply a changeset to the graph
   * @param ChangeSet cs the changeset to apply
   * @return HttpResponse
   */
  function apply_changeset($cs) {
    return $this->apply_changeset_rdfxml( $cs->to_rdfxml());
  }

  /**
   * Apply a changeset in a versioned manner to the graph
   * @param ChangeSet cs the changeset to apply
   * @return HttpResponse
   */
  function apply_versioned_changeset($cs) {
    return $this->apply_versioned_changeset_rdfxml( $cs->to_rdfxml());
  }

  /**
   * Apply a changeset to the graph
   * @param string rdfxml the changeset to apply, serialised as RDF/XML
   * @return HttpResponse
   */
  function apply_changeset_rdfxml($rdfxml) {
    if (empty( $this->request_factory) ) {
      $this->request_factory = new HttpRequestFactory();
    }

    $uri = $this->uri;

    $request = $this->request_factory->make( 'POST', $uri, $this->credentials);
    $request->set_accept("*/*");
    $request->set_content_type("application/vnd.talis.changeset+xml");
    $request->set_body( $rdfxml );

    return $request->execute();
  }

  /**
   * Apply a changeset in a versioned manner to the graph
   * @param string rdfxml the changeset to apply, serialised as RDF/XML
   * @return HttpResponse
   */
  function apply_versioned_changeset_rdfxml($rdfxml) {
    if (empty( $this->request_factory) ) {
      $this->request_factory = new HttpRequestFactory();
    }

    $uri = $this->uri . '/changesets';

    $request = $this->request_factory->make( 'POST', $uri, $this->credentials);
    $request->set_accept("*/*");
    $request->set_content_type("application/vnd.talis.changeset+xml");
    $request->set_body( $rdfxml );

    return $request->execute();
  }

  /**
   * Submit some RDF/XML to be added to the graph
   * @param string rdfxml the RDF to be submitted, serialised as RDF/XML
   * @return HttpResponse
   */
  function submit_rdfxml($rdfxml) {
    if (empty( $this->request_factory) ) {
      $this->request_factory = new HttpRequestFactory();
    }

    $uri = $this->uri;
    $request = $this->request_factory->make( 'POST', $uri, $this->credentials);
    $request->set_content_type("application/rdf+xml");
    $request->set_accept("*/*");
    $request->set_body( $rdfxml );
    return $request->execute();
  }


  /**
   * Submit some Turtle to be added to the graph
   * @param string turtle the RDF to be submitted, serialised as Turtle
   * @return HttpResponse
   */
  function submit_turtle($turtle) {
    if (empty( $this->request_factory) ) {
      $this->request_factory = new HttpRequestFactory();
    }
    $uri = $this->uri;
    $request = $this->request_factory->make( 'POST', $uri, $this->credentials);
    $request->set_content_type("text/turtle");
    $request->set_accept("*/*");
    $request->set_body( $turtle );
    return $request->execute();
  }

  /**
   * Obtain the graph's bounded description of a given resource
   * @see http://n2.talis.com/wiki/Metabox#Describing_a_Resource
   * @param string uri the URI of the resource to be described
   * @param string output the desired output format of the response (e.g. rdf, xml, json, ntriples, turtle)
   * @return HttpResponse
   */
  function describe( $uri, $output = null ) {

    $request = $this->get_describe_request($uri, $output);

    return $request->execute();
  }

  function get_describe_uri( $uri, $output = null ) {
    $request_uri = $this->uri . '?about=' . urlencode($uri);
    if ($output) {
      $request_uri .= '&output=' . urlencode($output);
    }
    return $request_uri;
  }
  function get_describe_request( $uri, $output = null ) {
    if (empty( $this->request_factory) ) {
      $this->request_factory = new HttpRequestFactory();
    }

    $request_uri = $this->get_describe_uri($uri, $output);
  
    $request = $this->request_factory->make( 'GET', $request_uri, $this->credentials);
    if ($output) {
      $request->set_accept("*/*");
    }
    else {
      $request->set_accept("application/rdf+xml");
    }
    $request->set_content_type("application/x-www-form-urlencoded");

    return $request;
  }

  /**
   * Obtain the graph's bounded description of a given resource
   * @deprecated triple lists are deprecated
   */
  function describe_to_triple_list( $uri ) {
    $triples = array();

    $response = $this->describe( $uri );
    $parser_args=array(
      "bnode_prefix"=>"genid",
      "base"=> $this->uri
    );
    $parser = ARC2::getRDFXMLParser($parser_args);

    if ( $response->body ) {
      $parser->parse($this->uri, $response->body );
      $triples = $parser->getTriples();
    }

    return $triples;
  }

  /**
   * Obtain the graph's bounded description of a given resource. This is designed to be fast since it uses RDF/JSON which requires no parsing by the SimpleGraph class. This method always returns a SimpleGraph, which will be empty if any HTTP errors occured.
   * @see http://n2.talis.com/wiki/Metabox#Describing_a_Resource
   * @param string uri the URI of the resource to be described
   * @return SimpleGraph
   */
  function describe_to_simple_graph( $uri ) {
    $graph = new SimpleGraph();

    $response = $this->describe( $uri, OUTPUT_TYPE_JSON );

    if ( $response->is_success() ) {
      $graph->from_json( $response->body );
    }

    return $graph;
  }

  /**
   * Tests whether the graph contains a bounded description of a given resource. This uses a conditional GET.
   * @see http://n2.talis.com/wiki/Metabox#Describing_a_Resource
   * @param string uri the URI of the resource to be described
   * @return boolean true if the graph contains triples with the resource as a subject, false otherwise
   */
  function has_description( $uri ) {
    if (empty( $this->request_factory) ) {
      $this->request_factory = new HttpRequestFactory();
    }

    $request_uri = $this->uri . '?about=' . urlencode($uri);
    $request = $this->request_factory->make( 'GET', $request_uri, $this->credentials);
    $request->set_accept("application/rdf+xml");
    $request->set_if_match("*");

    $response = $request->execute();

    if ($response->status_code == 200) {
      return true;
    }
    else if ($response->status_code == 412) {
      return false;
    }
  }
}
?>