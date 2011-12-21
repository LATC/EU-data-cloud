<?php
require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'constants.inc.php';
require_once MORIARTY_DIR . 'curlhttpclient.class.php';
require_once MORIARTY_DIR . 'httprequest.class.php';
require_once MORIARTY_TEST_DIR . 'fakecredentials.class.php';
require_once MORIARTY_TEST_DIR . 'fakecache.class.php';
require_once MORIARTY_TEST_DIR . 'fakehttprequest.class.php';

class CurlHttpClientTest extends PHPUnit_Framework_TestCase {

	function test_send_request_and_get_response()
	{
		$client = new CurlHttpClient();
		$request = new HttpRequest('GET', 'http://www.google.com/');
		$key = $client->send_request($request);
		$response = $client->get_response_for($key);
		$this->assertEquals('gws', $response->headers['server']);
	}

	function test_send_request_and_get_response_on_bad_server()
	{
		$client = new CurlHttpClient();
		$request = new HttpRequest('GET', 'http://i.do.not.exist.com/');
		$key = $client->send_request($request);
		$response = $client->get_response_for($key);
		$this->assertEquals('Error', $response->status_code);
	}

	function test_send_request_and_get_response_concurrently()
	{
		$client = new CurlHttpClient();

		$google_request = new HttpRequest('GET', 'http://www.google.com/');
		$yahoo_request = new HttpRequest('GET', 'http://www.yahoo.com/');

		$google_key = $client->send_request($google_request);
		$yahoo_key = $client->send_request($yahoo_request);

		$yahoo_response = $client->get_response_for($yahoo_key);
		$google_response = $client->get_response_for($google_key);

		$this->assertEquals('gws', $google_response->headers['server']);
		$this->assertEquals('YTS/1.20.0', $yahoo_response->headers['server']);
	}

	function test_send_request_with_head_method()
	{
		$client = new CurlHttpClient();
		$google_request = new HttpRequest('HEAD', 'http://www.google.com/');
		$google_key = $client->send_request($google_request);
		$google_response = $client->get_response_for($google_key);
		$this->assertEquals('gws', $google_response->headers['server']);
	}

	function test_parse_response_parses_all_responses() {
		$client = new CurlHttpClient();
		
		//Warning - the line endings in this string must be \r\n line endings.
		$server_response = 'HTTP/1.1 401 Unauthorized
Via: 1.1 DORY
Connection: Keep-Alive
Proxy-Support: Session-Based-Authentication
Connection: Proxy-Support
Content-Length: 12
Date: Wed, 03 Oct 2007 00:15:59 GMT
Content-Type: text/plain; charset=UTF-8
WWW-Authenticate: Digest realm=&quot;bigfoot&quot;, domain=&quot;null&quot;, nonce=&quot;8Q84YxUBAABJP5W9FaNm7Fli2QGGO99o&quot;, algorithm=MD5, qop=&quot;auth&quot;

HTTP/1.1 200 OK
Via: 1.1 DORY
Connection: Keep-Alive
Proxy-Connection: Keep-Alive
Content-Length: 3
Date: Wed, 03 Oct 2007 00:15:59 GMT
content-type: text/html; charset=UTF-8
Server: Bigfoot/5.282.18209
Cache-Control: max-age=7200, must-revalidate

foo';

		list($response_code,$response_headers,$response_body) = $client->parse_response($server_response);
		$this->assertEquals( 200, $response_code);

	}

}

?>