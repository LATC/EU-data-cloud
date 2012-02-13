<?php
require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'constants.inc.php';
require_once MORIARTY_DIR . 'store.class.php';
require_once MORIARTY_DIR . 'credentials.class.php';

class StoreTest extends PHPUnit_Framework_TestCase {
  var $_rdfxml_doc = '<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:foaf="http://xmlns.com/foaf/0.1/">
  <foaf:Person>
    <foaf:name>scooby</foaf:name>
  </foaf:Person>
</rdf:RDF>';

  function test_get_metabox() {
    $store = new Store("http://example.org/store");
    $this->assertEquals( "http://example.org/store/meta", $store->get_metabox()->uri );
  }

  function test_get_metabox_includes_sets_credentials() {
    $credentials = new Credentials('scooby', 'shaggy');
    $store = new Store("http://example.org/store", $credentials);
    $this->assertEquals( $credentials, $store->get_metabox()->credentials );
  }

  function test_get_sparql_service() {
    $store = new Store("http://example.org/store");
    $this->assertEquals( "http://example.org/store/services/sparql", $store->get_sparql_service()->uri );
  }

  function test_get_sparql_service_sets_credentials() {
    $credentials = new Credentials('scooby', 'shaggy');
    $store = new Store("http://example.org/store", $credentials);
    $this->assertEquals( $credentials, $store->get_sparql_service()->credentials );
  }

  function test_get_multisparql_service() {
    $store = new Store("http://example.org/store");
    $this->assertEquals( "http://example.org/store/services/multisparql", $store->get_multisparql_service()->uri );
  }

  function test_get_multisparql_service_sets_credentials() {
    $credentials = new Credentials('scooby', 'shaggy');
    $store = new Store("http://example.org/store", $credentials);
    $this->assertEquals( $credentials, $store->get_multisparql_service()->credentials );
  }

  function test_get_contentbox() {
    $store = new Store("http://example.org/store");
    $this->assertEquals( "http://example.org/store/items", $store->get_contentbox()->uri );
  }

  function test_get_contentbox_service_sets_credentials() {
    $credentials = new Credentials('scooby', 'shaggy');
    $store = new Store("http://example.org/store", $credentials);
    $this->assertEquals( $credentials, $store->get_contentbox()->credentials );
  }

  function test_get_job_queue() {
    $store = new Store("http://example.org/store");
    $this->assertEquals( "http://example.org/store/jobs", $store->get_job_queue()->uri );
  }

  function test_get_job_queue_service_sets_credentials() {
    $credentials = new Credentials('scooby', 'shaggy');
    $store = new Store("http://example.org/store", $credentials);
    $this->assertEquals( $credentials, $store->get_job_queue()->credentials );
  }

  function test_get_config() {
    $store = new Store("http://example.org/store");
    $this->assertEquals( "http://example.org/store/config", $store->get_config()->uri );
  }

  function test_get_config_service_sets_credentials() {
    $credentials = new Credentials('scooby', 'shaggy');
    $store = new Store("http://example.org/store", $credentials);
    $this->assertEquals( $credentials, $store->get_config()->credentials );
  }
  function test_get_facet_service() {
    $store = new Store("http://example.org/store");
    $this->assertEquals( "http://example.org/store/services/facet", $store->get_facet_service()->uri );
  }

  function test_get_facet_service_sets_credentials() {
    $credentials = new Credentials('scooby', 'shaggy');
    $store = new Store("http://example.org/store", $credentials);
    $this->assertEquals( $credentials, $store->get_facet_service()->credentials );
  }
  function test_get_snapshots() {
    $store = new Store("http://example.org/store");
    $this->assertEquals( "http://example.org/store/snapshots", $store->get_snapshots()->uri );
  }

  function test_get_snapshots_sets_credentials() {
    $credentials = new Credentials('scooby', 'shaggy');
    $store = new Store("http://example.org/store", $credentials);
    $this->assertEquals( $credentials, $store->get_snapshots()->credentials );
  }

  function test_get_augment_service() {
    $store = new Store("http://example.org/store");
    $this->assertEquals( "http://example.org/store/services/augment", $store->get_augment_service()->uri );
  }

  function test_get_augment_service_sets_credentials() {
    $credentials = new Credentials('scooby', 'shaggy');
    $store = new Store("http://example.org/store", $credentials);
    $this->assertEquals( $credentials, $store->get_augment_service()->credentials );
  }

  function test_describe_single_uri_performs_get_on_metabox() {
    $fake_request_factory = new FakeRequestFactory();
    $fake_request = new FakeHttpRequest( new HttpResponse() );
    $fake_request_factory->register('GET', "http://example.org/store/meta?about=" . urlencode('http://example.org/scooby') . "&output=rdf", $fake_request );

    $store = new Store("http://example.org/store", null, $fake_request_factory);

    $response = $store->describe( 'http://example.org/scooby' );
    $this->assertTrue( $fake_request->was_executed() );
  }

  function test_describe_multiple_uris_gets_from_sparql_service() {
    $query = 'DESCRIBE <http://example.org/scooby> <http://example.org/shaggy>';
    $fake_request_factory = new FakeRequestFactory();
    $fake_request = new FakeHttpRequest( new HttpResponse() );
    $fake_request_factory->register('GET', "http://example.org/store/services/sparql?query=" . urlencode($query) . "&output=rdf", $fake_request );

    $store = new Store("http://example.org/store", null, $fake_request_factory);

    $response = $store->describe( array( 'http://example.org/scooby', 'http://example.org/shaggy' )  );
    $this->assertTrue( $fake_request->was_executed() );
  }

  function test_get_oai_service() {
    $store = new Store("http://example.org/store");
    $this->assertEquals( "http://example.org/store/services/oai-pmh", $store->get_oai_service()->uri );
  }

  function test_get_oai_service_sets_credentials() {
    $credentials = new Credentials('scooby', 'shaggy');
    $store = new Store("http://example.org/store", $credentials);
    $this->assertEquals( $credentials, $store->get_oai_service()->credentials );
  }

  function test_search_and_facet_return_both_responses() {
    $fake_request_factory = new FakeRequestFactory();
    $fake_search_response = new HttpResponse(111);
    $expected_search_body = 'I am the search response body';
    $fake_search_response->body = $expected_search_body;
    $fake_facet_response = new HttpResponse(222);
    $expected_facet_body = 'I am the facet response body';
    $fake_facet_response->body = $expected_facet_body;
    $fake_search_request = new FakeHttpRequest( $fake_search_response );
    $fake_facet_request = new FakeHttpRequest( $fake_facet_response );
    $fake_request_factory->register('GET', "http://example.org/store/items?query=scooby&max=10&offset=0", $fake_search_request );
    $fake_request_factory->register('GET', "http://example.org/store/services/facet?query=scooby&fields=foo&top=10&output=xml", $fake_facet_request );

    $store = new Store("http://example.org/store", null, $fake_request_factory);

    $response = $store->search_and_facet( 'scooby', array( 'foo' ) );

    $this->assertEquals(111, $response['searchResponse']->status_code);
    $this->assertEquals(222, $response['facetResponse']->status_code);
    $this->assertEquals($expected_search_body, $response['searchResponse']->body);
    $this->assertEquals($expected_facet_body, $response['facetResponse']->body);
  }

  function test_search_and_facet_uses_uri() {
    $fake_request_factory = new FakeRequestFactory();
    $fake_search_request = new FakeHttpRequest( new HttpResponse() );
    $fake_facet_request = new FakeHttpRequest( new HttpResponse() );
    $fake_request_factory->register('GET', "http://example.org/store/items?query=scooby&max=10&offset=0", $fake_search_request );
    $fake_request_factory->register('GET', "http://example.org/store/services/facet?query=scooby&fields=foo&top=10&output=xml", $fake_facet_request );

    $store = new Store("http://example.org/store", null, $fake_request_factory);

    $response = $store->search_and_facet( 'scooby', array( 'foo' ) );
    $this->assertTrue( $fake_search_request->was_executed() );
    $this->assertTrue( $fake_facet_request->was_executed() );
  }

  function test_search_and_facet_passes_max_parameter() {
    $fake_request_factory = new FakeRequestFactory();
    $fake_search_request = new FakeHttpRequest( new HttpResponse() );
    $fake_facet_request = new FakeHttpRequest( new HttpResponse() );
    $fake_request_factory->register('GET', "http://example.org/store/items?query=scooby&max=45&offset=0", $fake_search_request );
    $fake_request_factory->register('GET', "http://example.org/store/services/facet?query=scooby&fields=foo&top=10&output=xml", $fake_facet_request );

    $store = new Store("http://example.org/store", null, $fake_request_factory);

    $response = $store->search_and_facet( 'scooby', array( 'foo' ), 45 );
    $this->assertTrue( $fake_search_request->was_executed() );
  }

  function test_search_and_facet_passes_sort_parameter() {
    $fake_request_factory = new FakeRequestFactory();
    $fake_search_request = new FakeHttpRequest( new HttpResponse() );
    $fake_facet_request = new FakeHttpRequest( new HttpResponse() );
    $fake_request_factory->register('GET', "http://example.org/store/items?query=scooby&max=45&offset=0&sort=bar", $fake_search_request );
    $fake_request_factory->register('GET', "http://example.org/store/services/facet?query=scooby&fields=foo&top=10&output=xml", $fake_facet_request );

    $store = new Store("http://example.org/store", null, $fake_request_factory);

    $response = $store->search_and_facet( 'scooby', array( 'foo' ), 45, 0, 'bar' );
    $this->assertTrue( $fake_search_request->was_executed() );
  }

  function test_search_and_facet_passes_offset_parameter() {
    $fake_request_factory = new FakeRequestFactory();
    $fake_search_request = new FakeHttpRequest( new HttpResponse() );
    $fake_facet_request = new FakeHttpRequest( new HttpResponse() );
    $fake_request_factory->register('GET', "http://example.org/store/items?query=scooby&max=45&offset=12", $fake_search_request );
    $fake_request_factory->register('GET', "http://example.org/store/services/facet?query=scooby&fields=foo&top=10&output=xml", $fake_facet_request );

    $store = new Store("http://example.org/store", null, $fake_request_factory);

    $response = $store->search_and_facet( 'scooby', array( 'foo' ), 45, 12 );
    $this->assertTrue( $fake_search_request->was_executed() );
  }

  function test_search_and_facet_sets_accept() {
    $fake_request_factory = new FakeRequestFactory();
    $fake_search_request = new FakeHttpRequest( new HttpResponse() );
    $fake_facet_request = new FakeHttpRequest( new HttpResponse() );
    $fake_request_factory->register('GET', "http://example.org/store/items?query=scooby&max=10&offset=0", $fake_search_request );
    $fake_request_factory->register('GET', "http://example.org/store/services/facet?query=scooby&fields=foo&top=10&output=xml", $fake_facet_request );

    $store = new Store("http://example.org/store", null, $fake_request_factory);

    $response = $store->search_and_facet( 'scooby', array( 'foo' ) );
    $this->assertTrue( in_array('Accept: application/rss+xml', $fake_search_request->get_headers() ) );
    $this->assertTrue( in_array('Accept: application/xml', $fake_facet_request->get_headers() ) );
  }

  function test_search_and_facet_uses_credentials() {
    $fake_request_factory = new FakeRequestFactory();
    $fake_search_request = new FakeHttpRequest( new HttpResponse() );
    $fake_facet_request = new FakeHttpRequest( new HttpResponse() );
    $fake_request_factory->register('GET', "http://example.org/store/items?query=scooby&max=10&offset=0", $fake_search_request );
    $fake_request_factory->register('GET', "http://example.org/store/services/facet?query=scooby&fields=foo&top=10&output=xml", $fake_facet_request );

    $store = new Store("http://example.org/store", new FakeCredentials(), $fake_request_factory);

    $response = $store->search_and_facet( 'scooby', array( 'foo') );
    $this->assertEquals( "user:pwd", $fake_search_request->get_auth() );
    $this->assertEquals( "user:pwd", $fake_facet_request->get_auth() );
  }


  function test_search_and_facet_uses_fields() {
    $fake_request_factory = new FakeRequestFactory();
    $fake_search_request = new FakeHttpRequest( new HttpResponse() );
    $fake_facet_request = new FakeHttpRequest( new HttpResponse() );
    $fake_request_factory->register('GET', "http://example.org/store/items?query=scooby&max=10&offset=0", $fake_search_request );
    $fake_request_factory->register('GET', "http://example.org/store/services/facet?query=scooby&fields=foo%2Cbar%2Cbaz%2Cqux&top=10&output=xml", $fake_facet_request );

    $store = new Store("http://example.org/store", new FakeCredentials(), $fake_request_factory);

    $response = $store->search_and_facet( 'scooby', array( 'foo', 'bar', 'baz', 'qux' ) );

    $this->assertTrue( $fake_facet_request->was_executed() );
  }

  function test_search_and_facet_uses_top() {
    $fake_request_factory = new FakeRequestFactory();
    $fake_search_request = new FakeHttpRequest( new HttpResponse() );
    $fake_facet_request = new FakeHttpRequest( new HttpResponse() );
    $fake_request_factory->register('GET', "http://example.org/store/items?query=scooby&max=10&offset=0", $fake_search_request );
    $fake_request_factory->register('GET', "http://example.org/store/services/facet?query=scooby&fields=foo%2Cbar%2Cbaz%2Cqux&top=93&output=xml", $fake_facet_request );

    $store = new Store("http://example.org/store", new FakeCredentials(), $fake_request_factory);

    $response = $store->search_and_facet( 'scooby', array( 'foo', 'bar', 'baz', 'qux' ), 10, 0, null, 93 );

    $this->assertTrue( $fake_facet_request->was_executed() );
  }

  function test_store_data_posts_to_metabox_uri() {
    $fake_request_factory = new FakeRequestFactory();
    $fake_request = new FakeHttpRequest( new HttpResponse() );
    $fake_request_factory->register('POST', "http://example.org/store/meta", $fake_request );

    $store = new Store("http://example.org/store", new FakeCredentials(), $fake_request_factory);
    $response = $store->store_data( $this->_rdfxml_doc );
    $this->assertTrue( $fake_request->was_executed() );
  }

  function test_store_data_posts_supplied_rdfxml() {
    $fake_request_factory = new FakeRequestFactory();
    $fake_request = new FakeHttpRequest( new HttpResponse() );
    $fake_request_factory->register('POST', "http://example.org/store/meta", $fake_request );

    $store = new Store("http://example.org/store", new FakeCredentials(), $fake_request_factory);
    $response = $store->store_data( $this->_rdfxml_doc );
    $this->assertEquals( $this->_rdfxml_doc , $fake_request->get_body() );
  }

  function test_store_data_sets_content_type() {
    $fake_request_factory = new FakeRequestFactory();
    $fake_request = new FakeHttpRequest( new HttpResponse() );
    $fake_request_factory->register('POST', "http://example.org/store/meta", $fake_request );

    $store = new Store("http://example.org/store", new FakeCredentials(), $fake_request_factory);
    $response = $store->store_data( $this->_rdfxml_doc );
    $this->assertTrue( in_array('Content-Type: application/rdf+xml', $fake_request->get_headers() ) );
  }

  function test_store_data_sets_accept() {
    $fake_request_factory = new FakeRequestFactory();
    $fake_request = new FakeHttpRequest( new HttpResponse() );
    $fake_request_factory->register('POST', "http://example.org/store/meta", $fake_request );

    $store = new Store("http://example.org/store", new FakeCredentials(), $fake_request_factory);
    $response = $store->store_data( $this->_rdfxml_doc );
    $this->assertTrue( in_array('Accept: */*', $fake_request->get_headers() ) );
  }

  function test_store_data_recognises_simple_graph() {
    $fake_request_factory = new FakeRequestFactory();
    $fake_request = new FakeHttpRequest( new HttpResponse() );
    $fake_request_factory->register('POST', "http://example.org/store/meta", $fake_request );

    $g = new SimpleGraph();
    $g->from_rdfxml($this->_rdfxml_doc);
    $store = new Store("http://example.org/store", new FakeCredentials(), $fake_request_factory);
    $response = $store->store_data( $g );
    $this->assertEquals( $g->to_turtle() , $fake_request->get_body() );
  }

  function test_store_data_sends_simple_graph_as_turtle() {
    $fake_request_factory = new FakeRequestFactory();
    $fake_request = new FakeHttpRequest( new HttpResponse() );
    $fake_request_factory->register('POST', "http://example.org/store/meta", $fake_request );

    $g = new SimpleGraph();
    $g->from_rdfxml($this->_rdfxml_doc);
    $store = new Store("http://example.org/store", new FakeCredentials(), $fake_request_factory);
    $response = $store->store_data( $g );
    $this->assertTrue( in_array('Content-Type: text/turtle', $fake_request->get_headers() ) );
  }

  function test_store_content() {
    $fake_request_factory = new FakeRequestFactory();
    $fake_request = new FakeHttpRequest( new HttpResponse() );
    $fake_request_factory->register('POST', "http://example.org/store/items", $fake_request );

    $store = new Store("http://example.org/store", new FakeCredentials(), $fake_request_factory);
    $response = $store->store_content('some content', 'text/html');
    $this->assertTrue( $fake_request->was_executed() );
  }

function test_store_content_sets_content_type() {
    $fake_request_factory = new FakeRequestFactory();
    $fake_request = new FakeHttpRequest( new HttpResponse() );
    $fake_request_factory->register('POST', "http://example.org/store/items", $fake_request );

    $store = new Store("http://example.org/store", new FakeCredentials(), $fake_request_factory);
    $response = $store->store_content('some content', 'text/html');
    $this->assertTrue( in_array('Content-Type: text/html', $fake_request->get_headers() ) );
  }

  function test_mirror_from_url(){
    $url =  "http://example.org/web-page";
    $fake_request_factory = new FakeRequestFactory();
    $webpage_response =  new HttpResponse('200') ;
    $webpage_response->body = file_get_contents(dirname(__FILE__).DIRECTORY_SEPARATOR.'documents/after.ttl');
    $fake_webpage_request = new FakeHttpRequest($webpage_response);
    $fake_request_factory->register('GET',$url, $fake_webpage_request );


    $contentbox_copy =  new HttpResponse('200');
    $contentbox_copy->body = file_get_contents(dirname(__FILE__).DIRECTORY_SEPARATOR.'documents/before.ttl');
    $fake_contentbox_request = new FakeHttpRequest($contentbox_copy);
    $fake_request_factory->register('GET','http://api.talis.com/stores/example/items/mirrors/'.$url , $fake_contentbox_request);

    $putResponse =new HttpResponse('201');
    $fake_postContent_request = new FakeHttpRequest($putResponse);
    $fake_request_factory->register('PUT', 'http://api.talis.com/stores/example/items/mirrors/'.$url , $fake_postContent_request );

    $postDataResponse =  new HttpResponse('201');
    $fake_postData_request = new FakeHttpRequest($postDataResponse);
    $fake_request_factory->register('POST', 'http://api.talis.com/stores/example/meta' , $fake_postData_request );

    $store = new Store("http://api.talis.com/stores/example", new FakeCredentials(), $fake_request_factory);
    $response = $store->mirror_from_url($url);

    $this->assertTrue($fake_webpage_request->was_executed(), "The webpage $url should be retrieved over HTTP");
    $this->assertTrue($fake_contentbox_request->was_executed(), "Store:mirror_from_url should attempt to retrieve a copy of $url from the content box at {storeuri}/items/mirrors/{$url} if it exists yet.");
    $this->assertTrue($fake_postContent_request->was_executed(), "The contents of $url should be POSTed to the contentbox");
    $this->assertTrue($fake_postData_request->was_executed(), "The data from $url  (and its metadata) should be added to the store by POSTing a document containing changesets to /meta");
    
    $expected_response =  array(
        'get_page' => $webpage_response,
        'get_copy' => $contentbox_copy,
        'put_page' => $putResponse,
        'update_data' => $postDataResponse,
        'success' => true,
      );
     $this->assertEquals($expected_response, $response,""); 
  }

  function test_mirror_from_url_does_not_PUT_if_changesets_fail(){
    $url =  "http://example.org/web-page";
    $fake_request_factory = new FakeRequestFactory();
    $webpage_response =  new HttpResponse('200') ;
    $webpage_response->body = file_get_contents(dirname(__FILE__).DIRECTORY_SEPARATOR.'documents/after.ttl');
    $fake_webpage_request = new FakeHttpRequest($webpage_response);
    $fake_request_factory->register('GET',$url, $fake_webpage_request );


    $contentbox_copy =  new HttpResponse('200');
    $contentbox_copy->body = file_get_contents(dirname(__FILE__).DIRECTORY_SEPARATOR.'documents/before.ttl');
    $fake_contentbox_request = new FakeHttpRequest($contentbox_copy);
    $fake_request_factory->register('GET','http://api.talis.com/stores/example/items/mirrors/'.$url , $fake_contentbox_request);

    $putResponse =new HttpResponse('201');
    $fake_postContent_request = new FakeHttpRequest($putResponse);
    $fake_request_factory->register('PUT', 'http://api.talis.com/stores/example/items/mirrors/'.$url , $fake_postContent_request );


    $postDataResponse =  new HttpResponse('500');
    $fake_postData_request = new FakeHttpRequest($postDataResponse);
    $fake_request_factory->register('POST', 'http://api.talis.com/stores/example/meta' , $fake_postData_request );

    $store = new Store("http://api.talis.com/stores/example", new FakeCredentials(), $fake_request_factory);
    $response = $store->mirror_from_url($url);

    $this->assertTrue($fake_webpage_request->was_executed(), "The webpage $url should be retrieved over HTTP");
    $this->assertTrue($fake_contentbox_request->was_executed(), "Store:mirror_from_url should attempt to retrieve a copy of $url from the content box at {storeuri}/items/mirrors/{$url} if it exists yet.");
    $this->assertFalse($fake_postContent_request->was_executed(), "The contents of $url should not be POSTed to the contentbox because the POST to the metabox failed");
    $this->assertTrue($fake_postData_request->was_executed(), "The data from $url  (and its metadata) should be added to the store by POSTing a document containing changesets to /meta");
    
    $expected_response =  array(
        'get_page' => $webpage_response,
        'get_copy' => $contentbox_copy,
        'put_page' => false,
        'update_data' => $postDataResponse,
        'success' => false,
      );
     $this->assertEquals($expected_response, $response,"the updated document should not be PUT to the contentbox if the changes fail (changes could fail if other changesets have already removed the triples)"); 
  }

  function test_mirror_from_url_resubmits_before_graph_if_changeset_409s(){
    $url =  "http://example.org/web-page";
    $fake_request_factory = new FakeRequestFactory();
    $webpage_response =  new HttpResponse('200') ;
    $webpage_response->body = file_get_contents(dirname(__FILE__).DIRECTORY_SEPARATOR.'documents/after.ttl');
    $fake_webpage_request = new FakeHttpRequest($webpage_response);
    $fake_request_factory->register('GET',$url, $fake_webpage_request );


    $contentbox_copy =  new HttpResponse('200');
    $contentbox_copy->body = file_get_contents(dirname(__FILE__).DIRECTORY_SEPARATOR.'documents/before.ttl');
    $fake_contentbox_request = new FakeHttpRequest($contentbox_copy);
    $fake_request_factory->register('GET','http://api.talis.com/stores/example/items/mirrors/'.$url , $fake_contentbox_request);

    $putResponse =new HttpResponse('201');
    $fake_postContent_request = new FakeHttpRequest($putResponse);
    $fake_request_factory->register('PUT', 'http://api.talis.com/stores/example/items/mirrors/'.$url , $fake_postContent_request );


    $postDataResponse =  new HttpResponse('409');
    $fake_postData_request = new FakeHttpRequest($postDataResponse);
    $fake_request_factory->register('POST', 'http://api.talis.com/stores/example/meta' , $fake_postData_request );

    $submitDataToMetaboxResponse =  new HttpResponse('204');
    $fake_submitDataToMetabox_request = new FakeHttpRequest($submitDataToMetaboxResponse );
    $fake_request_factory->register('POST', 'http://api.talis.com/stores/example/meta' ,  $fake_submitDataToMetabox_request );

    $resubmitChangesetResponse =  new HttpResponse('202');
    $fake_resubmitChangeset_request = new FakeHttpRequest($resubmitChangesetResponse );
    $fake_request_factory->register('POST', 'http://api.talis.com/stores/example/meta' ,  $fake_resubmitChangeset_request );




    $store = new Store("http://api.talis.com/stores/example", new FakeCredentials(), $fake_request_factory);
    $response = $store->mirror_from_url($url);


    $this->assertTrue($fake_webpage_request->was_executed(), "The webpage $url should be retrieved over HTTP");
    $this->assertTrue($fake_contentbox_request->was_executed(), "Store:mirror_from_url should attempt to retrieve a copy of $url from the content box at {storeuri}/items/mirrors/{$url} if it exists yet.");
    $this->assertTrue($fake_postData_request->was_executed(), "The data from $url  (and its metadata) should be added to the store by POSTing a document containing changesets to /meta");

    $this->assertTrue($fake_submitDataToMetabox_request->was_executed(), "The before graph should be resubmitted when the changeset returns 409");
    $this->assertTrue($fake_resubmitChangeset_request->was_executed(), "The changeset should be resubmitted");


    $this->assertTrue($fake_postContent_request->was_executed(), "The contents of $url should be POSTed to the contentbox");
    $expected_response =  array(
        'get_page' => $webpage_response,
        'get_copy' => $contentbox_copy,
        'put_page' => $putResponse,
        'update_data' => $resubmitChangesetResponse,
        'success' => true,
        'reapply_before_triples' => $submitDataToMetaboxResponse,
      );
    $this->assertEquals($expected_response, $response,""); 
     }

  


}
?>
