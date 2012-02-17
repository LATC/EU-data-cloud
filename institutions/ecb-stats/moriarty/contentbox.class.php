<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'moriarty.inc.php';
require_once MORIARTY_ARC_DIR . "ARC2.php";
require_once MORIARTY_DIR . 'httprequest.class.php';
require_once MORIARTY_DIR . 'httprequestfactory.class.php';

/**
 * Represents a store's contentbox
 * @see http://n2.talis.com/wiki/Contentbox
 */
class Contentbox {
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
   * @param string uri URI of the contentbox
   * @param Credentials credentials the credentials to use for authenticated requests (optional)
   */ 
  function __construct($uri, $credentials = null, $request_factory = null) {
    $this->uri = $uri;
    $this->credentials = $credentials;
    $this->request_factory = $request_factory;
  }

  /**
   * Get an item from the contentbox using only it's path 
   * @param string path the path of the item in the content box.
   * @return HttpResponse
   */
  function get_item_by_path( $path ) {
    return $this->get_item($this->uri . $path);
  }

  /**
   * Get an item from the contentbox
   * @param string uri the full uri of the item in the content box.
   * @return HttpResponse
   */
  function get_item( $uri ) {
    if (! isset( $this->request_factory) ) {
      $this->request_factory = new HttpRequestFactory();
    }

    $request = $this->request_factory->make( 'GET', $uri, $this->credentials );

    return $request->execute();
  }

  function make_search_uri( $query, $max=10, $offset=0, $sort=false) {
    if (! is_numeric($offset)) $offset = 0;
    if (! is_numeric($max)) $max = 10;
    $uri = $this->uri . '?query=' . urlencode($query) . '&max=' . urlencode($max) . '&offset=' . urlencode($offset);
    $uri.= ($sort)? '&sort='.urlencode($sort) : '';
    return $uri;
  }

  /**
   * Perform a search on the contentbox
   * @param string query the query expression used to query the content box.
   * @param int max maximum number of results for a search. (optional, defaults to 10)
   * @param int offset an offset into search results. Use with max to implement paging. (optional, defaults to 0)
   * @param string sort a comma separated list of field names that should be used to order the results.
   * @return HttpResponse
   */
  function search( $query, $max=10, $offset=0, $sort=false) {
    if (empty( $this->request_factory) ) {
      $this->request_factory = new HttpRequestFactory();
    }


    $request = $this->request_factory->make( 'GET', $this->make_search_uri($query, $max, $offset, $sort), $this->credentials );
    $request->set_accept(MIME_RSS);

    return $request->execute();
  }

  /**
   * Perform a search on the contentbox
   * @deprecated triple lists are deprecated
   */
  function search_to_triple_list( $query, $max=10, $offset=0 ) {
    $triples = array();

    $response = $this->search( $query, $max, $offset );
   $parser_args=array(
      "bnode_prefix"=>"genid",
      "base"=> $this->uri
    );


    if ( $response->body ) {
      $parser = ARC2::getRDFXMLParser($parser_args);
      $parser->parse($this->uri, $response->body );
      $triples = $parser->getTriples();
    }
    return $triples;
  }

  /**
   * Perform a search on the contentbox. This method returns an empty ResourceList if the HTTP request fails for any reason.
   * @param string query the query expression used to query the content box.
   * @param int max maximum number of results for a search. (optional, defaults to 10)
   * @param int offset an offset into search results. Use with max to implement paging. (optional, defaults to 0)
   * @return ResourceList
   */
  function search_to_resource_list( $query, $max=10, $offset=0 ) {
    $triples = array();
    $uri = $this->make_search_uri($query, $max, $offset);

    $response = $this->search( $query, $max, $offset );

    if ($response->is_success()) {
      return $this->parse_results_xml($uri, $response->body);
    }
    else {
      $resources = new ResourceList();
      $resources->items = Array();
      return $resources;
    }
  }

  /**
   * Parse the results of a search on the contentbox.
   * @param string uri the URI used to obtain the search
   * @param string xml the xml returned from a search request
   * @return ResourceList
   */
  function parse_results_xml($uri, $xml) {
    // fix up unprefixed rdf:resource in rss 1.0 otherwise ARC gets confused
    $xml = preg_replace("~rdf:li resource=~", "rdf:li rdf:resource=", $xml);

    $parser_args=array(
      "bnode_prefix"=>"genid",
      "base"=> $this->uri
    );
    $resources = new ResourceList();
    $resources->items = Array();

    $parser = ARC2::getRDFXMLParser($parser_args);
    $parser->parse($this->uri, $xml );
    $triples = $parser->getTriples();
    $index = ARC2::getSimpleIndex($triples, true) ;




    $resources->title = $index[$uri][RSS_TITLE][0];
    $resources->description = $index[$uri][RSS_DESCRIPTION][0];
    $resources->start_index = $index[$uri][OS_STARTINDEX][0];
    $resources->items_per_page = $index[$uri][OS_ITEMSPERPAGE][0];
    $resources->total_results = $index[$uri][OS_TOTALRESULTS][0];

    $items_resource = $index[$uri][RSS_ITEMS][0];
    foreach ($index[$items_resource] as $items_property => $items_property_value) {
      if ( strpos( $items_property, 'http://www.w3.org/1999/02/22-rdf-syntax-ns#_') === 0 ) {
        $resources->items[] = $index[$items_property_value[0]];
      }
    }

    return $resources;
  }
  
  /**
   * save_item
   *
   * @return HttpResponse
   * @author Keith Alexander
   **/
  function save_item($document, $content_type)
  {
      $request = $this->request_factory->make('POST', $this->uri, $this->credentials);
      $request->set_body($document);
      $request->set_content_type($content_type);
      return $request->execute();
  }

}

/**
 * Represents a list of resources returned from a contentbox search.
 */
class ResourceList {
  /**
   * The title of the search results
   * @var string
   */
  var $title;
  /**
   * The index of the first search result in the current set of search results. 
   * @var int
   */
  var $start_index;
  /**
   * The number of search results returned per page. 
   * @var int
   */
  var $items_per_page;
  /**
   * The total number of results matching the search terms
   * @var int
   */
  var $total_results;
  /**
   * The description of the search results
   * @var string
   */
  var $description;
  /**
   * An array of the search results as a list of triples
   * @var array
   */
  var $items;
}
?>