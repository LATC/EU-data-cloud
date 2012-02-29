<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'moriarty.inc.php';
require_once MORIARTY_DIR. 'simplegraph.class.php';

/**
 * Represents a simple data resource that exists on the network.
 */
class NetworkResource extends SimpleGraph {
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
   * @param string uri URI of the resource
   * @param Credentials credentials the credentials to use for authenticated requests (optional)
   */ 
  function __construct($uri, $credentials = null, $request_factory = null) {
    $this->uri = $uri;
    $this->credentials = $credentials;
    $this->request_factory = $request_factory;
    parent::__construct();
  }
  
  /**
   * Set the value of the resource's label property
   * @param string label the new label
   */
  function set_label($label) {
    $this->add_literal_triple( $this->uri, RDFS_LABEL, $label);
  }

  /**
   * Obtain the value of the resource's label property
   * @return string
   */
  function get_label() {
    return $this->get_first_literal($this->uri, RDFS_LABEL);
  }

  /**
   * Set the value of the resource's comment property
   * @param string comment the new comment
   */
  function set_comment($value) {
    $this->add_literal_triple( $this->uri, RDFS_COMMENT, $value);
  }

  /**
   * Obtain the value of the resource's comment property
   * @return string
   */
  function get_comment() {
    return $this->get_first_literal($this->uri, RDFS_COMMENT);
  }

 
  /**
   * Perform an HTTP GET on the resource's URI to obtain an RDF/XML document containing data associated with the resource
   * @return HttpResponse
   */
  function get_from_network() {
    if (empty( $this->request_factory) ) {
      $this->request_factory = new HttpRequestFactory();
    }
    $uri = $this->uri;

    $request = $this->request_factory->make( 'GET', $uri, $this->credentials);
    $request->set_accept(MIME_RDFXML);
    
    $response = $request->execute();

    if ($response->is_success()) {
      $this->from_rdfxml( $response->body );
    }
    
    return $response;
  }

  /**
   * Construct an RDF/XML document containing data associated with the resource and send it using HTTP PUT to the resource's URI
   * @return HttpResponse
   */
  function put_to_network() {
    if (empty( $this->request_factory) ) {
      $this->request_factory = new HttpRequestFactory();
    }
    $uri = $this->uri;

    $request = $this->request_factory->make( 'PUT', $uri, $this->credentials);
    $request->set_content_type(MIME_RDFXML);
    $request->set_body( $this->to_rdfxml() );
   
    $response = $request->execute();

    return $response;
  }

  /**
   * Issue an HTTP DELETE to the resource's URI
   * @return HttpResponse
   */
  function delete_from_network() {
    if (empty( $this->request_factory) ) {
      $this->request_factory = new HttpRequestFactory();
    }
    $uri = $this->uri;

    $request = $this->_make_request('DELETE', $uri);

    $response = $request->execute();

    return $response;
  }
  
  /**
   * @access private
   */
  function _make_request($method, $uri) {
    $request = $this->request_factory->make($method, $uri);
    if  ($this->credentials != null) {
      $request->set_auth( $this->credentials->get_auth() );
    }
    return $request;
    
  }

}
?>