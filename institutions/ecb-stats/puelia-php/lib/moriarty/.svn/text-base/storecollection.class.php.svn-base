<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'moriarty.inc.php';
require_once MORIARTY_DIR. 'simplegraph.class.php';
require_once MORIARTY_DIR. 'httprequestfactory.class.php';

/**
 * Represents the collection of all platform stores.
 */
class StoreCollection extends SimpleGraph {
  /**
   * @access private
   */
  var $uri;
  /**
   * @access private
   */
  var $request_factory;
  /**
   * @access private
   */
  var $credentials;


  /**
   * Create a new instance of this class
   * @param string uri URI of the store collection
   * @param Credentials credentials the credentials to use for authenticated requests (optional)
   */ 
  function __construct($uri, $credentials = null, $request_factory = null) {
    $this->uri = $uri;
    $this->credentials = $credentials;
    $this->request_factory = $request_factory;
  }

  /**
   * @access private
   * @deprecated this should be compatible with NetworkResource
   */
  function retrieve() {
    if (empty( $this->request_factory) ) {
      $this->request_factory = new HttpRequestFactory();
    }
    $uri = $this->uri;

    $request = $this->request_factory->make( 'GET', $uri);
    $request->set_accept(MIME_RDFXML);

    $response = $request->execute();

    if ($response->is_success()) {
      $this->from_rdfxml( $response->body );
    }
  }

  /**
   * Create a new store on the platform. This is currently restricted to Talis administrators.
   * @param string name the name of the store
   * @param string template_uri the URI of the store template to use
   * @return HttpRequest
   */
  function create_store($name, $template_uri) {
    if (empty( $this->request_factory) ) {
      $this->request_factory = new HttpRequestFactory();
    }

    $uri = $this->uri;
    $mimetype = MIME_RDFXML;

    $request = $this->request_factory->make( 'POST', $uri, $this->credentials);
    $request->set_accept("*/*");
    $request->set_content_type($mimetype);

    $sr = new SimpleGraph();
    $sr->add_resource_triple('_:req', BF_STORETEMPLATE, $template_uri);
    $sr->add_literal_triple( '_:req', BF_STOREREF, $name);


    $request->set_body( $sr->to_rdfxml() );
    return $request->execute();

  }

  /**
   * Obtain the list of store URIs. The retrieve method must be called first.
   * @return array
   */ 
  function get_store_uris() {
    $list = array();
    foreach ($this->_index[$this->uri][BF_STORE] as $store_resource) {
      if ( $store_resource['type'] == 'uri' || $store_resource['type'] == 'bnode') {
        $list[] = $store_resource['value'];
      }
    }

    return $list;
  }

}


?>