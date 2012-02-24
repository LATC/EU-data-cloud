<?php
$indicators = array();
$labels = array();
foreach($this->DataGraph->get_subjects() as $s){
  if($jsonpart = $this->DataGraph->get_first_literal($s, OPEN.'json')){
    $jsonData = json_decode($jsonpart,1);
    $indicators = array_merge_recursive($jsonData['indicators'], $indicators);
    $labels = array_merge_recursive($jsonData['labels'], $labels);
  }
}
$json = json_encode(array('indicators' => $indicators, 'labels' => $labels));

    if($callback = $Request->getParam('callback')){
        $callback = preg_replace("/[^_a-zA-Z0-9\.]/", "", $callback);
        echo "{$callback}({$json})"; 
    } else {
        echo $json;
    }
    


?>
