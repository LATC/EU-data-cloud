<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'moriarty.inc.php';
require_once MORIARTY_ARC_DIR . "ARC2.php";
require_once MORIARTY_DIR . 'httprequest.class.php';
require_once MORIARTY_DIR . 'httprequestfactory.class.php';
require_once MORIARTY_DIR . 'simplegraph.class.php';
require_once MORIARTY_DIR . 'changeset.class.php';
/**
 * The base class for graphs in a store.
 */
class Graph {
  /**
   * @access private
   */
  var $uri;
  /**
   * @access private
   */
  var $credentials;
  /**
   * @access private
   */
  var $request_factory;

  /**
   * Create a new instance of this class
   * @param string uri URI of the graph
   * @param Credentials credentials the credentials to use for authenticated requests (optional)
   */
  function __construct($uri, $credentials = null, $request_factory = null)  {
    $this->uri = $uri;
    $this->credentials = $credentials;
    $this->request_factory = $request_factory;
  }

  /**
   * Apply a changeset to the graph
   * @param ChangeSet cs the changeset to apply
   * @return HttpResponse
   */
  function apply_changeset($cs) {
    return $this->apply_changeset_turtle( $cs->to_turtle());
  }

  /**
   * Apply a changeset in a versioned manner to the graph
   * @param ChangeSet cs the changeset to apply
   * @return HttpResponse
   */
  function apply_versioned_changeset($cs) {
    return $this->apply_versioned_changeset_rdfxml( $cs->to_rdfxml());
  }

    /**
   * Apply a changeset to the graph
   * @param string rdfxml the changeset to apply, serialised as RDF/XML
   * @return HttpResponse
   */
  function apply_changeset_turtle($turtle) {
    if (empty( $this->request_factory) ) {
      $this->request_factory = new HttpRequestFactory();
    }

    $uri = $this->uri;

    $request = $this->request_factory->make( 'POST', $uri, $this->credentials);
    $request->set_accept("*/*");
    $request->set_content_type("application/vnd.talis.changeset+turtle");
    $request->set_body( $turtle );

    return $request->execute();
  }

 


  /**
   * Apply a changeset to the graph
   * @param string rdfxml the changeset to apply, serialised as RDF/XML
   * @return HttpResponse
   */
  function apply_changeset_rdfxml($rdfxml) {
    if (empty( $this->request_factory) ) {
      $this->request_factory = new HttpRequestFactory();
    }

    $uri = $this->uri;

    $request = $this->request_factory->make( 'POST', $uri, $this->credentials);
    $request->set_accept("*/*");
    $request->set_content_type("application/vnd.talis.changeset+xml");
    $request->set_body( $rdfxml );

    return $request->execute();
  }

  /**
   * Apply a changeset in a versioned manner to the graph
   * @param string rdfxml the changeset to apply, serialised as RDF/XML
   * @return HttpResponse
   */
  function apply_versioned_changeset_rdfxml($rdfxml) {
    if (empty( $this->request_factory) ) {
      $this->request_factory = new HttpRequestFactory();
    }

    $uri = $this->uri . '/changesets';

    $request = $this->request_factory->make( 'POST', $uri, $this->credentials);
    $request->set_accept("*/*");
    $request->set_content_type("application/vnd.talis.changeset+xml");
    $request->set_body( $rdfxml );

    return $request->execute();
  }

  /**
   * Submit some RDF/XML to be added to the graph
   * @param string rdfxml the RDF to be submitted, serialised as RDF/XML
   * @return HttpResponse
   */
  function submit_rdfxml($rdfxml) {
    if (empty( $this->request_factory) ) {
      $this->request_factory = new HttpRequestFactory();
    }

    $uri = $this->uri;
    $request = $this->request_factory->make( 'POST', $uri, $this->credentials);
    $request->set_content_type("application/rdf+xml");
    $request->set_accept("*/*");
    $request->set_body( $rdfxml );
    return $request->execute();
  }


  /**
   * Submit some Turtle to be added to the graph
   * @param string turtle the RDF to be submitted, serialised as Turtle
   * @return HttpResponse
   */
  function submit_turtle($turtle, $gzip_encode=false) {
    if (empty( $this->request_factory) ) {
      $this->request_factory = new HttpRequestFactory();
    }
    $uri = $this->uri;
    $request = $this->request_factory->make( 'POST', $uri, $this->credentials);
    $request->set_content_type("text/turtle");
    $request->set_accept("*/*");
    $request->set_body( $turtle, $gzip_encode);
    return $request->execute();
  }

  function submit_ntriples_in_batches_from_file($filename,$no_of_lines=500, $callback=false) {
    $responses = array();
    $pointer = fopen($filename, 'r');
    $batch = '';
    $lineCount=0;
    while($line =  fgets($pointer)){
      $batch.=$line;
      $lineCount++;
      if($lineCount==$no_of_lines){
        $response = $this->submit_turtle($batch);
        $responses[] = $response;
        if(is_callable($callback)){
          call_user_func_array($callback, array($response, $batch));
        } else if($response->is_success()===false){
          return $responses;
        } 

          $lineCount=0;
          $batch='';
        
      }
    }
    if(!empty($batch)){
      $response=$this->submit_turtle($batch);
      if(is_callable($callback)){
          call_user_func($callback, $response);
      } 
      $responses[]=$response;
    }
    return $responses;
  }

  /**
   * Obtain the graph's bounded description of a given resource
   * @see http://n2.talis.com/wiki/Metabox#Describing_a_Resource
   * @param string uri the URI of the resource to be described
   * @param string output the desired output format of the response (e.g. rdf, xml, json, ntriples, turtle)
   * @return HttpResponse
   */
  function describe( $uri, $output = null ) {

    $request = $this->get_describe_request($uri, $output);

    return $request->execute();
  }

  function get_describe_uri( $uri, $output = null ) {
    $request_uri = $this->uri . '?about=' . urlencode($uri);
    if ($output) {
      $request_uri .= '&output=' . urlencode($output);
    }
    return $request_uri;
  }
  function get_describe_request( $uri, $output = null ) {
    if (empty( $this->request_factory) ) {
      $this->request_factory = new HttpRequestFactory();
    }

    $request_uri = $this->get_describe_uri($uri, $output);
  
    $request = $this->request_factory->make( 'GET', $request_uri, $this->credentials);
    if ($output) {
      $request->set_accept("*/*");
    }
    else {
      $request->set_accept("application/rdf+xml");
    }
    $request->set_content_type("application/x-www-form-urlencoded");

    return $request;
  }

  /**
   * Obtain the graph's bounded description of a given resource
   * @deprecated triple lists are deprecated
   */
  function describe_to_triple_list( $uri ) {
    $triples = array();

    $response = $this->describe( $uri );
    $parser_args=array(
      "bnode_prefix"=>"genid",
      "base"=> $this->uri
    );
    $parser = ARC2::getRDFXMLParser($parser_args);

    if ( $response->body ) {
      $parser->parse($this->uri, $response->body );
      $triples = $parser->getTriples();
    }

    return $triples;
  }

  /**
   * Obtain the graph's bounded description of a given resource. This is designed to be fast since it uses RDF/JSON which requires no parsing by the SimpleGraph class. This method always returns a SimpleGraph, which will be empty if any HTTP errors occured.
   * @see http://n2.talis.com/wiki/Metabox#Describing_a_Resource
   * @param string uri the URI of the resource to be described
   * @return SimpleGraph
   */
  function describe_to_simple_graph( $uri ) {
    $graph = new SimpleGraph();

    $response = $this->describe( $uri, OUTPUT_TYPE_JSON );

    if ( $response->is_success() ) {
      $graph->from_json( $response->body );
    }

    return $graph;
  }

  /**
   * Tests whether the graph contains a bounded description of a given resource. This uses a conditional GET.
   * @see http://n2.talis.com/wiki/Metabox#Describing_a_Resource
   * @param string uri the URI of the resource to be described
   * @return boolean true if the graph contains triples with the resource as a subject, false otherwise
   */
  function has_description( $uri ) {
    if (empty( $this->request_factory) ) {
      $this->request_factory = new HttpRequestFactory();
    }

    $request_uri = $this->uri . '?about=' . urlencode($uri);
    $request = $this->request_factory->make( 'GET', $request_uri, $this->credentials);
    $request->set_accept("application/rdf+xml");
    $request->set_if_match("*");

    $response = $request->execute();

    if ($response->status_code == 200) {
      return true;
    }
    else if ($response->status_code == 412) {
      return false;
    }
  }

    /**
   * mirror_from_uri:
   *
   * @return array of responses from http requests, and overall success status 
   * @author Keith Alexander
   *
   *
  **/
  function mirror_from_uri($url, $rdf_content=false)
  {

      $return = array(
        'get_page' => false,
        'get_copy' => false,
        'update_data' => false,
        'success' => false,
      );

    if (empty( $this->request_factory) ) {
      $this->request_factory = new HttpRequestFactory();
    }
    

    if(!$rdf_content){
      
      $web_page_request  = $this->request_factory->make('GET', $url); 
      $web_page_request->set_accept('application/rdf+xml;q=0.8,text/turtle;q=0.9,*/*;q=0.1');
      $web_page_response = $web_page_request->execute();
      $return['get_page'] = $web_page_response;
      $web_page_content = $web_page_response->body;
    } else {
      $web_page_content = $rdf_content;
      $return['rdf_content'] = $rdf_content;
    }
    if($rdf_content OR $web_page_response->is_success() ){

    $newGraph = new SimpleGraph();
    $newGraph->add_rdf($web_page_content, $url);
    $jsonGraphContent = $newGraph->to_json();
    $newGraph->add_resource_triple($url, OPEN_JSON, $jsonGraphContent);
    $newGraph->skolemise_bnodes(trim($url,'#').'#');
    $after = $newGraph->get_index();
    # get previous copy if it exists
    $cached_page_response = $this->describe($url, 'json');
    $return['get_copy'] = $cached_page_response;
            if($cached_page_response->status_code == '200'){
              $description_index =  json_decode($cached_page_response->body, true);
              if(isset($description_index[$url]) AND isset($description_index[$url][OPEN_JSON])){
                $before = json_decode($description_index[$url][OPEN_JSON][0]['value'], 1);
              } else {
                $before = false;
              }
            } else if( $cached_page_response->status_code == '404' ) {
              $before = false;
            } else {
                return $return;
            }
    # build new changeset

    $Changeset = new ChangeSet(array('before' => $before, 'after' => $after, 'creatorName' => 'Graph::mirror_from_uri', 'changeReason' => 'mirroring from '.$url));

    if($Changeset->has_changes()){
      $return['update_data'] = $this->apply_changeset($Changeset);
      if($return['update_data']->is_success()){
        $return['success'] = true;
      } else if($return['update_data']->status_code=='409'){ # Conflict. some statements already removed.
        $before_graph = new SimpleGraph($before);
        $return['reapply_before_triples'] = $this->get_metabox()->submit_turtle($before_graph->to_turtle());
        if($return['reapply_before_triples']->status_code=='204'){ #Succeeded. No content
          $return['update_data'] = $this->get_metabox()->apply_changeset($Changeset);
          $return['success'] = $return['update_data']->is_success();
        }
      } else {
        return $return;
      } 
      return $return;
    } else {
       $return['success'] = true;
       return $return;
    }
    } else {
    
      return $return;
    }
  }




}
?>
