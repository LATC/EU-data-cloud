<?php
require_once '../graphs/pueliagraph.class.php';

class PueliaGraphTest extends PHPUnit_Framework_TestCase {
    
    var $Graph = false;
    
    function setup(){
        $this->Graph = new PueliaGraph(file_get_contents('../documents/config.ttl'));
    }
    
    function test_resource_is_first_list(){
        $Graph = new PueliaGraph(file_get_contents('../documents/test-data.ttl'));
        $parentLists = $Graph->get_subjects_where_resource(RDF_REST, '_:lastList');
        $this->assertFalse($Graph->resource_is_first_list('_:lastList'), "should return false because list is linked to from another list");
        $this->assertTrue($Graph->resource_is_first_list('_:itemsList'), "should return true because list is not rdf:rest of any other list");
    }
    
    function test_list_to_array(){
        $this->assertEquals(array(FOAF.'knows', REL.'knowsOf', REL.'siblingOf'), $this->Graph->list_to_array(Example_List_Uri) );
    }
    
    function test_resource_is_a_list(){
        $this->assertTrue($this->Graph->resource_is_a_list(Example_List_Uri));
    }
    
    function test_resource_is_a_list_false(){
        $this->assertFalse($this->Graph->resource_is_a_list('http://example.com/#apiDefaultViewer'));
    }
    
    function test_get_label(){
        $this->assertEquals("ChildEndpoint", $this->Graph->get_label('http://example.com/#childEndpoint'));
    }
    

}
?>
