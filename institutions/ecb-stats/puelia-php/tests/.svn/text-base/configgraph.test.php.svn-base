<?php
require_once '../graphs/configgraph.class.php';
require_once '../lda-request.class.php';

class ConfigGraphTest extends PHPUnit_Framework_TestCase {
    
    var $Graph = false;
    function getMockRequest($path='/doc/school/12345', $base = "http://example.com" ){
        $mockRequest = $this->getMock('LinkedDataApiRequest');
        $mockRequest->expects($this->any())
                     ->method('getParams')
                     ->will($this->returnValue(array('localAuthority.code'=> '00BX', '_view'=> 'detailed') ) );
                     
        $mockRequest->expects($this->any())
                    ->method('getUnreservedParams')
                    ->will($this->returnValue(array('localAuthority.code'=> '00BX') ) );
                     
        $mockRequest->expects($this->any())
                      ->method('getBase')
                      ->will($this->returnValue($base));
        $mockRequest->expects($this->any())
                      ->method('getBaseAndSubDir')
                      ->will($this->returnValue($base));
        $mockRequest->expects($this->any())
                        ->method('getPath')
                        ->will($this->returnValue($path));
        $mockRequest->expects($this->any())
                        ->method('getPathWithoutExtension')
                        ->will($this->returnValue($path));
        $mockRequest->expects($this->any())
                        ->method('getUri')
                        ->will($this->returnValue("{$base}{$path}"));
        return $mockRequest;
    }
    function setup(){
        
        
        $this->Graph = new ConfigGraph(file_get_contents('../documents/config.ttl'), $this->getMockRequest());
        $this->Graph->init();
    }
    
    function test_getEndpointUri(){
        $this->assertEquals(testEndpointUri, $this->Graph->getEndpointUri());
    }

    function test_getApiUriReturnsFalse(){
        $mockRequest = $this->getMock('LinkedDataApiRequest');
        $mockRequest->expects($this->any())
                     ->method('getParams')
                     ->will($this->returnValue(array('localAuthority.code'=> '123') ) );
        $mockRequest->expects($this->any())
                      ->method('getBase')
                      ->will($this->returnValue("http://example.org"));
        $mockRequest->expects($this->any())
                        ->method('getPath')
                        ->will($this->returnValue("doc/school/12345"));
        $mockRequest->expects($this->any())
                        ->method('getPathWithoutExtension')
                        ->will($this->returnValue("/doc/school/12345"));
        $mockRequest->expects($this->any())
                        ->method('getUri')
                        ->will($this->returnValue("http://example.org/doc/school/12345"));
       

        $Graph = new ConfigGraph(file_get_contents('../documents/config.ttl'), $mockRequest);
        $Graph->init();
            $this->assertFalse($Graph->getApiUri());
    }
    function test_getApiUri(){
            $this->assertEquals('http://example.com/#UnitTestApi', $this->Graph->getApiUri());
    }
    
    function test_getApisWithoutBase(){
        $this->assertContains('http://example.com/#ApiWithoutBase', $this->Graph->getApisWithoutBase());
    }
    
    function test_getRequestMatchesFromUriTemplate(){
        $actual = $this->Graph->getRequestMatchesFromUriTemplate("/doc/school/{identifier}?localAuthority.code={code}");
        $expected =  array(
                "paramBindings" => 
                array(
                  'code' => array('value' => '00BX'),
                ),
                'pathBindings' => array(
                
                    'identifier' => array('value' => '12345' , 'source' => 'request'),
                ),
              );
          $this->assertEquals($expected, $actual);
    }
    
    function test_matchRequestToEndpoint(){
        $actual = $this->Graph->getEndpointMatchingRequest();
        $expected = array (
          'endpoint' => testEndpointUri,
          'uriTemplate' => '/doc/school/{identifier}?localAuthority.code={code}',
          'variableBindings' => 
          array(
                "paramBindings" => 
                array(
                  'code' => array('value' => '00BX'),
                ),
                'pathBindings' => array(
                
                  'identifier' => array('value' => '12345', 'source' => 'request'),
                ),
              )
        );
        $this->assertEquals($expected, $actual);
    }

    function test_matchRequestToEndpointFalse(){
        $base = "http://example.com";
        $path = "/green/icecream/12345?localAuthority.code=00BX&_view=detailed";
        
        $mockRequest = $this->getMock('LinkedDataApiRequest');
        $mockRequest->expects($this->any())
                     ->method('getParams')
                     ->will($this->returnValue(array('localAuthority.code'=> '123') ) );
        $mockRequest->expects($this->any())
                      ->method('getBase')
                      ->will($this->returnValue($base));
        $mockRequest->expects($this->any())
                        ->method('getPath')
                        ->will($this->returnValue($path));
        $mockRequest->expects($this->any())
                        ->method('getPathWithoutExtension')
                        ->will($this->returnValue('/green/icecream/12345'));    
        $mockRequest->expects($this->any())
                        ->method('getUri')
                        ->will($this->returnValue("http://example.com/{$path}"));
    

        $Graph = new ConfigGraph(file_get_contents('../documents/config.ttl'), $mockRequest);
        $Graph->init();
        
        
        $actual = $Graph->getEndpointMatchingRequest();
        $expected = false;
        $this->assertEquals($expected, $actual);
    }


    function test_getTemplatePath(){
        $actual = $this->Graph->getPathTemplate('/a/b/c?d=1&e=2');
        $expected = '/a/b/c';
        $this->assertEquals($expected, $actual);
    }

    function test_getParameterTemplates(){
        $actual = $this->Graph->getParameterTemplates('doc/school/{identifier}?localAuthority.code={code}');
        $expected = array('localAuthority.code' => '{code}');
        $this->assertEquals($expected, $actual);
    }

    function test_getInheritedSelectorFiltersThrowsException(){
        $Config = new ConfigGraph(file_get_contents('../documents/config.ttl'), $this->getMockRequest('/child/endpoint'));
        $Config->init();
        try {
           $Config->getInheritedSelectFilters(); 
        } catch (ConfigGraphException $e){
            return ;
        }
        $this->fail("an exception should be thrown if  the parent selector doesn't use api:filter ");
    }

    function test_getInheritedSelectorFiltersDoesntThrowException(){
        $mockRequest = $this->getMockRequest('/Climbing/Routes/byGrade/HVS', 'http://localhost');
        $Config = new ConfigGraph(file_get_contents('../documents/config.ttl'), $mockRequest);   
        $Config->init();
             
        try {
           $filters = $Config->getInheritedSelectFilters(); 
        } catch (ConfigGraphException $e){
            
            $this->fail("an exception shouldn't be thrown if  the parent selector uses api:filter ");
            
        }
        $this->assertEquals(array('type={Route}'), $filters);
    }



    function test_paramsTemplateMatchesTrue(){
        $t = array('district.code' => '{foo}', 'e' => '{bar}');
        $d = array('district.code'=> '12', 'e'=> '34');
        $actual = $this->Graph->paramsTemplateMatches($t, $d);
        $this->assertEquals(array('foo' => array('value' => '12'), 'bar' => array('value' => '34') ), $actual);

    }

    function test_paramsTemplateMatchesFalse(){
        $t = array('district.code' => '{foo}', 'e' => '{bar}');
        $d = array('district.code'=> '12', 'f'=> '34');
        $actual = $this->Graph->paramsTemplateMatches($t, $d);
        $this->assertFalse($actual);
    }
    
    
    function test_getPathTemplate(){
        $url = 'foo?bar=x';
        $actual = $this->Graph->getPathTemplate($url);
        $expected = 'foo';
        $this->assertEquals($expected, $actual);
    }
    
    
    function test_pathTemplateMatchesReturnsValues(){
        $r = '/doc/school/localAuthority/00BX';
        $t = '/doc/school/localAuthority/{code}';
        $this->assertEquals(array('code' => array('value' => '00BX', 'source' => 'request')), $this->Graph->pathTemplateMatches($t, $r));
        $r = '/doc/school/localAuthority/00BX/';
        $t = '/doc/school/localAuthority/{code}';
        $this->assertEquals(array('code' => array('value' => '00BX', 'source' => 'request')), $this->Graph->pathTemplateMatches($t, $r), "request should still match with trailing slash");
    }
    

    function test_pathTemplateMatchesFalse(){
        $r = 'doc/school/localAuthority/00BX';
        $t = 'doc/school/district/{identifier}';
        $this->assertFalse($this->Graph->pathTemplateMatches($t, $r));
    }
 
    
    function test_getEndpointConfigVariableBindings(){
        
        $bindings = $this->Graph->getEndpointConfigVariableBindings();

            $expected = array(
                "school" => array(
                    "value" => "{base}/school/{identifier}",
                    "type" => RDFS_RESOURCE
                    ),
                "schoolNumber" => array(
                    "value" => "{identifier}",
                    "type" => XSD.'integer',
                    ),
                );
            
        $this->assertEquals($expected, $bindings);
    }


    function test_getApiConfigVariableBindings(){
        $bindings = $this->Graph->getApiConfigVariableBindings(testEndpointUri);
        $expected = array(
            "base" => array(
                "value" => "http://education.data.gov.uk/id",
                ),
            "areaBase" => array(
                    "value" => "http://statistics.data.gov.uk/id",
                ),
            "england" => array(
                    "value" => "{areaBase}/country/921",
                    "type" => RDFS_RESOURCE,
                ),
            );
        $this->assertEquals($expected, $bindings);
    }


    function test_getCompletedEndpointItemTemplate(){
        $actual = $this->Graph->getCompletedItemTemplate();
        $expected = "http://education.data.gov.uk/id/school/12345";
        $this->assertEquals($expected, $actual);
    }
    
    function test_getItemTemplate(){
        $this->assertEquals("{base}/school/{identifier}", $this->Graph->getEndpointItemTemplate(testEndpointUri));
    }
    
    function test_bindVariablesInValue(){
        $bindings = array(
            "base" => array( "value" => "http://education.data.gov.uk/id"),
            "identifier" => array( "value" => "123"),
            );
        $actual = $this->Graph->bindVariablesInValue("{base}/school/{identifier}", $bindings);
        $expected = "http://education.data.gov.uk/id/school/123";
        $this->assertEquals($expected, $actual);        
    }

    function test_getRequestVariableBindings(){
      $expected = array('localAuthority.code'=> array('value' => '00BX', 'source' => 'request'),
          '_view' => array('value' => 'detailed', 'source' => 'request'),
      );
        $actual = $this->Graph->getRequestVariableBindings();
        $this->assertEquals($expected, $actual);        
    }
    
    function test_processVariableBinding(){
        $bindings = array(
            'foo' => array('value'=> 'Hello {bar}'),
            'bar' => array('value'=>'World'),
            );
        
        $actual = $this->Graph->processVariableBinding('foo', $bindings);
        $expected = 'Hello World';
        $this->assertEquals($expected, $actual);        
    }

    function test_processVariableBinding_urlencode_values_in_Variables_when_Type_Is_Resource(){
      $bindings = array(
        'testUri' => array('value' => 'http://example.com/time/{time}', 'type' => RDFS.'Resource'),
        'time' => array('value' => '19:00:56', 'source' => 'request' ),
      );
      $expected = 'http://example.com/time/19%3A00%3A56';
      $actual = $this->Graph->processVariableBinding('testUri', $bindings);
      $this->assertEquals($expected, $actual, "the value bound within a uri should be urlencoded");
    }
    
    function test_variableNamesInValue(){
        $actual = $this->Graph->variableNamesInValue('Hello {bar}');
        $expected = array('bar');
        $this->assertEquals($expected, $actual);        
    }
    
    function test_getAllVariableBindings(){
        $actual= $this->Graph->getAllVariableBindings();
        $expected = array(
            "base" => array(
                "value" => "http://education.data.gov.uk/id",
                ),
            "areaBase" => array(
                    "value" => "http://statistics.data.gov.uk/id",
                ),
            "england" => array(
                    "value" => "{areaBase}/country/921",
                    "type" => RDFS_RESOURCE,
                ),
            'identifier' => array('value' => '12345', 'source' => 'request'),
            'code' => array('value' => '00BX'),
          'localAuthority.code'=> array('value' => '00BX', 'source' => 'request'),
            '_view' => array('value' => 'detailed', 'source' => 'request'),            
            "school" => array(
                "value" => "{base}/school/{identifier}",
                "type" => RDFS_RESOURCE
                ),
            "schoolNumber" => array(
                "value" => "{identifier}",
                "type" => XSD.'integer',
                ),
            
            );
        $this->assertEquals($expected, $actual);        
        
    }
    
    
    function test_getVocabularies(){
        $expected = array('http://puelia-php.googlecode.com/svn/trunk/documents/config.ttl#testVocab');
    }

    function test_getViewers()
    {
        $this->assertEquals(array('http://example.com/#testViewerA', "http://example.com/#apiDefaultViewer", 'http://example.com/#testViewerB',  "http://example.com/#endpointDefaultViewer"), $this->Graph->getViewers());
    }
    
    function test_getViewerByName(){
        $this->assertEquals('http://example.com/#testViewerB', $this->Graph->getViewerByName('testViewerB'), "failed to get testViewerB");
        $this->assertEquals('http://example.com/#testViewerA', $this->Graph->getViewerByName('testViewerA'), "failed to get testViewerA");
        
    }
    
    function test_getFormatters(){
        $this->assertEquals($this->Graph->getFormatters(), array(
        'rdf' => "http://purl.org/linked-data/api/vocab#RdfXmlFormatter",
        'ttl' => "http://purl.org/linked-data/api/vocab#TurtleFormatter",
        'json' => "http://purl.org/linked-data/api/vocab#JsonFormatter",
        'xml' => "http://purl.org/linked-data/api/vocab#XmlFormatter",
         )
        );
        
    }
    
    
    function test_getApiDefaultViewer(){
        $this->assertEquals('http://example.com/#apiDefaultViewer', $this->Graph->getApiDefaultViewer());
    }

    function test_getendpointDefaultViewer(){
        $this->assertEquals('http://example.com/#endpointDefaultViewer', $this->Graph->getEndpointDefaultViewer());
    }

    function test_getDisplayPropertiesOfViewer(){
        $this->assertEquals(array(RDFS_LABEL, RDF_TYPE, OWL_SAMEAS), $this->Graph->getDisplayPropertiesOfViewer('http://example.com/#apiDefaultViewer'));
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
    

    function test_getDisplayPropertyChainsOfViewer(){
        $this->assertEquals(array(array(FOAF.'knows', REL.'knowsOf', REL.'siblingOf'), array(FOAF.'name'), array(RDFS_LABEL)), $this->Graph->getDisplayPropertyChainsOfViewer('http://example.com/#viewerUsingLists'));
    }


    function test_viewerDisplayPropertiesValueToPropertyChain(){
        $this->assertEquals(array(array(gc_config.'testPropWithApiLabel', gc_config.'testPropWithRdfsLabel', gc_config.'age'), array(gc_config.'age')), $this->Graph->getViewerDisplayPropertiesValueAsPropertyChainArray('http://example.com/#viewerUsingPropertiesChain'));
    }


    function test_getApiContentNegotiation(){
        $actual = $this->Graph->getApiContentNegotiation();
        $this->assertEquals(API.'parameterBased', $actual);
    }
    
    function test_apiSupportsFormat(){
        $this->assertTrue($this->Graph->apiSupportsFormat('json'), "json should be supported");
        $this->assertTrue($this->Graph->apiSupportsFormat('ttl'), "turtle should be supported");
        $this->assertTrue($this->Graph->apiSupportsFormat('rdf'), "rdf should be supported");
        $this->assertTrue($this->Graph->apiSupportsFormat('xml'), "xml should be supported");
        $this->assertFalse($this->Graph->apiSupportsFormat('banana'), "banana isn't a supported format and should fail");
    }

    function test_getVocabPropertyRange(){
        $exampleX = 'http://example.com/X';
        $this->Graph->add_resource_triple(FOAF_KNOWS, RDFS_RANGE, $exampleX);
        $actual = $this->Graph->getVocabPropertyRange(FOAF_KNOWS);
        $expected = $exampleX;
        $this->assertEquals($expected, $actual, "real range should be overridden by configgraph assertion");

        $this->Graph->getVocabularyGraph()->add_resource_triple(FOAF_HOMEPAGE, RDFS_RANGE, FOAF_DOCUMENT);
        $actual = $this->Graph->getVocabPropertyRange(FOAF_HOMEPAGE);
        $this->assertEquals(FOAF_DOCUMENT, $actual, "range should be fetched from external vocab");
        
    }
    
    function test_getUriForVocabPropertyLabel(){
        $this->Graph->add_resource_triple(FOAF_HOMEPAGE, RDF_TYPE, RDF_PROPERTY);
        $this->Graph->add_literal_triple(FOAF_HOMEPAGE, API.'label', 'foafHomepage');

        $expected = FOAF_HOMEPAGE;
        $actual = $this->Graph->getUriForVocabPropertyLabel('foafHomepage');
        $this->assertEquals($expected, $actual, "real property definition should be overridden by configgraph assertion");
        
    }
    
    function test_getApiDefaultFormatter(){
        $this->assertEquals(API.'RdfXmlFormatter', $this->Graph->getApiDefaultFormatter(), "api defined default should be api:RdfXmlFormatter");
    }
    
    function test_getEndpointDefaultFormatter(){
        $this->assertEquals(API.'JsonFormatter', $this->Graph->getEndpointDefaultFormatter(), "endpoint defined formatter should be api:JsonFormatter");
    }
    
    
    function test_getRequestPropertyChainArray(){
        $act = $this->Graph->getRequestPropertyChainArray();
        $this->assertEquals(array(), $act); //localAuthority and code  are not defined in config
    }
    
    function test_propertiesStringToArray(){
        $act = $this->Graph->propertiesStringToArray("foo.bar");
        $expected = array (
          0 => 
          array (
            0 => gc_config.'testPropWithApiLabel',
            1 => gc_config.'testPropWithRdfsLabel',
          ),
        );
        $this->assertEquals($expected, $act, "foo.bar should be translated into a 2D array with 2 uris");
        
    }
	function test_dataUriToEndpointItem(){
		$dataUri = 'http://dbpedia.org/resource/Ben_Ledi';
		$expected = '/Climbing/Mountain/Ben_Ledi';
		$this->Graph->resetApiAndEndpoint('http://example.com/#ClimbingAPI');
		$actual = $this->Graph->dataUriToEndpointItem($dataUri);
		$this->assertEquals($expected,$actual, "Should convert a data Uri to an ItemEndpoint path using the itemTemplate");

	}

  function test_dataUriToEndpointItem_path_with_slash_should_not_match_variable(){
		$dataUri = 'http://lod-cloud.net/themes/geographic';
		$expected = false;
		$this->Graph->resetApiAndEndpoint('http://example.com/#UnitTestApi');
		$actual = $this->Graph->dataUriToEndpointItem($dataUri);
		$this->assertEquals($expected,$actual, "Should convert a data Uri to an ItemEndpoint path using the itemTemplate");

	}


	function test_dataUriToEndpointItem_with_URI_encoded_parts(){
		$dataUri = 'http://dbpedia.org/resource/Ben_Ledi_%28Hill%29';
		$expected = '/Climbing/Mountain/Ben_Ledi_%28Hill%29';
		$this->Graph->resetApiAndEndpoint('http://example.com/#ClimbingAPI');
		$actual = $this->Graph->dataUriToEndpointItem($dataUri);
		$this->assertEquals($expected,$actual, "Should convert a data Uri to an ItemEndpoint path using the itemTemplate");

	}

    function test_getItemEndpoints()
    {
        $this->Graph->resetApiAndEndpoint('http://example.com/#ClimbingAPI');
		$actual = $this->Graph->getItemEndpoints();
		$this->assertEquals(array('http://example.com/#mountain'), $actual);
    }

	function test_getDatasetUri(){
		$expected = "http://example.com/dataset/subsets/1";
		$actual = $this->Graph->getDatasetUri();
		$this->assertEquals($expected, $actual, "should return the dataset of the endpoint not the API");
	}


  function test_getInverseOfProperty(){
      $expected = 'http://xmlns.com/foaf/0.1/depicts';
      $this->Graph->add_resource_triple('http://xmlns.com/foaf/0.1/depiction',OWL_INVERSEOF,$expected);
      $this->Graph->add_resource_triple('http://example.com/#UnitTestApi', PUELIA.'inverseProperty', 'http://xmlns.com/foaf/0.1/depiction');
      $actual = $this->Graph->getInverseOfProperty( 'http://xmlns.com/foaf/0.1/depiction');
      $this->assertEquals(array($expected), $actual);
  }

  function test_getInverseOfPropertyWithEndpoint(){
      $expected = 'http://xmlns.com/foaf/0.1/depicts';
      $this->Graph->add_resource_triple('http://xmlns.com/foaf/0.1/depiction',OWL_INVERSEOF,$expected);
      $this->Graph->add_resource_triple(testEndpointUri, PUELIA.'inverseProperty', 'http://xmlns.com/foaf/0.1/depiction');
      $actual = $this->Graph->getInverseOfProperty( 'http://xmlns.com/foaf/0.1/depiction');
      $this->assertEquals(array($expected), $actual);
  }


  function test_getInverseOfPropertyOnlyWorksIfExplicitlyDeclaredOnApiOrEndpoint(){
      $expected = 'http://xmlns.com/foaf/0.1/depicts';
      $this->Graph->add_resource_triple('http://xmlns.com/foaf/0.1/depiction',OWL_INVERSEOF,$expected);
      $actual = $this->Graph->getInverseOfProperty( 'http://xmlns.com/foaf/0.1/depiction');
      $this->assertEquals(false, $actual);
  }



  function test_get_page_title(){
    $expected = "School 12345";
    $actual = $this->Graph->getPageTitle();
    $this->assertEquals($expected, $actual);

  }
 
  function test_getViewerRelatedPagesForItem(){
    $expected = array(
      'http://example.com/places-near-school/12345' => 'school item endpoint',
      'http://example.com/schools-near-school/12345' => 'school item endpoint',
    ); 
    $viewerUri = 'http://example.com/#endpointDefaultViewer';
    $itemUri = 'http://example.com/school/12345';

    $actual = $this->Graph->getViewerRelatedPagesForItemUri($viewerUri,$itemUri);
    $this->assertEquals($expected, $actual, "should return an array of related page URIs");
  }

}
?>
