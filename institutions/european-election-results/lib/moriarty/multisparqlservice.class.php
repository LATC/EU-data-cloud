<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'moriarty.inc.php';
require_once MORIARTY_ARC_DIR . DIRECTORY_SEPARATOR . "ARC2.php";
require_once MORIARTY_DIR . 'sparqlservicebase.class.php';

/**
 * Represents a store's multi sparql service
 * @see http://n2.talis.com/wiki/Store_Multisparql_Service
 */
class MultiSparqlService extends SparqlServiceBase {

  /**
   * Obtain a bounded description of a given resource
   * @param mixed uri the URI of the resource to be described or an array of URIs
   * @param array graphs the list of graph URIs the description should be drawn from
   * @return HttpResponse
   */
  function describe( $uri, $graphs=array() ) {
    if ( is_array( $uri ) ) {
      $query = "DESCRIBE <" . implode('> <' , $uri) . ">";
    }
    else {
      $query = "DESCRIBE <$uri>";
    }

    foreach( $graphs as $graph_uri) {
      $query .= ' FROM <' . $graph_uri . '>';
    }

    return $this->graph($query);
  }

  /**
   * @deprecated triple lists are deprecated
   */
  function describe_to_triple_list( $uri, $graphs=array() ) {
    $triples = array();

    $response = $this->describe( $uri, $graphs );

    if ( $response->body ) {

      $parser_args=array(
        "bnode_prefix"=>"genid",
        "base"=> $this->uri
      );
      $parser = ARC2::getRDFXMLParser($parser_args);

      $parser->parse("", $response->body );
      $triples = $parser->getTriples();
    }
    return $triples;
  }

}

?>