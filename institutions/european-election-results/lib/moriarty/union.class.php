<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'moriarty.inc.php';
require_once MORIARTY_ARC_DIR . "ARC2.php";
require_once MORIARTY_DIR . 'simplegraph.class.php';
require_once MORIARTY_DIR . 'httprequest.class.php';
require_once MORIARTY_DIR . 'httprequestfactory.class.php';

/**
 * Represents a virtual union of multiple stores
 * 
 */
class Union {
  var $stores = array();
  
  
  function add($store_uri, $credentials = null) {
    $this->stores[] = array('uri' => $store_uri, 'credentials' => $credentials);
  }
  
  function search( $query, $max=10, $offset=0, $sort=false) {
    if (empty( $this->request_factory) ) {
      $this->request_factory = new HttpRequestFactory();
    }

    $responses = array();
    foreach ($this->stores as $store_info) {
      $cb = new Contentbox($store_info['uri'] . '/items', $store_info['credentials']);
      $request = $this->request_factory->make( 'GET', $cb->make_search_uri($query, $max, $offset, $sort), $store_info['credentials'] );
      $request->set_accept(MIME_RSS);
      $response[$store_info['uri']] = $request->execute();
    }

    $g = new SimpleGraph();
    $channel_uri = 'tag:moriarty.talis.com,2009:union';
    foreach ($this->stores as $store_info) {
    
    }
  }
}
?>