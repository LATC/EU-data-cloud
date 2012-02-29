<?php
require_once '../graphs/linkeddataapigraph.class.php';
require_once '../graphs/configgraph.class.php';

class LinkedDataApiGraphTest extends PHPUnit_Framework_TestCase {
    
    var $Graph = false;
    
    function setup(){
        $mockRequest = $this->getMock('LinkedDataApiRequest');
        $mockRequest->expects($this->any())
                     ->method('getParams')
                     ->will($this->returnValue(array('localAuthority.code'=> '00BX', '_view'=> 'detailed') ) );
                     
        $mockRequest->expects($this->any())
                    ->method('getUnreservedParams')
                    ->will($this->returnValue(array('localAuthority.code'=> '00BX') ) );
                     
        $mockRequest->expects($this->any())
                      ->method('getBase')
                      ->will($this->returnValue("http://example.com"));
        $mockRequest->expects($this->any())
                        ->method('getPath')
                        ->will($this->returnValue("/doc/school/12345"));

        $mockRequest->expects($this->any())
                                    ->method('getPathWithoutExtension')
                                    ->will($this->returnValue("/doc/school/12345"));

        $mockRequest->expects($this->any())
                                    ->method('getUri')                                                    
                            ->will($this->returnValue("http://example.com/doc/school/12345"));
                                    


        
        $ConfigGraph = new ConfigGraph(file_get_contents('../documents/config.ttl'), $mockRequest);
        $ConfigGraph->add_rdf(file_get_contents('../documents/test-data.ttl'));        
        $ConfigGraph->init();
        $this->Graph = new LinkedDataApiGraph(file_get_contents('../documents/test-data.ttl'), $ConfigGraph);
        $this->Graph->_current_page_uri = "http://localhost/Things?_page=1";
    }
    
    function test_to_simple_json(){
       $json =  $this->Graph->to_simple_json("http://localhost/Things?_page=1");
       $struct = json_decode($json, 1);
       $this->assertTrue(isset($struct['result']), "root element should have a link to result resource");
       $this->assertTrue(isset($struct['version']), "root element should have a link to version number");
       $this->assertTrue(isset($struct['result']['_about']), "root element should have a link to result, which should have  a _about attribute");
       $this->assertEquals($struct['format'], "linked-data-api", "format should be linked-data-api");       
       
       
    }
    
    function test_map_rdf_value_to_json_value(){

        $this->assertEquals("test item a", $this->Graph->map_rdf_value_to_json_value(array("value" => "test item a", "type" => 'literal', "lang" => "en"), RDFS_LABEL, "http://example.com/doc/school/12345", array()), "should just return the string value");

        $this->assertEquals("test item a", $this->Graph->map_rdf_value_to_json_value(array("value" => "test item a", "type" => 'literal', 'datatype' => XSD.'string'), RDFS_LABEL, "http://example.com/doc/school/12345", array()), "should return the string value without ^^{datatype}");

        $obj = array("value" => "_:aksd", "type" => 'bnode');
        $actual = $this->Graph->map_rdf_value_to_json_value($obj, RDF_TYPE, "http://example.com/doc/school/12345", array());
        $this->assertEquals(new BlankObject(), $actual, "should return an empty object");
        
        $objNode = array("value" => "http://example.com/#test-list-b", 'type' => 'uri');
        $actual = $this->Graph->map_rdf_value_to_json_value($objNode, API.'items',      "http://localhost/Things?_page=1", array());
        $expected = array("_about" => "http://example.com/#test-list-b", "type" => 'http://www.w3.org/2002/07/owl#Thing', 'label' => 'test item b');
        $this->assertEquals($expected , $actual, "should return a  resource json object");
    }
    
    
    function test_get_short_name_for_uri(){
        $this->assertEquals('abc', $this->Graph->get_short_name_for_uri(gc_config.'abc'), "should return localname of property - ie the id after the # or last /");
        $this->assertEquals('foo', $this->Graph->get_short_name_for_uri(gc_config.'testPropWithApiLabel'), "should return api:label of property");
    }
    
    function test_get_simple_json_property_value(){
        $this->assertEquals("test item a", $this->Graph->get_simple_json_property_value(array(array("value" => "test item a", "type" => 'literal')), RDFS_LABEL, "http://example.com/doc/school/12345", array()) );
    }

    function test_propertyIsMultiValued(){
        $this->assertTrue($this->Graph->propertyIsMultivalued(gc_config.'alwaysMultiple'), "propertyIsMultiValued should return true if the property is alwaysMultiple");
        $this->assertFalse($this->Graph->propertyIsMultivalued(gc_config.'bar'), "propertyIsMultiValued should return false if the property isn't alwaysMultiple");
    }
    
    
    function test_simple_xml_output(){
        // $xml = $this->Graph->to_simple_xml("http://localhost/Things?_page=1");
        // echo $xml;
        // $simplexml = simplexml_load_string($xml);
    //     var_dump( get_class_methods($simplexml->xpath('/result/@href')));
    // var_dump($simplexml->xpath('/result/@href'));
        // $result = $simplexml->xpath('/result');
        // $this->assertEquals(1,$result, "xml should have one root element called result");
       // $this->assertEquals(1, $result = $xpath->evaluate('count(/result/@href)'), "result should have @href");
       $this->Graph->add_rdf(file_get_contents('../documents/climbing-routes.ttl'));
       $xml =  $this->Graph->to_simple_xml("http://localhost/Climbing/Routes.ttl?_page=1");
        
    }

    function test_resource_is_a_page_list_item(){
                 $this->assertTrue($this->Graph->resource_is_a_page_list_item('http://localhost/Things?_page=1', 'http://example.com/#test'), "should return true: http://localhost/Things?_page=1 has a list of items with http://example.com/#test in it ");
 
                 $this->assertFalse($this->Graph->resource_is_a_page_list_item('http://localhost/Things?_page=1', 'http://localhost/Things'), "should return false: http://localhost/Things?_page=1 has a list of items, but  http://localhost/Things isn't one of them ");
                 $this->assertFalse($this->Graph->resource_is_a_page_list_item('http://localhost/Things?_page=1', 'http://localhost/Things/non-existent-resource'), "should return false: http://localhost/Things?_page=1 has a list of items, but http://localhost/Things/non-existent-resource isn't one of them ");
    }

}
?>
