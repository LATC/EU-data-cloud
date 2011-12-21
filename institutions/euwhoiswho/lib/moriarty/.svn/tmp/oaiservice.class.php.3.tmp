<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'moriarty.inc.php';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'contentbox.class.php';

/** 
* Represents a store's OAI-PMH service
* @see http://n2.talis.com/wiki/Store_OAI_Service
*/
class OAIService {
  /** 
   * @access private 
   */
  var $uri;
  /** 
   * @access private 
   */
  var $request_factory;
  /** 
   * @access private 
   */
  var $credentials;

  /**
   * Create a new instance of this class
   * @param string uri URI of the augment service
   * @param Credentials credentials the credentials to use for authenticated requests (optional)
   */ 
  function __construct($uri, $credentials = null, $request_factory = null) {
    $this->uri = $uri;
    $this->credentials = $credentials;
    $this->request_factory = $request_factory;
  }

  /**
   * List records available via the OAI service
   * @param string $resumption_token a valid resumption token for paging results
   * @return HttpResponse
   */
  function list_records($resumption_token = null, $from = null, $until = null) {
    if (empty( $this->request_factory) ) {
      $this->request_factory = new HttpRequestFactory();
    }
    $uri = $this->make_list_records_uri($resumption_token, $from, $until);

    $request = $this->request_factory->make( 'GET', $uri , $this->credentials );
    $request->set_accept("text/xml");
    return $request->execute();
  }

  function make_list_records_uri($resumption_token = null, $from = null, $until = null) {
    $uri = $this->uri;
	$uri .= '?verb=ListRecords';
    if (null == $resumption_token) {
      $uri .= '&metadataPrefix=oai_dc';
	  if(!empty($from)) {
	    $uri .= '&from=' . urlencode($from);
	  }
	  if(!empty($until)) {
	    $uri .= '&until=' . urlencode($until);
	  }
    }
    else {
      $uri .= '&resumptionToken=' . urlencode($resumption_token);
    }


    return $uri;    
  }

  /**
   * Parse the response from an OAI query into an array.
   * This method returns an associative array with two keys
   * <ul>
   * <li><em>token</em> => the resumption token</li>
   * <li><em>items</em> => an array of items, each being an associative array with a single key called uri</li>
   * </ul>
   * @param string xml the OAI response as an XML document
   * @return array
   */
  function parse_oai_xml($xml) {
    $res = array();

    $reader = new XMLReader();
    $reader->XML($xml);

    $items = array();
    $item = array();
    $status = 'seeking_header';
    while ($reader->read()) {
      if ( $reader->nodeType == XMLReader::ELEMENT) {
        if ( $reader->name == 'header') {
          $status = 'seeking_identifier';
        }
        elseif ( $reader->name == 'identifier' && $status == 'seeking_identifier') {
          $status = 'reading_identifier';
        }
        elseif ( $reader->name == 'datestamp' && $status == 'seeking_datestamp') {
          $status = 'reading_datestamp';
        }
        elseif ( $reader->name == 'resumptionToken') {
          $status = 'reading_token';
          $res['entities_count'] = $reader->getAttribute('completeListSize');
        }
        
      }
      elseif ( $reader->nodeType == XMLReader::TEXT) {
        
        if ($status == 'reading_identifier') {
          $item = array( 'uri' => $reader->value);
          $status = 'seeking_datestamp';        
        }
        elseif ($status == 'reading_datestamp') {
          $item['datestamp'] = $reader->value;
          $items[] = $item;
          $status = 'seeking_header';        
        }
        elseif ($status == 'reading_token') {
          $res['token'] = $reader->value;
          $status = 'seeking_header';        
        }
      }
    }
    $reader->close();

    $res['items'] = $items;
    return $res;
  }

}
?>