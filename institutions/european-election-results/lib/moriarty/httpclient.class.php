<?php 
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'moriarty.inc.php';
require_once MORIARTY_DIR . 'phphttpclient.class.php';
require_once MORIARTY_DIR . 'curlhttpclient.class.php';

abstract class HttpClient
{
	private static $_instance;
	
	public static function Create()
	{
		if (HttpClient::$_instance != null)
		{
			return HttpClient::$_instance;
		}
		
		if (class_exists('http_class', false) && class_exists('sasl_interact_class', false)) {
			HttpClient::$_instance = new PhpHttpClient(); 
			return HttpClient::$_instance;
		}
		else if (function_exists('curl_init'))
		{
			HttpClient::$_instance = new CurlHttpClient();
			return HttpClient::$_instance;
		}
		
		throw new Exception('HttpClient could not find an underlying client library to use. Install Curl support or Manuel Lemos\' HttpClient');		
	}
	
	public abstract function send_request($request);
	public abstract function get_response_for($request);
	
	//to add in later for non-blocking check of response readiness
	//public abstract function checkResponseFor($request);
}


?>