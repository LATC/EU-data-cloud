<?php
require_once '../sparqlwriter.class.php';
require_once '../graphs/configgraph.class.php';


class SparqlWriterTest extends PHPUnit_Framework_TestCase {
    
    var $SW;
    var $mockRequest;
    
    function setUp(){

        
        $this->mockRequest = $this->getMockRequest();
        
        $config = new ConfigGraph(file_get_contents('../documents/config.ttl'), $this->mockRequest);
        $config->init();
        
        
        $this->SW = new SparqlWriter($config, $this->mockRequest);
    }
    
    
    function getMockRequest(){
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
                        ->method('getPathWithoutExtension')
                        ->will($this->returnValue("/doc/school/12345"));
        $mockRequest->expects($this->any())
                        ->method('getPath')
                        ->will($this->returnValue("/doc/school/12345"));
        $mockRequest->expects($this->any())
                        ->method('getUri')
                        ->will($this->returnValue("http://example.com/doc/school/12345"));
        return $mockRequest;
    }
    
    function test_getLimit(){
        
    }
    
    function test_mapParamNameToProperties(){
        $name = 'min-foo.bar';
        $actual = $this->SW->mapParamNameToProperties($name);
        $expected = array('foo'=> gc_config.'testPropWithApiLabel', 'bar'=> gc_config. 'testPropWithRdfsLabel');
        $this->assertEquals($expected, $actual, " Failed mapping for {$name}");
        $this->assertEquals($expected, $this->SW->mapParamNameToProperties('max-foo.bar'), " Failed mapping for foo.bar");
        $this->assertEquals(array('notfoo' =>null), $this->SW->mapParamNameToProperties('max-notfoo'), " shouldn't return mapping for notfoo");
    }

    // function test_mapParamNameThrowsException(){
    //     $name = 'min-unknown.bar';
    //     try {
    //         $actual = $this->SW->mapParamNameToProperties($name);
    //     } catch (Exception $e) {
    //         return ;
    //     }
    //     $this->fail("mapParamNameToProperties called with min-unknown.bar should raise a ConfigGraphException");
    // }
    
    function test_paramsToSparql(){
        
        $params = queryStringToParams("foo.bar={school}");
        $expected = " ?item gc:testPropWithApiLabel ?foo . ?foo gc:testPropWithRdfsLabel <http://education.data.gov.uk/id/school/12345> . ";
        $actual = $this->SW->paramsToSparql($params);
        $this->assertEquals(strip_whitespace($expected), strip_whitespace($actual), " Failed translating foo.bar={school}");
        $params = queryStringToParams("bar.foo.age={school}");
        $expected = "\n  ?item gc:testPropWithRdfsLabel ?bar .\n  ?bar gc:testPropWithApiLabel ?bar_foo . \n  ?bar_foo gc:age <http://education.data.gov.uk/id/school/12345> . ";
        $actual = $this->SW->paramsToSparql($params);
        $this->assertEquals(strip_whitespace($expected), strip_whitespace($actual), " Failed translating bar.foo.age={school}");        
    }


    function test_splitPrefixAndName(){
        foreach(array('min', 'max', 'minEx', 'maxEx', 'name','exists', 'true', 'false') as $p){
            $expected = array('prefix' => $p, 'name' => 'foo');
            $actual = $this->SW->splitPrefixAndName($p.'-foo');
            $this->assertEquals($expected, $actual, " Failed splitting {$p}-foo ");
        }

    }
    
    function test_paramNameToPropertyNames(){
        $r = $this->SW->paramNameToPropertyNames('notfoo');
        $this->assertEquals(array('notfoo'), $r);
        $r = $this->SW->paramNameToPropertyNames('min-foo.bar.ying.yang');
        $this->assertEquals(array('foo', 'bar', 'ying', 'yang'), $r);
        
    }
    
    function test_variableBindingToSparqlTerm(){
        $tests = array(
                '"""foo"""' => array('value' =>'foo'),
                '"""foo"""^^<http://example.com/datatype>' => array('value' => 'foo', 'datatype' => 'http://example.com/datatype', 'type' => RDFS_LITERAL),
                '"""foo"""@en-gb' => array('value' => 'foo', 'lang' => 'en-gb', 'type' => RDFS_LITERAL),
                '<http://example.com/resource>' => array('value' => 'http://example.com/resource', 'type' => RDFS_RESOURCE),
                
            );
            foreach($tests as $expected => $input){
                $actual = $this->SW->variableBindingToSparqlTerm($input, 'http://example.com/property');
                $this->assertEquals($expected, $actual, "");
            }
    }
    
    
    function test_hasUnknownPropertyNamesFromConfig(){
        
        $mockConfig = $this->getMock('ConfigGraph', array('getAllFilters', 'getVocabularies', 'getVocabularyGraph'), array(),'', false);
        $mockConfig->expects($this->any())
                     ->method('getAllFilters')
                     ->will($this->returnValue(array('notfoo=bar') ));
        $mockConfig->expects($this->any())
                     ->method('getVocabularies')
                     ->will($this->returnValue(array(gc_config)));
        $mockConfig->expects($this->any())
                      ->method('getVocabularyGraph')
                      ->will($this->returnValue(new VocabularyGraph(file_get_contents('../documents/config.ttl'))));

        
        $SW = new SparqlWriter($mockConfig, $this->mockRequest);
        
        $this->assertTrue($SW->hasUnknownPropertiesFromConfig(), "should have returned true for notfoo=bar");
        $this->assertEquals(array('notfoo'), $SW->getUnknownPropertiesFromConfig());
        
        $mockConfig = $this->getMock('ConfigGraph', array('getAllFilters', 'getVocabularies', 'getVocabularyGraph'), array(), '', false);
        $mockConfig->expects($this->any())
                     ->method('getAllFilters')
                     ->will($this->returnValue(array('foo=bar') ));
        $mockConfig->expects($this->any())
                      ->method('getVocabularies')
                      ->will($this->returnValue(array(gc_config)));
        $mockConfig->expects($this->any())
                    ->method('getVocabularyGraph')
                    ->will($this->returnValue(new VocabularyGraph(file_get_contents('../documents/config.ttl'))));

        
        $SW = new SparqlWriter($mockConfig, $this->mockRequest);
        $this->assertFalse($SW->hasUnknownPropertiesFromConfig(), "should have returned false for foo=bar");
    }


    
    function test_hasUnknownPropertyNamesFromRequest(){
           $mockVocab = $this->getMock('VocabularyGraph', array('getUriForPropertyLabel'), array(), '', false);
           $mockVocab->expects($this->any())
                        ->method('getUriForPropertyLabel')
                        ->will($this->returnValue(false));

           $this->SW->_vocab = $mockVocab;
           $this->assertTrue($this->SW->hasUnknownPropertiesFromRequest(), "should have returned true");


       }

       function test_hasUnknownPropertyNamesFromRequestReturnsFalse(){
           $mockVocab = $this->getMock('VocabularyGraph', array('getUriForPropertyLabel'));
           $mockVocab->expects($this->any())
                        ->method('getUriForPropertyLabel')
                        ->will($this->returnValue('http://example.com/'));

           $this->SW->getConfigGraph()->_vocab = $mockVocab;
           $this->assertFalse($this->SW->hasUnknownPropertiesFromRequest(), "should have returned false");
       }
    
    
    function test_prefixFromParamName(){
        
        $this->assertFalse($this->SW->prefixFromParamName('foo.bar'), "foo.bar has no prefix and should return false");
        $this->assertEquals($this->SW->prefixFromParamName('min-foo.bar'), 'min', "min-foo.bar should return 'min'");
        $this->assertEquals($this->SW->prefixFromParamName('max-foo.bar'), 'max', "max-foo.bar should return 'max'");
        $this->assertEquals($this->SW->prefixFromParamName('minEx-foo.bar'), 'minEx', "minEx-foo.bar should return 'minEx'");
        $this->assertEquals($this->SW->prefixFromParamName('maxEx-foo.bar'), 'maxEx', "maxEx-foo.bar should return 'maxEx'");
        $this->assertEquals($this->SW->prefixFromParamName('exists-foo.bar'), 'exists', "exists-foo.bar should return 'exists'");
        $this->assertEquals($this->SW->prefixFromParamName('name-foo.bar'), 'name', "name-foo.bar should return 'name'");
    }
    
    
    function test_castOrderByVariable(){
        $this->assertEquals('<'.XSD.'integer>(?age)', $this->SW->castOrderByVariable('age', gc_config.'age'), "properties with  a rdfs:range of xsd:integer should be cast in a sparql function call to xsd:integer()");
    }
    
    
    function test_getViewQueryForUriList(){
        $actual = $this->SW->getViewQueryForUriList(array('http://example.com/a'), API.'labelledDescribeViewer');
        $expected = "PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>\nCONSTRUCT { \n  <http://example.com/a> ?p0 ?o0 .\n}\nWHERE {\n  {\n    <http://example.com/a> ?p0 ?o0 .\n  }\n}\n";

        $this->assertTrue(preg_match('@CONSTRUCT\W+\{.+?@mi', $actual)==true, "should be a CONSTRUCT query for every property of the resource if no user request parameter property chains are given");
    }
    
    
    function test_propertyNameListToOrderBySparql(){
        $fooNS = 'http://example.com/foo/';
        $propertiesList = array( array(
                'property-list' => 
                    array('sibling' => $fooNS.'sibling', 'spouse' => $fooNS.'spouse', 'parent' => $fooNS.'parent', 'age' => $fooNS.'age'),
                'sort-order' => 'ASC'
                )
                );
        
        $actual = $this->SW->propertyNameListToOrderBySparql($propertiesList);
        
        $expected = array(
            'graphConditions' => "\n  ?item <{$fooNS}sibling> ?sibling .\n  ?sibling <{$fooNS}spouse> ?sibling_spouse .\n  ?sibling_spouse <{$fooNS}parent> ?sibling_spouse_parent .\n  ?sibling_spouse_parent <{$fooNS}age> ?sibling_spouse_parent_age .", 
        'orderBy' => "ORDER BY ASC(?sibling_spouse_parent_age) ");

        $this->assertEquals($expected, $actual);
    }

    function test_addPrefixesToQuery(){
	$query = 'DESCRIBE rdfs:label foaf:person api:itemendpoint';
	$expected="PREFIX rdfs: <".RDFS.">\nPREFIX foaf: <".FOAF.">\nPREFIX api: <".API.">\n".$query;
	$actual = $this->SW->addPrefixesToQuery($query);
	$this->assertEquals($expected, $actual, "should add only the needed namespaces to the query");
    }
    
}
function strip_whitespace($in){
    return str_replace("\n", '', str_replace("\t",'', str_replace(" ",'',$in)));
}


?>
