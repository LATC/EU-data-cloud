<?php
require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'constants.inc.php';
require_once MORIARTY_TEST_DIR . 'fakecredentials.class.php';
require_once MORIARTY_DIR . 'oaiservice.class.php';

class OAIServiceTest extends PHPUnit_Framework_TestCase {

  function test_list_records_issues_gets_to_service_uri() {
    $fake_request_factory = new FakeRequestFactory();
    $fake_request = new FakeHttpRequest( new HttpResponse() );
    $fake_request_factory->register('GET', "http://example.org/store/services/oai-pmh?verb=ListRecords&metadataPrefix=oai_dc", $fake_request );
    $oai = new OAIService("http://example.org/store/services/oai-pmh");
    $oai->request_factory = $fake_request_factory;

    $response = $oai->list_records();
    $this->assertTrue( $fake_request->was_executed() );
  }

  function test_list_records_sets_accept() {
    $fake_request_factory = new FakeRequestFactory();
    $fake_request = new FakeHttpRequest( new HttpResponse() );
    $fake_request_factory->register('GET', "http://example.org/store/services/oai-pmh?verb=ListRecords&metadataPrefix=oai_dc", $fake_request );
    $oai = new OAIService("http://example.org/store/services/oai-pmh");
    $oai->request_factory = $fake_request_factory;

    $response = $oai->list_records();
    $this->assertTrue( in_array('Accept: text/xml', $fake_request->get_headers() ) );
  }

  function test_list_records_with_resumption_token() {
    $fake_request_factory = new FakeRequestFactory();
    $fake_request = new FakeHttpRequest( new HttpResponse() );
    $fake_request_factory->register('GET', "http://example.org/store/services/oai-pmh?verb=ListRecords&resumptionToken=foobar", $fake_request );
    $oai = new OAIService("http://example.org/store/services/oai-pmh");
    $oai->request_factory = $fake_request_factory;

    $response = $oai->list_records("foobar");
    $this->assertTrue( $fake_request->was_executed() );
  }

  function test_list_records_with_from_and_to_dates() {
  	$from_date = '2010-05-29';
  	$until_date = '2010-06-05';
  	$fake_request_factory = new FakeRequestFactory();
    $fake_request = new FakeHttpRequest( new HttpResponse() );
    $fake_request_factory->register('GET', "http://example.org/store/services/oai-pmh?verb=ListRecords&metadataPrefix=oai_dc&from=$from_date&until=$until_date", $fake_request );
    $oai = new OAIService("http://example.org/store/services/oai-pmh");
    $oai->request_factory = $fake_request_factory;

    $response = $oai->list_records(null, $from_date, $until_date);
    $this->assertTrue( $fake_request->was_executed() );
  }

  function test_parse_oai_xml() {
    $xml = '<OAI-PMH  xmlns="http://www.openarchives.org/OAI/2.0/" 
          xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" 
          xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/ http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd">
  <responseDate>2009-04-28T19:53:06Z</responseDate>
  <request from="2009-04-28T19:53:06Z" metadataPrefix="oai_dc" verb="ListRecords">http://example.org/store/services/oai-pmh</request>
  <ListRecords>
    <record>
      <header>
        <identifier>http://example.org/757</identifier>
        <datestamp>2009-04-21T12:36:58Z</datestamp>
      </header>
      <metadata>
        <oai_dc:dc xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:oai_dc="http://www.openarchives.org/OAI/2.0/oai_dc/">
          <dc:identifier>http://example.org/757</dc:identifier>
        </oai_dc:dc>
      </metadata>
    </record>
    <record>
      <header>
        <identifier>http://example.org/558</identifier>
        <datestamp>2009-04-21T12:36:58Z</datestamp>
      </header>
      <metadata>
        <oai_dc:dc xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:oai_dc="http://www.openarchives.org/OAI/2.0/oai_dc/">
          <dc:identifier>http://example.org/558</dc:identifier>
        </oai_dc:dc>
      </metadata>
    </record>
    <record>
      <header>
        <identifier>http://example.org/359</identifier>
        <datestamp>2009-04-21T12:36:58Z</datestamp>
      </header>
      <metadata>
        <oai_dc:dc xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:oai_dc="http://www.openarchives.org/OAI/2.0/oai_dc/">
          <dc:identifier>http://example.org/359</dc:identifier>
        </oai_dc:dc>
      </metadata>
    </record>
    <resumptionToken completeListSize="911011" cursor="50">b2FpX2RjfDUwfDE5NzAtMDEtMDFUMDA6MDA6MDBafDIwMDktMDQtMjhUMTk6NTM6MDZa</resumptionToken>
  </ListRecords>
</OAI-PMH>';
  

    $oai = new OAIService("http://example.org/store/services/oai-pmh");

    $res = $oai->parse_oai_xml( $xml );
    $this->assertEquals( 'b2FpX2RjfDUwfDE5NzAtMDEtMDFUMDA6MDA6MDBafDIwMDktMDQtMjhUMTk6NTM6MDZa', $res['token'] );
    $this->assertEquals( 3, count( $res['items'] ) );
    $this->assertEquals( 'http://example.org/757', $res['items'][0]['uri'] );
    $this->assertEquals( '2009-04-21T12:36:58Z', $res['items'][0]['datestamp'] );
    $this->assertEquals( 'http://example.org/558', $res['items'][1]['uri'] );
    $this->assertEquals( '2009-04-21T12:36:58Z', $res['items'][1]['datestamp'] );
    $this->assertEquals( 'http://example.org/359', $res['items'][2]['uri'] );
    $this->assertEquals( '2009-04-21T12:36:58Z', $res['items'][2]['datestamp'] );
    $this->assertEquals( '911011', $res['entities_count'] );
    
  }


}
?>
