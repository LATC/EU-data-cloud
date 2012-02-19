<?php 
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'moriarty.inc.php';
require_once MORIARTY_DIR. 'simplegraph.class.php';

/**
 * Represents a store's job queue
 * @see http://n2.talis.com/wiki/Job_Queue
 */
class JobQueue {
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
   * @param string uri URI of the job queue
   * @param Credentials credentials the credentials to use for authenticated requests (optional)
   */ 
  function __construct($uri, $credentials = null, $request_factory = null) {
    $this->uri = $uri;
    $this->credentials = $credentials;
    $this->request_factory = $request_factory;
  }


  /**
   * Schedule a reset data job in the queue
   * @see http://n2.talis.com/wiki/Reset_Data_Job
   * @param string time the time at which the job should commence (optional, defaults to current date and time)
   * @param string label a descriptive label to give the job (optional, defaults to boilerplate text)
   * @return HttpResponse
   */ 
  function schedule_reset_data($time = null, $label = null) {
    return $this->schedule_job($this->make_job_request(BF_RESETDATAJOB, $time, $label));
  }

  /**
   * Schedule a snaphot job in the queue
   * @see http://n2.talis.com/wiki/Snapshot_Job
   * @param string time the time at which the job should commence (optional, defaults to current date and time)
   * @param string label a descriptive label to give the job (optional, defaults to boilerplate text)
   * @return HttpResponse
   */ 
  function schedule_snapshot($time = null, $label = null) {
    return $this->schedule_job($this->make_job_request(BF_SNAPSHOTJOB, $time, $label));
  }

  /**
   * Schedule a reindex data job in the queue
   * @see http://n2.talis.com/wiki/Reindex_Job
   * @param string time the time at which the job should commence (optional, defaults to current date and time)
   * @param string label a descriptive label to give the job (optional, defaults to boilerplate text)
   * @return HttpResponse
   */ 
  function schedule_reindex($time = null, $label = null) {
    return $this->schedule_job($this->make_job_request(BF_REINDEXJOB, $time, $label));
  }

  /**
   * Schedule a restore data job in the queue
   * @see http://n2.talis.com/wiki/Restore_Job
   * @param string time the time at which the job should commence (optional, defaults to current date and time)
   * @param string label a descriptive label to give the job (optional, defaults to boilerplate text)
   * @return HttpResponse
   */ 
  function schedule_restore($snapshot_uri, $time = null, $label = null) {
    $job = $this->make_job_request(BF_RESTOREJOB, $time, $label);
    $job->add_resource_triple('_:job', BF_SNAPSHOTURI, $snapshot_uri);
    return $this->schedule_job($job);
  }

  /**
   * @access private
   */
  function make_job_request($jobtype, $time = null, $label = null) {
    $time = $time == null ? gmmktime() : $time;

    $formatted_time = gmdate("Y-m-d\TH:i:s\Z", $time);
    $label = $label == null ? 'Job submitted ' . $formatted_time : $label;

    $job = new SimpleGraph();
    $job->add_resource_triple('_:job', BF_JOBTYPE,   $jobtype);
    $job->add_resource_triple('_:job', RDF_TYPE,     BF_JOBREQUEST);
    $job->add_literal_triple( '_:job', BF_STARTTIME, $formatted_time);
    $job->add_literal_triple( '_:job', RDFS_LABEL ,  $label);
    return $job;
  }

  /**
   * @access private
   */
  function schedule_job($job) {
    if (empty( $this->request_factory) ) {
      $this->request_factory = new HttpRequestFactory();
    }

    $body = $job->to_rdfxml();

    $uri = $this->uri;
    $mimetype = MIME_RDFXML;

    $request = $this->request_factory->make( 'POST', $uri, $this->credentials);
    $request->set_accept("*/*");
    $request->set_content_type($mimetype);
    $request->set_body( $body );
    return $request->execute();
  }


  /**
   * Get a list of URIs of jobs that have been scheduled. This returns an empty array if any HTTP errors occur.
   * @return array list of job URIs
   */ 
   function get_item_uris(){
    if (empty( $this->request_factory) ) {
      $this->request_factory = new HttpRequestFactory();
    }

    $request = $this->request_factory->make( 'GET', $this->uri, $this->credentials);
    $request->set_accept("application/rdf+xml");
    $response =  $request->execute();
  
    if($response->is_success()){
      
      $parser = ARC2::getRDFXMLParser();
      $parser->parse($this->uri, $response->body);
      $triples = $parser->getTriples();
      $this->errors = $parser->getErrors();
      $uris = array();
      foreach($triples as $t)
      {
        if($t['p']=='http://schemas.talis.com/2006/bigfoot/configuration#job') $uris[]=$t['o'];
      }
      return $uris;
      
    } else {
      return array(); 
    }
    
  }

  public function get_item($jobUri)
  {
    if (empty( $this->request_factory)) 
    {
      $this->request_factory = new HttpRequestFactory();
    }

    $request = $this->request_factory->make( 'GET', $jobUri, $this->credentials);
    $request->set_accept("application/rdf+xml");
    $response =  $request->execute();
  
    return $response;
  }
}
?>