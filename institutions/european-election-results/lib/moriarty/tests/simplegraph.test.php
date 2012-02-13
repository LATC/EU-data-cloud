<?php
require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'constants.inc.php';
require_once MORIARTY_DIR . 'simplegraph.class.php';
require_once MORIARTY_TEST_DIR . 'fakerequestfactory.class.php';
require_once MORIARTY_TEST_DIR . 'fakehttprequest.class.php';
require_once MORIARTY_TEST_DIR . 'fakehttpresponse.class.php';
define('exampleNS', 'http://example.com/');

class SimpleGraphTest extends PHPUnit_Framework_TestCase {
    var $_single_triple =  '<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:ex="http://example.org/">
  <rdf:Description rdf:about="http://example.org/subj">
    <ex:pred>foo</ex:pred>
  </rdf:Description>
</rdf:RDF>';

    var $_single_triple_invalid_rdf =  '<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:ex="http://example.org/">
  <rdf:Description rdf:about="http://example.org/subj">
    <ex:pred>foo</ex:pred>
</rdf:RDF>';

    var $_single_triple_turtle =  '@prefix ex: <http://example.org/> .
     <http://example.org/subj> ex:pred "foo" .';

    var $_single_triple_invalid_turtle =  '@prefix ex: <http://example.org/> .
     <http://example.org/subj> foo:pred "foo" .';

    var $_single_triple_json = '{ "http:\/\/example.org\/subj" : {"http:\/\/example.org\/pred" : [ { "value" : "foo", "type" : "literal" } ] } }';



  function test_add_resource_triple() {
    $g = new SimpleGraph();
    $g->add_resource_triple('http://example.org/subj', 'http://example.org/pred', 'http://example.org/obj');

    $this->assertEquals( 1, count($g->get_triples()));
  }

  function test_add_resource_triple_sets_object_type() {
    $g = new SimpleGraph();
    $g->add_resource_triple('http://example.org/subj', 'http://example.org/pred', 'http://example.org/obj');

    $triples = $g->get_triples();
    $this->assertTrue( isset($triples[0]['o_type']));
    $this->assertEquals( 'iri', $triples[0]['o_type']);
  }

  function test_add_resource_triple_ignores_duplicates() {
    $g = new SimpleGraph();
    $g->add_resource_triple('http://example.org/subj', 'http://example.org/pred', 'http://example.org/obj');
    $g->add_resource_triple('http://example.org/subj', 'http://example.org/pred', 'http://example.org/obj');


    $this->assertEquals( 1, count($g->get_triples()));
  }

  function test_add_resource_triple_accepts_bnode_subjects() {
    $g = new SimpleGraph();
    $g->add_resource_triple('_:subj', 'http://example.org/pred', 'http://example.org/obj');
    $this->assertEquals( 1, count($g->get_triples()));
  }

  function test_add_resource_triple_accepts_bnode_objects() {
    $g = new SimpleGraph();
    $g->add_resource_triple('http://example.org/subj', 'http://example.org/pred', '_:obj');
    $this->assertEquals( 1, count($g->get_triples()));
  }

  function test_add_resource_triple_sets_bnode_object_type() {
    $g = new SimpleGraph();
    $g->add_resource_triple('http://example.org/subj', 'http://example.org/pred', '_:obj');

    $triples = $g->get_triples();
    $this->assertTrue( isset($triples[0]['o_type']));
    $this->assertEquals( 'bnode', $triples[0]['o_type']);
  }

  function test_add_literal_triple() {
    $g = new SimpleGraph();
    $g->add_literal_triple('http://example.org/subj', 'http://example.org/pred', 'literal');

    $this->assertEquals( 1, count($g->get_triples()));
  }

  function test_add_literal_triple_sets_object_type() {
    $g = new SimpleGraph();
    $g->add_literal_triple('http://example.org/subj', 'http://example.org/pred', 'literal');

    $triples = $g->get_triples();
    $this->assertTrue( isset($triples[0]['o_type']));
    $this->assertEquals( 'literal', $triples[0]['o_type']);
  }

  function test_add_literal_triple_sets_object_language() {
    $g = new SimpleGraph();
    $g->add_literal_triple('http://example.org/subj', 'http://example.org/pred', 'literal', 'en');

    $triples = $g->get_triples();
    $this->assertTrue( isset($triples[0]['o_lang']));
    $this->assertEquals('en', $triples[0]['o_lang']);
  }
  function test_add_literal_triple_sets_object_datatype() {
    $g = new SimpleGraph();
    $g->add_literal_triple('http://example.org/subj', 'http://example.org/pred', 'literal', 'en', 'http://example.org/dt');

    $triples = $g->get_triples();
    $this->assertTrue( isset($triples[0]['o_datatype']));
    $this->assertEquals('http://example.org/dt', $triples[0]['o_datatype']);
  }

  function test_add_resource_triple_ignores_duplicate_languages() {
    $g = new SimpleGraph();
    $g->add_literal_triple('http://example.org/subj', 'http://example.org/pred', 'literal', 'en');
    $g->add_literal_triple('http://example.org/subj', 'http://example.org/pred', 'literal', 'de');
    $g->add_literal_triple('http://example.org/subj', 'http://example.org/pred', 'literal', 'en');


    $this->assertEquals( 2, count($g->get_triples()));
  }

  function test_add_resource_triple_ignores_duplicate_datatypes() {
    $g = new SimpleGraph();
    $g->add_literal_triple('http://example.org/subj', 'http://example.org/pred', 'literal', null, 'http://example.org/dt');
    $g->add_literal_triple('http://example.org/subj', 'http://example.org/pred', 'literal', null, 'http://example.org/dt2');
    $g->add_literal_triple('http://example.org/subj', 'http://example.org/pred', 'literal', null, 'http://example.org/dt');


    $this->assertEquals( 2, count($g->get_triples()));
  }

  function test_get_first_literal() {
    $g = new SimpleGraph();
    $g->add_literal_triple('http://example.org/subj', 'http://example.org/pred', 'literal');

    $this->assertEquals( "literal", $g->get_first_literal('http://example.org/subj', 'http://example.org/pred'));
  }
  function test_get_first_literal_ignores_resources() {
    $g = new SimpleGraph();
    $g->add_resource_triple('http://example.org/subj', 'http://example.org/pred', 'http://example.org/obj');
    $g->add_literal_triple('http://example.org/subj', 'http://example.org/pred', 'literal');

    $this->assertEquals( "literal", $g->get_first_literal('http://example.org/subj', 'http://example.org/pred'));
  }

  function test_remove_resource_triple() {
    $g = new SimpleGraph();
    $g->add_resource_triple('http://example.org/subj', 'http://example.org/pred', 'http://example.org/obj');

    $this->assertEquals( 1, count($g->get_triples()));

    $g->remove_resource_triple('http://example.org/subj', 'http://example.org/pred', 'http://example.org/obj');
    $this->assertEquals( 0, count($g->get_triples()));
  }

  function test_remove_literal_triple() {
    $g = new SimpleGraph();
    $g->add_literal_triple('http://example.org/subj', 'http://example.org/pred', 'literal');

    $this->assertEquals( 1, count($g->get_triples()));

    $g->remove_literal_triple('http://example.org/subj', 'http://example.org/pred', 'literal');
    $this->assertEquals( 0, count($g->get_triples()));
  }


  function test_remove_triples_about() {
    $g = new SimpleGraph();
    $g->add_resource_triple('http://example.org/subj', 'http://example.org/pred', 'http://example.org/obj');
    $g->add_literal_triple('http://example.org/subj', 'http://example.org/pred', 'literal');

    $g->remove_triples_about('http://example.org/subj');

    $this->assertEquals( 0, count($g->get_triples()));
  }

  function test_remove_triples_about_affects_only_specified_subject() {
    $g = new SimpleGraph();
    $g->add_resource_triple('http://example.org/subj', 'http://example.org/pred', 'http://example.org/obj');
    $g->add_literal_triple('http://example.org/subj2', 'http://example.org/pred', 'literal');

    $g->remove_triples_about('http://example.org/subj');

    $this->assertEquals( 1, count($g->get_triples()));
  }

  function test_from_rdfxml() {
    $g = new SimpleGraph();
    $g->from_rdfxml($this->_single_triple);
    $this->assertEquals( 1, count($g->get_triples()));

    $index = $g->get_index();
    $this->assertEquals("foo", $index['http://example.org/subj']['http://example.org/pred'][0]['value']);
  }

  function test_from_rdfxml_replaces_existing_triples() {
    $g = new SimpleGraph();
    $g->add_resource_triple('http://example.org/subj1', 'http://example.org/pred1', 'http://example.org/obj1');
    $g->from_rdfxml($this->_single_triple);
    $this->assertEquals( 1, count($g->get_triples()));

    $index = $g->get_index();
    $this->assertEquals("foo", $index['http://example.org/subj']['http://example.org/pred'][0]['value']);
  }

  function test_has_resource_triple() {
    $g = new SimpleGraph();
    $g->add_resource_triple('http://example.org/subj1', 'http://example.org/pred1', 'http://example.org/obj1');

    $this->assertTrue( $g->has_resource_triple('http://example.org/subj1', 'http://example.org/pred1', 'http://example.org/obj1'));
    $this->assertFalse( $g->has_resource_triple('http://example.org/subj1', 'http://example.org/pred1', 'http://example.org/obj2'));
  }
  function test_get_first_resource() {
    $g = new SimpleGraph();
    $g->add_resource_triple('http://example.org/subj', 'http://example.org/pred', 'http://example.org/obj');

    $this->assertEquals( "http://example.org/obj", $g->get_first_resource('http://example.org/subj', 'http://example.org/pred'));
  }
  function test_get_first_resource_ignores_literals() {
    $g = new SimpleGraph();
    $g->add_resource_triple('http://example.org/subj', 'http://example.org/pred', 'http://example.org/obj');
    $g->add_literal_triple('http://example.org/subj', 'http://example.org/pred', 'literal');

    $this->assertEquals( "http://example.org/obj", $g->get_first_resource('http://example.org/subj', 'http://example.org/pred'));
  }


  function test_remove_property_values() {
    $g = new SimpleGraph();
    $g->add_resource_triple('http://example.org/subj', 'http://example.org/pred', 'http://example.org/obj');

    $this->assertEquals( 1, count($g->get_triples()));

    $g->remove_property_values('http://example.org/subj', 'http://example.org/pred');
    $this->assertEquals( 0, count($g->get_triples()));
  }

  function test_remove_property_values_removes_multiple_values() {
    $g = new SimpleGraph();
    $g->add_resource_triple('http://example.org/subj', 'http://example.org/pred', 'http://example.org/obj');
    $g->add_resource_triple('http://example.org/subj', 'http://example.org/pred', 'http://example.org/obj2');
    $g->add_resource_triple('http://example.org/subj', 'http://example.org/pred', 'http://example.org/obj3');
    $g->add_resource_triple('http://example.org/subj', 'http://example.org/pred', 'http://example.org/obj4');
    $g->add_resource_triple('http://example.org/subj', 'http://example.org/pred', 'http://example.org/obj5');

    $this->assertEquals( 5, count($g->get_triples()));

    $g->remove_property_values('http://example.org/subj', 'http://example.org/pred');
    $this->assertEquals( 0, count($g->get_triples()));
  }

  function test_remove_property_values_ignores_unknown_properties() {
    $g = new SimpleGraph();
    $g->add_resource_triple('http://example.org/subj', 'http://example.org/pred', 'http://example.org/obj');

    $this->assertEquals( 1, count($g->get_triples()));

    $g->remove_property_values('http://example.org/subj', 'http://example.org/pred2');
    $this->assertEquals( 1, count($g->get_triples()));
  }

  function test_remove_all_triples() {
    $g = new SimpleGraph();
    $g->add_resource_triple('http://example.org/subj', 'http://example.org/pred', 'http://example.org/obj');
    $g->add_resource_triple('http://example.org/subj', 'http://example.org/pred', 'http://example.org/obj2');

    $this->assertEquals( 2, count($g->get_triples()));

    $g->remove_all_triples();
    $this->assertEquals( 0, count($g->get_triples()));
  }

  function test_add_rdfxml_appends_new_triples() {
    $g = new SimpleGraph();
    $g->add_resource_triple('http://example.org/subj', 'http://example.org/pred1', 'http://example.org/obj1');
    $g->add_rdfxml($this->_single_triple);
    $this->assertEquals( 2, count($g->get_triples()));

    $index = $g->get_index();
    $this->assertEquals("http://example.org/obj1", $index['http://example.org/subj']['http://example.org/pred1'][0]['value']);
    $this->assertEquals("foo", $index['http://example.org/subj']['http://example.org/pred'][0]['value']);
    $this->assertEquals("literal", $index['http://example.org/subj']['http://example.org/pred'][0]['type']);
  }

  function test_add_rdfxml_ignores_duplicate_triples() {
    $g = new SimpleGraph();
    $g->from_rdfxml($this->_single_triple);
    $g->add_rdfxml($this->_single_triple);
    $this->assertEquals( 1, count($g->get_triples()));

  }
  
  function test_add_rdf_adds_rdf_json(){
      $g = new SimpleGraph();
      $g->add_rdf($this->_single_triple_json);
      $this->assertEquals( 1, count($g->get_triples()));      
  }

  function test_add_turtle_appends_new_triples() {
    $g = new SimpleGraph();
    $g->add_resource_triple('http://example.org/subj', 'http://example.org/pred1', 'http://example.org/obj1');
    $g->add_turtle($this->_single_triple_turtle);
    $this->assertEquals( 2, count($g->get_triples()));

    $index = $g->get_index();
    $this->assertEquals("http://example.org/obj1", $index['http://example.org/subj']['http://example.org/pred1'][0]['value']);
    $this->assertEquals("foo", $index['http://example.org/subj']['http://example.org/pred'][0]['value']);
    $this->assertEquals("literal", $index['http://example.org/subj']['http://example.org/pred'][0]['type']);
  }

  function test_add_turtle_does_not_merge_bnodes_with_same_name() {
    $g = new SimpleGraph();
    $g->add_turtle('_:foo <http://example.org/pred> "foo".');
    $g->add_turtle('_:foo <http://example.org/pred> "foo".');
    $this->assertEquals( 2, count($g->get_triples()));

    $index = $g->get_index();
    $this->assertEquals( 2, count($index)); // two different subjects
  }


  function test_from_turtle() {
    $g = new SimpleGraph();
    $g->from_turtle($this->_single_triple_turtle);
    $this->assertEquals( 1, count($g->get_triples()));

    $index = $g->get_index();
    $this->assertEquals("foo", $index['http://example.org/subj']['http://example.org/pred'][0]['value']);
  }

  function test_from_json() {
    $g = new SimpleGraph();
    $g->from_json($this->_single_triple_json);
    $this->assertEquals( 1, count($g->get_triples()));

    $index = $g->get_index();
    $this->assertEquals("foo", $index['http://example.org/subj']['http://example.org/pred'][0]['value']);
  }

  function test_add_json_appends_new_triples() {
    $g = new SimpleGraph();
    $g->add_resource_triple('http://example.org/subj', 'http://example.org/pred1', 'http://example.org/obj1');
    $g->add_json($this->_single_triple_json);
    $this->assertEquals( 2, count($g->get_triples()));

    $index = $g->get_index();
    $this->assertEquals("http://example.org/obj1", $index['http://example.org/subj']['http://example.org/pred1'][0]['value']);
    $this->assertEquals("foo", $index['http://example.org/subj']['http://example.org/pred'][0]['value']);
    $this->assertEquals("literal", $index['http://example.org/subj']['http://example.org/pred'][0]['type']);
  }


  function test_is_empty() {
    $g = new SimpleGraph();

    $this->assertTrue( $g->is_empty() );
    $g->add_resource_triple('http://example.org/subj', 'http://example.org/pred1', 'http://example.org/obj1');

    $this->assertFalse( $g->is_empty() );

  }

  function test_from_turtle_parses_datatypes() {

    $g = new SimpleGraph();
    $g->from_turtle('<http://example.org/subj> <http://example.org/pred> "1390"^^<http://www.w3.org/2001/XMLSchema#gYear> .');
    $this->assertEquals( 1, count($g->get_triples()));

    $index = $g->get_index();
    $this->assertEquals("1390", $index['http://example.org/subj']['http://example.org/pred'][0]['value']);
    $this->assertEquals("http://www.w3.org/2001/XMLSchema#gYear", $index['http://example.org/subj']['http://example.org/pred'][0]['datatype']);
  }

  function test_qname_to_uri() {
    $g = new SimpleGraph();
    $g->set_namespace_mapping('ex', 'http://example.org/');
    $this->assertEquals("http://example.org/foo", $g->qname_to_uri('ex:foo'));
  }

  function test_qname_to_uri_returns_null_if_no_match() {
    $g = new SimpleGraph();
    $g->set_namespace_mapping('ex', 'http://example.org/');
    $this->assertEquals(null, $g->qname_to_uri('bar:foo'));
  }

  function test_uri_to_qname() {
    $g = new SimpleGraph();
    $g->set_namespace_mapping('ex', 'http://example.org/');
    $this->assertEquals("ex:foo", $g->uri_to_qname('http://example.org/foo'));
  }

  function test_uri_to_qname_returns_null_if_no_match() {
    $g = new SimpleGraph();
    $g->set_namespace_mapping('ex', 'http://example.org/');
    $this->assertEquals(null, $g->uri_to_qname('http://example.blah/'));
  }

  function test_uri_to_qname_returns_null_if_uri_not_representable_as_qname() {
    $g = new SimpleGraph();
    $g->set_namespace_mapping('ex', 'http://example.org/');
    $this->assertEquals(null, $g->uri_to_qname('http://example.org/foo/'));
  }

  function test_get_first_literal_uses_preferred_language() {
    $g = new SimpleGraph();
    $g->add_literal_triple('http://example.org/subj', 'http://example.org/pred', 'en', 'en');
    $g->add_literal_triple('http://example.org/subj', 'http://example.org/pred', 'fr', 'fr');
    $g->add_literal_triple('http://example.org/subj', 'http://example.org/pred', 'de', 'de');

    $this->assertEquals( "en", $g->get_first_literal('http://example.org/subj', 'http://example.org/pred', null, 'en'));
    $this->assertEquals( "fr", $g->get_first_literal('http://example.org/subj', 'http://example.org/pred', null, 'fr'));
    $this->assertEquals( "de", $g->get_first_literal('http://example.org/subj', 'http://example.org/pred', null, 'de'));
  }

  function test_get_subjects_of_type() {
    $g = new SimpleGraph();
    $g->add_resource_triple('http://example.org/subj1', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://example.org/type_1');
    $g->add_resource_triple('http://example.org/subj2', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://example.org/type_2');
    $g->add_resource_triple('http://example.org/subj3', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://example.org/type_1');
    $g->add_literal_triple('http://example.org/subj4', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://example.org/type_1');

    $subjects = $g->get_subjects_of_type('http://example.org/type_1');
    $this->assertEquals(2, count($subjects), 'The returned subjects should be exactly 2');
    $this->assertContains('http://example.org/subj1', $subjects, 'subj1 matches and should be returned');
    $this->assertContains('http://example.org/subj3', $subjects, 'subj3 matches and should be returned');
    $this->assertNotContains('http://example.org/subj2', $subjects, 'subj2 does not match and should not be returned');
    $this->assertNotContains('http://example.org/subj4', $subjects, 'subj4 does not match and should not be returned');
  }

  function test_get_subjects_where_resource() {
    $g = new SimpleGraph();
    $g->add_resource_triple('http://example.org/subj1', 'http://example.org/pred', 'http://example.org/obj1');
    $g->add_literal_triple('http://example.org/subj1', 'http://example.org/pred', 'http://example.org/obj1');

    $g->add_resource_triple('http://example.org/subj2', 'http://example.org/pred', 'http://example.org/obj1');
    $g->add_literal_triple('http://example.org/subj2', 'http://example.org/pred', 'http://example.org/obj2');

    $g->add_resource_triple('http://example.org/subj3', 'http://example.org/pred', 'http://example.org/obj2');
    $g->add_resource_triple('_:bnode', 'http://example.org/pred', 'http://example.org/obj1');
    $g->add_literal_triple('http://example.org/subj3', 'http://example.org/pred', 'http://example.org/obj1');

    $subjects = $g->get_subjects_where_resource('http://example.org/pred', 'http://example.org/obj1');
    $this->assertEquals(3, count($subjects), 'The returned subjects should be exactly 3');
    $this->assertContains('http://example.org/subj1', $subjects, 'subj1 matches and should be returned');
    $this->assertContains('http://example.org/subj2', $subjects, 'subj2 matches and should be returned');
    $this->assertContains('_:bnode', $subjects, 'bnodes match and should be returned');
    $this->assertNotContains('http://example.org/subj3', $subjects, 'subj3 does not match and should not be returned');
  }

  function test_get_subjects_where_resource_no_match_on_predicate() {
    $g = new SimpleGraph();
    $g->add_resource_triple('http://example.org/subj1', 'http://example.org/pred', 'http://example.org/obj1');

    $subjects = $g->get_subjects_where_resource('http://example.org/pred_foo', 'http://example.org/obj1');
    $this->assertTrue(empty($subjects), 'The returned subjects should be empty');
  }

  function test_get_subjects_where_resource_no_match_on_object() {
    $g = new SimpleGraph();
    $g->add_resource_triple('http://example.org/subj1', 'http://example.org/pred', 'http://example.org/obj1');

    $subjects = $g->get_subjects_where_resource('http://example.org/pred', 'http://example.org/obj_foo');
    $this->assertTrue(empty($subjects), 'The returned subjects should be empty');
  }

  function test_get_subjects_where_literal() {
    $g = new SimpleGraph();
    $g->add_resource_triple('http://example.org/subj1', 'http://example.org/pred', 'http://example.org/obj1');
    $g->add_literal_triple('http://example.org/subj1', 'http://example.org/pred', 'http://example.org/obj1');

    $g->add_resource_triple('http://example.org/subj2', 'http://example.org/pred', 'http://example.org/obj1');
    $g->add_literal_triple('http://example.org/subj2', 'http://example.org/pred', 'http://example.org/obj2');

    $g->add_resource_triple('http://example.org/subj3', 'http://example.org/pred', 'http://example.org/obj2');
    $g->add_literal_triple('http://example.org/subj3', 'http://example.org/pred', 'http://example.org/obj1');

    $subjects = $g->get_subjects_where_literal('http://example.org/pred', 'http://example.org/obj1');
    $this->assertEquals(2, count($subjects), 'The returned subjects should be exactly 2');
    $this->assertContains('http://example.org/subj1', $subjects, 'subj1 matches and should be returned');
    $this->assertContains('http://example.org/subj3', $subjects, 'subj3 matches and should be returned');
    $this->assertNotContains('http://example.org/subj2', $subjects, 'subj2 does not match and should not be returned');
  }

  function test_get_subjects_where_literal_no_match_on_predicate() {
    $g = new SimpleGraph();
    $g->add_literal_triple('http://example.org/subj1', 'http://example.org/pred', 'http://example.org/obj1');

    $subjects = $g->get_subjects_where_literal('http://example.org/pred_foo', 'http://example.org/obj1');
    $this->assertTrue(empty($subjects), 'The returned subjects should be empty');
  }

  function test_get_subjects_where_literal_no_match_on_object() {
    $g = new SimpleGraph();
    $g->add_literal_triple('http://example.org/subj1', 'http://example.org/pred', 'http://example.org/obj1');

    $subjects = $g->get_subjects_where_literal('http://example.org/pred', 'http://example.org/obj_foo');
    $this->assertTrue(empty($subjects), 'The returned subjects should be empty');
  }

  function test_reify(){

    $triple = array(
      '#foo' => array('#knows' => array(array('type'=>'uri','value' =>'#bar'))),
      );
      $RDF = 'http://www.w3.org/1999/02/22-rdf-syntax-ns#';
    $expected = array(
      '_:Statement1' => array(
        $RDF.'type' => array(
          array(
              'type' => 'uri',
              'value' => $RDF.'Statement',
            )
          ),
        $RDF.'subject' => array(
            array(
                'type' => 'uri',
                'value' => '#foo',
              )
          ),
        $RDF.'predicate' => array(
            array(
                'type' => 'uri',
                'value' => '#knows',
              )
          ),
        $RDF.'object' => array(
            array(
                'type' => 'uri',
                'value' => '#bar',
              )
          ),

        )
      );
    $actual = SimpleGraph::reify($triple);

    $this->assertEquals($expected, $actual);
  }

  function test_diff_static_call(){

    $_1 = array(
      '#x' => array('#name' => array(array('value'=> 'Keith'),), '#nick'=> array(array('value'=> 'keithA')), '#foo' => array(array('value'=>'foo')) )
      );

    $_2 = array(
        '#x' => array('#name' => array(array('value'=> 'Keith'),), '#nick'=> array(array('value'=> 'keithAlexander')), '#foo' => array(array('value'=>'foo')) )
        );
    $expected = array(
          '#x' => array( '#nick'=> array(array('value'=> 'keithA'))),
          );
    $actual = SimpleGraph::diff($_1,$_2);

    $this->assertEquals( $expected, $actual);
  }

  function test_diff_object_call(){

    $_1 = array(
      '#x' => array('#name' => array(array('value'=> 'Keith'),), '#nick'=> array(array('value'=> 'keithA')), '#foo' => array(array('value'=>'foo')) )
      );

    $_2 = array(
        '#x' => array('#name' => array(array('value'=> 'Keith'),), '#nick'=> array(array('value'=> 'keithAlexander')), '#foo' => array(array('value'=>'foo')) )
        );
    $expected = array(
          '#x' => array( '#nick'=> array(array('value'=> 'keithA'))),
          );
    $object = new SimpleGraph($_1);
    $actual = $object->diff($_2);
    $this->assertEquals( $expected, $actual);
  }

  function test_diff_where_subsequent_array_is_empty(){

    $_1 = array(
      '#x' => array('#name' => array(array('value'=> 'Keith'),), '#nick'=> array(array('value'=> 'keithA')), '#foo' => array(array('value'=>'foo')) )
      );

    $_2 = array();

    $actual = SimpleGraph::diff($_1,$_2);

    $this->assertEquals( $_1, $actual);
  }

  function test_diff_where_original_array_is_empty(){

    $_1 = array();

    $_2 = array(
        '#x' => array('#name' => array(array('value'=> 'Keith'),), '#nick'=> array(array('value'=> 'keithAlexander')), '#foo' => array(array('value'=>'foo')) )
        );

    $actual = SimpleGraph::diff($_1,$_2);
    
    $this->assertEquals( $_1, $actual);
  }

  function test_diff_where_array_key_order_is_different()
  {
      $_1 = array(
          '#x' => array('#name' => array(array('value'=> 'Keith', 'type' => 'literal')))
          );

      $_2 = array(
          '#x' => array('#name' => array(array('type' => 'literal', 'value'=> 'Keith')))
          );
      
      $actual = SimpleGraph::diff($_1,$_2);
      
      $this->assertEquals(array(), $actual);
      
  }

  function test_diff_to_ensure_type_insensitive_comparison()
  {
      $_1 = array(
        '#x' => array('#lcn' => array(array('value'=> '1521278'),) )
      );
      $_2 = array(
        '#x' => array('#lcn' => array(array('value'=> '00001521278'),) )
      );
     
     $actual = SimpleGraph::diff($_1,$_2); 
     $this->assertEquals( $_1, $actual);
  }

  function test_merge_static(){

    $g1 = array(            //uri
      '#x' => array(            //prop
          'name' => array(        //obj
            array(
            'value' => 'Joe',
            'type' => 'literal',
              ),
            ),        //obj
        ),          //prop
      '_:y' => array(
          'name' => array(array(
            'value' => 'Joan',
            'type' => 'literal',
            ),),
        ),

      );

      $g2 = array(
        '#x' => array(
            'knows' => array( array(
              'value' => '_:y',
              'type' => 'bnode',
              ),
            ),
          ),

        '_:y' => array(
            'name' => array(
              array(
              'value' => 'Susan',
              'type' => 'literal',
              ),
              ),
          ),

        );

      $g3 = array (
        '#x' =>
        array (
          'name' =>
          array (
            0 =>
            array (
              'value' => 'Joe',
              'type' => 'literal',
            ),
          ),
          'knows' =>
          array (
            0 =>
            array (
              'value' => '_:y1',
              'type' => 'bnode',
            ),
          ),
        ),
        '_:y' =>
        array (
          'name' =>
          array (
            0 =>
            array (
              'value' => 'Joan',
              'type' => 'literal',
            ),
          ),
        ),
        '_:y1' =>
        array (
          'name' =>
          array (
            0 =>
            array (
              'value' => 'Susan',
              'type' => 'literal',
            ),
          ),
        ),
      );

    $g4 = array(
      '#x' => array('#knows' => array(
        'type' => 'uri',
        'value' => 'Me'
        ),
      ),
      );

    $r1 = (SimpleGraph::merge($g1,$g2));
    $this->assertEquals($r1, $g3);
  }


  function test_merge_object_call(){

    $g1 = array(            //uri
      '#x' => array(            //prop
          'name' => array(        //obj
            array(
            'value' => 'Joe',
            'type' => 'literal',
              ),
            ),        //obj
        ),          //prop
      '_:y' => array(
          'name' => array(array(
            'value' => 'Joan',
            'type' => 'literal',
            ),),
        ),

      );

      $g2 = array(
        '#x' => array(
            'knows' => array( array(
              'value' => '_:y',
              'type' => 'bnode',
              ),
            ),
          ),

        '_:y' => array(
            'name' => array(
              array(
              'value' => 'Susan',
              'type' => 'literal',
              ),
              ),
          ),

        );

      $g3 = array (
        '#x' =>
        array (
          'name' =>
          array (
            0 =>
            array (
              'value' => 'Joe',
              'type' => 'literal',
            ),
          ),
          'knows' =>
          array (
            0 =>
            array (
              'value' => '_:y1',
              'type' => 'bnode',
            ),
          ),
        ),
        '_:y' =>
        array (
          'name' =>
          array (
            0 =>
            array (
              'value' => 'Joan',
              'type' => 'literal',
            ),
          ),
        ),
        '_:y1' =>
        array (
          'name' =>
          array (
            0 =>
            array (
              'value' => 'Susan',
              'type' => 'literal',
            ),
          ),
        ),
      );

    $g4 = array(
      '#x' => array('#knows' => array(
        'type' => 'uri',
        'value' => 'Me'
        ),
      ),
      );
    $graph = new SimpleGraph($g1);
    $r1 = ($graph->merge($g2));
    $this->assertEquals($r1, $g3);
  }


  function test_replace_resource_object() {
    $g = new SimpleGraph();
    $g->add_resource_triple('http://example.org/subj', 'http://example.org/pred', 'http://example.org/obj');

    $g->replace_resource('http://example.org/obj', 'http://example.org/other');
    $this->assertTrue( $g->has_resource_triple('http://example.org/subj', 'http://example.org/pred', 'http://example.org/other'));
    $this->assertFalse( $g->has_resource_triple('http://example.org/subj', 'http://example.org/pred', 'http://example.org/obj'));
  }

  function test_replace_resource_property() {
    $g = new SimpleGraph();
    $g->add_resource_triple('http://example.org/subj', 'http://example.org/pred', 'http://example.org/obj');

    $g->replace_resource('http://example.org/pred', 'http://example.org/other');
    $this->assertTrue( $g->has_resource_triple('http://example.org/subj', 'http://example.org/other', 'http://example.org/obj'));
    $this->assertFalse( $g->has_resource_triple('http://example.org/subj', 'http://example.org/pred', 'http://example.org/obj'));
  }

  function test_replace_resource_property_with_literal_objects() {
    $g = new SimpleGraph();
    $g->add_resource_triple('http://example.org/subj', 'http://example.org/pred', 'http://example.org/obj');
    $g->add_literal_triple('http://example.org/subj', 'http://example.org/pred', 'foo', 'en');

    $g->replace_resource('http://example.org/pred', 'http://example.org/other');
    $this->assertTrue( $g->has_resource_triple('http://example.org/subj', 'http://example.org/other', 'http://example.org/obj'));
    $this->assertFalse( $g->has_resource_triple('http://example.org/subj', 'http://example.org/pred', 'http://example.org/obj'));
    $this->assertTrue( $g->has_literal_triple('http://example.org/subj', 'http://example.org/other', 'foo','en'));
    $this->assertFalse( $g->has_literal_triple('http://example.org/subj', 'http://example.org/pred', 'foo','en'));
  }

  function test_replace_resource_property_with_object_needing_replacing_too() {
    $g = new SimpleGraph();
    $g->add_resource_triple('http://example.org/subj', 'http://example.org/pred', 'http://example.org/pred');

    $g->replace_resource('http://example.org/pred', 'http://example.org/other');
    $this->assertTrue( $g->has_resource_triple('http://example.org/subj', 'http://example.org/other', 'http://example.org/other'));
    $this->assertFalse( $g->has_resource_triple('http://example.org/subj', 'http://example.org/pred', 'http://example.org/pred'));
  }

  function test_replace_resource_subject() {
    $g = new SimpleGraph();
    $g->add_resource_triple('http://example.org/subj', 'http://example.org/pred', 'http://example.org/obj');

    $g->replace_resource('http://example.org/subj', 'http://example.org/other');
    $this->assertTrue( $g->has_resource_triple('http://example.org/other', 'http://example.org/pred', 'http://example.org/obj'));
    $this->assertFalse( $g->has_resource_triple('http://example.org/subj', 'http://example.org/pred', 'http://example.org/obj'));
  }

  function test_replace_resource_subject_with_object_needing_replacing_too() {
    $g = new SimpleGraph();
    $g->add_resource_triple('http://example.org/subj', 'http://example.org/pred', 'http://example.org/subj');

    $g->replace_resource('http://example.org/subj', 'http://example.org/other');
    $this->assertTrue( $g->has_resource_triple('http://example.org/other', 'http://example.org/pred', 'http://example.org/other'));
    $this->assertFalse( $g->has_resource_triple('http://example.org/subj', 'http://example.org/pred', 'http://example.org/subj'));
  }

  function test_replace_resource_subject_with_property_needing_replacing_too() {
    $g = new SimpleGraph();
    $g->add_resource_triple('http://example.org/subj', 'http://example.org/subj', 'http://example.org/obj');

    $g->replace_resource('http://example.org/subj', 'http://example.org/other');
    $this->assertTrue( $g->has_resource_triple('http://example.org/other', 'http://example.org/other', 'http://example.org/obj'));
    $this->assertFalse( $g->has_resource_triple('http://example.org/subj', 'http://example.org/subj', 'http://example.org/obj'));
  }

  function test_replace_resource_all_components() {
    $g = new SimpleGraph();
    $g->add_resource_triple('http://example.org/subj', 'http://example.org/subj', 'http://example.org/subj');

    $g->replace_resource('http://example.org/subj', 'http://example.org/other');
    $this->assertTrue( $g->has_resource_triple('http://example.org/other', 'http://example.org/other', 'http://example.org/other'));
    $this->assertFalse( $g->has_resource_triple('http://example.org/subj', 'http://example.org/subj', 'http://example.org/subj'));
  }


  function test_get_subjects() {
    $g = new SimpleGraph();
    $g->add_resource_triple('http://example.org/subj1', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://example.org/type_1');
    $g->add_resource_triple('http://example.org/subj2', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://example.org/type_2');
    $g->add_resource_triple('http://example.org/subj3', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://example.org/type_1');
    $g->add_literal_triple('http://example.org/subj4', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', 'http://example.org/type_1');

    $subjects = $g->get_subjects();
    $this->assertEquals(4, count($subjects), 'The returned subjects should be exactly 4');
    $this->assertContains('http://example.org/subj1', $subjects, 'subj1 matches and should be returned');
    $this->assertContains('http://example.org/subj2', $subjects, 'subj2 matches and should be returned');
    $this->assertContains('http://example.org/subj3', $subjects, 'subj3 matches and should be returned');
    $this->assertContains('http://example.org/subj4', $subjects, 'subj4 matches and should be returned');
  }


  function test_read_data_fetches_single_url() {
    $fake_request_factory = new FakeRequestFactory();
    $fake_response = new HttpResponse();
    $fake_response->status_code = 200;
    $fake_response->body = $this->_single_triple;
    $fake_response->headers['content-type'] = 'application/rdf+xml';

    $fake_request = new FakeHttpRequest( $fake_response );
    $fake_request_factory->register('GET', 'http://example.org/thing', $fake_request );

    $g = new SimpleGraph();
    $g->set_request_factory($fake_request_factory);
    $g->read_data('http://example.org/thing');

    $this->assertTrue( $fake_request->was_executed() );
  }

  function test_read_data_sets_accept() {
    $fake_request_factory = new FakeRequestFactory();
    $fake_response = new HttpResponse();
    $fake_response->status_code = 200;
    $fake_response->body = $this->_single_triple;
    $fake_response->headers['content-type'] = 'application/rdf+xml';

    $fake_request = new FakeHttpRequest( $fake_response );
    $fake_request_factory->register('GET', 'http://example.org/thing', $fake_request );

    $g = new SimpleGraph();
    $g->set_request_factory($fake_request_factory);
    $g->read_data('http://example.org/thing');

    $this->assertTrue( in_array('Accept: application/json, text/turtle, text/n3, text/rdf+n3, application/x-turtle, application/rdf+xml;q=0.8,application/xml;q=0.6, */*', $fake_request->get_headers() ) );
  }

  function test_read_data_parses_application_rdfxml() {
    $fake_request_factory = new FakeRequestFactory();
    $fake_response = new HttpResponse();
    $fake_response->status_code = 200;
    $fake_response->body = $this->_single_triple;
    $fake_response->headers['content-type'] = 'application/rdf+xml';

    $fake_request = new FakeHttpRequest( $fake_response );
    $fake_request_factory->register('GET', 'http://example.org/thing', $fake_request );

    $g = new SimpleGraph();
    $g->set_request_factory($fake_request_factory);
    $g->read_data('http://example.org/thing');

    $this->assertTrue($g->has_literal_triple('http://example.org/subj', 'http://example.org/pred', 'foo'));
  }

  function test_read_data_parses_application_xml() {
    $fake_request_factory = new FakeRequestFactory();
    $fake_response = new HttpResponse();
    $fake_response->status_code = 200;
    $fake_response->body = $this->_single_triple;
    $fake_response->headers['content-type'] = 'application/xml';

    $fake_request = new FakeHttpRequest( $fake_response );
    $fake_request_factory->register('GET', 'http://example.org/thing', $fake_request );

    $g = new SimpleGraph();
    $g->set_request_factory($fake_request_factory);
    $g->read_data('http://example.org/thing');

    $this->assertTrue($g->has_literal_triple('http://example.org/subj', 'http://example.org/pred', 'foo'));
  }

  function test_read_data_parses_text_turtle() {
    $fake_request_factory = new FakeRequestFactory();
    $fake_response = new HttpResponse();
    $fake_response->status_code = 200;
    $fake_response->body = $this->_single_triple_turtle;
    $fake_response->headers['content-type'] = 'text/turtle';

    $fake_request = new FakeHttpRequest( $fake_response );
    $fake_request_factory->register('GET', 'http://example.org/thing', $fake_request );

    $g = new SimpleGraph();
    $g->set_request_factory($fake_request_factory);
    $g->read_data('http://example.org/thing');

    $this->assertTrue($g->has_literal_triple('http://example.org/subj', 'http://example.org/pred', 'foo'));
  }

  function test_read_data_parses_text_n3() {
    $fake_request_factory = new FakeRequestFactory();
    $fake_response = new HttpResponse();
    $fake_response->status_code = 200;
    $fake_response->body = $this->_single_triple_turtle;
    $fake_response->headers['content-type'] = 'text/n3';

    $fake_request = new FakeHttpRequest( $fake_response );
    $fake_request_factory->register('GET', 'http://example.org/thing', $fake_request );

    $g = new SimpleGraph();
    $g->set_request_factory($fake_request_factory);
    $g->read_data('http://example.org/thing');

    $this->assertTrue($g->has_literal_triple('http://example.org/subj', 'http://example.org/pred', 'foo'));
  }

  function test_read_data_parses_application_x_turtle() {
    $fake_request_factory = new FakeRequestFactory();
    $fake_response = new HttpResponse();
    $fake_response->status_code = 200;
    $fake_response->body = $this->_single_triple_turtle;
    $fake_response->headers['content-type'] = 'application/x-turtle';

    $fake_request = new FakeHttpRequest( $fake_response );
    $fake_request_factory->register('GET', 'http://example.org/thing', $fake_request );

    $g = new SimpleGraph();
    $g->set_request_factory($fake_request_factory);
    $g->read_data('http://example.org/thing');

    $this->assertTrue($g->has_literal_triple('http://example.org/subj', 'http://example.org/pred', 'foo'));
  }

  function test_read_data_parses_text_rdf_n3() {
    $fake_request_factory = new FakeRequestFactory();
    $fake_response = new HttpResponse();
    $fake_response->status_code = 200;
    $fake_response->body = $this->_single_triple_turtle;
    $fake_response->headers['content-type'] = 'text/rdf+n3';

    $fake_request = new FakeHttpRequest( $fake_response );
    $fake_request_factory->register('GET', 'http://example.org/thing', $fake_request );

    $g = new SimpleGraph();
    $g->set_request_factory($fake_request_factory);
    $g->read_data('http://example.org/thing');

    $this->assertTrue($g->has_literal_triple('http://example.org/subj', 'http://example.org/pred', 'foo'));
  }
  function test_read_data_parses_application_json() {
    $fake_request_factory = new FakeRequestFactory();
    $fake_response = new HttpResponse();
    $fake_response->status_code = 200;
    $fake_response->body = $this->_single_triple_json;
    $fake_response->headers['content-type'] = 'application/json';

    $fake_request = new FakeHttpRequest( $fake_response );
    $fake_request_factory->register('GET', 'http://example.org/thing', $fake_request );

    $g = new SimpleGraph();
    $g->set_request_factory($fake_request_factory);
    $g->read_data('http://example.org/thing');

    $this->assertTrue($g->has_literal_triple('http://example.org/subj', 'http://example.org/pred', 'foo'));
  }

  function test_read_data_does_not_add_body_triples_on_failed_request() {
    $fake_request_factory = new FakeRequestFactory();
    $fake_response = new HttpResponse();
    $fake_response->status_code = 404;
    $fake_response->body = $this->_single_triple;
    $fake_response->headers['content-type'] = 'application/rdf+xml';

    $fake_request = new FakeHttpRequest( $fake_response );
    $fake_request_factory->register('GET', 'http://example.org/thing', $fake_request );

    $g = new SimpleGraph();
    $g->set_request_factory($fake_request_factory);
    $g->read_data('http://example.org/thing');

    $this->assertFalse($g->has_literal_triple('http://example.org/subj', 'http://example.org/pred', 'foo'));
  }


  function test_read_data_fetches_multiple_urls() {
    $fake_request_factory = new FakeRequestFactory();
    $fake_response = new HttpResponse();
    $fake_response->status_code = 200;
    $fake_response->body = $this->_single_triple;
    $fake_response->headers['content-type'] = 'application/rdf+xml';

    $fake_request1 = new FakeHttpRequest( $fake_response );
    $fake_request_factory->register('GET', 'http://example.org/thing1', $fake_request1 );
    $fake_request2 = new FakeHttpRequest( $fake_response );
    $fake_request_factory->register('GET', 'http://example.org/thing2', $fake_request2 );
    $fake_request3 = new FakeHttpRequest( $fake_response );
    $fake_request_factory->register('GET', 'http://example.org/thing3', $fake_request3 );

    $g = new SimpleGraph();
    $g->set_request_factory($fake_request_factory);
    $g->read_data(array('http://example.org/thing1', 'http://example.org/thing2', 'http://example.org/thing3'));

    $this->assertTrue( $fake_request1->was_executed() );
    $this->assertTrue( $fake_request2->was_executed() );
    $this->assertTrue( $fake_request3->was_executed() );
  }


  function test_get_list_values(){
      $g = new SimpleGraph(file_get_contents(dirname(__FILE__).DIRECTORY_SEPARATOR.'documents'.DIRECTORY_SEPARATOR.'lists-seqs-collections.ttl'));
      $actual = $g->get_list_values(exampleNS.'#list');
      $expected = array(exampleNS.'#a', exampleNS.'#b', exampleNS.'#c');
      $this->assertEquals($expected, $actual, "list should be tranformed into the array");
      
  }

  public function testGetSequenceValues()
    {
        $graph = new SimpleGraph();
        $graph->add_resource_triple('http://some/subject/1', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#_4', 'http://value/4');
        $graph->add_resource_triple('http://some/subject/1', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#_2', 'http://value/2');
        $graph->add_resource_triple('http://some/subject/1', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#_3', 'http://value/3');
        $graph->add_resource_triple('http://some/subject/1', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#_5', 'http://value/5');
        $graph->add_resource_triple('http://some/subject/1', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#_1', 'http://value/1');


        $expectedArray = array('http://value/1', 'http://value/2', 'http://value/3', 'http://value/4', 'http://value/5');
        $this->assertEquals($expectedArray, $graph->get_sequence_values('http://some/subject/1'));
    }

    
    public function testGetParserErrors(){
    $graph = new SimpleGraph();
    $graph->add_rdf($this->_single_triple_turtle);
    $errors = $graph->get_parser_errors();
    $this->assertTrue(empty($errors), "Errors should be empty");
    $graph->add_rdf($this->_single_triple_invalid_rdf );
    $errors = $graph->get_parser_errors();    
    $this->assertFalse(empty($errors), "Errors should not be empty");
    $this->assertTrue(!empty($errors[0]), "Errors first item should not be empty");

  }


  public function test_skolemise_bnodes(){
      
    $input =  array(
        '_:a' => array(
            RDFS_LABEL => array(
              array(
                'value' => 'A Bnode',
                'type' => 'literal',
              ),
              array(
                'value' => '_:b',
                'type' => 'bnode',
              ),
            ),
          ),

          '_:b' => array(
            RDFS_LABEL => array(
              array(
                'type' => 'literal',
                'value' => 'bnode B',
              )
            )
          )
    
      );  

    $expected_output = array(
        'http://example.org/document/id-1' => array(
            RDFS_LABEL => array(
              array(
                'value' => 'A Bnode',
                'type' => 'literal',
              ),
              array(
                'value' => 'http://example.org/document/id-2',
                'type' => 'uri',
              ),
 
            ),
        ),
          'http://example.org/document/id-2' => array(
            RDFS_LABEL => array(
              array(
                'type' => 'literal',
                'value' => 'bnode B',
              )
            )
          )
      );  

    $graph = new SimpleGraph($input);
    $graph->skolemise_bnodes('http://example.org/document/');
    $output = $graph->get_index();
    $this->assertEquals($expected_output, $output, "bnodes in the graph should be replaced with URIs");

  
  }


  function test_graph_pattern_is_unchanged_by_replace_resource(){
  
  }

  function test_number_of_resources_remains_constant_after_skolemise_bnodes(){
    $graph = new SimpleGraph(file_get_contents(dirname(__FILE__).'/documents/ckan-ds.ttl'));
    $index = $graph->get_index();
    $before = count($graph->get_subjects());
    $graph->skolemise_bnodes('http://example.com/test/');
    $after = count($graph->get_subjects());
    $this->assertEquals($before, $after, "skolemise_bnodes shouldn't reduce the number of resources");
  }
  
  function test_get_bnodes(){
    
       $input =  array(
        '_:a' => array(
            RDFS_SEEALSO => array(
              array(
                'value' => '_:b',
                'type' => 'bnode',
              ),
            ),
        ),
    
      );


    $graph = new SimpleGraph($input);
    $actual = $graph->get_bnodes();
    $expected = array('_:a', '_:b');
    $this->assertEquals($expected, $actual, "get_bnodes() should return bnodes in subject and object positions");
  }
  
  function test_to_html_renders_bnodes_as_anchors() {
    
    $g = new SimpleGraph();
    $g->from_rdfxml($this->_single_triple);
    $g->add_resource_triple('http://example.org/subj', 'http://example.org/pred', '_:bn123');
    $g->add_resource_triple('_:bn123', 'http://example.org/pred', 'http://example.org/obj');

    $html = $g->to_html();

    $this->assertContains('<a href="http://example.org/subj">subj</a>', $html, "html should contain links to bnode anchors");
    $this->assertContains('<a href="#bn123">_:bn123</a>', $html, "html should contain links to bnode anchors");
    $this->assertContains('<a id="bn123" href="#bn123">_:bn123</a>', $html, "html should contain anchors for bnodes");
  }

}
?>
