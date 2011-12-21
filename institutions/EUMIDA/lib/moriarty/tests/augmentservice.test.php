<?php
require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'constants.inc.php';
require_once MORIARTY_TEST_DIR . 'fakecredentials.class.php';
require_once MORIARTY_DIR . 'augmentservice.class.php';

class AugmentServiceTest extends PHPUnit_Framework_TestCase {

  function test_augment_issues_gets_to_service_uri() {
    $fake_request_factory = new FakeRequestFactory();
    $fake_request = new FakeHttpRequest( new HttpResponse() );
    $fake_request_factory->register('GET', "http://example.org/store/services/augment?data-uri=http%3A%2F%2Fexample.org%2Ffoo", $fake_request );
    $ss = new AugmentService("http://example.org/store/services/augment");
    $ss->request_factory = $fake_request_factory;

    $response = $ss->augment( 'http://example.org/foo' );
    $this->assertTrue( $fake_request->was_executed() );
  }

  function test_augment_sets_accept() {
    $fake_request_factory = new FakeRequestFactory();
    $fake_request = new FakeHttpRequest( new HttpResponse() );
    $fake_request_factory->register('GET', "http://example.org/store/services/augment?data-uri=http%3A%2F%2Fexample.org%2Ffoo", $fake_request );
    $ss = new AugmentService("http://example.org/store/services/augment");
    $ss->request_factory = $fake_request_factory;

    $response = $ss->augment( 'http://example.org/foo' );
    $this->assertTrue( in_array('Accept: application/rss+xml', $fake_request->get_headers() ) );
  }

  function test_augment_graph_issues_post_to_service_uri() {
    $fake_request_factory = new FakeRequestFactory();
    $fake_request = new FakeHttpRequest( new HttpResponse() );
    $fake_request_factory->register('POST', "http://example.org/store/services/augment", $fake_request );
    $ss = new AugmentService("http://example.org/store/services/augment");
    $ss->request_factory = $fake_request_factory;

    $response = $ss->augment_graph( new SimpleGraph() );
    $this->assertTrue( $fake_request->was_executed() );
  }

  function test_augment_graph_sets_accept() {
    $fake_request_factory = new FakeRequestFactory();
    $fake_request = new FakeHttpRequest( new HttpResponse() );
    $fake_request_factory->register('POST', "http://example.org/store/services/augment", $fake_request );
    $ss = new AugmentService("http://example.org/store/services/augment");
    $ss->request_factory = $fake_request_factory;

    $response = $ss->augment_graph( new SimpleGraph() );
    $this->assertTrue( in_array('Accept: application/rss+xml', $fake_request->get_headers() ) );
  }

  function test_augment_graph_sets_content_type() {
    $fake_request_factory = new FakeRequestFactory();
    $fake_request = new FakeHttpRequest( new HttpResponse() );
    $fake_request_factory->register('POST', "http://example.org/store/services/augment", $fake_request );
    $ss = new AugmentService("http://example.org/store/services/augment");
    $ss->request_factory = $fake_request_factory;

    $response = $ss->augment_graph( new SimpleGraph() );
    $this->assertTrue( in_array('Content-Type: application/rss+xml', $fake_request->get_headers() ) );
  }
  
}
?>
