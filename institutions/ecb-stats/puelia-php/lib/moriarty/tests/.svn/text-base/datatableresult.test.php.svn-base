<?php
require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'constants.inc.php';
require_once MORIARTY_DIR . 'datatableresult.class.php';


class DataTableResultTest extends PHPUnit_Framework_TestCase {
  var $_select_result1 = '{
  "head": {
    "vars": [ "_uri", "name" , "address1" , "address2" , "postcode" , "town" ]
  } ,
  "results": {
    "bindings": [
      {
        "_uri": {"type": "uri", "value":"http://example.com/uri1"},
        "name": { "type": "literal" , "value": "Barbican Playgroup" } ,
        "address1": { "type": "literal" , "value": "01 & 02 Level" } ,
        "address2": { "type": "literal" , "value": "Andrewes House" } ,
        "postcode": { "type": "literal" , "value": "EC2Y 8AX" } ,
        "town": { "type": "literal" , "value": "London" }
      } ,
      {
        "_uri": {"type": "uri", "value":"http://example.com/uri2"},
        "name": { "type": "literal" , "value": "Buffer Bear @ Barts & the London" } ,
        "address1": { "type": "literal" , "value": "Surgery House" } ,
        "address2": { "type": "literal" , "value": "St Bartholomew\'s Hospital" } ,
        "postcode": { "type": "literal" , "value": "EC1A 7BE" } ,
        "town": { "type": "literal" , "value": "London" }
      } ,
      {
        "_uri": {"type": "uri", "value":"http://example.com/uri3"},
        "name": { "type": "literal" , "value": "Charterhouse Square School" }
      } ,
      {
        "_uri": {"type": "uri", "value":"http://example.com/uri4"},
        "name": { "type": "literal" , "value": "City Child" } ,
        "address1": { "type": "literal" , "value": "1 Bridgewater Square" } ,
        "address2": { "type": "literal" , "value": "Barbican" } ,
        "postcode": { "type": "literal" , "value": "EC2Y 8AH" } ,
        "town": { "type": "literal" , "value": "London" }
      } ,
      {
        "_uri": {"type": "uri", "value":"http://example.com/uri5"},
        "name": { "type": "literal" , "value": "City of London School" }
      } ,
      {
        "_uri": {"type": "uri", "value":"http://example.com/uri6"},
        "name": { "type": "literal" , "value": "City of London School for Girls" } ,
        "address1": { "type": "literal" , "value": "St Gile\'s Terrace" } ,
        "address2": { "type": "literal" , "value": "Barbican" } ,
        "postcode": { "type": "literal" , "value": "EC2Y 8BB" } ,
        "town": { "type": "literal" , "value": "London" }
      } ,
      {
        "_uri": {"type": "uri", "value":"http://example.com/uri7"},
        "name": { "type": "literal" , "value": "Golden Lane Playgroup" } ,
        "address1": { "type": "literal" , "value": "Golden Lane Estate" } ,
        "address2": { "type": "literal" , "value": "Golden Lane" } ,
        "postcode": { "type": "literal" , "value": "EC1Y 0RN" } ,
        "town": { "type": "literal" , "value": "London" }
      } ,
      {
        "_uri": {"type": "uri", "value":"http://example.com/uri8"},
        "name": { "type": "literal" , "value": "Guildhall School of Music and Drama" } ,
        "address1": { "type": "literal" , "value": "Silk Street" } ,
        "address2": { "type": "literal" , "value": "Barbican" } ,
        "postcode": { "type": "literal" , "value": "EC2Y 8DT" } ,
        "town": { "type": "literal" , "value": "London" }
      } ,
      {
        "_uri": {"type": "uri", "value":"http://example.com/uri9"},
        "name": { "type": "literal" , "value": "Leapfrog Day Nurseries" } ,
        "address1": { "type": "literal" , "value": "Weddel House" } ,
        "address2": { "type": "literal" , "value": "13-21 West Smithfield" } ,
        "postcode": { "type": "literal" , "value": "EC1A 9HY" } ,
        "town": { "type": "literal" , "value": "London" }
      } ,
      {
        "_uri": {"type": "uri", "value":"http://example.com/uri10"},
        "name": { "type": "literal" , "value": "Sir John Cass\'s Foundation Primary School" } ,
        "address1": { "type": "literal" , "value": "St James\'s Passage" } ,
        "address2": { "type": "literal" , "value": "Duke\'s Place" } ,
        "postcode": { "type": "literal" , "value": "EC3A 5DE" } ,
        "town": { "type": "literal" , "value": "London" }
      } ,
      {
        "_uri": {"type": "uri", "value":"http://example.com/uri11"},
        "name": { "type": "literal" , "value": "St Paul\'s Cathedral School" }
      } ,
      {
        "_uri": {"type": "uri", "value":"http://example.com/uri12"},
        "name": { "type": "literal" , "value": "The Charterhouse Square School" }
      } ,
      {
        "_uri": {"type": "uri", "value":"http://example.com/uri13"},
        "name": { "type": "literal" , "value": "The Charterhouse Square School" }
      } ,
      {
        "_uri": {"type": "uri", "value":"http://example.com/uri14"},
        "name": { "type": "literal" , "value": "The Childrens Centre" }
      } ,
      {
        "_uri": {"type": "uri", "value":"http://example.com/uri15"},
        "name": { "type": "literal" , "value": "Tower Hill Nursery" } ,
        "address1": { "type": "literal" , "value": "London Metropolitan University" } ,
        "address2": { "type": "literal" , "value": "100 Minories" } ,
        "postcode": { "type": "literal" , "value": "EC3N 1JY" } ,
        "town": { "type": "literal" , "value": "London" }
      }
    ]
  }
}';

  var $_select_result2 = '{
  "head": {
    "vars": [ "_uri", "link" , "title" , "name" , "body" , "misc" ]
  } ,
  "results": {
    "bindings": [
      {
        "_uri": {"type": "uri", "value":"http://example.com/uri1"},
        "link": { "type": "uri" , "value": "http://example.com/" } ,
        "title": { "type": "literal" , "value": "Example", "xml:lang": "en" } ,
        "name": { "type": "literal" , "value": "Andrew" } ,
        "body": { "type": "typed-literal" , "value": "<p xmlns=\"http://www.w3.org/1999/xhtml\">My name is <b>Andrew</b></p>", "datatype":"http://www.w3.org/1999/02/22-rdf-syntax-ns#XMLLiteral"} ,
        "misc": { "type": "bnode", "value":"foo"}
      },
      {
        "_uri": {"type": "uri", "value":"http://example.com/uri2"},
        "link": { "type": "uri" , "value": "http://example.com/" } ,
        "title": { "type": "literal" , "value": "Example", "xml:lang": "en" } ,
        "name": { "type": "literal" , "value": "Andrew" } ,
        "body": { "type": "literal" , "value": "foo"} ,
        "misc": { "type": "bnode", "value":"foo"}
      }
    ]
  }
}';



  var $_select_result_without_uri_pseudo_variable = '{
  "head": {
    "vars": [ "link" , "title" , "name" , "body" , "misc" ]
  } ,
  "results": {
    "bindings": [
      {
        "name": { "type": "uri" , "value": "http://example.com/" }
      }
    ]
  }
}';





  function test_num_rows() {
    $result = new DataTableResult($this->_select_result1);
    $this->assertEquals( 15, $result->num_rows() );
  }

  function test_num_fields() {
    $result = new DataTableResult($this->_select_result1);
    $this->assertEquals( 6, $result->num_fields() );
  }


  function test_result() {
    $result = new DataTableResult($this->_select_result1);
    $results = $result->result();
    
    $this->assertEquals( 15, count($results) );
    $this->assertEquals( 'Barbican Playgroup', $results[0]->name );
    $this->assertEquals( 'Buffer Bear @ Barts & the London', $results[1]->name );
    $this->assertEquals( 'London', $results[14]->town );
  }


  function test_result_array() {
    $result = new DataTableResult($this->_select_result1);
    $result_array = $result->result_array();
    
    $this->assertEquals( 15, count($result_array) );
    $this->assertEquals( 'Barbican Playgroup', $result_array[0]['name'] );
    $this->assertEquals( 'Buffer Bear @ Barts & the London', $result_array[1]['name'] );
    $this->assertEquals( 'London', $result_array[14]['town'] );
  }

  function test_row_array_returns_first_row() {
    $result = new DataTableResult($this->_select_result1);
    $row_array = $result->row_array();
    
    $this->assertEquals( 'Barbican Playgroup', $row_array['name'] );
    $this->assertEquals( '01 & 02 Level' ,$row_array['address1'] );
    $this->assertEquals( 'Andrewes House' ,$row_array['address2'] );
    $this->assertEquals( 'EC2Y 8AX',$row_array['postcode'] );
    $this->assertEquals( 'London', $row_array['town'] );
  }

  function test_row_array_with_row_index() {
    $result = new DataTableResult($this->_select_result1);
    $row_array = $result->row_array(14);
  
    $this->assertEquals( 'Tower Hill Nursery', $row_array['name'] );
    $this->assertEquals( 'London Metropolitan University' ,$row_array['address1'] );
    $this->assertEquals( '100 Minories' ,$row_array['address2'] );
    $this->assertEquals( 'EC3N 1JY',$row_array['postcode'] );
    $this->assertEquals( 'London', $row_array['town'] );
  }


  function test_row_returns_first_row() {
    $result = new DataTableResult($this->_select_result1);
    $row = $result->row();
    
    $this->assertEquals( 'Barbican Playgroup', $row->name );
    $this->assertEquals( '01 & 02 Level' ,$row->address1 );
    $this->assertEquals( 'Andrewes House' ,$row->address2 );
    $this->assertEquals( 'EC2Y 8AX',$row->postcode );
    $this->assertEquals( 'London', $row->town );
  }

  function test_row_with_row_index() {
    $result = new DataTableResult($this->_select_result1);
    $row = $result->row(14);
  
    $this->assertEquals( 'Tower Hill Nursery', $row->name );
    $this->assertEquals( 'London Metropolitan University' ,$row->address1 );
    $this->assertEquals( '100 Minories' ,$row->address2 );
    $this->assertEquals( 'EC3N 1JY',$row->postcode );
    $this->assertEquals( 'London', $row->town );
  }
  
  function test_rowdata_returns_first_row_data() {
    $result = new DataTableResult($this->_select_result1);
    $rowdata = $result->rowdata();
    $this->assertEquals( 'literal', $rowdata['name']['type'] );
    $this->assertEquals( 'literal', $rowdata['address1']['type'] );
    $this->assertEquals( 'literal', $rowdata['address2']['type'] );
    $this->assertEquals( 'literal', $rowdata['postcode']['type'] );
    $this->assertEquals( 'literal', $rowdata['town']['type'] );
  }
  
  function test_rowdata_returns_types() {
    $result = new DataTableResult($this->_select_result2);
    $rowdata = $result->rowdata();
    $this->assertEquals( 'uri', $rowdata['link']['type'] );
    $this->assertEquals( 'literal', $rowdata['title']['type'] );
    $this->assertEquals( 'literal', $rowdata['name']['type'] );
    $this->assertEquals( 'literal', $rowdata['body']['type'] );
    $this->assertEquals( 'bnode', $rowdata['misc']['type'] );
  }  

  function test_rowdata_returns_datatypes() {
    $result = new DataTableResult($this->_select_result2);
    $rowdata = $result->rowdata();
    $this->assertEquals( null, $rowdata['link']['datatype'] );
    $this->assertEquals( null, $rowdata['title']['datatype'] );
    $this->assertEquals( null, $rowdata['name']['datatype'] );
    $this->assertEquals( 'http://www.w3.org/1999/02/22-rdf-syntax-ns#XMLLiteral', $rowdata['body']['datatype'] );
    $this->assertEquals( null, $rowdata['misc']['datatype'] );
  }    
  
  function test_rowdata_returns_languagues() {
    $result = new DataTableResult($this->_select_result2);
    $rowdata = $result->rowdata();
    $this->assertEquals( null, $rowdata['link']['lang'] );
    $this->assertEquals( 'en', $rowdata['title']['lang'] );
    $this->assertEquals( null, $rowdata['name']['lang'] );
    $this->assertEquals( null, $rowdata['body']['lang'] );
    $this->assertEquals( null, $rowdata['misc']['lang'] );
  }    

  function test_rowdata_with_row_index() {
    $result = new DataTableResult($this->_select_result2);
    $rowdata = $result->rowdata(1);
    $this->assertEquals( 'literal', $rowdata['body']['type'] );
  }

  function test_result_without_uri_pseudo_variable() {
    $result = new DataTableResult($this->_select_result_without_uri_pseudo_variable, 'http://example.org/scooby');
    $results = $result->result();
    
    $this->assertEquals( 1, count($results) );
    $this->assertEquals( 'http://example.org/scooby', $results[0]->_uri );
  }


}
?>
