<?php
require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'constants.inc.php';
require_once MORIARTY_DIR . 'httpresponse.class.php';
require_once MORIARTY_TEST_DIR . 'fakecredentials.class.php';
require_once MORIARTY_TEST_DIR . 'fakecache.class.php';

class HttpResponseTest extends PHPUnit_Framework_TestCase {


  function test_get_content_type(){

    $response = new HttpResponse('200');
    $response->headers = array('content-type'=> 'text/html');
    $this->assertEquals('text/html', $response->get_content_type(), 'Content type should be text/html');
    $response->headers = array('Content-Type'=> 'text/xml');
    $this->assertEquals('text/xml', $response->get_content_type(), 'Content type should be text/xml');
  
  }
}
?>
