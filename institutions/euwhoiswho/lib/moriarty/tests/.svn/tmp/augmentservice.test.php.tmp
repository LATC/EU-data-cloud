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
  
  function test_augment_graph_creates_rss_channel() {
    $fake_request_factory = new FakeRequestFactory();
    $fake_request = new FakeHttpRequest( new HttpResponse() );
    $fake_request_factory->register('POST', "http://example.org/store/services/augment", $fake_request );
    $ss = new AugmentService("http://example.org/store/services/augment");
    $ss->request_factory = $fake_request_factory;

    $response = $ss->augment_graph( new SimpleGraph() );
    
    $data = new SimpleGraph();
    $data->from_rdfxml($fake_request->get_body());
    
    $this->assertTrue( $data->has_resource_triple('tag:talis.com,2008:moriarty-tmp-augment-channel', RDF_TYPE, 'http://purl.org/rss/1.0/channel'));
    
      
  }

  function test_augment_graph_creates_items_list() {
    $fake_request_factory = new FakeRequestFactory();
    $fake_request = new FakeHttpRequest( new HttpResponse() );
    $fake_request_factory->register('POST', "http://example.org/store/services/augment", $fake_request );
    $ss = new AugmentService("http://example.org/store/services/augment");
    $ss->request_factory = $fake_request_factory;

    $response = $ss->augment_graph( new SimpleGraph() );
    
    $data = new SimpleGraph();
    $data->from_rdfxml($fake_request->get_body());
    
    $this->assertTrue( $data->has_resource_triple('tag:talis.com,2008:moriarty-tmp-augment-channel', RSS_ITEMS, 'tag:talis.com,2008:moriarty-tmp-augment-channel-items'));
    $this->assertTrue( $data->has_resource_triple('tag:talis.com,2008:moriarty-tmp-augment-channel-items', RDF_TYPE, 'http://www.w3.org/1999/02/22-rdf-syntax-ns#Seq'));
  }
  
/*  
  function test_augment_graph_creates_item_for_each_subject_in_graph() {
    $fake_request_factory = new FakeRequestFactory();
    $fake_request = new FakeHttpRequest( new HttpResponse() );
    $fake_request_factory->register('POST', "http://example.org/store/services/augment", $fake_request );
    $ss = new AugmentService("http://example.org/store/services/augment");
    $ss->request_factory = $fake_request_factory;

    $g = new SimpleGraph();
    $g->add_resource_triple('http://example.org/subj', 'http://example.org/pred', 'http://example.org/obj');
    $response = $ss->augment_graph( $g );
    
    $data = new SimpleGraph();
    $data->from_rdfxml($fake_request->get_body());
    
    $this->assertTrue( $data->has_resource_triple('tag:talis.com,2008:moriarty-tmp-augment-channel-items', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#_1', 'http://example.org/subj'));
    $this->assertTrue( $data->has_resource_triple('http://example.org/subj', 'http://example.org/pred', 'http://example.org/obj'));
  }  
*/
}
?>
