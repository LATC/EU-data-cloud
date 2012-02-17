<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'moriarty.inc.php';
require_once MORIARTY_DIR . 'httpcache.class.php';
require_once MORIARTY_DIR . 'httpclient.class.php';

/**
 * Represents an HTTP protocol response.
 */
class HttpResponse {
  /**
   * The HTTP status code of the response
   * @var int
   */
  var $status_code;
  /**
   * The HTTP headers returned with this response. This is an associative array whose keys are the header name and values are the header values.
   * @var array
   */
  var $headers = array();
  /**
   * Additional information about this response
   * @var array
   */
  var $info = array();
  /**
   * The entity body returned with the response
   * @var string
   */
  private $body;
  /**
   * The request that was responsible for generating this response
   * @var HttpRequest
   */
  var $request_uri;
  var $request_method;
  var $request_headers;
  var $request_body;
  /**
   * @access private
   */
  var $_is_cacheable;
  /**
   * @access private
   */
  var $_max_age;

  /**
   * Create a new instance of this class
   * @param int status_code the status code of the response
   */
  function __construct($status_code = null) {
    $this->status_code = $status_code;
  }

  public function __get($k){
    return $this->$k;
  }
  
  public function __set($k, $v){
    if($k=='body' AND $this->content_is_gzip_encoded()){
      //strip the gzip header, explicitly suppressing errors
	  if(ord(@$v[0]) == 0x1f && ord(@$v[1]) == 0x8b)
	  {
	    $v = substr($v,10);
	    $v = gzinflate($v);
      }
    }
    $this->$k=$v;
  }

  private function content_is_gzip_encoded(){
    return (isset($this->headers['content-encoding']) 
      AND 
      strpos($this->headers['content-encoding'], 'gzip')!==false);
  }

  /**
   * Tests whether the response indicates the request was successful
   * @return boolean true if the status code is between 200 and 299 inclusive, false otherwise
   */
  function is_success() {
    return $this->status_code >= 200 && $this->status_code < 300;
  }


  /**
   * Obtain a string representation of this response
   * @return string
   */
  function to_string() {
    $ret = $this->status_code . "\n";
    foreach ($this->headers as $k=>$v) {
      $ret .= "$k: $v\n";
    }
    $ret .= "\n";
    $ret .= $this->body;

    return $ret;
  }

  /**
   * Obtain an html representation of this response
   * @return string
   */
  function to_html()
  {
    if(!is_array($this->headers))
    $html = '<div xmlns:http="http://www.w3.org/2006/http#" about="#http-response-'.time().'" typeof="http:Response"><h3>HTTP Response</h3><dl><dt>Status Code</dt><dd rel="http:responseCode" resource="http://www.w3.org/2006/http#'.htmlentities($this->status_code).'">'.htmlentities($this->status_code).'</dd>';
    foreach($this->headers as $k => $v) $html.='<dt>'.htmlentities($k).'</dt><dd>'.htmlentities($v).'</dd>';
    $html.='<dt>Body</dt><dd class="http:body">'.htmlentities($this->body, 0, 'UTF-8').'</dd></dl></div>';
    return $html;
  }

  function to_turtle() {
    $turtle = '';
    $turtle .= '@prefix h: <http://www.w3.org/2006/http#> .';

    $turtle .= '_:request a ';

    if ($this->request_method == 'GET') {
      $turtle .= 'h:GetRequest';
    }
    else if ($this->request_method == 'POST') {
      $turtle .= 'h:PostRequest';
    }
    else if ($this->request_method == 'DELETE') {
      $turtle .= 'h:DeleteRequest';
    }
    else if ($this->request_method == 'PUT') {
      $turtle .= 'h:PutRequest';
    }
    else {
      $turtle .= 'h:Request';
    }

    $turtle .= ' ; h:requestURI "' . $this->request_uri . '"';
    $turtle .= ' ; h:response _:response';
    $turtle .= ' .';


    $turtle .= '_:response a h:Response';
    $turtle .= ' ; h:responseCode h:' . $this->status_code;

    foreach($this->headers as $k => $v) {
      $turtle .= '; h:header [ h:fieldName "'. $k. '" ; h:fieldValue "' . $v . '" .]';
    }
    $turtle .= ' .';


    return $turtle;
  }


  /**
   * Tests whether this response is suitable for caching
   * @return boolean true if the response can be cached, false otherwise
   */
  function is_cacheable() {
    if (!isset($this->_is_cacheable)) {
      if ( isset($this->headers['cache-control'])) {
        $cache_control = $this->headers['cache-control'];
        $cache_control_tokens = preg_split('/,/', $cache_control);
        foreach ( $cache_control_tokens as $token) {
          $token = trim($token);
          if ( preg_match('/private/', $token, $m) ) {
            $this->_is_cacheable = false;
            return false;
          }
          elseif ( preg_match('/no-cache/', $token, $m) ) {
            $this->_is_cacheable = false;
            return false;
          }
          elseif ( preg_match('/no-store/', $token, $m) ) {
            $this->_is_cacheable = false;
            return false;
          }
        }
      }


      if ( $this->status_code == 200 ||
      $this->status_code == 203 ||
      $this->status_code == 300 ||
      $this->status_code == 301 ||
      $this->status_code == 410 ) {
        $this->_is_cacheable = true;
      }
      else {
        $this->_is_cacheable = false;
      }
    }
    return $this->_is_cacheable;
  }


}
?>
