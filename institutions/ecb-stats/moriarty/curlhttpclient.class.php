<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'moriarty.inc.php';
require_once MORIARTY_DIR . 'httpcache.class.php';
require_once MORIARTY_DIR . 'httprequest.class.php';
require_once MORIARTY_DIR . 'httpresponse.class.php';

/**
 * A CURL based http client implementation.
 */
class CurlHttpClient extends HttpClient
{
  protected $curl_handles = array();
  protected $requests = array();
  protected $responses = array();
  protected $multicurl;
  protected $running;

  public function __construct()
  {
    $this->multicurl = curl_multi_init();
  }

  public function send_request($request)
  {
    $curl_handle = curl_init($request->uri);
    $key = (string)$curl_handle;
    $this->requests[$key] = $request;
    $this->curl_handles[$key] = $curl_handle;

    if ($request->credentials != null) {
      curl_setopt($curl_handle, CURLOPT_HTTPAUTH, CURLAUTH_DIGEST);
      curl_setopt($curl_handle, CURLOPT_USERPWD, $request->credentials->get_auth());
    }
    // curl_setopt($curl_handle, CURLOPT_VERBOSE, 1);

    curl_setopt($curl_handle, CURLOPT_FRESH_CONNECT,TRUE);
    curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER,1);
    curl_setopt($curl_handle, CURLOPT_BINARYTRANSFER, TRUE);
    /**
     * @see http://bugs.typo3.org/view.php?id=4292
     */

    if ( !(ini_get('open_basedir')) && ini_get('safe_mode') !== 'On')
    {
        curl_setopt($curl_handle, CURLOPT_FOLLOWLOCATION, TRUE);
    }

    curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($curl_handle, CURLOPT_TIMEOUT, 600);
    curl_setopt($curl_handle, CURLOPT_HEADER, 1);

    if ( !empty( $request->_proxy ) ) {
      curl_setopt($curl_handle, CURLOPT_PROXY, $request->_proxy );
    }

    switch($request->method) {
      case 'GET'  : break;
      case 'POST' : curl_setopt($curl_handle, CURLOPT_POST, 1); break;
      case 'HEAD' : curl_setopt($curl_handle, CURLOPT_NOBODY, 1); break;
      default     : curl_setopt($curl_handle, CURLOPT_CUSTOMREQUEST,strtoupper($request->method));
    }

    if ($request->body != null) {
      curl_setopt($curl_handle, CURLOPT_POSTFIELDS, $request->body);
    }

    curl_setopt($curl_handle, CURLOPT_HTTPHEADER, $request->get_headers() );

    $res = curl_multi_add_handle($this->multicurl, $curl_handle);

    if($res === CURLM_OK || $res === CURLM_CALL_MULTI_PERFORM)
    {
      do
      {
        $this->execStatus = curl_multi_exec($this->multicurl, $this->running);
      }
      while ($this->execStatus === CURLM_CALL_MULTI_PERFORM);
    }

    return $key;
  }

  public function get_response_for($key)
  {
    if(isset($this->responses[$key]))
    {
      return $this->responses[$key];
    }

    do
    {
      curl_multi_select($this->multicurl, 2);
      do
      {
        $this->execStatus = curl_multi_exec($this->multicurl, $this->running);
      }
      while ($this->execStatus === CURLM_CALL_MULTI_PERFORM);
      while($msg = curl_multi_info_read($this->multicurl))
      {
        $this->processCurlMessage($msg);
      }
      if(isset($this->responses[$key]))
      {
        return $this->responses[$key];
      }
    }
    while (count($this->curl_handles) > 0);

    throw new Exception("Request did not return");

    }

    protected function processCurlMessage($done)
    {
      $key = (string)$done['handle'];
      $raw_response = curl_multi_getcontent($done['handle']);
      $response_info = curl_getinfo($done['handle']);

      list($response_code,$response_headers,$response_body) = $this->parse_response($raw_response);

      $response = new HttpResponse();
      $response->status_code = $response_code;
      $response->headers = $response_headers;
      $response->body = $response_body;
      $response->info = $response_info;
//ID20100317      $response->request = $this->requests[$key];
      $response->request_method = $this->requests[$key]->method;
      $response->request_uri = $this->requests[$key]->uri;
      $response->request_headers = $this->requests[$key]->headers;
      $response->request_body = $this->requests[$key]->body;

      $this->responses[$key] = $response;

      curl_multi_remove_handle($this->multicurl, $done['handle']);
      curl_close($done['handle']);
      unset($this->curl_handles[$key]);
    }

    /**
     * @access private
     */
    function parse_response($response){
      /*
       ***original code extracted from examples at
       ***http://www.webreference.com/programming
       /php/cookbook/chap11/1/3.html

       ***returns an array in the following format which varies depending on headers returned

       [0] => the HTTP error or response code such as 404
       [1] => Array
       (
       [Server] => Microsoft-IIS/5.0
       [Date] => Wed, 28 Apr 2004 23:29:20 GMT
       [X-Powered-By] => ASP.NET
       [Connection] => close
       [Set-Cookie] => COOKIESTUFF
       [Expires] => Thu, 01 Dec 1994 16:00:00 GMT
       [Content-Type] => text/html
       [Content-Length] => 4040
       )
       [2] => Response body (string)
       */

      do
      {
        if ( strstr($response, "\r\n\r\n") == FALSE) {
          $response_headers = $response;
          $response = '';
        }
        else {
          list($response_headers,$response) = explode("\r\n\r\n",$response,2);
        }
        $response_header_lines = explode("\r\n",$response_headers);

        // first line of headers is the HTTP response code
        $http_response_line = array_shift($response_header_lines);
        if (preg_match('@^HTTP/[0-9]\.[0-9] ([0-9]{3})@',$http_response_line,
        $matches)) {
          $response_code = $matches[1];
        }
        else
        {
          $response_code = "Error";
        }
      }
      while (preg_match('@^HTTP/[0-9]\.[0-9] ([0-9]{3})@',$response));

      $response_body = $response;

      // put the rest of the headers in an array
      $response_header_array = array();
      foreach ($response_header_lines as $header_line) {
        list($header,$value) = explode(': ',$header_line,2);
        $response_header_array[strtolower($header)] = $value;
      }

      return array($response_code,$response_header_array,$response_body);
    }
}
?>
