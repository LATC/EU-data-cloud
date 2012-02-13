<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'moriarty.inc.php';

/** 
* Represents a store's augment service
* @see http://n2.talis.com/wiki/Augment_Service
*/
class AugmentService {
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
   * @param string uri URI of the augment service
   * @param Credentials credentials the credentials to use for authenticated requests (optional)
   */ 
  function __construct($uri, $credentials = null, $request_factory = null) {
    $this->uri = $uri;
    $this->credentials = $credentials;
    $this->request_factory = $request_factory;
  }

  /**
   * 
   * @param string $uri the URI of the RSS feed that the augment service will fetch and augment
   * @return HttpResponse
   */
  function augment($uri) {
    if (empty( $this->request_factory) ) {
      $this->request_factory = new HttpRequestFactory();
    }

    $uri = $this->uri . '?data-uri=' . urlencode($uri);
    $request = $this->request_factory->make( 'GET', $uri , $this->credentials );
    $request->set_accept(MIME_RSS);
    return $request->execute();
  }

  /**
   * Warning - this method is incomplete
   * @param SimpleGraph $graph an RDF graph that should be augmented
   * @return HttpResponse
   */
  function augment_graph($graph) {
    if (empty( $this->request_factory) ) {
      $this->request_factory = new HttpRequestFactory();
    }

    $request = $this->request_factory->make( 'POST', $this->uri , $this->credentials );
    $request->set_accept(MIME_RSS);
    $request->set_content_type(MIME_RSS);
    
    $data = new SimpleGraph();
    $data->add_turtle( $graph->to_turtle());
    
    $data->add_resource_triple( 'tag:talis.com,2008:moriarty-tmp-augment-channel', RDF_TYPE, 'http://purl.org/rss/1.0/channel');
    $data->add_resource_triple( 'tag:talis.com,2008:moriarty-tmp-augment-channel', RSS_ITEMS, 'tag:talis.com,2008:moriarty-tmp-augment-channel-items');
    $data->add_resource_triple( 'tag:talis.com,2008:moriarty-tmp-augment-channel-items', RDF_TYPE, 'http://www.w3.org/1999/02/22-rdf-syntax-ns#Seq');
    $request->set_body( $data->to_rdfxml() );
    
    return $request->execute();
  }


}
?>
