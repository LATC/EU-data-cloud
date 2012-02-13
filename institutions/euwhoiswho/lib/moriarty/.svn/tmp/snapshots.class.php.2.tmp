<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'moriarty.inc.php';
require_once MORIARTY_ARC_DIR . "ARC2.php";
require_once MORIARTY_DIR . 'httprequest.class.php';
require_once MORIARTY_DIR . 'httprequestfactory.class.php';

class Snapshots{

	var $uri, $errors, $credentials;
	
	function __construct($uri, $credentials=false)
	{
		$this->uri = $uri;
		$this->credentials = $credentials;
	}
	/**
	 * get_snapshots
	 *
	 * @return array
	 * @author Keith Alexander
	 **/
	public function get_item_uris()
	{
		$parser = ARC2::getRDFXMLParser();
		$parser->parse($this->uri);
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