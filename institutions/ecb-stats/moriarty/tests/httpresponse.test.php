<?php
require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'constants.inc.php';
require_once MORIARTY_DIR . 'httpresponse.class.php';
require_once MORIARTY_TEST_DIR . 'fakecredentials.class.php';
require_once MORIARTY_TEST_DIR . 'fakecache.class.php';

class HttpResponseTest extends PHPUnit_Framework_TestCase {

	private $old_error_reporting = 0;
	
	// switch off error suppression while we test test_set_body_empty
	// because the error reporting in an end-user environment can't be guaranteed
	// after the tests, restore the error reporting
	
	function setUp() {
		$this->old_error_reporting = error_reporting();	
	  	error_reporting(E_ALL);
	}
	
	function tearDown() {
	  	error_reporting($this->old_error_reporting);	
	}
	
  function test_set_body_with_empty_string(){

    $response = new HttpResponse(200);
    
    $headers = array();
    $headers['content-encoding'] = 'gzip';
    $response->headers = $headers;
    $response->body = "";

    $this->assertTrue($response->is_success(), 'Should have handled empty body without causing error');
  }
}
?>
