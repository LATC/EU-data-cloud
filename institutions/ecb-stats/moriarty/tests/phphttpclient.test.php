<?php
require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'constants.inc.php';
require_once MORIARTY_DIR . 'curlhttpclient.class.php';
require_once MORIARTY_DIR . 'httprequest.class.php';
require_once MORIARTY_TEST_DIR . 'fakecredentials.class.php';
require_once MORIARTY_TEST_DIR . 'fakecache.class.php';
require_once MORIARTY_TEST_DIR . 'fakehttprequest.class.php';

class PhpHttpClientTest extends PHPUnit_Framework_TestCase {
	
	function test_send_request_and_get_response()
	{
		$client = new CurlHttpClient();
		$request = new HttpRequest('GET', 'http://www.google.com/');
		$key = $client->send_request($request);
		$response = $client->get_response_for($key);
		$this->assertContains('Server: gws', $response);
	}
	
	function test_send_request_and_get_response_on_bad_server()
	{
		$client = new CurlHttpClient();
		$request = new HttpRequest('GET', 'http://i.do.not.exist.com/');
		$key = $client->send_request($request);
		$response = $client->get_response_for($key);
		//returns null, need to think about failure scenarios
		$this->assertNull($response);
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
		
		$this->assertContains('Server: gws', $google_response);
		$this->assertContains('X-XRDS-Location: http://open.login.yahooapis.com/openid20/www.yahoo.com/xrds', $yahoo_response);
	}
	
}

?>