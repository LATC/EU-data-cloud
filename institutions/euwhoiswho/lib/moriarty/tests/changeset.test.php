<?php
require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'constants.inc.php';
require_once MORIARTY_DIR. "changeset.class.php";

class ChangeSetTest extends PHPUnit_Framework_TestCase
{
    var $_parser;
    var $_single_triple =  '<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:ex="http://example.org/">
  <rdf:Description rdf:about="http://example.org/subj">
    <ex:pred rdf:resource="http://example.org/obj" />
  </rdf:Description>
</rdf:RDF>';

    var $_two_triples =  '<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:ex="http://example.org/">
  <rdf:Description rdf:about="http://example.org/subj">
    <ex:pred rdf:resource="http://example.org/obj" />
    <ex:pred rdf:resource="http://example.org/obj2" />
  </rdf:Description>
</rdf:RDF>';

    var $_different_subjects =  '<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:ex="http://example.org/">
  <rdf:Description rdf:about="http://example.org/subj">
    <ex:pred rdf:resource="http://example.org/obj" />
  </rdf:Description>
  <rdf:Description rdf:about="http://example.org/subj2">
    <ex:pred rdf:resource="http://example.org/obj" />
  </rdf:Description>
</rdf:RDF>';

    var $_single_triple_literal =  '<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:ex="http://example.org/">
  <rdf:Description rdf:about="http://example.org/subj">
    <ex:pred>obj</ex:pred>
  </rdf:Description>
</rdf:RDF>';

    var $_single_triple_literal_lang =  '<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:ex="http://example.org/">
  <rdf:Description rdf:about="http://example.org/subj">
    <ex:pred xml:lang="foo">obj</ex:pred>
  </rdf:Description>
</rdf:RDF>';

    var $_single_blank_subject =  '<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:ex="http://example.org/">
  <rdf:Description rdf:nodeID="a">
    <ex:pred rdf:resource="http://example.org/obj" />
  </rdf:Description>
</rdf:RDF>';

    var $_with_entity_literal =  '<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:ex="http://example.org/">
  <rdf:Description rdf:about="http://example.org/subj">
    <ex:pred xml:lang="foo">o&amp;amp;bj</ex:pred>
  </rdf:Description>
</rdf:RDF>';

    var $_with_entity_uri_subject =  '<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:ex="http://example.org/">
  <rdf:Description rdf:about="http://example.org/subj?1&amp;2">
    <ex:pred xml:lang="foo">o&amp;amp;bj</ex:pred>
  </rdf:Description>
</rdf:RDF>';

    var $_with_etag =  '<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:ex="http://schemas.talis.com/2005/dir/schema#">
  <rdf:Description rdf:about="http://example.org/subj">
    <ex:etag>abcdef</ex:etag>
  </rdf:Description>
</rdf:RDF>';


	var $Changeset;

	var $cs = 'http://purl.org/vocab/changeset/schema#';

	var $g1 = array(
			'http://example.org' => array(
				'http://purl.org/dc/elements/1.1/title' => array(
						array('value'=> 'Hello World', 'type' => 'literal'),
					),
				'http://purl.org/dc/elements/1.1/subject' => array(
						array('value'=> 'http://dbpedia.org/resource/Hello_World', 'type' => 'uri'),
					),
				'http://purl.org/dc/elements/1.1/subject' => array(
							array('value'=> '_:bnode1', 'type' => 'bnode'),
						),
			
			
				),
			'_:bnode1' => array(
						'http://purl.org/dc/elements/1.1/title' => array(
								array('value'=> 'Foo Bar', 'type' => 'literal'),
							),
				),
		);



  function setUp() {
      $parser_args=array(
        "bnode_prefix"=>"genid",
        "base"=>""
      );
      $this->_parser = ARC2::getRDFXMLParser($parser_args);
		$args =  array(
			'before' => $this->g1,
			'after' => array(),
			);
		$this->Changeset = new ChangeSet($args);
  }

	function tearDown(){
		unset($this->ChangeSet);
	}


  function parse($base, $rdfxml) {
    $parser = ARC2::getRDFXMLParser();
    $parser->parse($base, $rdfxml );
    return $parser->getTriples();
  }

  function _build_single_triple_change_set_model() {
    $triples = $this->parse("", $this->_single_triple );

    $cs = new ChangeSet( array( 'subjectOfChange'=>"http://example.org/subj", 'after'=>$triples ) );
    return $cs->get_index();
  }

  function _build_two_triple_change_set_model() {
    $triples = $this->parse("", $this->_two_triples );

    $cs = new ChangeSet( array( 'subjectOfChange'=>"http://example.org/subj", 'after'=>$triples ) );

    return $cs->get_index();
  }

  function _build_different_subjects_change_set_model() {
    $triples = $this->parse("", $this->_different_subjects );

    $cs = new ChangeSet( array( 'subjectOfChange'=>"http://example.org/subj", 'after'=>$triples ) );

    return $cs->get_index();
  }

  function _find_changeset_resource($index) {
    $changesetResource = null;
    foreach ($index as $subject => $properties) {
      if ( array_key_exists('http://www.w3.org/1999/02/22-rdf-syntax-ns#type', $properties)) {
        foreach ($properties['http://www.w3.org/1999/02/22-rdf-syntax-ns#type'] as $property) {
          if ( $property['type'] == 'uri' && $property['value'] =='http://purl.org/vocab/changeset/schema#ChangeSet') {
            return $subject;
          }
        }
      }

    }

  }


  function test_changeset_has_one_resource_of_type_changeset() {
    $index = $this->_build_single_triple_change_set_model();

    $numberOfChangeSets = 0;

    foreach ($index as $subject => $properties) {
      if ( array_key_exists('http://www.w3.org/1999/02/22-rdf-syntax-ns#type', $properties)) {

        foreach ($properties['http://www.w3.org/1999/02/22-rdf-syntax-ns#type'] as $property) {
          if ( $property['type'] == 'uri' && $property['value'] =='http://purl.org/vocab/changeset/schema#ChangeSet') {
            $numberOfChangeSets++;
          }
        }
      }

    }

	if(!$numberOfChangeSets) {
		var_dump($index);
	}
	
    $this->assertEquals(1,  $numberOfChangeSets);
  }

  function test_resource_of_type_changeset_is_a_blank_node() {
    $index = $this->_build_single_triple_change_set_model();
    $changesetResource = $this->_find_changeset_resource($index);

    $this->assertTrue(strpos($changesetResource, '_:' ) === 0);
  }

  function test_changeset_resource_has_subject_of_change() {
    $index = $this->_build_single_triple_change_set_model();
    $changesetResource = $this->_find_changeset_resource($index);

    $objects = $index[$changesetResource]["http://purl.org/vocab/changeset/schema#subjectOfChange"];

    $this->assertEquals('uri',  $objects[0]["type"]);
    $this->assertEquals("http://example.org/subj",  $objects[0]['value']);
  }

  function test_changeset_resource_has_addition() {
    $index = $this->_build_single_triple_change_set_model();
    $changesetResource = $this->_find_changeset_resource($index);

    $objects = $index[$changesetResource]["http://purl.org/vocab/changeset/schema#addition"];
    $this->assertEquals("bnode",  $objects[0]["type"]);
  }

  function test_changeset_resource_has_addition_of_type_statement() {
    $index = $this->_build_single_triple_change_set_model();
    $changesetResource = $this->_find_changeset_resource($index);

    $objects = $index[$changesetResource]["http://purl.org/vocab/changeset/schema#addition"];

    $types = $index[ $objects[0]['value'] ]["http://www.w3.org/1999/02/22-rdf-syntax-ns#type"];

    $this->assertEquals('uri',  $types[0]["type"]);
    $this->assertEquals("http://www.w3.org/1999/02/22-rdf-syntax-ns#Statement",  $types[0]['value']);
  }

  function test_changeset_resource_has_addition_with_subject() {
    $index = $this->_build_single_triple_change_set_model();
    $changesetResource = $this->_find_changeset_resource($index);

    $objects = $index[$changesetResource]["http://purl.org/vocab/changeset/schema#addition"];

    $types = $index[ $objects[0]['value'] ]["http://www.w3.org/1999/02/22-rdf-syntax-ns#subject"];

    $this->assertEquals('uri',  $types[0]["type"]);
    $this->assertEquals("http://example.org/subj",  $types[0]['value']);
  }

  function test_changeset_resource_has_addition_with_predicate() {
    $index = $this->_build_single_triple_change_set_model();
    $changesetResource = $this->_find_changeset_resource($index);

    $objects = $index[$changesetResource]["http://purl.org/vocab/changeset/schema#addition"];

    $types = $index[ $objects[0]['value'] ]["http://www.w3.org/1999/02/22-rdf-syntax-ns#predicate"];

    $this->assertEquals('uri',  $types[0]["type"]);
    $this->assertEquals("http://example.org/pred",  $types[0]['value']);
  }

  function test_changeset_resource_has_addition_with_object() {
    $index = $this->_build_single_triple_change_set_model();
    $changesetResource = $this->_find_changeset_resource($index);

    $objects = $index[$changesetResource]["http://purl.org/vocab/changeset/schema#addition"];

    $types = $index[ $objects[0]['value'] ]["http://www.w3.org/1999/02/22-rdf-syntax-ns#object"];

    $this->assertEquals('uri',  $types[0]["type"]);
    $this->assertEquals("http://example.org/obj",  $types[0]['value']);
  }

  function test_changeset_has_created_date_if_supplied() {
    $this->_parser->parse("", $this->_single_triple );
    $triples = $this->_parser->getTriples();
    $cs = new ChangeSet( array( 'subjectOfChange'=>"http://example.org/subj", 'after'=>$triples, 'createdDate'=>'2006-01-01T00:00:00Z' ) );

    $index = $cs->get_index();
    $changesetResource = $this->_find_changeset_resource($index);

    $objects = $index[$changesetResource]["http://purl.org/vocab/changeset/schema#createdDate"];

    $this->assertEquals("literal",  $objects[0]["type"]);
    $this->assertEquals("2006-01-01T00:00:00Z",  $objects[0]['value']);
  }

  function test_changeset_has_creator_name_if_supplied() {
    $this->_parser->parse("", $this->_single_triple );
    $triples = $this->_parser->getTriples();
    $cs = new ChangeSet( array( 'subjectOfChange'=>"http://example.org/subj", 'after'=>$triples, 'creatorName'=>'scooby doo' ) );

    $index = $cs->get_index();
    $changesetResource = $this->_find_changeset_resource($index);

    $objects = $index[$changesetResource]["http://purl.org/vocab/changeset/schema#creatorName"];

    $this->assertEquals("literal",  $objects[0]["type"]);
    $this->assertEquals("scooby doo",  $objects[0]['value']);
  }

  function test_changeset_has_change_reason_if_supplied() {
    $this->_parser->parse("", $this->_single_triple );
    $triples = $this->_parser->getTriples();
    $cs = new ChangeSet( array( 'subjectOfChange'=>"http://example.org/subj", 'after'=>$triples, 'changeReason'=>'cos i wanna' ) );

    $index = $cs->get_index();
    $changesetResource = $this->_find_changeset_resource($index);

    $objects = $index[$changesetResource]["http://purl.org/vocab/changeset/schema#changeReason"];

    $this->assertEquals("literal",  $objects[0]["type"]);
    $this->assertEquals("cos i wanna",  $objects[0]['value']);
  }

  function test_changeset_resource_has_multiple_additions() {
    $index = $this->_build_two_triple_change_set_model();
    $changesetResource = $this->_find_changeset_resource($index);

    $additions = $index[$changesetResource]["http://purl.org/vocab/changeset/schema#addition"];

    $this->assertEquals(2,  count($additions));
  }

  function test_additions_are_different_bnodes() {
    $index = $this->_build_two_triple_change_set_model();
    $changesetResource = $this->_find_changeset_resource($index);

    $additions = $index[$changesetResource]["http://purl.org/vocab/changeset/schema#addition"];

    $this->assertTrue($additions[0]['value'] != $additions[1]['value']);
  }

  function test_all_additions_have_same_subject() {
    $index = $this->_build_two_triple_change_set_model();
    $changesetResource = $this->_find_changeset_resource($index);

    $additions = $index[$changesetResource]["http://purl.org/vocab/changeset/schema#addition"];

    $subject0 = $index[ $additions[0]['value'] ]["http://www.w3.org/1999/02/22-rdf-syntax-ns#subject"];
    $subject1 = $index[ $additions[1]['value'] ]["http://www.w3.org/1999/02/22-rdf-syntax-ns#subject"];

    $this->assertEquals($subject0, $subject1);
  }


  function test_all_additions_have_different_objects() {
    $index = $this->_build_two_triple_change_set_model();
    $changesetResource = $this->_find_changeset_resource($index);

    $additions = $index[$changesetResource]["http://purl.org/vocab/changeset/schema#addition"];

    $object0 = $index[ $additions[0]['value'] ]["http://www.w3.org/1999/02/22-rdf-syntax-ns#object"];
    $object1 = $index[ $additions[1]['value'] ]["http://www.w3.org/1999/02/22-rdf-syntax-ns#object"];

    $this->assertTrue($object0 != $object1);
    $this->assertTrue( $object0[0]['value'] == "http://example.org/obj" || $object1[0]['value'] == "http://example.org/obj");
    $this->assertTrue( $object0[0]['value'] == "http://example.org/obj2" || $object1[0]['value'] == "http://example.org/obj2");

  }

  function test_only_triples_with_matching_subject_of_change_are_used() {
    $index = $this->_build_different_subjects_change_set_model();
    $changesetResource = $this->_find_changeset_resource($index);

    $additions = $index[$changesetResource]["http://purl.org/vocab/changeset/schema#addition"];
    $this->assertEquals(1,  count($additions));
  }


  function test_changeset_reads_after_rdf_xml() {
    $cs = new ChangeSet( array( 'subjectOfChange'=>"http://example.org/subj", 'after_rdfxml'=>$this->_single_triple ) );

    $index = $cs->get_index();
    $changesetResource = $this->_find_changeset_resource($index);


    $additions = $index[$changesetResource]["http://purl.org/vocab/changeset/schema#addition"];

    $this->assertEquals(1,  count($additions));
    $this->assertEquals("bnode",  $additions[0]["type"]);

    $subjects = $index[ $additions[0]['value'] ]["http://www.w3.org/1999/02/22-rdf-syntax-ns#subject"];
    $this->assertEquals('uri',  $subjects [0]["type"]);
    $this->assertEquals("http://example.org/subj",  $subjects [0]['value']);

    $predicates = $index[ $additions[0]['value'] ]["http://www.w3.org/1999/02/22-rdf-syntax-ns#predicate"];
    $this->assertEquals('uri',  $predicates [0]["type"]);
    $this->assertEquals("http://example.org/pred",  $predicates [0]['value']);

    $objects = $index[ $additions[0]['value'] ]["http://www.w3.org/1999/02/22-rdf-syntax-ns#object"];
    $this->assertEquals('uri',  $objects [0]["type"]);
    $this->assertEquals("http://example.org/obj",  $objects [0]['value']);
  }

  function test_to_rdfxml() {
    $cs = new ChangeSet( array( 'subjectOfChange'=>"http://example.org/subj", 'after_rdfxml'=>$this->_two_triples ) );

    $this->_parser->parse("", $cs->to_rdfxml()  );
    $triples = $this->_parser->getTriples();

    $this->assertEquals( count( $cs->get_triples() ), count( $triples ) );
  }

  function test_changeset_resource_has_addition_with_literal_object() {
    $cs = new ChangeSet( array( 'subjectOfChange'=>"http://example.org/subj", 'after_rdfxml'=>$this->_single_triple_literal ) );

    $index = $cs->get_index();
    $changesetResource = $this->_find_changeset_resource($index);


    $objects = $index[$changesetResource]["http://purl.org/vocab/changeset/schema#addition"];

    $types = $index[ $objects[0]['value'] ]["http://www.w3.org/1999/02/22-rdf-syntax-ns#object"];

    $this->assertEquals("literal",  $types[0]["type"]);
    $this->assertEquals("obj",  $types[0]['value']);
  }
  function test_changeset_resource_has_addition_with_literal_object_and_lang() {
    $cs = new ChangeSet( array( 'subjectOfChange'=>"http://example.org/subj", 'after_rdfxml'=>$this->_single_triple_literal_lang ) );

    $index = $cs->get_index();
    $changesetResource = $this->_find_changeset_resource($index);


    $objects = $index[$changesetResource]["http://purl.org/vocab/changeset/schema#addition"];

    $types = $index[ $objects[0]['value'] ]["http://www.w3.org/1999/02/22-rdf-syntax-ns#object"];

    $this->assertEquals("literal",  $types[0]["type"]);
    $this->assertEquals("obj",  $types[0]['value']);
    $this->assertEquals("foo",  $types[0]["lang"]);
  }

  function test_to_rdfxml_with_lang() {
    $cs = new ChangeSet( array( 'subjectOfChange'=>"http://example.org/subj", 'after_rdfxml'=>$this->_single_triple_literal_lang ) );

    $this->_parser->parse("", $cs->to_rdfxml() );
    $triples = $this->_parser->getTriples();
    $objectLanguage = null;
    for($i=0,$i_max=count($triples);$i<$i_max;$i++) {
      if ( $triples[$i]['p'] == 'http://www.w3.org/1999/02/22-rdf-syntax-ns#object' ) {
        $objectLanguage = $triples[$i]['o_lang'];
      }
    }
    $this->assertEquals( 'foo' , $objectLanguage);
  }

  function test_to_rdfxml_with_blank_subject() {
    $cs = new ChangeSet( array( 'subjectOfChange'=>"_:a", 'after_rdfxml'=>$this->_single_blank_subject ) );
    $index = $cs->get_index();
    $changesetResource = $this->_find_changeset_resource($index);

    $objects = $index[$changesetResource]["http://purl.org/vocab/changeset/schema#subjectOfChange"];


    $this->assertEquals("bnode",  $objects[0]["type"]);
    $this->assertEquals("_:a",  $objects[0]['value']);

    $this->_parser->parse("", $cs->to_rdfxml() );
    $triples = $this->_parser->getTriples();

    $subjectId = null;
    for($i=0,$i_max=count($triples);$i<$i_max;$i++) {
      if ( $triples[$i]['p'] == 'http://www.w3.org/1999/02/22-rdf-syntax-ns#subject' ) {
        $subjectId = $triples[$i]['o'];
      }
    }

    $this->assertEquals( '_:a' ,$subjectId );
  }


  function test_to_rdfxml_with_blank_subject_and_no_changes() {
    $cs = new ChangeSet( array( 'subjectOfChange'=>"_:a" ) );
    $index = $cs->get_index();
    $changesetResource = $this->_find_changeset_resource($index);


    $objects = $index[$changesetResource]["http://purl.org/vocab/changeset/schema#subjectOfChange"];

    $this->assertEquals("bnode",  $objects[0]["type"]);
    $this->assertEquals("_:a",  $objects[0]['value']);
  }

  function test_to_rdfxml_with_entity_literal() {
    $cs = new ChangeSet( array( 'subjectOfChange'=>"http://example.org/subj", 'after_rdfxml'=>$this->_with_entity_literal ) );

    $index = $cs->get_index();
    $changesetResource = $this->_find_changeset_resource($index);


    $objects = $index[$changesetResource]["http://purl.org/vocab/changeset/schema#addition"];
    $types = $index[ $objects[0]['value'] ]["http://www.w3.org/1999/02/22-rdf-syntax-ns#object"];

    $this->assertEquals("literal",  $types[0]["type"]);
    $this->assertEquals("o&amp;bj",  $types[0]['value']);
  }


  function test_to_rdfxml_with_entity_uri_subject() {
    $cs = new ChangeSet( array( 'subjectOfChange'=>"http://example.org/subj?1&2", 'after_rdfxml'=>$this->_with_entity_uri_subject ) );
    $index = $cs->get_index();
    $changesetResource = $this->_find_changeset_resource($index);


    $objects = $index[$changesetResource]["http://purl.org/vocab/changeset/schema#addition"];
    $types = $index[ $objects[0]['value'] ]["http://www.w3.org/1999/02/22-rdf-syntax-ns#subject"];

    $this->assertEquals('uri',  $types[0]["type"]);
    $this->assertEquals("http://example.org/subj?1&2",  $types[0]['value']);
  }

  function test_changeset_caclulates_single_addition() {
    $before_triples = $this->parse("", $this->_single_triple );

    $after_triples = $this->parse("", $this->_two_triples );

    $cs = new ChangeSet( array( 'subjectOfChange'=>"http://example.org/subj", 'after'=>$after_triples, 'before'=>$before_triples ) );
    $index = $cs->get_index();
    $changesetResource = $this->_find_changeset_resource($index);


    $objects = $index[$changesetResource]["http://purl.org/vocab/changeset/schema#addition"];

    // $this->assertEquals("foo",  $cs->to_rdfxml() );
    $this->assertEquals(1,  count( $objects ));
  }

  function test_changeset_caclulates_single_removal() {
    $before_triples = $this->parse("", $this->_two_triples );
    $after_triples = $this->parse("", $this->_single_triple );

    $cs = new ChangeSet( array( 'subjectOfChange'=>"http://example.org/subj", 'after'=>$after_triples, 'before'=>$before_triples ) );

    $index = $cs->get_index();
    $changesetResource = $this->_find_changeset_resource($index);

    $objects = $index[$changesetResource]["http://purl.org/vocab/changeset/schema#removal"];

    // $this->assertEquals("foo",  $cs->to_rdfxml() );
    $this->assertEquals(1,  count( $objects ));
  }

  function test_changeset_calculates_all_removals_when_no_after() {
    $this->_parser->parse("", $this->_two_triples );
    $before_triples = $this->_parser->getTriples();

    $cs = new ChangeSet( array( 'subjectOfChange'=>"http://example.org/subj", 'before'=>$before_triples ) );

    $index = $cs->get_index();
    $changesetResource = $this->_find_changeset_resource($index);


    $objects = $index[$changesetResource]["http://purl.org/vocab/changeset/schema#removal"];

    // $this->assertEquals("foo",  $cs->to_rdfxml() );
    $this->assertEquals(2,  count( $objects ));
  }

  function test_changeset_reads_before_rdfxml() {

    $cs = new ChangeSet( array( 'subjectOfChange'=>"http://example.org/subj", 'before_rdfxml'=>$this->_two_triples ) );

    $index = $cs->get_index();
    $changesetResource = $this->_find_changeset_resource($index);


    $objects = $index[$changesetResource]["http://purl.org/vocab/changeset/schema#removal"];

    // $this->assertEquals("foo",  $cs->to_rdfxml() );
    $this->assertEquals(2,  count( $objects ));
  }
  
  function test_changeset_ignores_etag_removals() {
    $this->_parser->parse("", $this->_with_etag );
    $before_triples = $this->_parser->getTriples();

    $cs = new ChangeSet( array( 'subjectOfChange'=>"http://example.org/subj", 'before'=>$before_triples ) );
    $this->assertFalse($cs->has_changes());
  }  
  
  function test_changeset_ignores_etag_additions() {
    $this->_parser->parse("", $this->_with_etag );
    $after_triples = $this->_parser->getTriples();

    $cs = new ChangeSet( array( 'subjectOfChange'=>"http://example.org/subj", 'after'=>$after_triples ) );
    $this->assertFalse($cs->has_changes());
  }    

	function test_delete_resources(){
		$expected = 0;
		foreach($this->g1 as $uri => $props){
			foreach($props as $p => $os){
				$expected += count($os);
			}
		}
		$actual = 0;
		foreach($this->Changeset->_index as $uri => $props){
			if(isset($props[$this->cs.'removal'])) $actual += count($props[$this->cs.'removal']);
		}
		$this->assertEquals($expected, $actual);
	}	

	function test_added_metadata(){
		$args =  array(
			'after' => $this->g1,
			'before' => array(),
			'properties' => array(
				'http://purl.org/dc/elements/1.1/source' => array(array('value' => 'http://example.org/home.rdf', 'type' => 'uri')),
				),
			);
		$this->ChangeSet = new ChangeSet($args);
		$test = false;
		foreach($this->ChangeSet->_index as $uri => $props){
			if($types = ($props['http://www.w3.org/1999/02/22-rdf-syntax-ns#type']) AND $types[0]['value'] == 'http://purl.org/vocab/changeset/schema#ChangeSet' ){
				foreach($args['properties'] as $mp => $mobjs){
					$test = (isset($props[$mp]) AND $props[$mp]==$mobjs);
				}
			}
		}
		return $this->assertTrue($test);
	}


	function test_add_resources(){
		
		$args =  array(
			'after' => $this->g1,
			'before' => array(),
			);
		$this->Changeset = new ChangeSet($args);
		
		
		$expected = 0;
		foreach($this->g1 as $uri => $props){
			foreach($props as $p => $os){
				$expected += count($os);
			}
		}
		$actual = 0;
		foreach($this->Changeset->_index as $uri => $props){
			if(isset($props[$this->cs.'addition'])) $actual += count($props[$this->cs.'addition']);
		}

		$this->assertEquals($expected, $actual);
	}	
	
	function test_multiple_subjects_some_without_changes() {
		$before = '
		<rdf:RDF xmlns:admin="http://talis.com/schema/2009/admin#" xmlns:multitenant="http://purl.org/vocab/multi-tenant-configuration/schema#" xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:dir="http://schemas.talis.com/2005/dir/schema#" xmlns:rdfs="http://www.w3.org/2000/01/rdf-schema#" > 
		  <rdf:Description rdf:about="http://admin.talis.com/tenancies/152424261671726325413132526/themes/1">
		    <admin:file rdf:resource="http://admin.talis.com/tenancies/152424261671726325413132526/files/build.sh"/>
		    <dir:etag>"be8a419e-7434-47d8-af1d-3eeeec846c9d"</dir:etag>
		  </rdf:Description>
		  <rdf:Description rdf:about="http://admin.talis.com/tenancies/152424261671726325413132526">
		    <admin:theme rdf:resource="http://admin.talis.com/tenancies/152424261671726325413132526/themes/1"/>
		    <multitenant:customer rdf:resource="http://admin.talis.com/customers/prism-one"/>
		    <multitenant:subscription rdf:resource="http://admin.talis.com/tenancies/152424261671726325413132526#subscription"/>
		    <rdfs:label>I am Prism Tenancy One, for Prism Customer One</rdfs:label>
		    <rdf:type rdf:resource="http://purl.org/vocab/multi-tenant-configuration/schema#Tenancy"/>
		    <multitenant:enabled rdf:datatype="http://www.w3.org/2001/XMLSchema#boolean">true</multitenant:enabled>
		    <multitenant:tenancyType rdf:resource="http://admin.talis.com/types/prism_tenancy"/>
		    <dir:etag>"38df183f-ddd7-42a1-bda8-51c8dece1110"</dir:etag>
		    <admin:activeTheme rdf:resource="http://admin.talis.com/tenancies/152424261671726325413132526/themes/1"/>
		    <multitenant:createdAt rdf:datatype="http://www.w3.org/2001/XMLSchema#dateTime">2008-10-26T21:32:52</multitenant:createdAt>
		  </rdf:Description>
		  <rdf:Description rdf:about="http://admin.talis.com/tenancies/152424261671726325413132526/files/build.sh">
		    <admin:filesize>163</admin:filesize>
		    <admin:filename>build.sh</admin:filename>
		    <dir:etag>"9f3fc3b2-3391-4cb6-bac4-20468b1b6f06"</dir:etag>
		  </rdf:Description>
		</rdf:RDF>';
		
		$after = '
		<rdf:RDF xmlns:admin="http://talis.com/schema/2009/admin#" xmlns:multitenant="http://purl.org/vocab/multi-tenant-configuration/schema#" xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:dir="http://schemas.talis.com/2005/dir/schema#" xmlns:rdfs="http://www.w3.org/2000/01/rdf-schema#" > 
		  <rdf:Description rdf:about="http://admin.talis.com/tenancies/152424261671726325413132526/themes/1">
		    <admin:file rdf:resource="http://admin.talis.com/tenancies/152424261671726325413132526/files/build.sh"/>
		    <dir:etag>"be8a419e-7434-47d8-af1d-3eeeec846c9d"</dir:etag>
		  </rdf:Description>
		  <rdf:Description rdf:about="http://admin.talis.com/tenancies/152424261671726325413132526">
		    <admin:theme rdf:resource="http://admin.talis.com/tenancies/152424261671726325413132526/themes/1"/>
		    <multitenant:customer rdf:resource="http://admin.talis.com/customers/prism-one"/>
		    <multitenant:subscription rdf:resource="http://admin.talis.com/tenancies/152424261671726325413132526#subscription"/>
		    <rdfs:label>I am Prism Tenancy One, for Prism Customer One</rdfs:label>
		    <rdf:type rdf:resource="http://purl.org/vocab/multi-tenant-configuration/schema#Tenancy"/>
		    <multitenant:enabled rdf:datatype="http://www.w3.org/2001/XMLSchema#boolean">true</multitenant:enabled>
		    <multitenant:tenancyType rdf:resource="http://admin.talis.com/types/prism_tenancy"/>
		    <dir:etag>"38df183f-ddd7-42a1-bda8-51c8dece1110"</dir:etag>
		    <admin:activeTheme rdf:resource="http://admin.talis.com/tenancies/152424261671726325413132526/themes/1"/>
		    <multitenant:createdAt rdf:datatype="http://www.w3.org/2001/XMLSchema#dateTime">2008-10-26T21:32:52</multitenant:createdAt>
		  </rdf:Description>
		  <rdf:Description rdf:about="http://admin.talis.com/tenancies/152424261671726325413132526/files/build.sh">
		    <admin:filesize>165</admin:filesize>
		    <admin:filename>build.sh</admin:filename>
		    <dir:etag>"9f3fc3b2-3391-4cb6-bac4-20468b1b6f06"</dir:etag>
		  </rdf:Description>
		</rdf:RDF>';
		
		$changeset = new ChangeSet(array('before' => $before,
							'after' => $after,
							'changeReason' => 'Upload of file from admin.talis.com',
							'creatorName' => 'fooUser'));
		
		$changesets = $changeset->get_subjects_of_type('http://purl.org/vocab/changeset/schema#ChangeSet');
		$this->assertEquals(1, count($changesets), 'There should only be one changeset produced');
		$subjectOfChange = $changeset->get_first_resource($changesets[0], 'http://purl.org/vocab/changeset/schema#subjectOfChange');
		$this->assertEquals('http://admin.talis.com/tenancies/152424261671726325413132526/files/build.sh', $subjectOfChange, 'The subject of change of the one changeset should the subject that has changes');
		
	}
	
		function test_multiple_subjects_multiple_changes_some_without_changes() {
		$before = '
		<rdf:RDF xmlns="http://example.com/foo#" xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#">
		  <rdf:Description rdf:about="http://example.com/subject/before_only/one">
		    <one>before</one>
		    <two>before</two>
		    <three>unchanged</three>
		  </rdf:Description>
		  <rdf:Description rdf:about="http://example.com/subject/one">
		    <one>before</one>
		    <two>before</two>
		    <three>unchanged</three>
		  </rdf:Description>
		  <rdf:Description rdf:about="http://example.com/subject/two">
		    <one>before</one>
		    <two>before</two>
		    <three>unchanged</three>
		  </rdf:Description>
		  <rdf:Description rdf:about="http://example.com/subject/three">
		    <one>before</one>
		    <two>before</two>
		    <three>unchanged</three>
		  </rdf:Description>
		  <rdf:Description rdf:about="http://example.com/subject/four">
		    <one>unchanged</one>
		    <two>unchanged</two>
		    <three>unchanged</three>
		  </rdf:Description>
		  <rdf:Description rdf:about="http://example.com/subject/before_only/two">
		    <one>before</one>
		    <two>before</two>
		    <three>unchanged</three>
		  </rdf:Description>
		  </rdf:RDF>';

		$after = '
		<rdf:RDF xmlns="http://example.com/foo#" xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"> 
		  <rdf:Description rdf:about="http://example.com/subject/five">
		  	<four>new</four>
		  </rdf:Description>
		  <rdf:Description rdf:about="http://example.com/subject/four">
		    <one>unchanged</one>
		    <two>unchanged</two>
		    <three>unchanged</three>
		  </rdf:Description>
		  <rdf:Description rdf:about="http://example.com/subject/three">
		    <one>after</one>
		    <two>after</two>
		    <three>unchanged</three>
		  </rdf:Description>
		  <rdf:Description rdf:about="http://example.com/subject/two">
		    <one>after</one>
		    <two>after</two>
		    <three>unchanged</three>
		  </rdf:Description>
		  <rdf:Description rdf:about="http://example.com/subject/one">
		    <one>after</one>
		    <two>after</two>
		    <three>unchanged</three>
		  </rdf:Description>
		  </rdf:RDF>';
		
		
		$changeset = new ChangeSet(array('before' => $before,
							'after' => $after,
							'changeReason' => 'foo reason',
							'creatorName' => 'fooUser'));
		
		$changesets = $changeset->get_subjects_of_type('http://purl.org/vocab/changeset/schema#ChangeSet');

				$subjectsThatChanged = array (
			'http://example.com/subject/five',
			'http://example.com/subject/three',
			'http://example.com/subject/two',
			'http://example.com/subject/one',
			'http://example.com/subject/before_only/one',
			'http://example.com/subject/before_only/two'
		);
		
		$this->assertEquals(count($subjectsThatChanged), count($changesets), 'There should be the right number of changesets produced');
		
		array ('http://example.com/subject/one', 'http://example.com/subject/two', 'http://example.com/subject/three');
		foreach ($subjectsThatChanged as $subjectThatChanged)
		{
			$csSubject = $changeset->get_subjects_where_resource('http://purl.org/vocab/changeset/schema#subjectOfChange', $subjectThatChanged);
			$this->assertEquals(1, count($csSubject), "$subjectThatChanged should have exactly one changeset");
		}
		
	}
		function test_different_etags_dont_cause_a_changeset() {
		$before = '
		<rdf:RDF xmlns="http://example.com/foo#" xmlns:dir="http://schemas.talis.com/2005/dir/schema#" xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#">
		  <rdf:Description rdf:about="http://example.com/subject/one">
		  <dir:etag>"83a10a68-5d8a-47f6-bfbe-0dbba55794d4"</dir:etag>
		  </rdf:Description>
		  </rdf:RDF>';
		
		$after = '
		<rdf:RDF xmlns="http://example.com/foo#" xmlns:dir="http://schemas.talis.com/2005/dir/schema#" xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#">
		  <rdf:Description rdf:about="http://example.com/subject/one">
		  </rdf:Description>
		  </rdf:RDF>';
		
		
		$changeset = new ChangeSet(array('before' => $before,
							'after' => $after,
							'changeReason' => 'foo reason',
							'creatorName' => 'fooUser'));
		
		$changesets = $changeset->get_subjects_of_type('http://purl.org/vocab/changeset/schema#ChangeSet');

		$this->assertEquals(0, count($changesets), 'There should be no changesets produced');
		
	}
	
}

?>
