<?php
require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'constants.inc.php';
require_once MORIARTY_DIR . 'graph.class.php';
require_once MORIARTY_TEST_DIR . 'fakecredentials.class.php';

class GraphTest extends PHPUnit_Framework_TestCase {
  var $_empty_changeset = '<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:cs="http://purl.org/vocab/changeset/schema#">
  <rdf:Description rdf:nodeID="cs">
    <rdf:type rdf:resource="http://purl.org/vocab/changeset/schema#ChangeSet" />
    <cs:subjectOfChange rdf:nodeID="a" />
    <cs:creatorName>Ian</cs:creatorName>
    <cs:changeReason>PHP Client Test</cs:changeReason>
  </rdf:Description>
</rdf:RDF>';

  var $_rdfxml_doc = '<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:foaf="http://xmlns.com/foaf/0.1/">
  <foaf:Person>
    <foaf:name>scooby</foaf:name>
  </foaf:Person>
</rdf:RDF>';

  var $_turtle_doc = '@prefix foaf: <http://xmlns.com/foaf/0.1/> . [] a foaf:Person ; foaf:name "scooby" .';

  function make_graph($uri, $credentials = null) {
    // abstract
  }

  function test_apply_changeset_rdfxml_posts_to_metabox_uri() {
    $fake_request_factory = new FakeRequestFactory();
    $fake_request = new FakeHttpRequest( new HttpResponse() );
    $fake_request_factory->register('POST', "http://example.org/store/meta", $fake_request );

    $g = $this->make_graph("http://example.org/store/meta");
    $g->request_factory = $fake_request_factory;

    $response = $g->apply_changeset_rdfxml( $this->_empty_changeset );
    $this->assertTrue( $fake_request->was_executed() );
  }

  function test_apply_changeset_rdfxml_posts_supplied_rdfxml() {
    $fake_request_factory = new FakeRequestFactory();
    $fake_request = new FakeHttpRequest( new HttpResponse() );
    $fake_request_factory->register('POST', "http://example.org/store/meta", $fake_request );

    $g = $this->make_graph("http://example.org/store/meta");
    $g->request_factory = $fake_request_factory;

    $response = $g->apply_changeset_rdfxml( $this->_empty_changeset );
    $this->assertEquals( $this->_empty_changeset , $fake_request->get_body() );
  }

  function test_apply_changeset_rdfxml_sets_content_type() {
    $fake_request_factory = new FakeRequestFactory();
    $fake_request = new FakeHttpRequest( new HttpResponse() );
    $fake_request_factory->register('POST', "http://example.org/store/meta", $fake_request );

    $g = $this->make_graph("http://example.org/store/meta");
    $g->request_factory = $fake_request_factory;

    $response = $g->apply_changeset_rdfxml( $this->_empty_changeset );
    $this->assertTrue( in_array('Content-Type: application/vnd.talis.changeset+xml', $fake_request->get_headers() ) );
  }

  function test_apply_changeset_rdfxml_sets_accept() {
    $fake_request_factory = new FakeRequestFactory();
    $fake_request = new FakeHttpRequest( new HttpResponse() );
    $fake_request_factory->register('POST', "http://example.org/store/meta", $fake_request );

    $g = $this->make_graph("http://example.org/store/meta");
    $g->request_factory = $fake_request_factory;

    $response = $g->apply_changeset_rdfxml( $this->_empty_changeset );
    $this->assertTrue( in_array('Accept: */*', $fake_request->get_headers() ) );
  }



  function test_apply_versioned_changeset_rdfxml_posts_to_metabox_uri() {
    $fake_request_factory = new FakeRequestFactory();
    $fake_request = new FakeHttpRequest( new HttpResponse() );
    $fake_request_factory->register('POST', "http://example.org/store/meta/changesets", $fake_request );

    $g = $this->make_graph("http://example.org/store/meta");
    $g->request_factory = $fake_request_factory;

    $response = $g->apply_versioned_changeset_rdfxml( $this->_empty_changeset );
    $this->assertTrue( $fake_request->was_executed() );
  }

  function test_apply_versioned_changeset_rdfxml_posts_supplied_rdfxml() {
    $fake_request_factory = new FakeRequestFactory();
    $fake_request = new FakeHttpRequest( new HttpResponse() );
    $fake_request_factory->register('POST', "http://example.org/store/meta/changesets", $fake_request );

    $g = $this->make_graph("http://example.org/store/meta");
    $g->request_factory = $fake_request_factory;

    $response = $g->apply_versioned_changeset_rdfxml( $this->_empty_changeset );
    $this->assertEquals( $this->_empty_changeset , $fake_request->get_body() );
  }

  function test_apply_versioned_changeset_rdfxml_sets_content_type() {
    $fake_request_factory = new FakeRequestFactory();
    $fake_request = new FakeHttpRequest( new HttpResponse() );
    $fake_request_factory->register('POST', "http://example.org/store/meta/changesets", $fake_request );

    $g = $this->make_graph("http://example.org/store/meta");
    $g->request_factory = $fake_request_factory;

    $response = $g->apply_versioned_changeset_rdfxml( $this->_empty_changeset );
    $this->assertTrue( in_array('Content-Type: application/vnd.talis.changeset+xml', $fake_request->get_headers() ) );
  }

  function test_apply_versioned_changeset_rdfxml_sets_accept() {
    $fake_request_factory = new FakeRequestFactory();
    $fake_request = new FakeHttpRequest( new HttpResponse() );
    $fake_request_factory->register('POST', "http://example.org/store/meta/changesets", $fake_request );

    $g = $this->make_graph("http://example.org/store/meta");
    $g->request_factory = $fake_request_factory;

    $response = $g->apply_versioned_changeset_rdfxml( $this->_empty_changeset );
    $this->assertTrue( in_array('Accept: */*', $fake_request->get_headers() ) );
  }


  function test_submit_rdfxml_posts_to_metabox_uri() {
    $fake_request_factory = new FakeRequestFactory();
    $fake_request = new FakeHttpRequest( new HttpResponse() );
    $fake_request_factory->register('POST', "http://example.org/store/meta", $fake_request );

    $g = $this->make_graph("http://example.org/store/meta");
    $g->request_factory = $fake_request_factory;

    $response = $g->submit_rdfxml( $this->_rdfxml_doc );
    $this->assertTrue( $fake_request->was_executed() );
  }

  function test_submit_rdfxml_posts_supplied_rdfxml() {
    $fake_request_factory = new FakeRequestFactory();
    $fake_request = new FakeHttpRequest( new HttpResponse() );
    $fake_request_factory->register('POST', "http://example.org/store/meta", $fake_request );

    $g = $this->make_graph("http://example.org/store/meta");
    $g->request_factory = $fake_request_factory;

    $response = $g->submit_rdfxml( $this->_rdfxml_doc );
    $this->assertEquals( $this->_rdfxml_doc , $fake_request->get_body() );
  }

  function test_submit_rdfxml_sets_content_type() {
    $fake_request_factory = new FakeRequestFactory();
    $fake_request = new FakeHttpRequest( new HttpResponse() );
    $fake_request_factory->register('POST', "http://example.org/store/meta", $fake_request );

    $g = $this->make_graph("http://example.org/store/meta");
    $g->request_factory = $fake_request_factory;

    $response = $g->submit_rdfxml( $this->_rdfxml_doc );
    $this->assertTrue( in_array('Content-Type: application/rdf+xml', $fake_request->get_headers() ) );
  }

  function test_submit_rdfxml_sets_accept() {
    $fake_request_factory = new FakeRequestFactory();
    $fake_request = new FakeHttpRequest( new HttpResponse() );
    $fake_request_factory->register('POST', "http://example.org/store/meta", $fake_request );

    $g = $this->make_graph("http://example.org/store/meta");
    $g->request_factory = $fake_request_factory;

    $response = $g->submit_rdfxml( $this->_rdfxml_doc );
    $this->assertTrue( in_array('Accept: */*', $fake_request->get_headers() ) );
  }

  function test_apply_changeset_rdfxml_uses_credentials() {
    $fake_request_factory = new FakeRequestFactory();
    $fake_request = new FakeHttpRequest( new HttpResponse() );
    $fake_request_factory->register('POST', "http://example.org/store/meta", $fake_request );

    $g = $this->make_graph("http://example.org/store/meta", new FakeCredentials());
    $g->request_factory = $fake_request_factory;

    $response = $g->apply_changeset_rdfxml( $this->_empty_changeset );
    $this->assertEquals( "user:pwd" , $fake_request->get_auth() );
  }

  function test_submit_rdfxml_uses_credentials() {
    $fake_request_factory = new FakeRequestFactory();
    $fake_request = new FakeHttpRequest( new HttpResponse() );
    $fake_request_factory->register('POST', "http://example.org/store/meta", $fake_request );

    $g = $this->make_graph("http://example.org/store/meta", new FakeCredentials());
    $g->request_factory = $fake_request_factory;

    $response = $g->submit_rdfxml( $this->_rdfxml_doc );
    $this->assertEquals( "user:pwd" , $fake_request->get_auth() );
  }

  function test_describe_performs_get() {
    $fake_request_factory = new FakeRequestFactory();
    $fake_request = new FakeHttpRequest( new HttpResponse() );
    $fake_request_factory->register('GET', "http://example.org/store/meta?about=" . urlencode('http://example.org/scooby'), $fake_request );

    $g = $this->make_graph("http://example.org/store/meta", new FakeCredentials());
    $g->request_factory = $fake_request_factory;

    $response = $g->describe( 'http://example.org/scooby' );
    $this->assertTrue( $fake_request->was_executed() );
  }

  function test_describe_sets_accept() {
    $fake_request_factory = new FakeRequestFactory();
    $fake_request = new FakeHttpRequest( new HttpResponse() );
    $fake_request_factory->register('GET', "http://example.org/store/meta?about=" . urlencode('http://example.org/scooby'), $fake_request );

    $g = $this->make_graph("http://example.org/store/meta", new FakeCredentials());
    $g->request_factory = $fake_request_factory;

    $response = $g->describe( 'http://example.org/scooby' );
    $this->assertTrue( in_array('Accept: application/rdf+xml', $fake_request->get_headers() ) );
  }

  function test_describe_sets_content_type() {
    $fake_request_factory = new FakeRequestFactory();
    $fake_request = new FakeHttpRequest( new HttpResponse() );
    $fake_request_factory->register('GET', "http://example.org/store/meta?about=" . urlencode('http://example.org/scooby'), $fake_request );

    $g = $this->make_graph("http://example.org/store/meta", new FakeCredentials());
    $g->request_factory = $fake_request_factory;

    $response = $g->describe( 'http://example.org/scooby' );
    $this->assertTrue( in_array('Content-Type: application/x-www-form-urlencoded', $fake_request->get_headers() ) );
  }

  function test_describe_uses_output_param() {
    $fake_request_factory = new FakeRequestFactory();
    $fake_request = new FakeHttpRequest( new HttpResponse() );
    $fake_request_factory->register('GET', "http://example.org/store/meta?about=" . urlencode('http://example.org/scooby') . "&output=turtle", $fake_request );

    $g = $this->make_graph("http://example.org/store/meta", new FakeCredentials());
    $g->request_factory = $fake_request_factory;

    $response = $g->describe( 'http://example.org/scooby', 'turtle' );
    $this->assertTrue( $fake_request->was_executed() );
  }

  function test_has_description_performs_head() {
    $fake_request_factory = new FakeRequestFactory();
    $fake_request = new FakeHttpRequest( new HttpResponse() );
    $fake_request_factory->register('GET', "http://example.org/store/meta?about=" . urlencode('http://example.org/scooby'), $fake_request );

    $g = $this->make_graph("http://example.org/store/meta", new FakeCredentials());
    $g->request_factory = $fake_request_factory;

    $response = $g->has_description( 'http://example.org/scooby' );
    $this->assertTrue( $fake_request->was_executed() );
  }

  function test_has_description_sets_accept() {
    $fake_request_factory = new FakeRequestFactory();
    $fake_request = new FakeHttpRequest( new HttpResponse() );
    $fake_request_factory->register('GET', "http://example.org/store/meta?about=" . urlencode('http://example.org/scooby'), $fake_request );

    $g = $this->make_graph("http://example.org/store/meta", new FakeCredentials());
    $g->request_factory = $fake_request_factory;

    $response = $g->has_description( 'http://example.org/scooby' );
    $this->assertTrue( in_array('Accept: application/rdf+xml', $fake_request->get_headers() ) );
  }

  function test_has_description_sets_if_match() {
    $fake_request_factory = new FakeRequestFactory();
    $fake_request = new FakeHttpRequest( new HttpResponse() );
    $fake_request_factory->register('GET', "http://example.org/store/meta?about=" . urlencode('http://example.org/scooby'), $fake_request );

    $g = $this->make_graph("http://example.org/store/meta", new FakeCredentials());
    $g->request_factory = $fake_request_factory;

    $response = $g->has_description( 'http://example.org/scooby' );
    $this->assertTrue( in_array('If-Match: *', $fake_request->get_headers() ) );
  }

  function test_has_description_returns_true_for_200_response() {
    $fake_request_factory = new FakeRequestFactory();
    $fake_request = new FakeHttpRequest( new HttpResponse(200) );
    $fake_request_factory->register('GET', "http://example.org/store/meta?about=" . urlencode('http://example.org/scooby'), $fake_request );

    $g = $this->make_graph("http://example.org/store/meta", new FakeCredentials());
    $g->request_factory = $fake_request_factory;

    $response = $g->has_description( 'http://example.org/scooby' );
    $this->assertTrue( $response );
  }

  function test_has_description_returns_true_for_412_response() {
    $fake_request_factory = new FakeRequestFactory();
    $fake_request = new FakeHttpRequest( new HttpResponse(412) );
    $fake_request_factory->register('GET', "http://example.org/store/meta?about=" . urlencode('http://example.org/scooby'), $fake_request );

    $g = $this->make_graph("http://example.org/store/meta", new FakeCredentials());
    $g->request_factory = $fake_request_factory;

    $response = $g->has_description( 'http://example.org/scooby' );
    $this->assertFalse( $response );
  }


  function test_submit_turtle_posts_to_metabox_uri() {
    $fake_request_factory = new FakeRequestFactory();
    $fake_request = new FakeHttpRequest( new HttpResponse() );
    $fake_request_factory->register('POST', "http://example.org/store/meta", $fake_request );

    $g = $this->make_graph("http://example.org/store/meta");
    $g->request_factory = $fake_request_factory;

    $response = $g->submit_turtle( $this->_turtle_doc );
    $this->assertTrue( $fake_request->was_executed() );
  }

  function test_submit_turtle_posts_supplied_turtle() {
    $fake_request_factory = new FakeRequestFactory();
    $fake_request = new FakeHttpRequest( new HttpResponse() );
    $fake_request_factory->register('POST', "http://example.org/store/meta", $fake_request );

    $g = $this->make_graph("http://example.org/store/meta");
    $g->request_factory = $fake_request_factory;

    $response = $g->submit_turtle( $this->_turtle_doc );
    $this->assertEquals( $this->_turtle_doc , $fake_request->get_body() );
  }

  function test_submit_turtle_sets_content_type() {
    $fake_request_factory = new FakeRequestFactory();
    $fake_request = new FakeHttpRequest( new HttpResponse() );
    $fake_request_factory->register('POST', "http://example.org/store/meta", $fake_request );

    $g = $this->make_graph("http://example.org/store/meta");
    $g->request_factory = $fake_request_factory;

    $response = $g->submit_turtle( $this->_turtle_doc );
    $this->assertTrue( in_array('Content-Type: text/turtle', $fake_request->get_headers() ) );
  }

  function test_submit_turtle_sets_accept() {
    $fake_request_factory = new FakeRequestFactory();
    $fake_request = new FakeHttpRequest( new HttpResponse() );
    $fake_request_factory->register('POST', "http://example.org/store/meta", $fake_request );

    $g = $this->make_graph("http://example.org/store/meta");
    $g->request_factory = $fake_request_factory;

    $response = $g->submit_turtle( $this->_turtle_doc );
    $this->assertTrue( in_array('Accept: */*', $fake_request->get_headers() ) );
  }

  function test_submit_ntriples_from_file_in_batches(){
    $response = new HttpResponse('202');
    $graph = $this->getMock('Graph', array('submit_turtle'), array(),'',false ); 
    $graph->expects($this->exactly(10))
      ->method('submit_turtle')
      ->will($this->returnValue($response));
    $filename = dirname(__FILE__).'/documents/10-ntriples.nt';
    $result = $graph->submit_ntriples_in_batches_from_file($filename,1);
    $this->assertEquals(count($result), 10);
  }
  function test_submit_ntriples_from_file_in_batches_stop_on_failure(){
    $response = new HttpResponse('500');
    $graph = $this->getMock('Graph', array('submit_turtle'), array(),'',false ); 
    $graph->expects($this->exactly(1))
      ->method('submit_turtle')
      ->will($this->returnValue($response));
    $filename = dirname(__FILE__).'/documents/10-ntriples.nt';
    $result = $graph->submit_ntriples_in_batches_from_file($filename,1,true);
    $this->assertEquals(count($result), 1);
  }

  function test_mirror_from_uri(){
  
   $url =  "http://example.org/web-page";
    $fake_request_factory = new FakeRequestFactory();
    $webpage_response =  new HttpResponse('200') ;
    $webpage_response->body = file_get_contents(dirname(__FILE__).DIRECTORY_SEPARATOR.'documents/after.ttl');
    $fake_webpage_request = new FakeHttpRequest($webpage_response);
    $fake_request_factory->register('GET',$url, $fake_webpage_request );


    $contentbox_copy =  new HttpResponse('200');
    $contentbox_copy->body = file_get_contents(dirname(__FILE__).DIRECTORY_SEPARATOR.'documents/before.ttl');
    $fake_copy_request = new FakeHttpRequest($contentbox_copy);
    $fake_request_factory->register('GET','http://api.talis.com/stores/example/meta?about='.urlencode($url).'&output=json' , $fake_copy_request);

       $postDataResponse =  new HttpResponse('201');
    $fake_postData_request = new FakeHttpRequest($postDataResponse);
    $fake_request_factory->register('POST', 'http://api.talis.com/stores/example/meta' , $fake_postData_request );

    $graph = new Graph("http://api.talis.com/stores/example/meta", new FakeCredentials(), $fake_request_factory);
    $response = $graph->mirror_from_uri($url);
    $this->assertTrue($fake_webpage_request->was_executed(), "The webpage $url should be retrieved over HTTP");
    $this->assertTrue($fake_copy_request->was_executed(), "");
    $this->assertTrue($fake_postData_request->was_executed(), "The data from $url  (and its metadata) should be added to the store by POSTing a document containing changesets to /meta");
    
    $expected_response =  array(
        'get_page' => $webpage_response,
        'get_copy' => $contentbox_copy,
        'update_data' => $postDataResponse,
        'success' => true,
      );
     $this->assertEquals($expected_response, $response,""); 
 
  }

}


?>
