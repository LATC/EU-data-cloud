<?php
require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'constants.inc.php';
require_once MORIARTY_DIR . 'simplegraph.class.php';
require_once MORIARTY_DIR . 'graphpath.class.php';

class GraphPathTest extends PHPUnit_Framework_TestCase {

  function assertPathSelects($gp, $g, $trace = FALSE) {
    $uri='http://example.org/subj';  
    $matches = $gp->select($g, $trace);
    $this->assertEquals( 1, count($matches));
    $this->assertTrue( is_array($matches[0]));
    $this->assertEquals('uri', $matches[0]['type']);
    $this->assertEquals($uri, $matches[0]['value']);
  }


  function test_match_returns_array() {
    $g = new SimpleGraph();
    $g->set_namespace_mapping('ex', 'http://example.org/');
    $gp = new GraphPath('ex:subj');
    $this->assertTrue( is_array($gp->select($g)));
  }

  function test_match_single_subject() {
    $g = new SimpleGraph();
    $g->set_namespace_mapping('ex', 'http://example.org/');
    $g->add_resource_triple('http://example.org/subj', 'http://example.org/pred', 'http://example.org/obj');
    $g->add_resource_triple('http://example.org/subj', RDF_TYPE, 'http://example.org/Type');

    $gp = new GraphPath('ex:Type');
    $matches = $gp->select($g);
    $this->assertEquals( 1, count($matches));
    $this->assertTrue( is_array($matches[0]));
    $this->assertEquals('uri', $matches[0]['type']);
    $this->assertEquals('http://example.org/subj', $matches[0]['value']);
  }

  function test_match_unknown_type_returns_empty_array() {
    $g = new SimpleGraph();
    $g->set_namespace_mapping('ex', 'http://example.org/');
    $g->add_resource_triple('http://example.org/subj', 'http://example.org/pred', 'http://example.org/obj');
    $g->add_resource_triple('http://example.org/subj', RDF_TYPE, 'http://example.org/Type');

    $gp = new GraphPath('ex:Bogus');
    $this->assertEquals( 0, count($gp->select($g)));
  }

  function test_match_non_existent_type_returns_empty_array() {
    $g = new SimpleGraph();
    $g->set_namespace_mapping('ex', 'http://example.org/');
    $g->add_resource_triple('http://example.org/subj', 'http://example.org/pred', 'http://example.org/obj');
    $g->add_resource_triple('http://example.org/subj', RDF_TYPE, 'http://example.org/Type2');

    $gp = new GraphPath('ex:Type');
    $this->assertEquals( 0, count($gp->select($g)));
  }


  function test_match_wildcard_subject() {
    $g = new SimpleGraph();
    $g->set_namespace_mapping('ex', 'http://example.org/');
    $g->add_resource_triple('http://example.org/subj', 'http://example.org/pred', 'http://example.org/obj');
    $g->add_resource_triple('http://example.org/subj', RDF_TYPE, 'http://example.org/Type');
    $g->add_resource_triple('http://example.org/subj2', 'http://example.org/pred', 'http://example.org/obj');
    $g->add_resource_triple('http://example.org/subj2', RDF_TYPE, 'http://example.org/Type2');

    $gp = new GraphPath('*');
    $matches = $gp->select($g);
    
    $this->assertEquals( 2, count($matches));
    $this->assertTrue( is_array($matches[0]));
    $this->assertEquals('uri', $matches[0]['type']);

    $this->assertTrue( is_array($matches[1]));
    $this->assertEquals('uri', $matches[1]['type']);

    $values = array( $matches[0]['value'], $matches[1]['value']);

    $this->assertTrue( in_array('http://example.org/subj', $values));
    $this->assertTrue( in_array('http://example.org/subj2', $values));
  }
  
  function test_match_single_subject_and_property() {
    $g = new SimpleGraph();
    $g->set_namespace_mapping('ex', 'http://example.org/');
    $g->add_resource_triple('http://example.org/subj', 'http://example.org/pred', 'http://example.org/obj');
    $g->add_resource_triple('http://example.org/subj', RDF_TYPE, 'http://example.org/Type');

    $gp = new GraphPath('ex:Type/ex:pred');
    $matches = $gp->select($g);
    
    $this->assertEquals( 1, count($matches));
    $this->assertTrue( is_array($matches[0]));
    $this->assertEquals('uri', $matches[0]['type']);
    $this->assertEquals('http://example.org/pred', $matches[0]['value']);
  } 

  function test_match_single_subject_and_wildcard_property() {
    $g = new SimpleGraph();
    $g->set_namespace_mapping('ex', 'http://example.org/');
    $g->add_resource_triple('http://example.org/subj', 'http://example.org/pred', 'http://example.org/obj');
    $g->add_resource_triple('http://example.org/subj', RDF_TYPE, 'http://example.org/Type');

    $gp = new GraphPath('ex:Type/*');
    $matches = $gp->select($g);
    $this->assertEquals( 2, count($matches));
    $this->assertTrue( is_array($matches[0]));
    $this->assertEquals('uri', $matches[0]['type']);

    $this->assertTrue( is_array($matches[1]));
    $this->assertEquals('uri', $matches[1]['type']);

    $values = array( $matches[0]['value'], $matches[1]['value']);

    $this->assertTrue( in_array('http://example.org/pred', $values));
    $this->assertTrue( in_array(RDF_TYPE, $values));

  } 
  
  function test_match_repeated_properties_only_once() {
    $g = new SimpleGraph();
    $g->set_namespace_mapping('ex', 'http://example.org/');
    $g->add_resource_triple('http://example.org/subj', 'http://example.org/pred', 'http://example.org/obj');
    $g->add_resource_triple('http://example.org/subj', 'http://example.org/pred', 'http://example.org/obj2');
    $g->add_resource_triple('http://example.org/subj', RDF_TYPE, 'http://example.org/Type');

    $gp = new GraphPath('ex:Type/ex:pred');
    $matches = $gp->select($g);
   
    $this->assertEquals( 1, count($matches));
    $this->assertTrue( is_array($matches[0]));
    $this->assertEquals('uri', $matches[0]['type']);
    $this->assertEquals('http://example.org/pred', $matches[0]['value']);
  } 
  
  function test_match_triple() {
    $g = new SimpleGraph();
    $g->set_namespace_mapping('ex', 'http://example.org/');
    $g->add_resource_triple('http://example.org/subj', 'http://example.org/pred', 'http://example.org/obj');
    $g->add_resource_triple('http://example.org/subj', RDF_TYPE, 'http://example.org/Type');
    $g->add_resource_triple('http://example.org/obj', RDF_TYPE, 'http://example.org/Type2');

    $gp = new GraphPath('ex:Type/ex:pred/ex:Type2');
    $matches = $gp->select($g);
    
    $this->assertEquals( 1, count($matches));
    $this->assertTrue( is_array($matches[0]));
    $this->assertEquals('uri', $matches[0]['type']);
    $this->assertEquals('http://example.org/obj', $matches[0]['value']);
  }   

  function test_match_long_path() {
    $g = new SimpleGraph();
    $g->set_namespace_mapping('ex', 'http://example.org/');
    $g->add_resource_triple('http://example.org/r1', 'http://example.org/pred', 'http://example.org/r2');
    $g->add_resource_triple('http://example.org/r2', 'http://example.org/pred', 'http://example.org/r3');
    $g->add_resource_triple('http://example.org/r3', 'http://example.org/pred', 'http://example.org/r4');
    $g->add_resource_triple('http://example.org/r4', 'http://example.org/pred', 'http://example.org/r5');
    $g->add_resource_triple('http://example.org/r1', RDF_TYPE, 'http://example.org/Type');
    $g->add_resource_triple('http://example.org/r2', RDF_TYPE, 'http://example.org/Type');
    $g->add_resource_triple('http://example.org/r3', RDF_TYPE, 'http://example.org/Type');
    $g->add_resource_triple('http://example.org/r4', RDF_TYPE, 'http://example.org/Type');
    $g->add_resource_triple('http://example.org/r5', RDF_TYPE, 'http://example.org/Type');

    $gp = new GraphPath('ex:Type/ex:pred/ex:Type/ex:pred/ex:Type/ex:pred/ex:Type/ex:pred/ex:Type');
    $matches = $gp->select($g);
    
    $this->assertEquals( 1, count($matches));
    $this->assertTrue( is_array($matches[0]));
    $this->assertEquals('uri', $matches[0]['type']);
    $this->assertEquals('http://example.org/r5', $matches[0]['value']);
  }   
  
  function test_match_triple_with_wildcard_property() {
    $g = new SimpleGraph();
    $g->set_namespace_mapping('ex', 'http://example.org/');
    $g->add_resource_triple('http://example.org/subj', 'http://example.org/pred', 'http://example.org/obj');
    $g->add_resource_triple('http://example.org/subj', 'http://example.org/pred', 'http://example.org/obj2');
    $g->add_resource_triple('http://example.org/subj', 'http://example.org/pred', 'http://example.org/obj3');
    $g->add_resource_triple('http://example.org/subj', RDF_TYPE, 'http://example.org/Type');
    $g->add_resource_triple('http://example.org/obj', RDF_TYPE, 'http://example.org/Type2');
    $g->add_resource_triple('http://example.org/obj2', RDF_TYPE, 'http://example.org/Type2');
    $g->add_resource_triple('http://example.org/obj3', RDF_TYPE, 'http://example.org/Bogus');

    $gp = new GraphPath('ex:Type/*/ex:Type2');
    $matches = $gp->select($g);
    
    $this->assertEquals( 2, count($matches));
    $this->assertTrue( is_array($matches[0]));
    $this->assertEquals('uri', $matches[0]['type']);

    $this->assertTrue( is_array($matches[1]));
    $this->assertEquals('uri', $matches[1]['type']);

    $values = array( $matches[0]['value'], $matches[1]['value']);

    $this->assertTrue( in_array('http://example.org/obj', $values));
    $this->assertTrue( in_array('http://example.org/obj2', $values));
  }

  function test_match_single_subject_with_wildcard_filter() {
    $g = new SimpleGraph();
    $g->set_namespace_mapping('ex', 'http://example.org/');
    $g->add_resource_triple('http://example.org/subj', 'http://example.org/pred', 'http://example.org/obj');
    $g->add_resource_triple('http://example.org/subj', RDF_TYPE, 'http://example.org/Type');

    $gp = new GraphPath('ex:Type[*]');
    $this->assertPathSelects($gp, $g);
  }

  function test_match_single_subject_with_specific_filter() {
    $g = new SimpleGraph();
    $g->set_namespace_mapping('ex', 'http://example.org/');
    $g->add_resource_triple('http://example.org/subj', 'http://example.org/pred', 'http://example.org/obj');
    $g->add_resource_triple('http://example.org/subj', RDF_TYPE, 'http://example.org/Type');
    $g->add_resource_triple('http://example.org/bogus', 'http://example.org/pred2', 'http://example.org/obj');
    $g->add_resource_triple('http://example.org/bogus', RDF_TYPE, 'http://example.org/Type');

    $gp = new GraphPath('ex:Type[ex:pred]');
    $this->assertPathSelects($gp, $g);
  }



  function test_match_single_subject_with_multiple_filters() {
    $g = new SimpleGraph();
    $g->set_namespace_mapping('ex', 'http://example.org/');
    $g->add_resource_triple('http://example.org/subj', 'http://example.org/pred', 'http://example.org/obj');
    $g->add_resource_triple('http://example.org/subj', 'http://example.org/pred2', 'http://example.org/obj');
    $g->add_resource_triple('http://example.org/subj', RDF_TYPE, 'http://example.org/Type');
    $g->add_resource_triple('http://example.org/bogus', 'http://example.org/pred', 'http://example.org/obj');
    $g->add_resource_triple('http://example.org/bogus', 'http://example.org/pred3', 'http://example.org/obj');
    $g->add_resource_triple('http://example.org/bogus', RDF_TYPE, 'http://example.org/Type');

    $gp = new GraphPath('ex:Type[ex:pred][ex:pred2]');
    $this->assertPathSelects($gp, $g);
  }

  function test_match_single_subject_with_nested_filters() {
    $g = new SimpleGraph();
    $g->set_namespace_mapping('ex', 'http://example.org/');
    $g->add_resource_triple('http://example.org/subj', 'http://example.org/pred', 'http://example.org/obj');
    $g->add_resource_triple('http://example.org/subj', RDF_TYPE, 'http://example.org/Type');
    $g->add_resource_triple('http://example.org/obj', 'http://example.org/pred2', 'http://example.org/obj2');
    $g->add_resource_triple('http://example.org/obj', RDF_TYPE, 'http://example.org/Type2');
    $g->add_resource_triple('http://example.org/obj2', 'http://example.org/pred2', 'http://example.org/obj3');

    $gp = new GraphPath('ex:Type[ex:pred/ex:Type2[ex:pred2]]');
    $this->assertPathSelects($gp, $g);
  }


  function test_match_single_subject_with_or_filters() {
    $g = new SimpleGraph();
    $g->set_namespace_mapping('ex', 'http://example.org/');
    $g->add_resource_triple('http://example.org/subj', 'http://example.org/pred', 'http://example.org/obj');
    $g->add_resource_triple('http://example.org/subj2', 'http://example.org/pred2', 'http://example.org/obj');
    $g->add_resource_triple('http://example.org/subj3', 'http://example.org/pred3', 'http://example.org/obj');

    $gp = new GraphPath('*[ex:pred or ex:pred2]');
//    $this->assertEquals( "*[ex:pred or ex:pred2]", $gp->to_string());
    $matches = $gp->select($g);
    
    $this->assertEquals( 2, count($matches));
    $this->assertTrue( is_array($matches[0]));
    $this->assertEquals('uri', $matches[0]['type']);

    $this->assertTrue( is_array($matches[1]));
    $this->assertEquals('uri', $matches[1]['type']);

    $values = array( $matches[0]['value'], $matches[1]['value']);

    $this->assertTrue( in_array('http://example.org/subj', $values));
    $this->assertTrue( in_array('http://example.org/subj2', $values));
  }

  function test_match_single_subject_with_and_filters() {
    $g = new SimpleGraph();
    $g->set_namespace_mapping('ex', 'http://example.org/');
    $g->add_resource_triple('http://example.org/subj', 'http://example.org/pred', 'http://example.org/obj');
    $g->add_resource_triple('http://example.org/subj', 'http://example.org/pred2', 'http://example.org/obj');
    $g->add_resource_triple('http://example.org/subj2', 'http://example.org/pred', 'http://example.org/obj');

    $gp = new GraphPath('*[ex:pred and ex:pred2]');
    $this->assertPathSelects($gp, $g);
  }

  function test_match_single_subject_with_comparison() {
    $g = new SimpleGraph();
    $g->set_namespace_mapping('ex', 'http://example.org/');
    $g->add_literal_triple('http://example.org/subj', 'http://example.org/pred', 'foo');
    $g->add_literal_triple('http://example.org/subj', 'http://example.org/pred2', 'foo');
    $g->add_literal_triple('http://example.org/subj2', 'http://example.org/pred', 'bar');
    $g->add_literal_triple('http://example.org/subj2', 'http://example.org/pred2', 'foo');
    
    $gp = new GraphPath('*[ex:pred/* = ex:pred2/*]');
    $this->assertPathSelects($gp, $g);
  }


  function test_match_single_subject_with_literal_last_step() {
    $g = new SimpleGraph();
    $g->set_namespace_mapping('ex', 'http://example.org/');
    $g->add_literal_triple('http://example.org/subj', 'http://example.org/pred', 'foo');
    $g->add_literal_triple('http://example.org/subj', 'http://example.org/pred2', 'foo');
    $g->add_literal_triple('http://example.org/subj2', 'http://example.org/pred', 'bar');
    $g->add_literal_triple('http://example.org/subj2', 'http://example.org/pred2', 'foo');
    
    $gp = new GraphPath('*[ex:pred/"foo"]');
    $this->assertPathSelects($gp, $g);
  }


  function test_match_single_subject_with_comparison_to_string() {
    $g = new SimpleGraph();
    $g->set_namespace_mapping('ex', 'http://example.org/');
    $g->add_literal_triple('http://example.org/subj', 'http://example.org/pred', 'foo');
    $g->add_literal_triple('http://example.org/subj', 'http://example.org/pred2', 'foo');
    $g->add_literal_triple('http://example.org/subj2', 'http://example.org/pred', 'bar');
    $g->add_literal_triple('http://example.org/subj2', 'http://example.org/pred2', 'foo');
    
    $gp = new GraphPath('*[ex:pred/* = "foo"]');
    $this->assertPathSelects($gp, $g);
  }
  
  function test_match_single_subject_with_text_function() {
    $g = new SimpleGraph();
    $g->set_namespace_mapping('ex', 'http://example.org/');
    $g->add_literal_triple('http://example.org/subj', 'http://example.org/pred', 'foo');
    $g->add_resource_triple('http://example.org/subj2', 'http://example.org/pred', 'http://example.org/obj');
    
    $gp = new GraphPath('*[ex:pred/text()]');
    $this->assertPathSelects($gp, $g);
  } 
  
  function test_match_single_subject_with_comparison_to_string_reverse_order() {
    $g = new SimpleGraph();
    $g->set_namespace_mapping('ex', 'http://example.org/');
    $g->add_literal_triple('http://example.org/subj', 'http://example.org/pred', 'foo');
    $g->add_literal_triple('http://example.org/subj', 'http://example.org/pred2', 'foo');
    $g->add_literal_triple('http://example.org/subj2', 'http://example.org/pred', 'bar');
    $g->add_literal_triple('http://example.org/subj2', 'http://example.org/pred2', 'foo');
    
    $gp = new GraphPath('*["foo" = ex:pred/*]');
    $this->assertPathSelects($gp, $g);
  } 
  
  
  function test_match_single_subject_with_number_comparison() {
    $g = new SimpleGraph();
    $g->set_namespace_mapping('ex', 'http://example.org/');
    $g->add_literal_triple('http://example.org/subj', 'http://example.org/pred', '2');
    $g->add_literal_triple('http://example.org/subj2', 'http://example.org/pred', '1');
    
    $gp = new GraphPath('*[ex:pred/* = 2]');
    $this->assertPathSelects($gp, $g);

    $gp = new GraphPath('*[2 = ex:pred/*]');
    $this->assertPathSelects($gp, $g);
  }   
  
  function test_match_single_subject_with_count() {
    $g = new SimpleGraph();
    $g->set_namespace_mapping('ex', 'http://example.org/');
    $g->add_literal_triple('http://example.org/subj', 'http://example.org/pred', 'foo');
    $g->add_literal_triple('http://example.org/subj', 'http://example.org/pred', 'bar');
    $g->add_literal_triple('http://example.org/subj2', 'http://example.org/pred', 'bar');
    
    $gp = new GraphPath('*[count(ex:pred) = 2]');
    $this->assertPathSelects($gp, $g);

    $gp = new GraphPath('*[2 = count(ex:pred)]');
    $this->assertPathSelects($gp, $g);
  } 
  
  function test_match_single_subject_with_count_on_right() {
    $g = new SimpleGraph();
    $g->set_namespace_mapping('ex', 'http://example.org/');
    $g->add_literal_triple('http://example.org/subj', 'http://example.org/pred', 'foo');
    $g->add_literal_triple('http://example.org/subj', 'http://example.org/pred', 'bar');
    $g->add_literal_triple('http://example.org/subj', 'http://example.org/pred2', 'foo');
    $g->add_literal_triple('http://example.org/subj', 'http://example.org/pred2', 'bar');
    $g->add_literal_triple('http://example.org/subj2', 'http://example.org/pred', 'bar');
    $g->add_literal_triple('http://example.org/subj2', 'http://example.org/pred2', 'foo');
    $g->add_literal_triple('http://example.org/subj2', 'http://example.org/pred2', 'bar');
    
    $gp = new GraphPath('*[count(ex:pred) = count(ex:pred2)]');
    $this->assertPathSelects($gp, $g);
  }   

  function test_match_local_name_function() {
    $g = new SimpleGraph();
    $g->set_namespace_mapping('ex', 'http://example.org/');
    $g->add_resource_triple('http://example.org/subj', 'http://example.org/pred', 'http://example.org/obj');
    $g->add_resource_triple('http://example.org/subj2', 'http://example.org/pred', 'http://example.org/foo');
    
    $gp = new GraphPath('*[local-name(ex:pred/*) = "obj"]');
    $this->assertPathSelects($gp, $g);

    $gp = new GraphPath('*["obj" = local-name(ex:pred/*)]');
    $this->assertPathSelects($gp, $g);
  }   

  function test_match_namespace_uri_function() {
    $g = new SimpleGraph();
    $g->set_namespace_mapping('ex', 'http://example.org/');
    $g->add_resource_triple('http://example.org/subj', 'http://example.org/pred', 'http://example.org/obj');
    $g->add_resource_triple('http://example.org/subj2', 'http://example.org/pred', 'http://bogus.org/foo');
    
    $gp = new GraphPath('*[namespace-uri(ex:pred/*) = "http://example.org/"]');
    $this->assertPathSelects($gp, $g);
  }   

  function test_match_uri_function() {
    $g = new SimpleGraph();
    $g->set_namespace_mapping('ex', 'http://example.org/');
    $g->add_resource_triple('http://example.org/subj', 'http://example.org/pred', 'http://example.org/obj');
    $g->add_resource_triple('http://example.org/subj2', 'http://example.org/pred', 'http://bogus.org/foo');
    
    $gp = new GraphPath('*[uri(ex:pred/*) = "http://example.org/obj"]');
    $this->assertPathSelects($gp, $g);
  }   
  
  function test_match_exp_function() {
    $g = new SimpleGraph();
    $g->set_namespace_mapping('ex', 'http://example.org/');
    $g->add_resource_triple('http://example.org/subj', 'http://example.org/pred', 'http://example.org/obj');
    $g->add_resource_triple('http://example.org/subj2', 'http://example.org/pred', 'http://bogus.org/foo');
    
    $gp = new GraphPath('*[uri(ex:pred/*) = exp("ex:obj")]');
    $this->assertPathSelects($gp, $g);
  }     

  function test_match_literal_value_function() {
    $g = new SimpleGraph();
    $g->set_namespace_mapping('ex', 'http://example.org/');
    $g->add_literal_triple('http://example.org/subj', 'http://example.org/pred', 'obj');
    $g->add_literal_triple('http://example.org/subj2', 'http://example.org/pred', 'foo');
    
    $gp = new GraphPath('*[literal-value(ex:pred) = "obj"]');
    $this->assertPathSelects($gp, $g);
  } 

  function test_match_literal_value_function_no_literal() {
    $g = new SimpleGraph();
    $g->set_namespace_mapping('ex', 'http://example.org/');
    $g->add_resource_triple('http://example.org/subj', 'http://example.org/pred', 'http://example.org/obj');
    
    $gp = new GraphPath('*[literal-value(ex:pred) = ""]');
    $this->assertPathSelects($gp, $g);
  } 

  function test_match_literal_value_function_no_matches() {
    $g = new SimpleGraph();
    $g->set_namespace_mapping('ex', 'http://example.org/');
    $g->add_resource_triple('http://example.org/subj', 'http://example.org/pred2', 'http://example.org/obj');
    
    $gp = new GraphPath('*[literal-value(ex:pred) = ""]');
    $this->assertPathSelects($gp, $g);
  } 

  function test_match_literal_dt_function() {
    $g = new SimpleGraph();
    $g->set_namespace_mapping('ex', 'http://example.org/');
    $g->add_literal_triple('http://example.org/subj', 'http://example.org/pred', 'obj', null, 'http://example.org/dt');
    $g->add_literal_triple('http://example.org/subj2', 'http://example.org/pred', 'obj', null, 'http://example.org/bogus');
    
    $gp = new GraphPath('*[literal-dt(ex:pred) = "http://example.org/dt"]');
    $this->assertPathSelects($gp, $g);
  } 

  function test_match_literal_dt_function_no_literal() {
    $g = new SimpleGraph();
    $g->set_namespace_mapping('ex', 'http://example.org/');
    $g->add_resource_triple('http://example.org/subj', 'http://example.org/pred', 'http://example.org/obj');
    
    $gp = new GraphPath('*[literal-dt(ex:pred) = ""]');
    $this->assertPathSelects($gp, $g);
  } 

  function test_match_literal_dt_function_no_matches() {
    $g = new SimpleGraph();
    $g->set_namespace_mapping('ex', 'http://example.org/');
    $g->add_resource_triple('http://example.org/subj', 'http://example.org/pred2', 'http://example.org/obj');
    
    $gp = new GraphPath('*[literal-dt(ex:pred) = ""]');
    $this->assertPathSelects($gp, $g);
  } 


  function test_match_string_length_function() {
    $g = new SimpleGraph();
    $g->set_namespace_mapping('ex', 'http://example.org/');
    $g->add_literal_triple('http://example.org/subj', 'http://example.org/pred', 'obj');
    $g->add_literal_triple('http://example.org/subj2', 'http://example.org/pred', 'foobar');
    
    $gp = new GraphPath('*[string-length(literal-value(ex:pred)) = 3]');
    $this->assertPathSelects($gp, $g);
  } 

  function test_match_normalize_space_function_trims() {
    $g = new SimpleGraph();
    $g->set_namespace_mapping('ex', 'http://example.org/');
    $g->add_literal_triple('http://example.org/subj', 'http://example.org/pred', ' obj ');
    
    $gp = new GraphPath('*[normalize-space(literal-value(ex:pred)) = "obj"]');
    $this->assertPathSelects($gp, $g);
  } 

  function test_match_normalize_space_function_removes_double_spaces() {
    $g = new SimpleGraph();
    $g->set_namespace_mapping('ex', 'http://example.org/');
    $g->add_literal_triple('http://example.org/subj', 'http://example.org/pred', "x  x\nx\rx  x    x");
    
    $gp = new GraphPath('*[normalize-space(literal-value(ex:pred)) = "x x x x x x"]');
    $this->assertPathSelects($gp, $g);
  } 


  function test_match_abbreviated_self() {
    $g = new SimpleGraph();
    $g->set_namespace_mapping('ex', 'http://example.org/');
    $g->add_resource_triple('http://example.org/subj', 'http://example.org/pred', 'http://example.org/obj');
    $g->add_resource_triple('http://example.org/subj2', 'http://example.org/pred', 'http://bogus.org/foo');
    
    $gp = new GraphPath('*[uri(.) = "http://example.org/subj"]');
    $this->assertPathSelects($gp, $g);
  }     


  function test_match_boolean_function() {
    $g = new SimpleGraph();
    $g->set_namespace_mapping('ex', 'http://example.org/');
    $g->add_resource_triple('http://example.org/subj', 'http://example.org/pred', 'http://example.org/obj');
    $g->add_resource_triple('http://example.org/subj2', 'http://example.org/bogus', 'http://example.org/obj');
    
    $gp = new GraphPath('*[boolean(ex:bogus) = false()]');
    $this->assertPathSelects($gp, $g);

    $gp2 = new GraphPath('*[boolean(ex:pred) = true()]');
    $this->assertPathSelects($gp2, $g);
  }     

  function test_match_compare_node_set_with_boolean() {
    $g = new SimpleGraph();
    $g->set_namespace_mapping('ex', 'http://example.org/');
    $g->add_resource_triple('http://example.org/subj', 'http://example.org/pred', 'http://example.org/obj');
    $g->add_resource_triple('http://example.org/subj2', 'http://example.org/bogus', 'http://example.org/obj');
    
    $gp = new GraphPath('*[ex:pred = true()]');
    $this->assertPathSelects($gp, $g);

    $gp2 = new GraphPath('*[true() = ex:pred]');
    $this->assertPathSelects($gp2, $g);
  }     


  function test_match_compare_string_with_boolean() {
    $g = new SimpleGraph();
    $g->set_namespace_mapping('ex', 'http://example.org/');
    $g->add_literal_triple('http://example.org/subj', 'http://example.org/pred', 'obj');
    $g->add_literal_triple('http://example.org/subj2', 'http://example.org/pred', '');
    
    $gp = new GraphPath('*[literal-value(ex:pred) = true()]');
    $this->assertPathSelects($gp, $g);
  }     

}

