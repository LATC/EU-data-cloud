<?php
require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'constants.inc.php';
require_once MORIARTY_DIR . 'union.class.php';
require_once MORIARTY_DIR . 'credentials.class.php';

class UnionTest extends PHPUnit_Framework_TestCase {

  var $_store1_results = '<?xml version="1.0" encoding="utf-8"?>
<rdf:RDF xmlns="http://purl.org/rss/1.0/" xmlns:dct="http://purl.org/dc/terms/" xmlns:relevance="http://a9.com/-/opensearch/extensions/relevance/1.0/" xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:os="http://a9.com/-/spec/opensearch/1.1/" xmlns:sioc="http://rdfs.org/sioc/ns#" xmlns:dc="http://purl.org/dc/elements/1.1/">
  <channel rdf:about="http://example.org/store1/items?query=scooby&amp;max=10&amp;offset=0">
    <title>scooby</title>
    <link>http://example.org/store1/items?query=scooby&amp;max=10&amp;offset=0</link>
    <description>Results of a search for scooby on store2</description>
    <items>
      <rdf:Seq rdf:about="urn:uuid:daeec0bd-efae-4542-9062-14be77745201">
        <rdf:li resource="http://jingyeluo.blogspot.com/2006/11/appdomain-process-and-components.html"/>
        <rdf:li resource="http://jingyeluo.blogspot.com/2006/10/export-import-goodie-fromto-photoshop.html"/>
      </rdf:Seq>
    </items>
    <os:startIndex>0</os:startIndex>
    <os:itemsPerPage>10</os:itemsPerPage>
    <os:totalResults>2</os:totalResults>
  </channel>

  <item rdf:about="http://example.org/results/1">
    <title>Store 1 Item 1</title>
    <link>http://example.org/results/1</link>
    <relevance:score>1.0</relevance:score>
  </item>

  <item rdf:about="http://example.org/results/2">
    <title>Store 1 Item 2</title>
    <link>http://example.org/results/2</link>
    <relevance:score>0.5</relevance:score>
  </item>
    
</rdf:RDF>';



  var $_store2_results = '<?xml version="1.0" encoding="utf-8"?>
<rdf:RDF xmlns="http://purl.org/rss/1.0/" xmlns:dct="http://purl.org/dc/terms/" xmlns:relevance="http://a9.com/-/opensearch/extensions/relevance/1.0/" xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:os="http://a9.com/-/spec/opensearch/1.1/" xmlns:sioc="http://rdfs.org/sioc/ns#" xmlns:dc="http://purl.org/dc/elements/1.1/">
  <channel rdf:about="http://example.org/store2/items?query=scooby&amp;max=10&amp;offset=0">
    <title>scooby</title>
    <link>http://example.org/store2/items?query=scooby&amp;max=10&amp;offset=0</link>
    <description>Results of a search for scooby on store2</description>
    <items>
      <rdf:Seq rdf:about="urn:uuid:daeec0bd-efae-4542-9062-14be77745201">
        <rdf:li resource="http://jingyeluo.blogspot.com/2006/11/appdomain-process-and-components.html"/>
        <rdf:li resource="http://jingyeluo.blogspot.com/2006/10/export-import-goodie-fromto-photoshop.html"/>
      </rdf:Seq>
    </items>
    <os:startIndex>0</os:startIndex>
    <os:itemsPerPage>10</os:itemsPerPage>
    <os:totalResults>2</os:totalResults>
  </channel>

  <item rdf:about="http://example.org/results/3">
    <title>Store 2 Item 1</title>
    <link>http://example.org/results/3</link>
    <relevance:score>1.0</relevance:score>
  </item>

  <item rdf:about="http://example.org/results/4">
    <title>Store 2 Item 2</title>
    <link>http://example.org/results/4</link>
    <relevance:score>0.5</relevance:score>
  </item>
    
</rdf:RDF>';

  
  function test_one_store_search_issues_get() {
    $fake_request_factory = new FakeRequestFactory();
    $fake_request = new FakeHttpRequest( new HttpResponse() );
    $fake_request_factory->register('GET', "http://example.org/store1/items?query=scooby&max=10&offset=0", $fake_request );

    $u = new Union();
    $u->add("http://example.org/store1");
    $u->request_factory = $fake_request_factory;

    $response = $u->search( 'scooby' );
    $this->assertTrue( $fake_request->was_executed() );
  }


  function test_one_store_search_passes_max_parameter() {
    $fake_request_factory = new FakeRequestFactory();
    $fake_request = new FakeHttpRequest( new HttpResponse() );
    $fake_request_factory->register('GET', "http://example.org/store1/items?query=scooby&max=45&offset=0", $fake_request );

    $u = new Union();
    $u->add("http://example.org/store1");
    $u->request_factory = $fake_request_factory;

    $response = $u->search( 'scooby', 45);
    $this->assertTrue( $fake_request->was_executed() );
  }

  function test_one_store_search_passes_sort_parameter() {
    $fake_request_factory = new FakeRequestFactory();
    $fake_request = new FakeHttpRequest( new HttpResponse() );
    $fake_request_factory->register('GET', "http://example.org/store1/items?query=scooby&max=45&offset=0&sort=title", $fake_request );

    $u = new Union();
    $u->add("http://example.org/store1");
    $u->request_factory = $fake_request_factory;

    $response = $u->search( 'scooby', 45,0,'title');
    $this->assertTrue( $fake_request->was_executed() );
  }


  function test_one_store_search_passes_offset_parameter() {
    $fake_request_factory = new FakeRequestFactory();
    $fake_request = new FakeHttpRequest( new HttpResponse() );
    $fake_request_factory->register('GET', "http://example.org/store1/items?query=scooby&max=45&offset=12", $fake_request );

    $u = new Union();
    $u->add("http://example.org/store1");
    $u->request_factory = $fake_request_factory;

    $response = $u->search( 'scooby', 45, 12);
    $this->assertTrue( $fake_request->was_executed() );
  }

  function test_one_store_search_sets_accept() {
    $fake_request_factory = new FakeRequestFactory();
    $fake_request = new FakeHttpRequest( new HttpResponse() );
    $fake_request_factory->register('GET', "http://example.org/store1/items?query=scooby&max=10&offset=0", $fake_request );

    $u = new Union();
    $u->add("http://example.org/store1");
    $u->request_factory = $fake_request_factory;

    $response = $u->search( 'scooby' );
    $this->assertTrue( in_array('Accept: application/rss+xml', $fake_request->get_headers() ) );
  }

  function test_one_store_search_uses_credentials() {
    $fake_request_factory = new FakeRequestFactory();
    $fake_request = new FakeHttpRequest( new HttpResponse() );
    $fake_request_factory->register('GET', "http://example.org/store1/items?query=scooby&max=10&offset=0", $fake_request );

    $u = new Union();
    $u->add("http://example.org/store1", new FakeCredentials());
    $u->request_factory = $fake_request_factory;

    $response = $u->search( 'scooby' );
    $this->assertEquals( "user:pwd", $fake_request->get_auth() );
  }

  function test_two_store_search_issues_gets() {
    $fake_request_factory = new FakeRequestFactory();
    
    $fake_request1 = new FakeHttpRequest( new HttpResponse() );
    $fake_request_factory->register('GET', "http://example.org/store1/items?query=scooby&max=10&offset=0", $fake_request1 );

    $fake_request2 = new FakeHttpRequest( new HttpResponse() );
    $fake_request_factory->register('GET', "http://example.org/store2/items?query=scooby&max=10&offset=0", $fake_request2 );

    $u = new Union();
    $u->add("http://example.org/store1");
    $u->add("http://example.org/store2");
    $u->request_factory = $fake_request_factory;

    $response = $u->search( 'scooby' );
    $this->assertTrue( $fake_request1->was_executed() );
    $this->assertTrue( $fake_request2->was_executed() );
  }
/*
  function test_two_store_search_merges_results_into_single_response() {
    $fake_request_factory = new FakeRequestFactory();
    
    $fake_request1 = new FakeHttpRequest( new HttpResponse() );
    $fake_request_factory->register('GET', "http://example.org/store1/items?query=scooby&max=10&offset=0", $fake_request1 );

    $fake_request2 = new FakeHttpRequest( new HttpResponse() );
    $fake_request_factory->register('GET', "http://example.org/store2/items?query=scooby&max=10&offset=0", $fake_request2 );

    $u = new Union();
    $u->add("http://example.org/store1");
    $u->add("http://example.org/store2");
    $u->request_factory = $fake_request_factory;

    $response = $u->search( 'scooby' );
    
    $cb = new Contentbox('http://example.org/foo');
    
    $resources = $cb->parse_results_xml('tag:moriarty.talis.com,2009:union', $response->body);
    $this->assertEquals( 2, count($resources->items) );
    $this->assertEquals( "AppDomain, process and components...", $resources->items[0]['http://purl.org/dc/elements/1.1/title'][0] );
    $this->assertEquals( "Export & Import Goodie from/to Photoshop", $resources->items[1]['http://purl.org/dc/elements/1.1/title'][0] );


  }
*/

}
