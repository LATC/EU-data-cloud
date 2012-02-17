<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'moriarty.inc.php';
require_once MORIARTY_DIR. 'simplegraph.class.php';
require_once MORIARTY_DIR. 'queryprofile.class.php';

/**
 * A helper class to assist with configuring store groups.
 */
class StoreGroupConfig {
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
   * @param string uri URI of the store group config
   * @param Credentials credentials the credentials to use for authenticated requests (optional)
   */ 
  function __construct($uri, $credentials = null, $request_factory = null) {
    $this->uri = $uri;
    $this->credentials = $credentials;
    $this->request_factory = $request_factory;
  }

  /**
   * Obtain a reference to the store group's query profile. This needs to be subsequently fetched using get_from_network
   * @return QueryProfile
   */
  function get_first_query_profile() {
    return new QueryProfile( $this->uri . '/queryprofiles/1', $this->credentials);
  }

}

?>