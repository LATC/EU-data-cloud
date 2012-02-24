<?php
switch($this->ConfigGraph->getEndpointType()){
    case API.'ListEndpoint' : 
        $pageUri = $this->Request->getUriWithPageParam();
        break;
    case API.'ItemEndpoint' :
    default:
        $pageUri = $this->Request->getUri();
        break;
}

$json = $DataGraph->to_simple_json($pageUri) ;
if($callback = $Request->getParam('callback')){
    $callback = preg_replace("/[^_a-zA-Z0-9\.]/", "", $callback);
    echo "{$callback}({$json})"; 
} else {
    echo $json;
}
?>