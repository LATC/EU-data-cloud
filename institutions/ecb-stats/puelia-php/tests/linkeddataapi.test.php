<?php
require_once '../graphs/configgraph.class.php';
require_once '../lda-request.class.php';


class LinkedDataApiTest extends PHPUnit_Framework_TestCase {
    
    function test_queryStringToParams(){
        $qs = '?a.b=Hello%20World&a_b=Die%20&subcategory.label=Future%20Media%20%26%20Technology';
        $expected = array(
            'a.b' => 'Hello World',
            'a_b' => 'Die ',
            'subcategory.label' => 'Future Media & Technology',
            );
        $this->assertEquals($expected, queryStringToParams($qs));
    }

}
?>
