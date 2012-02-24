<?php
require_once '../graphs/vocabularygraph.class.php';
require_once '../lda-request.class.php';

class VocabularyGraphTest extends PHPUnit_Framework_TestCase {
    
    var $Graph = false;
    
    function setup(){
        
        $this->Graph = new VocabularyGraph(file_get_contents('../documents/config.ttl'));
    }
    
    function test_getPropertyLabels(){        
        $expected = array(
           'foo' => gc_config. 'testPropWithApiLabel' ,
           'bar' => gc_config. 'testPropWithRdfsLabel',
           'age' => gc_config. 'age',
           'grading' => "http://climb.dataincubator.org/vocabs/climb/grading",
            'value' => "http://www.w3.org/1999/02/22-rdf-syntax-ns#value",
            'type' => "http://www.w3.org/1999/02/22-rdf-syntax-ns#type",
           
            );
        $actual = $this->Graph->getPropertyLabels();
        foreach($expected as $label => $uri) {
            $this->assertContains($label, array_keys($actual));
            $this->assertContains($uri, array_values($actual));
        }

    }
    
    function test_getPropertyLabelForUri(){        
        $expected = array(
           'foo' => gc_config. 'testPropWithApiLabel' ,
           'bar' => gc_config. 'testPropWithRdfsLabel',
            );
        $actual = $this->Graph->getUriForPropertyLabel('bar');
        $this->assertEquals(gc_config. 'testPropWithRdfsLabel', $actual);
    }
    
    
}
?>