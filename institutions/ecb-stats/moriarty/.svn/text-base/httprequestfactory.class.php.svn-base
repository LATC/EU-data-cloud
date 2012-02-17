<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'moriarty.inc.php';
require_once MORIARTY_DIR. 'httprequest.class.php';

/**
 * A factory for creating instances of HttpRequests. Required so unit tests can mock out HTTP behaviour
 */
class HttpRequestFactory {
  var $_cache = null;
  var $_read_from_cache = TRUE;
  var $_always_validate_cache = TRUE;
  var $_use_stale_response_on_failure = FALSE;
  var $_proxy = null;

  function __construct() {
    if (defined('MORIARTY_HTTP_CACHE_READ_ONLY') || defined('MORIARTY_ALWAYS_CACHE_EVERYTHING') ) {
      $this->always_validate_cache(FALSE);
    } 
    else {
      $this->always_validate_cache(TRUE);
    }

    if (defined('MORIARTY_HTTP_CACHE_USE_STALE_ON_FAILURE') ) {
      $this->use_stale_response_on_failure(TRUE);
    }
    else {
      $this->use_stale_response_on_failure(FALSE);
    }

    if (defined('MORIARTY_PROXY') ) {
      $this->set_proxy(MORIARTY_PROXY);
    }

    if (defined('MORIARTY_HTTP_CACHE_DIR')) {
      $this->_cache = new HttpCache( array('directory' => MORIARTY_HTTP_CACHE_DIR) );
    }

  }

  function make( $method, $uri, $credentials = null) {
    $request = new HttpRequest( $method, $uri, $credentials );
    $request->set_accept_encoding('gzip');
    $request->always_validate_cache($this->_always_validate_cache);
    $request->use_stale_response_on_failure($this->_use_stale_response_on_failure);
    $request->set_proxy($this->_proxy);
    $request->set_cache($this->_cache);
    $request->read_from_cache($this->_read_from_cache);
    return $request;
  }

  function set_cache($cache) {
    $this->_cache = $cache;
  }

  function always_validate_cache($val) {
    $this->_always_validate_cache = $val;
  }

  function use_stale_response_on_failure($val) {
    $this->_use_stale_response_on_failure = $val;
  }

  function read_from_cache($val) {
    $this->_read_from_cache = $val;
  }

  function set_proxy($val) {
    $this->_proxy = $val;
  }


}
?>
