<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'moriarty.inc.php';
require_once MORIARTY_DIR. 'networkresource.class.php';
require_once MORIARTY_DIR. 'sparqlservice.class.php';
require_once MORIARTY_DIR. 'contentbox.class.php';
require_once MORIARTY_DIR. 'storegroupconfig.class.php';

/**
 * Represents a virtual group of stores.
 */
class StoreGroup {

  /**
   * Create a new instance of this class
   * @param string uri URI of the store group
   * @param Credentials credentials the credentials to use for authenticated requests (optional)
   */ 
  function __construct($store_uris, $credentials = null) {
    $this->stores = array();
    
    foreach ($store_uris as $store_uri) {
      $this->stores[] = new Store($store_uri, $credentials);
    }
    $this->credentials = $credentials;
  }
  
  function describe($uri, $type='cbd', $output='rdf') {
    if (empty( $this->request_factory) ) {
      $this->request_factory = new HttpRequestFactory();
    }

    $requests = array();
    foreach ($this->stores as $store) {
      $request = $store->get_metabox()->get_describe_request($uri, $output);
      $request->execute_async();
      $requests[] = $request;
    }

    $responses = array();
    foreach ($requests as $request) {
      $responses[] = $request->get_async_response();
    }
    
    return $responses;
  }



}
?>