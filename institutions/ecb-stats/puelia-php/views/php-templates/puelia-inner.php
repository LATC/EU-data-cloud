<?php
switch ($page->endpointType):
    case API.'ItemEndpoint' :
        $topic = $page->topic; 
        require 'puelia-item.php';
        break;
    case API.'ListEndpoint':
    default:
        require 'puelia-list.php';
        break;
endswitch;
?>
