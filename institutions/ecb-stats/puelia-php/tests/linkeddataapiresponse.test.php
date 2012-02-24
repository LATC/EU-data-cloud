<?php
require_once '../lda-response.class.php';
require_once '../lda-request.class.php';

class LinkedDataApiResponseTest extends PHPUnit_Framework_TestCase {
    
    var $Response  = false;
    
    function setUp(){
        $this->Response = new LinkedDataApiResponse();
    }
    
    function tearDown(){
        $this->Response = false;
    }
    
    function test_chooseOutput(){
        $this->Response->chooseOutputFormat();
    }

    function test_getViewer(){

        
        
    } 
}
?>
