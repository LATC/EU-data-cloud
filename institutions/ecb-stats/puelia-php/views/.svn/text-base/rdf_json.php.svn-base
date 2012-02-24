<?php
    $json = $DataGraph->to_json();
    if($callback = $Request->getParam('callback')){
        $callback = preg_replace("/[^_a-zA-Z0-9\.]/", "", $callback);
        echo "{$callback}({$json})"; 
    } else {
        echo $json;
    }
    
?>
