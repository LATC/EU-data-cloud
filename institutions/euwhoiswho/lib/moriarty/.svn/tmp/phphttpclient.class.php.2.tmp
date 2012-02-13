<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'moriarty.inc.php';
require_once MORIARTY_DIR . 'httpcache.class.php';
require_once MORIARTY_DIR . 'httprequest.class.php';
require_once MORIARTY_DIR . 'httpresponse.class.php';

/**
 * A CURL based http client implementation.
 */
class PhpHttpClient extends HttpClient
{
  private $responses = array();

  public function send_request($request)
  {
    $response_code = '0';
    $response_info = array();
    $response_headers = array();
    $error = '';

    $http=new http_class;
    $http->follow_redirect = 1;
    $http->redirection_limit = 5;
    $http->prefer_curl = 0;

    $error = $http->GetRequestArguments($request->uri, $arguments);

    if ($request->credentials != null) {
      $http->authentication_mechanism = "Digest";
      $arguments['AuthUser'] = $request->credentials->get_username();
      $arguments['AuthPassword'] = $request->credentials->get_password();
    }

    $arguments["RequestMethod"] = $request->method;

    foreach ($request->headers as $k => $v) {
      $arguments["Headers"][$k] = $v;
    }

    if ($request->body != null) {
      $arguments["Body"] = $request->body;
    }

    $error = $http->Open($arguments);
    if (! $error) {
      $error = $http->SendRequest($arguments);
    }

    if ( ! $error ) {
      $error = $http->ReadReplyHeaders($response_headers);
      $response_code = $http->response_status;
      $response_body = '';

      for(;;) {
        $error=$http->ReadReplyBody($body,1000);
        if($error!="" || strlen($body)==0)
        break;
        $response_body .= $body;
      }
    }
    else {
      if ( $request->_cache  && $cached_response) {
        return $cached_response;
      }
      $response_body = "Request failed: " . $error;
    }

    $http->Close();

    $response = new HttpResponse();
    $response->status_code = $response_code;
    $response->headers = $response_headers;
    $response->body = $response_body;
    $response->info = $response_info;
//ID20100317    $response->request = $request;
    $response->request_method = $request->method;
    $response->request_uri = $request->uri;
    $response->request_headers = $request->headers;
    $response->request_body = $request->body;

    $key = spl_object_hash($request);
    $this->responses[$key] = $response;

    return $key;

  }

  public function get_response_for($key)
  {
    return @$this->responses[$key];
  }
}
?>