<?php
require 'deployment.settings.php';
require_once 'lda.inc.php';
require 'setup.php';
require_once 'lda-cache.class.php';
require_once 'lda-request.class.php';
require_once 'lda-response.class.php';
require_once 'graphs/configgraph.class.php';
require_once 'responses/Response304.class.php';
Logger::configure("puelia.logging.properties");

$HttpRequestFactory = new HttpRequestFactory();

if(function_exists('memcache_connect')){
  $MemCacheObject = new LinkedDataApiCache();
  $HttpRequestFactory->set_cache($MemCacheObject);
} 
$Request = new LinkedDataApiRequest();
header("Access-Control-Allow-Origin: *");

define("CONFIG_PATH", '/api-config');
define("CONFIG_URL", $Request->getBaseAndSubDir().CONFIG_PATH);
logDebug("Request URI: ".$Request->getUri());
if(rtrim($Request->getPath(), '/')==$Request->getInstallSubDir()){
	header("Location: ".CONFIG_URL, true, 303);
	exit;
} 	    

if (  
    defined("PUELIA_SERVE_FROM_CACHE") 
        AND 
    !$Request->hasNoCacheHeader() 
        AND 
    $cachedResponse = LinkedDataApiCache::hasCachedResponse($Request)
    )
{
	logDebug("Found cached response");
	if (isset($Request->ifNoneMatch) && $cachedResponse->eTag == $Request->ifNoneMatch)
	{
		logDebug("ETag matched, returning 304");
		$Response = new Response304($cachedResponse);
	}
	else if (isset($Request->ifModifiedSince) && $cachedResponse->generatedTime <= $Request->ifModifiedSince)
	{
		logDebug("Last modified date matched, returning 304");
		$Response = new Response304($cachedResponse);
	}
	else
	{
		logDebug("Re-Serving cached response");
		$Response = $cachedResponse;
	}
}
else
{
	logDebug("Generating fresh response");

    $files = glob('api-config-files/*.ttl');
    
    /*
	keep the config graph that matches the request, and keep a 'complete' configgraph to serve if none match
    */
        $CompleteConfigGraph = new ConfigGraph(null, $Request, $HttpRequestFactory);
	foreach($files as $file){
	  logDebug("Iterating over files in /api-config: $file"); 
		if($ConfigGraph = LinkedDataApiCache::hasCachedConfig($file)){
			logDebug("Found Cached Config");
			$CompleteConfigGraph->add_graph($ConfigGraph);
			$ConfigGraph->setRequest($Request);

		} else {
			logDebug("Checking Config file: $file");
			$rdf = file_get_contents($file);
			$CompleteConfigGraph->add_rdf($rdf);
			$ConfigGraph =  new ConfigGraph(null, $Request, $HttpRequestFactory);
      $ConfigGraph->add_rdf($rdf);
      $errors = $ConfigGraph->get_parser_errors();
      if(!empty($errors)){
          foreach($ConfigGraph->get_parser_errors() as $errorList){
            foreach($errorList as $errorMsg){
              logDebug('Error parsing '.$file.'  '.$errorMsg);
            }
          }
      }
			logDebug("Caching $file");
			LinkedDataApiCache::cacheConfig($file, $ConfigGraph);
		}
		$ConfigGraph->init();
		if($selectedEndpointUri = $ConfigGraph->getEndpointUri()){
			logDebug("Endpoint Uri Selected: $selectedEndpointUri");
			unset($CompleteConfigGraph);
		    	$Response =  new LinkedDataApiResponse($Request, $ConfigGraph, $HttpRequestFactory);
        		$Response->process();
			break;
		} else if($docPath = $ConfigGraph->dataUriToEndpointItem($Request->getUri())){
      logDebug("Redirecting ".$Request->getUri()." to {$docPath}");
			header("Location: $docPath", 303);
			exit;
		}
	}
	if(!isset($selectedEndpointUri)){	
		logDebug("No Endpoint Selected");
	    $Response =  new LinkedDataApiResponse($Request, $CompleteConfigGraph);
	    if($Request->getPathWithoutExtension()==$Request->getInstallSubDir().CONFIG_PATH){
		logDebug("Serving ConfigGraph");
	        $Response->serveConfigGraph();
	    } else {
		    logDebug("URI Requested:" . $Request->getPathWithoutExtension());
	        $Response->process();
	    }

	}
}

$Response->serve();
if (defined("PUELIA_SERVE_FROM_CACHE") AND  $Response->cacheable)
{
	LinkedDataApiCache::cacheResponse($Request, $Response);
}
?>
