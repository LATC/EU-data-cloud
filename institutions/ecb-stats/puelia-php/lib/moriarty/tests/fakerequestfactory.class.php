<?php
require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'constants.inc.php';
require_once MORIARTY_DIR . 'httprequestfactory.class.php';

class FakeRequestFactory extends HttpRequestFactory {
  var $_requests;
  var $_received = array();
  function __construct() {
    $this->_requests = array();
    $this->_received = array();
  }

  function register($method, $uri, $request ) {
    $key = $method . ' ' . $uri;
    $this->_requests[$key][] = $request;
  }

  function get_registered_request($method, $uri){
      $key = $method . ' ' . $uri;
      if(isset($this->_requests[$key])) return array_shift($this->_requests[$key]);
      else return false;
  }

  function make( $method, $uri, $credentials = null) {
    $this->_received[] = $method . ' ' . $uri;
    if ($request = $this->get_registered_request($method,$uri)) {
     if ( $credentials != null) {
        $request->set_auth( $credentials->get_auth());
      }
      return $request;
    }

    $response = new HttpResponse();
    $response->status_code = 404;
      
    return new FakeHttpRequest( $response );
  }

  function dump_received() {
    foreach ($this->_received as $received) {
      echo $received, "\n"; 
    } 
  }
}
?>
