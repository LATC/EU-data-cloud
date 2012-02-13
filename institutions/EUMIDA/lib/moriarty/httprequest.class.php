<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'moriarty.inc.php';
require_once MORIARTY_DIR . 'httpcache.class.php';
require_once MORIARTY_DIR . 'httpclient.class.php';
require_once MORIARTY_DIR . 'httpresponse.class.php';

/**
 * Represents an HTTP protocol request.
 */
class HttpRequest {
  /**
   * @access private
   */
  var $method;
  /**
   * @access private
   */
  var $uri;
  /**
   * @access private
   */
  var $headers = array();
  /**
   * @access private
   */
  var $client;
  /**
   * @access private
   */
  var $body;
  /**
   * @access private
   */
  var $credentials;


  var $_cache = null;
  var $_read_from_cache = TRUE;
  var $_always_validate_cache = TRUE;
  var $_use_stale_response_on_failure = FALSE;

  var $_proxy = null;

  var $_async_key = null;

  var $_response_from_cache = null;

  /**
   * Create a new instance of this class
   * @param string method the HTTP method to issue (i.e. GET, POST, PUT etc)
   * @param string uri the URI to issue the request to
   * @param Credentials credentials the credentials to use for secure requests (optional)
   */
  function __construct($method, $uri, $credentials = null, $cache = null) {
    $uri = preg_replace("~#.*$~", '', $uri);
    $this->uri = $uri;
    $this->method = strtoupper($method);
    if ( $credentials != null ) {
      $this->credentials = $credentials;
    }
    else {
      $this->credentials = null;
    }

    $this->headers = array();
    $this->headers['Accept'] = '*/*';


    $this->options = array();
    $this->body = null;


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

  /**
   * Whether to read from the cache.
   * When set to FALSE, the request will be written to the cache but not read from it
   */
  function read_from_cache($val) {
    $this->_read_from_cache = $val;
  }

  function set_proxy($val) {
    $this->_proxy = $val;
  }

  /**
   * Issue the HTTP request
   * @return HttpResponse
   */
  function execute() {
    $this->execute_async();
    return $this->get_async_response();
  }

  function execute_async() {

    if ( $this->_cache && $this->_read_from_cache ) {

      $cached_response = $this->_cache->load($this->cache_id(), $this->_use_stale_response_on_failure);
      if ($cached_response) {
        $cached_response->request_method = $this->method;
        $cached_response->request_uri = $this->uri;
        $cached_response->request_headers = $this->headers;
        $cached_response->request_body = $this->body;
        $cached_response->headers['x-moriarty-from-cache'] = 'yes';
        $this->_response_from_cache = $cached_response;

        if ($this->_always_validate_cache ) {
          if ( isset($cached_response->headers['etag']) ) {
            $this->set_if_none_match($cached_response->headers['etag']);
          }
        }
        else {
          return;
        }
      }
    }

    $this->client = HttpClient::Create();
    $this->_async_key = $this->client->send_request($this);
  }

 function get_async_response()
  {
    $response = null;
    if ($this->_response_from_cache === null || $this->_always_validate_cache) {
      $response = $this->client->get_response_for($this->_async_key);
    }

    if ( $response === null ) {
      if ( $this->_cache && $this->_response_from_cache) {
        return $this->_response_from_cache;
      }

      $response_code = $response_info['http_code'];
      $response_body = "Request failed: " . $response_error;
      $response_headers = array();
    }


    /*
     echo '<p>The HTTP request sent was:</p>';
     echo '<pre>' . htmlspecialchars($this->to_string()) . '</pre>';
     echo '<p>The server response was:</p>';
     echo '<pre>' . htmlspecialchars($response->to_string()) . '</pre>';
     */

    if ( $this->_cache ) {
      if ( $this->_read_from_cache && $this->_response_from_cache && $response->status_code == 304) {
        return $this->_response_from_cache;
      }

      if (! $this->_response_from_cache ) {
        $max_age = FALSE;
        if ( (defined('MORIARTY_ALWAYS_CACHE_EVERYTHING') && $response->is_success()) || ($this->method == 'GET' && $response->is_cacheable())  ) {
            if(isset($response->headers['cache-control'])){
                  $cache_control = $response->headers['cache-control'];
                  $cache_control_tokens = preg_split('/,/', $cache_control);
                  foreach ( $cache_control_tokens as $token) {
                    $token = trim($token);
                    if ( preg_match('/max-age=(.+)/', $token, $m) ) {
                      $max_age = $m[1];
                      break;
                    }
                  }
            }
          $this->_cache->save($response, $this->cache_id(), array(), $max_age);
        }
      }
    }

    return $response;

  }

  /**
   * Obtain the HTTP headers to be sent with this request
   * @return array headers in the format "name:value"
   */
  function get_headers() {
    $flat_headers = array();
    foreach ($this->headers as $k=>$v) {
      $flat_headers[] = "$k: $v";
    }
    return $flat_headers;
  }

  /**
   * Set content to be sent with the request
   * @param string val the content to be sent
   */
  function set_body($val, $gzip_body=false) {
    if($gzip_body) {
      $val = gzencode($val);
      $this->body = $val;
      $this->headers['Content-Encoding'] = 'gzip';
      $this->headers['Content-Length'] = strlen($val);
    } 
    else 
    {
      $this->body = $val;
    }
  }

  /**
   * Get the content to be sent with the request
   * @return string the content to be sent
   */
  function get_body() {
    return $this->body;
  }

  /**
   * Set the HTTP accept-encoding header for the request
   * @param string val the media types to be used as the accept header value
 */
  function set_accept_encoding($val) {
    $this->headers['Accept-Encoding'] = $val;
  }
  
  /**
   * Set the HTTP accept header for the request
   * @param string val the media types to be used as the accept header value
   */
  function set_accept($val) {
    $this->headers['Accept'] = $val;
  }

  /**
   * Set the HTTP content-type header for the request
   * @param string val the media type to be used as the content-type header value
   */
  function set_content_type($val) {
    $this->headers['Content-Type'] = $val;
  }

  /**
   * Set the HTTP if-match header for the request
   * @param string val the etag to be used as the if-match header value
   */
  function set_if_match($val) {
    $this->headers['If-Match'] = $val;
  }

  /**
   * Set the HTTP if-none-match header for the request
   * @param string val the etag to be used as the if-none-match header value
   */
  function set_if_none_match($val) {
    $this->headers['If-None-Match'] = $val;
  }


  function cache_id() {
    $accept = '*/*';
    if (array_key_exists('Accept', $this->headers)) {
      $accept = $this->headers['Accept'];
    }

    $accept_parts = preg_split('/,/', $accept);
    sort($accept_parts);
    $accept = join(',', $accept_parts);
    return md5('<' . $this->uri . '>' . $accept . $this->get_body());
  }

  /**
   * Obtain a string representation of this request
   * @return string
   */
  function to_string() {
    $ret = strtoupper($this->method) . ' ' . $this->uri . "\n";
    foreach ($this->headers as $k=>$v) {
      $ret .= "$k: $v\n";
    }
    $ret .= "\n";
    $ret .= $this->get_body();

    return $ret;
  }


}
?>
