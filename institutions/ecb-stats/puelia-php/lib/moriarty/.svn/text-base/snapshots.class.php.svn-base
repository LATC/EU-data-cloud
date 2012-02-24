<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'moriarty.inc.php';
require_once MORIARTY_ARC_DIR . "ARC2.php";
require_once MORIARTY_DIR . 'httprequest.class.php';
require_once MORIARTY_DIR . 'httprequestfactory.class.php';

class Snapshots{

	var $uri, $errors, $credentials, $request_factory;
	
	function __construct($uri, $credentials=false, $request_factory=false)
	{
		$this->uri = $uri;
    $this->credentials = $credentials;
    $this->request_factory = $request_factory;
    if (empty( $this->request_factory) ) {
      $this->request_factory = new HttpRequestFactory();
    }
 
	}

/**
	 * get_item_uris
	 *
	 * @return array
	 * @author Chris Clarke
	 **/
	public function get_item_uris()
	{
        $request = $this->request_factory->make( 'GET', $this->uri, $this->credentials );
        $request->set_accept("application/rdf+xml");
        $response = $request->execute();

		$parser = ARC2::getRDFXMLParser();
		$parser->parse('',$response->body);
		$triples = $parser->getTriples();

		$this->errors = $parser->getErrors();
		$uris = array();
		foreach($triples as $t)
		{
			if($t['p']=='http://schemas.talis.com/2006/bigfoot/configuration#snapshot') $uris[]=$t['o'];
		}
		return $uris;
	}

	/**
	 * get_errors
	 *
	 * @return array
	 * @author Keith Alexander
	 **/
	public function get_errors()
	{
		return $this->errors;
	}
	
}
?>
