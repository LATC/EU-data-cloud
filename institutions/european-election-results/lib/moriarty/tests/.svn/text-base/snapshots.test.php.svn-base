<?php
require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'constants.inc.php';
require_once MORIARTY_DIR . 'snapshots.class.php';
require_once MORIARTY_DIR . 'credentials.class.php';
require_once MORIARTY_TEST_DIR . 'fakecredentials.class.php';

class SnapshotsTest extends PHPUnit_Framework_TestCase {

	function test_get_item_uris(){
    $fake_request_factory = new FakeRequestFactory();
    $fake_response = new HttpResponse();
    $fake_response->body=file_get_contents(realpath(dirname(__FILE__)).'/documents/snapshots.rdf');
    $fake_request = new FakeHttpRequest($fake_response);
    $snapshotsUri = "http://example.com/store/snapshots";
    $fake_request_factory->register('GET',$snapshotsUri, $fake_request );    
		$snapshots = new Snapshots($snapshotsUri, false, $fake_request_factory);
		$expected = array("http://api.talis.com/stores/schema-cache/snapshots/20071129173353.tar");
		$actual = $snapshots->get_item_uris();
		$this->assertEquals($expected, $actual);
	}


}
?>
