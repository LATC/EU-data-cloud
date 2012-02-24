<?php

define('HTTP_Unsupported_Media_Type', 'HTTP/1.1 415 Unsupported Media Type');
define('HTTP_OK', 'HTTP/1.1 200 OK');
define('HTTP_Not_Found', 'HTTP/1.1 404 Not Found');
define('HTTP_Internal_Server_Error', 'HTTP/1.1 500 Internal Server Error');
define('HTTP_Bad_Request', 'HTTP/1.1 400 Bad Request');

require_once 'lib/moriarty/moriarty.inc.php';
require_once 'lib/moriarty/sparqlservice.class.php';
require_once 'lib/moriarty/credentials.class.php';
require_once 'graphs/linkeddataapigraph.class.php';
require_once 'sparqlwriter.class.php';

class LinkedDataApiResponse {
    
    var $statusCode = HTTP_OK;
    var $Request = false;
    var $ConfigGraph = false;
    var $SparqlWriter = false;
    var $DataGraph = false;
    var $SparqlEndpoint = false;
    var $outputFormats = array(
	
	 'html' => array(
                'ext' => 'html',
                'view' => 'views/php-templates/puelia-outer.php',
                'mimetypes' => array(
			'text/html', 'application/xhtml+xml'
               )
            ),
      
        'json' => array(
                'ext' => 'json',
                'view' => 'views/simple_json.php',
                'mimetypes' => array(
                        'application/json',
                    ),
            ),
        'rdfjson' => array(
                'ext' => 'rdfjson',
                'view' => 'views/rdf_json.php',
                'mimetypes' => array(
                        'application/x-rdf+json',
                    ),
            ),
        
        'ttl' => array(
                'ext' => 'ttl',
                'view' => 'views/turtle.php',
                'mimetypes' => array(
                        'text/plain',
                    ),
            ),
        
        'rdf' => array(
                'ext' => 'rdf',
                'view' => 'views/rdf_xml.php',
                'mimetypes' => array(
                        'application/rdf+xml',
                    ),
            ),
        'xml' => array(
                'ext' => 'xml',
                'view' => 'views/simple_xml.php',
                'mimetypes' => array(
                        'application/xml',
                    ),
            ),
           
        );
        
    var $pageUri = false;
    var $selectQuery = '';
    var $viewQuery = '';
    var $viewer = false;
    var $errorMessages = array();
    var $listUri = false;
    var $endpointUrl = false;
    var $overrideUserConfig= false;
    var $HttpRequestFactory=null;
    var $list_of_item_uris = null;

    function __construct($request, $ConfigGraph, &$HttpRequestFactory=false){

        $this->Request = $request;
        $this->pageUri = $this->Request->getUriWithPageParam();
        $this->ConfigGraph = $ConfigGraph;
        $this->DataGraph = new LinkedDataApiGraph(false, $this->ConfigGraph);
        $this->generatedTime = time();
        $this->lastModified = gmdate("D, d M Y H:i:s") . " GMT";
        $this->cacheable = false;
        if($HttpRequestFactory){
          $this->HttpRequestFactory = $HttpRequestFactory;
        }
    }
    
    function serveConfigGraph(){
        $this->overrideUserConfig=true;
        $api = API;
        $configUrl = CONFIG_URL;
        $configPath = CONFIG_PATH;
        $this->endpointUrl = CONFIG_URL;
        $this->ConfigGraph->resetApiAndEndpoint(CONFIG_URL.'#api', CONFIG_URL.'#endpoint');
        $this->ConfigGraph->add_resource_triple(CONFIG_URL.'#endpoint', RDF_TYPE, API.'ItemEndpoint');
        $this->ConfigGraph->add_resource_triple(CONFIG_URL.'#api', API.'contentNegotiation', API.'parameterBased');
        $this->ConfigGraph->add_resource_triple(CONFIG_URL.'#api', API.'defaultFormatter', CONFIG_URL.'#HtmlFormatter');
        $this->ConfigGraph->add_literal_triple(CONFIG_URL.'#HtmlFormatter',  API.'mimeType', 'text/html');
        $this->ConfigGraph->add_literal_triple(CONFIG_URL.'#HtmlFormatter',  API.'name', 'html');
        $this->ConfigGraph->add_resource_triple(CONFIG_URL.'#HtmlFormatter',  RDF.'type', API.'Formatter');
        
        $this->outputFormats['xml']['view']= 'views/rdf_xml.php';
        $this->outputFormats['json']['view']= 'views/rdf_json.php';
        $this->outputFormats['html']['view']= 'views/config.html.php';
        $this->DataGraph->add_graph($this->ConfigGraph);
        $this->addMetadataToPage();   
    }
    
    function process(){

        try{
            if($param = $this->Request->hasUnrecognisedReservedParams()){
                logError("Bad Request: Unrecognised Unreserved Param: {$param}");
                $this->errorMessages[]="Unrecognised Unreserved Param: {$param}";
                $this->setStatusCode(HTTP_Bad_Request);
                $this->serve();
            }
            
            $endpointUri = $this->ConfigGraph->getEndpointUri();
            $apiUri = $this->ConfigGraph->getApiUri();
            if(empty($endpointUri) OR empty($apiUri)){
                $this->setStatusCode(HTTP_Not_Found);
                $this->serve();
            }
            
            $this->SparqlWriter = new SparqlWriter($this->ConfigGraph, $this->Request);            
            $viewerUri = $this->getViewer();
            logDebug("Viewer URI: " . $viewerUri);
            if($this->SparqlWriter->hasUnknownPropertiesFromRequest()){
                $this->errorMessages[]="Unknown Properties in Request: {$param}";
                $this->setStatusCode(HTTP_Bad_Request);
                $this->serve();
            } else if($this->SparqlWriter->hasUnknownPropertiesFromConfig($viewerUri)){
                $this->setStatusCode(HTTP_Internal_Server_Error);
                $unknownProps = implode(', ', $this->SparqlWriter->getUnknownPropertiesFromConfig());
                $msg = "One or more properties named in filters for API {$apiUri} are not in a vocabulary linked to from the API: {$unknownProps}";
                logError($msg);
                $this->errorMessages[]=$msg;
                $this->serve();
            }
        } catch (Exception $e) {
            $this->setStatusCode(HTTP_Internal_Server_Error);
            $this->errorMessages[]=$e->getMessage();
            logError($e->getMessage());
            $this->serve();
        }
        
        
        $requestUri = $this->Request->getUri();

        $endpointUri = $this->ConfigGraph->getEndpointUri();
        $this->endpointUrl = $endpointUri;
        if(strpos($this->endpointUrl, '_:')===0) $this->endpointUrl = CONFIG_URL;
        
        try {
            $sparqlEndpointUri = $this->ConfigGraph->getSparqlEndpointUri();
        } catch (Exception $e) {
            $this->setStatusCode(HTTP_Internal_Server_Error);
            logError($e->getMessage());
            $apiUri = $this->ConfigGraph->getApiUri();
            $this->errorMessages[]=" The API is not configured correctly; <{$apiUri}> needs to be given an api:sparqlEndpoint property";
            $this->serve();        
        }
        if(defined('SPARQL_Username') && defined('SPARQL_Password')){
          $credentials = new Credentials(SPARQL_Username, SPARQL_Password);
        } else {
          $credentials = false;
        }
        $this->SparqlEndpoint = new SparqlService($sparqlEndpointUri, $credentials, $this->HttpRequestFactory);

        
        switch($this->ConfigGraph->getEndpointType()){
            case API.'ListEndpoint' : 
            case PUELIA.'SearchEndpoint' :
                $this->loadDataFromList();    
                break;
            case API.'ItemEndpoint' :
                $this->loadDataFromItem();
                break;
        }
        
        $this->addMetadataToPage();
        
    }
    

    function loadDataFromItem(){
        $uri = $this->ConfigGraph->getCompletedItemTemplate();
        $this->list_of_item_uris = array($uri);
        $viewerUri = $this->getViewer();
        $this->viewQuery  = $this->SparqlWriter->getViewQueryForUri($uri, $viewerUri);
        if (LOG_VIEW_QUERIES) {
          logViewQuery($this->Request, $this->viewQuery);
        }
        $response = $this->SparqlEndpoint->graph($this->viewQuery, PUELIA_RDF_ACCEPT_MIMES);
        $pageUri = $this->Request->getUriWithoutPageParam();
        if($response->is_success()){
            $rdf = $response->body;
            $this->DataGraph->add_rdf($rdf);
            $this->DataGraph->add_resource_triple($pageUri, FOAF.'primaryTopic', $uri);
            $label = $this->DataGraph->get_first_literal($uri, SKOS.'prefLabel');
            if(!empty($label) || $label = $this->DataGraph->get_label($uri)){
              $this->DataGraph->add_literal_triple($pageUri, RDFS_LABEL, $label);
            }
 
            $this->DataGraph->add_resource_triple($uri , FOAF.'isPrimaryTopicOf', $pageUri);
            $this->DataGraph->add_resource_triple($this->Request->getUri(), API.'definition', $this->endpointUrl);
			if($datasetUri = $this->ConfigGraph->getDatasetUri()){
            	$this->DataGraph->add_resource_triple($pageUri, VOID.'inDataset', $datasetUri);				
              $voidRequest = $this->HttpRequestFactory->make('GET', $datasetUri);
              $voidRequest->set_accept(PUELIA_RDF_ACCEPT_MIMES);
              $voidResponse = $voidRequest->execute();
              if($voidResponse->is_success()){
                $voidGraph = new SimpleGraph();
                $base = array_shift(explode('#',$datasetUri));
                $voidGraph->add_rdf($voidResponse->body, $base) ;
                if($licenseUri = $voidGraph->get_first_resource($datasetUri, DCT.'license')){
                  $this->DataGraph->add_resource_triple($this->Request->getUri(), DCT.'license', $licenseUri);
                } else {
                  logDebug($datasetUri.' has no dct:license');
                }
              } else {
                logDebug("VoID document could not be fetched from {$datasetUri}");
              }

			}


        } else {
            logError("Endpoint returned {$response->status_code} {$response->body} View Query <<<{$this->viewQuery}>>> failed against {$this->SparqlEndpoint->uri}");
            $this->setStatusCode(HTTP_Internal_Server_Error);
            $this->errorMessages[]="The SPARQL endpoint used by this URI configuration did not return a successful response.";
        }
        $this->pageUri = $pageUri;
    }


    function addRelatedPages(){
      $viewerUri = $this->getViewer();
      if($list = $this->getListOfUris() and is_array($list)){
      foreach($list as $itemUri){
        if($relatedPages = $this->ConfigGraph->getViewerRelatedPagesForItemUri($viewerUri, $itemUri)){
          foreach($relatedPages as $pageUri => $label){
            $this->DataGraph->add_resource_triple($itemUri, PUELIA.'related', $pageUri);
            $this->DataGraph->add_literal_triple($pageUri, RDFS_LABEL, $label);
          }
        }
      }
      }
    }

    function addSiteMetadata(){
      
      $apiLiteralProperties = array(
       DCT.'description',
        API.'base'
      );
      $apiResourceProperties = array(
        FOAF.'logo',
        XHTML.'icon',
        PUELIA.'javascript',
        XHTML.'stylesheet',
      );

      $siteUri = $this->ConfigGraph->getApiUri();
      $this->DataGraph->add_resource_triple($this->pageUri , PUELIA.'site', $siteUri);
      $this->DataGraph->add_literal_triple($siteUri, RDFS_LABEL, $this->ConfigGraph->get_label($siteUri));
      foreach($apiLiteralProperties as $p){
        if($v = $this->ConfigGraph->get_first_literal($siteUri, $p)){
          $this->DataGraph->add_literal_triple($siteUri, $p, $v);
        }
      }
      foreach($apiResourceProperties as $p){
        if($v = $this->ConfigGraph->get_first_resource($siteUri, $p)){
           $this->DataGraph->add_resource_triple($siteUri, $p, $v);
        }
      }

      $pathsAndLabels = $this->ConfigGraph->getUriTemplatesWithoutVariables();
      $paths = array_keys($pathsAndLabels);

      foreach($paths  as $no => $path){
        $fullLink = $this->Request->getBaseAndSubDir().$path.'?_page=1';
        $label = $pathsAndLabels[$path];
        $this->DataGraph->add_literal_triple($fullLink, RDFS_LABEL, $label);

        foreach($paths as $noB => $pathB){
          if(strpos($path, $pathB)===0 AND $path!=$pathB){
            $this->DataGraph->add_resource_triple($this->Request->getBaseAndSubDir().$pathB.'?_page=1', PUELIA.'link', $fullLink);
            continue 2;
          }
        }
        $this->DataGraph->add_resource_triple($siteUri, PUELIA.'link', $fullLink);
      }
    }

    function loadDataFromList(){
        $list = $this->getListOfUris();
        $viewerUri = $this->getViewer();
        logDebug("Viewer URI is $viewerUri");
        $this->viewQuery  = $this->SparqlWriter->getViewQueryForUriList($list, $viewerUri);
        if (LOG_VIEW_QUERIES) {
          logViewQuery( $this->Request, $this->viewQuery);
        }
        $response = $this->SparqlEndpoint->graph($this->viewQuery, PUELIA_RDF_ACCEPT_MIMES);
        if($response->is_success()){
            $rdf = $response->body;
            if(isset($response->headers['content-type'])){
              if(strpos($response->headers['content-type'], 'turtle')){
                  $this->DataGraph->add_turtle($rdf);
              } else {
                  $this->DataGraph->add_rdf($rdf);
              }
            } else {
              $this->DataGraph->add_rdf($rdf);
            }

            $listUri = $this->Request->getUriWithoutParam(array('_view', '_page'), 'strip extension');
            $this->listUri = $listUri;
            $pageUri = $this->Request->getUriWithPageParam();
            $currentPage = $this->Request->getPage();
            $this->DataGraph->add_resource_triple($listUri, API.'definition', $this->endpointUrl);
            $this->DataGraph->add_resource_triple($listUri, RDF_TYPE, API.'List');
            $this->DataGraph->add_resource_triple($pageUri, RDF_TYPE, API.'Page');
            if($label = $this->ConfigGraph->getPageTitle()){
              $this->DataGraph->add_literal_triple($pageUri, RDFS_LABEL, $label);
            }
            $this->DataGraph->add_resource_triple($listUri, DCT.'hasPart', $pageUri);
            $this->DataGraph->add_resource_triple($pageUri, DCT.'isPartOf', $listUri);
            $this->DataGraph->add_resource_triple($pageUri, XHV.'first', $this->Request->getUriWithPageParam(1));
            if(count($list) >= $this->SparqlWriter->getLimit()){
                $this->DataGraph->add_resource_triple($pageUri, XHV.'next', $this->Request->getUriWithPageParam($currentPage+1));
            }
            if($currentPage > 1){
                $this->DataGraph->add_resource_triple($pageUri, XHV.'prev', $this->Request->getUriWithPageParam($currentPage-1));
            }
            $this->DataGraph->add_literal_triple($pageUri, OPENSEARCH.'itemsPerPage', $this->SparqlWriter->getLimit(), null, XSD.'integer');
            $this->DataGraph->add_literal_triple($pageUri, OPENSEARCH.'startIndex', $this->SparqlWriter->getOffset(), null, XSD.'integer');
            $this->DataGraph->add_literal_triple($pageUri, DCT.'modified', date("Y-m-d\TH:i:s"), null, XSD.'dateTime' );
            $rdfListUri = '_:itemsList';

			if($datasetUri = $this->ConfigGraph->getDatasetUri()){
            	$this->DataGraph->add_resource_triple($pageUri, VOID.'inDataset', $datasetUri);				
			}


            $this->DataGraph->add_resource_triple($pageUri, API.'items', $rdfListUri);
            $this->DataGraph->add_resource_triple($rdfListUri, RDF_TYPE, RDF_LIST);
            foreach($list as $no => $resourceUri){
                $nextNo = ($no+1);
                $nextList = (($no+1) == count($list))? RDF_NIL : '_:itemsList'.$nextNo;
                $this->DataGraph->add_resource_triple($rdfListUri, RDF_FIRST, $resourceUri);
                $this->DataGraph->add_resource_triple($rdfListUri, RDF_REST, $nextList);
                $rdfListUri = $nextList;
            }
            
        } else {
            logError("Endpoint returned {$response->status_code} {$response->body} View Query <<<{$this->viewQuery}>>> failed against {$this->SparqlEndpoint->uri}");
            $this->setStatusCode(HTTP_Internal_Server_Error);
            $this->errorMessages[]="The SPARQL endpoint used by this URI configuration did not return a successful response.";
            
        }
        
    }
    
    
    function getViewer(){
        if($this->viewer) return $this->viewer;

        if($name = $this->Request->getView()){
            logDebug("Viewer Name from Request is $name");
            if($this->viewerUri = $this->ConfigGraph->getViewerByName($name)){
                return $this->viewerUri;
            } else {
                logError("Bad Request when picking viewer for {$name}");
                $this->errorMessages[]="There is no viewer called \"{$name}\" configured for this endpoint";
                $this->setStatusCode(HTTP_Bad_Request);
                $this->serve();
            }
        } else {
            if($this->viewerUri = $this->ConfigGraph->getEndpointDefaultViewer()){
              logDebug("Endpoint Default Viewer is $this->viewerUri");
                return $this->viewerUri;
            } else if($this->viewerUri = $this->ConfigGraph->getApiDefaultViewer()){
              logDebug("API Default Viewer is $this->viewerUri");
                return $this->viewerUri;
            } else {
                logDebug("returning default viewer");
                return  API.'describeViewer';
            }
        }
    }
    
    function getListOfUrisFromSearchEndpoint(){
      // get searchindexendpoint
      $rssTextSearchIndex = $this->ConfigGraph->getRssTextSearchIndex();
      // construct url with search parameter
      $searchQuery = $this->Request->getParam('_search');
      $filterstring = implode('&', $this->ConfigGraph->getAllFilters());
      foreach($this->Request->getUnreservedParams() as $k => $v){
          list($k, $v) = array(urlencode($k), urlencode($v));
          $filterstring.="&{$k}={$v}";
        }

      $params = queryStringToParams($filterstring);
      $query_filter='';
      foreach($params as $k => $v){
        if($uri = $this->ConfigGraph->getUriForVocabPropertyLabel($v)){
          $v = $uri;
        }
        $query_filter.=' '.$k.':'.addcslashes($v, ':');
      }
      $queryUri = $rssTextSearchIndex.'?query='.urlencode($searchQuery.$query_filter);
      logDebug($queryUri);
      $request = $this->HttpRequestFactory->make('GET', $queryUri);
      $request->set_accept(PUELIA_RDF_ACCEPT_MIMES);
      // do request
      $response = $request->execute();
      if($response->is_success()){
        $ListGraph =  new PueliaGraph();
        $ListGraph->add_rdf($response->body);
        //get Sequence
        $SequenceUri = $ListGraph->get_first_resource($queryUri, RSS_ITEMS);
        $this->list_of_item_uris = $ListGraph->get_sequence_values($SequenceUri);
        return $this->list_of_item_uris;
      } else {
            logError("Endpoint returned {$response->status_code} {$response->body} Search Query URI: {$queryUri}");
            $this->setStatusCode(HTTP_Internal_Server_Error);
            $this->errorMessages[]="The endpoint used for this search did not return a successful response.";
        }
      return array();
    }

    function getListOfUrisFromSparqlEndpoint(){
        $list = array();
        try {
            $this->selectQuery = $this->SparqlWriter->getSelectQueryForUriList(); 
            if(LOG_SELECT_QUERIES){
                logSelectQuery($this->Request, $this->selectQuery);
            }
        } catch (Exception $e){
            logError($e->getMessage());
            $this->setStatusCode(HTTP_Internal_Server_Error);
            $this->errorMessages[]="There was a problem generating the SPARQL query for this request. There may be a configuration error.";
            $this->serve();
        }
        
        $response = $this->SparqlEndpoint->query($this->selectQuery, PUELIA_SPARQL_ACCEPT_MIMES);
        
        if($response->is_success()){
            if($response->body[0]=='{')//is JSON
             {
                $sparqlResults = json_decode($response->body, true);
                $results = $sparqlResults['results']['bindings'];
            } 
            else // is XML 
            {
                $xml = $response->body;
                $results = $this->SparqlEndpoint->parse_select_results($xml);
            }
            
            foreach($results as $row){
                if(isset($row['item'])) $list[]=$row['item']['value'];
            }
            
        } else {
            logError("Endpoint returned {$response->status_code} {$response->body} Select Query <<<{$this->selectQuery}>>> failed against {$this->SparqlEndpoint->uri}");
            $this->setStatusCode(HTTP_Internal_Server_Error);
            $this->errorMessages[]="The SPARQL endpoint used by this URI configuration did not return a successful response.";
            
        }
        $this->list_of_item_uris = $list;
        return $list;

    }
    
    function getListOfUris(){
      
      if($this->list_of_item_uris) return $this->list_of_item_uris;

        switch($this->ConfigGraph->getEndpointType()){
            case API.'ListEndpoint' : 
                $this->list_of_item_uris = $this->getListOfUrisFromSparqlEndpoint();
                break;
            case PUELIA.'SearchEndpoint' :
                $this->list_of_item_uris = $this->getListOfUrisFromSearchEndpoint();
                break;
            default:
              return false;
              break;
        }

        return $this->list_of_item_uris;
    }
    
    function setStatusCode($code){
        $this->statusCode = $code;
    }
    
    function extensionIsSupported($ext){
        logDebug("Requested File Extension: $ext");
        foreach($this->outputFormats as $name => $props){
            if($props['ext'] == $ext){
                logDebug("File Extension $ext is supported.");
                return true;
            }
        }
        if($formatUri = $this->ConfigGraph->getFormatterUriByName($ext)) return true;
        return false;        
    }
    
    function getOutputFormat(){
        if($this->Request->hasFormatExtension()){
            $extension = $this->Request->getFormatExtension();
            if($this->extensionIsSupported($extension)){
                foreach($this->outputFormats as $name => $props){
                    if($props['ext'] == $extension){
                        return $name;
                    }
                }
            } else {
                $this->setStatusCode(HTTP_Unsupported_Media_Type);
                return false;
            } 
        } 
        else if($this->Request->hasAcceptTypes()){
            $mimeTypes = $this->ConfigGraph->getDefaultMimeTypes();
            foreach($this->Request->getAcceptTypes($mimeTypes) as $acceptType){
                foreach($this->outputFormats as $formatName => $props){
                    if($props['ext'] == $acceptType || $acceptType == '*/*'){
                        return $formatName;
                    }
                }
            }
            return false;
        } else {
            return false;
        }
    }
  
	function addMetadataToPage(){
    
    $this->addRelatedPages();

		$metadataRequested = $this->Request->getMetadataParam();
		if(!empty($metadataRequested[0])){
			foreach($metadataRequested as $option){
				switch($option){
					case 'all':
						$this->addViewsMetadata();
						$this->addFormattersMetadata();
						$this->addExecutionMetadata();
						$this->addTermBindingsMetadata();
            $this->addSiteMetadata();
					case 'views':
						$this->addViewsMetadata();
						break;
					case 'formats':
						$this->addFormattersMetadata();
						break;
					case 'execution':
						$this->addExecutionMetadata();
						break;
					case 'bindings':
						$this->addTermBindingsMetadata();
            break;
          case 'site':
            $this->addSiteMetadata();
            break;
				}
			}
		} else {
			$this->DataGraph->add_resource_triple($this->pageUri, API.'extendedMetadataVersion', $this->Request->getUriWithParam('_metadata', 'all,views,formats,execution,bindings,site'));
		}
	}

	function addViewsMetadata(){
      foreach($this->ConfigGraph->getViewers() as $viewer) {
          $viewerName=$this->ConfigGraph->get_first_literal($viewer, API.'name');
          $altViewUri = $this->Request->getUriWithViewParam($viewerName);
          if ($altViewUri !== $this->pageUri) {
            $this->DataGraph->add_resource_triple($this->pageUri, DCT.'hasVersion', $altViewUri);
            $this->DataGraph->add_resource_triple($altViewUri, DCT.'isVersionOf',  $this->pageUri);
            $this->DataGraph->add_literal_triple($altViewUri, RDFS_LABEL,  $viewerName);
          }
      }		
	}
	
	
	function addFormattersMetadata(){
		$currentFormat = $this->getOutputFormat();
		foreach($this->ConfigGraph->getFormatters() as $formatName => $formatUri){
          $altFormatUri = $this->Request->getPageUriWithFormatExtension($this->pageUri, $formatName);
          if($this->pageUri!==$altFormatUri){
              $this->DataGraph->add_resource_triple($this->pageUri, DCT.'hasFormat', $altFormatUri);
              $this->DataGraph->add_resource_triple($altFormatUri, DCT.'isFormatOf', $this->pageUri);
              $this->DataGraph->add_resource_triple($altFormatUri, DCT.'format', '_:format_'.$formatName);
              $formatLabel = $this->ConfigGraph->get_label($formatUri);
              $formatName = $this->ConfigGraph->get_first_literal($formatUri, API.'name');
              logDebug("Formatter URI: {$formatUri} ; Format Label: {$formatLabel}");
              $this->DataGraph->add_literal_triple($altFormatUri, RDFS_LABEL, $formatLabel);
              $this->DataGraph->add_literal_triple($altFormatUri, API.'name', $formatName);
              $mimetypes = $this->ConfigGraph->getMimetypesOfFormatterByName($formatName);
              if(isset($mimetypes[0])){
                $this->DataGraph->add_literal_triple('_:format_'.$formatName, RDFS_LABEL, $mimetypes[0]);
              }
        }
      }
	}
	
	function addExecutionMetadata(){
		$endpointUrl = $this->ConfigGraph->getEndpointUri();
 
		if(strpos($endpointUrl, '_:')===0) $endpointUrl = CONFIG_URL;


		 $this->DataGraph->add_resource_triple($this->pageUri, API.'wasResultOf', '_:execution');
		 $this->DataGraph->add_resource_triple('_:execution', RDF.'type', API.'Execution');
		 $this->DataGraph->add_resource_triple('_:execution', API.'selectedEndpoint', $this->endpointUrl);
		 $this->DataGraph->add_resource_triple('_:execution', API.'selectedAPI', $this->ConfigGraph->getApiUri());
		 $this->DataGraph->add_resource_triple('_:execution', API.'selectedViewer', $this->getViewer());
		 $this->DataGraph->add_resource_triple('_:execution', API.'processor', '_:processor');

		 $this->DataGraph->add_resource_triple('_:processor', RDF.'type', API.'Service');
		 $this->DataGraph->add_resource_triple('_:processor', COPMV.'software', '_:pueliaVersion');
		 $this->DataGraph->add_literal_triple('_:pueliaVersion', RDFS.'label', 'Puelia v'.PUELIA_VERSION);
		 $this->DataGraph->add_resource_triple('_:pueliaVersion', RDF.'type', DOAP.'Version');
		 $this->DataGraph->add_literal_triple('_:pueliaVersion', DOAP.'revision', PUELIA_VERSION);
		 $this->DataGraph->add_resource_triple('_:pueliaVersion', COPMV.'releaseOf', '_:puelia');
		 $this->DataGraph->add_resource_triple('_:puelia', RDF.'type', DOAP.'Project');
		 $this->DataGraph->add_literal_triple('_:puelia', RDFS.'label', 'Puelia');
		 $this->DataGraph->add_resource_triple('_:puelia', DOAP.'homepage', 'http://code.google.com/p/puelia-php/');
		 $this->DataGraph->add_resource_triple('_:puelia', DOAP.'wiki', 'http://code.google.com/p/puelia-php/w/list');
		 $this->DataGraph->add_resource_triple('_:puelia', DOAP.'bug-database', 'http://code.google.com/p/puelia-php/issues/list');
		 $this->DataGraph->add_literal_triple('_:puelia', DOAP.'programming-language', 'PHP');
		 $this->DataGraph->add_resource_triple('_:puelia', DOAP.'repository', '_:pueliaRepository');
		 $this->DataGraph->add_resource_triple('_:pueliaRepository', RDF.'type', DOAP.'SVNRepository');
		 $this->DataGraph->add_resource_triple('_:pueliaRepository', DOAP.'location', 'http://puelia-php.googlecode.com/svn/trunk/');
		 $this->DataGraph->add_resource_triple('_:pueliaRepository', DOAP.'browse', 'http://code.google.com/p/puelia-php/source/browse/');
		 $this->DataGraph->add_resource_triple('_:puelia', DOAP.'implements', 'http://code.google.com/p/linked-data-api/wiki/Specification');
		 $this->DataGraph->add_resource_triple('http://code.google.com/p/linked-data-api/wiki/Specification', RDF.'type', DOAP.'Specification');
		 $this->DataGraph->add_literal_triple('http://code.google.com/p/linked-data-api/wiki/Specification', RDFS.'label', 'Linked Data API Specification', 'en');

		 $this->DataGraph->add_resource_triple('_:execution', API.'viewingResult', '_:viewingResult');
		 $this->DataGraph->add_resource_triple('_:viewingResult', RDF.'type', SPARQL.'QueryResult');
		 $this->DataGraph->add_resource_triple('_:viewingResult', SPARQL.'query', '_:viewingQuery');
		 $this->DataGraph->add_literal_triple('_:viewingQuery', RDF.'value', $this->viewQuery);
		 $this->DataGraph->add_resource_triple('_:viewingResult', SPARQL.'endpoint', '_:sparqlEndpoint');
		 $this->DataGraph->add_resource_triple('_:sparqlEndpoint', RDF.'type', SD.'Service');
		 $this->DataGraph->add_resource_triple('_:sparqlEndpoint', SD.'url', $this->SparqlEndpoint->uri);

		 if(!empty($this->selectQuery)){
			 $this->DataGraph->add_resource_triple('_:execution', API.'selectionResult', '_:selectionResult');
			 $this->DataGraph->add_resource_triple('_:selectionResult', RDF.'type', SPARQL.'QueryResult');
			 $this->DataGraph->add_resource_triple('_:selectionResult', SPARQL.'query', '_:selectionQuery');
			 $this->DataGraph->add_literal_triple('_:selectionQuery', RDF.'value', $this->selectQuery);
			 $this->DataGraph->add_resource_triple('_:selectionResult', SPARQL.'endpoint', '_:sparqlEndpoint');
		 } 
	}

	function addTermBindingsMetadata(){
		 $this->DataGraph->add_resource_triple($this->pageUri, API.'wasResultOf', '_:execution');
		$variableBindings = $this->ConfigGraph->getAllProcessedVariableBindings();
      foreach ($variableBindings as $name => $value) {
        $bindingBnode = '_:var_'.$name;
        $this->DataGraph->add_resource_triple('_:execution', API.'variableBinding', $bindingBnode);
        $this->DataGraph->add_literal_triple($bindingBnode, API.'name', $name);
        if (isset($value['type']) AND $value['type'] == RDFS.'Resource') {
          $this->DataGraph->add_resource_triple($bindingBnode, API.'value', $value['value']);
        } else if (isset($value['type'])) {
          $this->DataGraph->add_literal_triple($bindingBnode, API.'value', $value['value'], null, $value['type']);
        } else if (isset($value['lang'])) {
          $this->DataGraph->add_literal_triple($bindingBnode, API.'value', $value['value'], $value['lang']);
        } else {
          $this->DataGraph->add_literal_triple($bindingBnode, API.'value', $value['value']);
        }
      };

      $configFilters = $this->ConfigGraph->getAllFilters();
      foreach($configFilters as $filter) {
        $paramsArray = queryStringToParams($filter);
        foreach(array_keys($paramsArray) as $paramName){
          $this->addTermBindingsToExecution($paramName);
        }
      }
      $uriFilters = $this->Request->getUnreservedParams();
      foreach(array_keys($uriFilters) as $propertyPath) {
        $this->addTermBindingsToExecution($propertyPath);
      }
      $sort = $this->Request->getParam('_sort');
      if (!$sort) {
        $sort = $this->ConfigGraph->getSort();
      }
      if ($sort) {
        $sortPropPaths = explode(',',$sort);
        foreach ($sortPropPaths as $sortPropPath) {
            $sortPropPath = ltrim($sortPropPath, '-');
            $this->addTermBindingsToExecution($sortPropPath);
        }
      }
      if ($requestProperties = $this->Request->getParam('_properties')) {
        foreach (explode(',', $requestProperties) as $propPath) {
          $this->addTermBindingsToExecution($propPath);
        }
      }
      if ($viewerProperties = $this->ConfigGraph->get_first_literal($this->getViewer(), API.'properties')) {
        foreach (explode(',', $viewerProperties) as $propPath) {
          $this->addTermBindingsToExecution($propPath);
        }
      }
      
	}

  function addTermBindingsToExecution($propertyPath) {
    $propertyNamesWithUris = $this->SparqlWriter->mapParamNameToProperties($propertyPath);
    foreach($propertyNamesWithUris as $propertyName=>$uri) {
      $termName = '_:term_'.$propertyName;
      $this->DataGraph->add_resource_triple('_:execution', API.'termBinding', $termName);
      $this->DataGraph->add_literal_triple($termName, API.'label', $propertyName);
      $this->DataGraph->add_resource_triple($termName, API.'property', $uri);
    }
  }



  
  function getFormatter(){
	logDebug("getFormatter called on ldaresponse");
      if($format = $this->Request->getParam('_format')){
          if($this->ConfigGraph->getApiContentNegotiation()==API.'parameterBased'){
              if($this->ConfigGraph->apiSupportsFormat($format)){
                  return $format;
              } else {
                  logError("Bad Request when selecting formatter: {$format}");
                  $this->errorMessages[]="Sorry. This API does not support {$format}";
                  $this->setStatusCode(HTTP_Bad_Request);
                  $this->serve();
              }
          }
          else {
              logError("This API does not support parameter based format selection.");
              $this->errorMessages[]="This API does not support parameter based format selection. Try content-negotiation.";
              $this->setStatusCode(HTTP_Bad_Request);
              $this->serve();              
          }
      } else if($this->Request->hasFormatExtension()) { 
          $extension = $this->Request->getFormatExtension();
          if($this->extensionIsSupported($extension)){
                return $extension;
          } else {
              $this->errorMessages[]="Sorry, the '$extension' extension is not supported here.";
              $this->setStatusCode(HTTP_Unsupported_Media_Type);
              return false;
          } 
          
       } else if($this->Request->hasAcceptTypes()){
         logDebug("Doing content-negotiation");
         $configFormatters = $this->ConfigGraph->getFormatters();
            foreach($this->Request->getAcceptTypes($this->ConfigGraph->getDefaultMimeTypes()) as $acceptType){
              logDebug("request accept type: '{$acceptType}'");
                foreach($configFormatters as $formatName => $formatterUri){
                    $formatterMimetypes = $this->ConfigGraph->getMimetypesOfFormatterByName($formatName);
                    if(in_array($acceptType, $formatterMimetypes)){
			logDebug("$acceptType matches $formatterUri");
                        return $formatName;
                    }
                }
            }
        }
        
        if($formatUri = $this->ConfigGraph->getEndpointDefaultFormatter()) { 
            return $this->ConfigGraph->get_first_literal($formatUri, API.'name');
        } else if($formatUri = $this->ConfigGraph->getApiDefaultFormatter()){ 
            return $this->ConfigGraph->get_first_literal($formatUri, API.'name');
        }
        return 'json';
        
  }
  
  function serve(){
              $Request = $this->Request;
              header($this->statusCode);
              
              if($this->statusCode == HTTP_OK){
                  try {

                  $outputFormat = $this->getFormatter();

          
                  if(!$outputFormat){
                      throw new Exception("No output format provided");
                  }
                if(!$mimetype = $this->ConfigGraph->getMimetypesOfFormatterByName($outputFormat)){
                    if(isset( $this->outputFormats[$outputFormat])) $mimetype = $this->outputFormats[$outputFormat]['mimetypes'];
		    else $mimetype= array('text/html');
                }
                $mimetype = $mimetype[0];
                $this->mimetype = $mimetype;

                $endpointType = $this->ConfigGraph->getEndpointType();
                switch($endpointType){
                    case API.'ListEndpoint' : 
                    case PUELIA.'SearchEndpoint':
                        $pageUri = $this->Request->getUriWithPageParam();
                        break;
                    case API.'ItemEndpoint' :
                        $pageUri = $this->Request->getUri();
                        break;
                    default:
                      throw new ConfigGraphException("<{$endpointType}> is not an implemented Endpoint type");
                }
                    if($this->overrideUserConfig AND isset($this->outputFormats[$outputFormat])){
                        $viewFile = $this->outputFormats[$outputFormat]['view'];
                        $mimetype = $this->outputFormats[$outputFormat]['mimetypes'][0];
                        $this->mimetype = $mimetype;    
                    }
                    else if($this->ConfigGraph->getFormatterTypeByName($outputFormat)== API.'XsltFormatter'){
                    $viewFile = 'views/xslt.php';
                    $styleSheetFile = $this->ConfigGraph->getXsltStylesheetOfFormatterByName($outputFormat);
                    require $viewFile;
                    die;
                } else if($this->ConfigGraph->getFormatterTypeByName($outputFormat)== PUELIA.'PhpFormatter') {
                    $formatterUri = $this->ConfigGraph->getFormatterUriByName($outputFormat);
                    $innerTemplate = $this->ConfigGraph->get_first_literal($formatterUri, PUELIA.'innerTemplate');
                    $outerTemplate = $this->ConfigGraph->get_first_literal($formatterUri, PUELIA.'outerTemplate');
                    require $outerTemplate;
                    die;
                }
                 else if(isset($this->outputFormats[$outputFormat])) {
                    $viewFile = $this->outputFormats[$outputFormat]['view'];
                } else {
                  throw new Exception("{$outputFormat} is not an accepted output format");
                }
            } 
        catch(Exception $e){
            logError("Error when serving response: ".$e->getMessage());
            $this->setStatusCode(HTTP_Internal_Server_Error);
            $this->serve();
        }
        header("Content-Type: {$mimetype}");
        header("Last-Modified: {$this->lastModified}");
		header("x-served-from-cache: false");
        $DataGraph = $this->getDataGraph();
        try {
            ob_start();
            require $viewFile;
            $page = ob_get_clean();
            $this->eTag = md5($page);
			header("ETag: {$this->eTag}");
            $this->body = $page;
            echo $page;
            $this->cacheable = true;
        } catch (Exception $e){
            $this->setStatusCode(HTTP_Internal_Server_Error);
            $this->errorMessages[]="Sorry, Puelia experienced an error trying to serve this page.";
            logError('Error from Response:serve() '.$e->getMessage());
            $this->serve();
            exit;
        }
      } else {
          header("Content-Type: text/html");
          switch($this->statusCode){
              case HTTP_Unsupported_Media_Type:
              case HTTP_Bad_Request :
                require 'views/errors/400.php';
                break;
              default:
              case HTTP_Internal_Server_Error :
                require 'views/errors/500.php';
                break;
              case HTTP_Not_Found :
                require 'views/errors/404.php';
                break;
              
          }
          exit;
      }
      
  }  
    
    function getDataGraph(){
        return $this->DataGraph;
    }
        
}


?>
