<?php
require_once MORIARTY_DIR. 'store.class.php';
require_once MORIARTY_DIR. 'datatableresult.class.php';
require_once MORIARTY_DIR. 'simplegraph.class.php';

/**
 * DataTable is Moriarty's implementation of the Active Record pattern for RDF data. It provides a very simple way to create and run SPARQL queries. See   DataTableExamples  for examples of how to use DataTable
 *
 * See http://blogs.talis.com/n2/archives/965 for an introduction to DataTable.
 *
 * DataTable is the class that constructs the queries. DataTableResult (in datatableresult.class.php) is a class that represents the results of a query. The interface to DataTable takes inspiration from CodeIgniter's Active Record class, adapted slightly for some RDF specifics.
 *
 * DataTable uses method chaining to make the code more compact and readable. All of the following are equivalent:
 *
 * <code language="php">
 * $dt->select('name')->from('person')->limit(5);
 *
 * $dt->select('name');
 * $dt->from('person');
 * $dt->limit(5);
 *
 * $dt->select('name')->limit(5);
 * $dt->from('person');
 * </code>
 *
*/

class DataTable {
  var $_store_uri = '';
  var $_credentials = '';
  var $_request_factory = '';
  var $_sparql = '';
  var $_limit = 0;
  var $_offset = 0;
  var $_map = array();
  var $_rmap = array();

  var $_subject = null;
  var $_types = array();
  var $_is_distinct = FALSE;
  var $_fields = array();
  var $_optionals = array();
  var $_orders = array();
  var $_filters = array();
  var $_patterns = array();
  var $_joins = array();
  var $_selections = array();
  var $_data = array();
  var $_field_defaults = array();

  /**
  * The DataTable constructor requires the URI of the Talis Platform store as its first parameter, e.g.:
  *
  * <code language="php">
  * $dt = new DataTable('http://api.talis.com/stores/mystore');
  * </code>
  *
  * Optionally a Credentials object can be supplied as the second parameter.
  *
  * Advanced: A third, optional, parameter allows an alternate HttpRequestFactory to be specified for when you need an alternate HTTP implementation to the default cURL-based one
  */
  function __construct($store_uri, $credentials = null, $request_factory = null) {
    $this->_store_uri = $store_uri;
    $this->_credentials = $credentials;
    $this->_request_factory = $request_factory;
  }

  /**
  * Maps a URI to a short name. The first parameter can either be a URI or an associative array of uri and shortname mappings in which case the second parameter is ignored. Short names are used by other methods to refer to property and class URIs.
  *
  * The following are equivalent:
  *
  * <code language="php">
  * $dt->map('http://xmlns.com/foaf/0.1/name', 'name');
  * $dt->map('http://xmlns.com/foaf/0.1/nick', 'nick');
  * </code>
  *
  * <code language="php">
  * $dt->map( array('http://xmlns.com/foaf/0.1/name' => 'name', 'http://xmlns.com/foaf/0.1/nick' => 'nick'));
  * </code>
  */
  function map($uri_or_array, $short_name = null) {
    if (is_array($uri_or_array)) {
      foreach ($uri_or_array as $uri => $short_name) {
        $this->_map[$uri] = $short_name;
        $this->_rmap[$short_name] = $uri;
      }
    }
    else if ($short_name !== null) {
      $this->_map[$uri_or_array] = $short_name;
      $this->_rmap[$short_name] = $uri_or_array;
    }
    return $this;
  }

  /**
   * Specifies the maximum number of rows to return in the query and, optionally, an offset row number to start from. This could be used to implement a paging scheme. The default offset is zero.
   *
   * Select the first five names in a store:
   *
   * <code language="php">
   * $dt->select('name')->limit(5);
   * </code>
   *
   * Select names 15 through to 19 in a store:
   *
   * <code language="php">
   * $dt->select('name')->distinct()->limit(5, 15);
   * </code>
   *
   * Note: Using offset without specifying a sort order may lead to unpredictable results.
   */
  function limit($value, $offset = 0) {
    $this->_limit = $value;
    $this->_offset = $offset;
    return $this;
  }

  /**
  * Specifies the variables you want to select in your query. It takes a single parameter which is a comma separated list of field names (which must be mapped short names) or "dotted path names", explained below.
  *
  * All of the following are valid:
  *
  * <code language="php">
  * $dt->select('name');
  * $dt->select('name,age');
  * $dt->select(' name  , age');
  * </code>
  *
  * The following code will select the foaf:names of every resource in a store:
  *
  * <code language="php">
  * $dt = new DataTable('http://api.talis.com/stores/mystore');
  * $dt->map('http://xmlns.com/foaf/0.1/name', 'name');
  * $dt->select('name');
  * $dt->get();
  * </code>
  *
  * In addition to mapped field names, DataTable supports an extended syntax for expressing traversal of the relationships in the RDF. Dotted path names are a pair of mapped names delimited by a full stop, e.g. friend.name
  *
  * Both parts of a dotted path name must be mapped short names. They can be interpreted as a join between resources in the data. friend.name can be translated as "the name of the resource that is the value of the matching result's friend property". In the query resules the dotted path name is referenced by replacing the dot with an underscore, so friend.name becomes a field called friend_name
  *
  * The following code will select the foaf:names of every resource in a store and the foaf:names of everyone they know:
  *
  * <code language="php">
  * $dt = new DataTable('http://api.talis.com/stores/mystore');
  * $dt->map('http://xmlns.com/foaf/0.1/name', 'name');
  * $dt->map('http://xmlns.com/foaf/0.1/knows', 'knows');
  * $dt->select('name,knows.name');
  * $dt->get();
  * $res = $dt->get();
  *   * foreach ($res->result() as $row) {
  *    echo $row->name;
  *    echo $row->knows_name;
  * }
  * </code>
  */

  function select($field_list) {
    $field_list = trim($field_list);
    $fields = explode(',', $field_list);
    foreach ($fields as $field) {
      $field = trim($field);
      if (strpos($field, '.') >0 ) {
        $parts = explode('.', $field);
        $this->_joins[] = $parts;
        $this->_selections[] = $parts[0].'_'.$parts[count($parts)-1];
      }
      else {
        $this->_fields[] = $field;
        $this->_selections[] = $field;
      }
    }
    return $this;
  }

  /**
   * Specifies the variables you want to optionally select in your query. It takes a single parameter which is a comma separated list of field names (which must be mapped short names). Optional variables will be returned only if there is matching data for them, otherwise they have a null value. In contrast the select method requires that all results must have values for the fields specified. At least one variable must be specified by select before any optional variables can be used.
   *
   * Select the names of all the resources in a store and the nicknames of those resources that have them:
   *
   * <code language="php">
   * $dt = new DataTable('http://api.talis.com/stores/mystore');
   * $dt->map('http://xmlns.com/foaf/0.1/name', 'name');
   * $dt->map('http://xmlns.com/foaf/0.1/nick', 'nick');
   * $dt->select('name')->optional('nick');
   * </code>
   */
  function optional($field_list) {
    $field_list = trim($field_list);
    $fields = explode(',', $field_list);
    $field_list = array();
    foreach ($fields as $field) {
      $field = trim($field);
      $field_list[] = $field;
    }
    $this->_optionals[] = $field_list;
    return $this;
  }

  /**
   * Specifies types of the resources you want to select in your query. It takes a single parameter which is a comma separated list of types (which must be mapped short names). If multiple types are specified then the selected resources must have an rdf:type triple for every one of the types.
   *
   * All of the following are valid:
   *
   * <code language="php">
   * $dt->from('person');
   * $dt->from('document,book');
   * </code>
   *
   * The following code will select the foaf:names of every foaf:Person in a store:
   *
   * <code language="php">
   * $dt = new DataTable('http://api.talis.com/stores/mystore');
   * $dt->map('http://xmlns.com/foaf/0.1/name', 'name');
   * $dt->map('http://xmlns.com/foaf/0.1/Person', 'person');
   * $dt->select('name')->from('person');
   * $dt->get();
   * </code>
   */
  function from($type_list) {
    $type_list = trim($type_list);
    $types = explode(',', $type_list);
    foreach ($types as $type) {
      $type = trim($type);
      $this->_types[] = $type;
    }
    return $this;
  }

  /**
   * Specifies that the query results must be distinct (i.e. without duplicate rows).
   *
   * All of the following are valid:
   *
   * <code language="php">
   * $dt->from('person');
   * $dt->from('document,book');
   * </code>
   *
   * The following code will select the unique foaf:names of every resource in a store:
   *
   * <code language="php">
   * $dt = new DataTable('http://api.talis.com/stores/mystore');
   * $dt->map('http://xmlns.com/foaf/0.1/name', 'name');
   * $dt->select('name')->distinct();
   * $dt->get();
   * </code>
   */
  function distinct() {
    $this->_is_distinct = TRUE;
    return $this;
  }

  /**
   * Specifies a sort order for the query results. The first parameter is required and specifies the field name to sort by (which must be a mapped short name). The second parameter is optional and specifies the ordering of the results. It must be one of 'asc' (meaning ascending order) or 'desc' (meaning descending order). The default ordering is 'asc'.
   *
   * Select names and ages in a store and return them in age order
   *
   * <code language="php">
   * $dt->select('name,age')->order_by('age');
   * </code>
   *
   * Select names in a store and return them in descending order
   *
   * <code language="php">
   * $dt->select('name')->order_by('name', 'desc');
   * </code>
   *
   * Multiple orderings can be specified by repeating this method call:
   *
   * Select names and ages in a store and return them in age order. For example to sort by age and then by name descending:
   *
   * <code language="php">
   * $dt->select('name,age')->order_by('age')->order_by('name', 'desc');
   * </code>
   */
  function order_by($field, $ordering='ASC') {
    $this->_orders[] = array('field' => $field, 'ordering' => $ordering);
    return $this;
  }

   /**
   * Specifies a constraint on a literal value. Multiple calls to this method are conjunctive, i.e. all the constraints must apply to the resources.
   *
   * Select all names where the person has a nickname of santa:
   *
   * <code language="php">
   * $dt = new DataTable('http://api.talis.com/stores/mystore');
   * $dt->map('http://xmlns.com/foaf/0.1/name', 'name');
   * $dt->map('http://xmlns.com/foaf/0.1/nick', 'nick');
   * $dt->select('name')->where('nick', 'santa');
   * </code>
   *
   * Select all names where the person has a nickname of santa and a shoe size of 9:
   *
   * <code language="php">
   * $dt->select('name')->where('nick', 'santa')->where('shoesize', 9);
   * </code>
   *
   * The field name can be suffixed by a boolean operator, one of =, >, <, !=, <=, >=
   *
   * Select names of all resources that are older than 68
   *
   * <code language="php">
   * $dt->select('name')->where('age >', 68);
   * </code>
   *
   * Select names of all resources that do not have a nickname of santa:
   *
   * <code language="php">
   * $dt->select('name')->where('nick !=', 'santa');
   * </code>
   *
   * Boolean, floats and integer types are compared as those specific types, not strings:
   *
   * <code language="php">
   * $dt->select('name')->where('jolly', TRUE);
   * $dt->select('name')->where('age >=', 21);
   * $dt->select('name')->where('shoesize <', 12.76);
   * </code>
   *
   * *SPARQL Note:* These constraints are implemented as filters with appropriate casts based on the type of variable supplied for the second parameter.
   */
  function where($field, $value) {
    if ($field === '_uri') {
      $this->_subject = $value;
    }
    else {
      if (preg_match('~^(.+)\s*(>|<|\!\=|<=|>=)$~', $field, $m)) {
        $op = $m[2];
        $field = trim($m[1]);
      }
      else {
        $op = '=';
      }
      $this->_filters[] = array('field'=>$field, 'op'=>$op, 'value'=>$value);
    }
    return $this;
  }

  /**
   * Specifies a constraint on a resource value. Multiple calls to this method are conjunctive, i.e. all the constraints must apply to the resources. The first parameter is required and specifies the field name to test (which must be a mapped short name). The second parameter is also required and specifies a URI against which the field name is tested.
   *
   * Select names of all resources that have a location of http://sws.geonames.org/6269203/
   *
   * <code language="php">
   * $dt->select('name')->where('location', 'http://sws.geonames.org/6269203/');
   * </code>
   *
   * *SPARQL Note:* These constraints are implemented as additional graph patterns.
   */
  function where_uri($field, $uri) {
    $this->_patterns[] = array('field'=>$field, 'value'=> '<' . $uri . '>');
    return $this;
  }

  /**
   * Returns the generated SPARQL query
   */
  function get_sparql() {
    $prefixes  = array();

    $join_groups = array();
    foreach ($this->_joins as $join) {
      if (!array_key_exists($join[0], $join_groups)) {
        $join_group_properties = array();
      }
      else {
        $join_group_properties = $join_groups[$join[0]];
      }
      $join_group_properties[] = $join[1];
      $join_groups[$join[0]] = $join_group_properties;
    }

    $this->_sparql = 'select ';
    if ($this->_is_distinct) {
      $this->_sparql .= 'distinct ';
    }

    if ($this->_subject === null) {
      $this->_sparql .= '?_uri ';
    }

    if (count($this->_selections) > 0) {
      $this->_sparql .= '?' . join(' ?', $this->_selections) . ' ';
    }

    foreach ($this->_optionals as $optionals) {
      $this->_sparql .= '?' . join(' ?', $optionals) . ' ';
    }
    $this->_sparql .= 'where {';

    if (count($this->_patterns) > 0 ||
        count($this->_fields) > 0 ||
        count($this->_filters) > 0 ||
        count($this->_types) > 0 ||
        count($this->_joins) > 0 ) {
      if ($this->_subject === null) {
        $this->_sparql .= '?_uri';
      }
      else {
        $this->_sparql .= sprintf('<%s>', $this->_subject);
      }
      $done_first = FALSE;
      foreach ($this->_patterns as $pattern) {
        if ($done_first) $this->_sparql .= ';';
        $this->_sparql .= ' <' . $this->_rmap[$pattern['field']] . '> ' . $pattern['value'];
        $done_first = TRUE;
      }
      foreach ($this->_fields as $field) {
        if ($done_first) $this->_sparql .= ';';
        $this->_sparql .= ' <' . $this->_rmap[$field] . '> ?' . $field;
        $done_first = TRUE;
      }
      foreach ($this->_filters as $filter) {
        if (!in_array($filter['field'], $this->_fields)) {
          if ($done_first) $this->_sparql .= ';';
          $this->_sparql .= ' <' . $this->_rmap[$filter['field']] . '> ?' . $filter['field'];
          $done_first = TRUE;
        }
      }

      foreach ($this->_types as $type) {
        if ($done_first) $this->_sparql .= ';';
        $this->_sparql .= ' a <' . $this->_rmap[$type] . '>';
        $done_first = TRUE;
      }


      foreach ($join_groups as $join_group => $join_group_properties) {
        if ($done_first) $this->_sparql .= ';';
        $this->_sparql .= ' <' . $this->_rmap[$join_group] . '> ?' . $join_group;
        $done_first = TRUE;
      }

      $this->_sparql .= '.';
    }

    foreach ($this->_optionals as $optionals) {
      $this->_sparql .= ' optional {';
      if ($this->_subject === null) {
        $this->_sparql .= '?_uri';
      }
      else {
        $this->_sparql .= sprintf('<%s>', $this->_subject);
      }

      $done_first = FALSE;
      foreach ($optionals as $field) {
        if ($done_first) $this->_sparql .= ';';
        $this->_sparql .= ' <' . $this->_rmap[$field] . '> ?' . $field;
        $done_first = TRUE;
      }
      $this->_sparql .= '. }';
    }

    foreach ($this->_filters as $filter) {
      $field = $filter['field'];
      $op = $filter['op'];
      $this->_sparql .= ' filter(';
      if (is_string($filter['value'])) {
        $this->_sparql .= sprintf("str(?%s)%s'%s'", $field, $op, str_replace("'","\\'", $filter['value']));
      }
      else if (is_bool($filter['value'])) {
        $prefixes['xsd'] = 'http://www.w3.org/2001/XMLSchema#';
        $this->_sparql .= 'xsd:boolean(?'. $field . ')';
        $this->_sparql .= $op;
        if ($filter['value'] === FALSE) {
          $this->_sparql .= 'false';
        }
        else {
          $this->_sparql .= 'true';
        }
      }
      else if (is_int($filter['value'])) {
        $prefixes['xsd'] = 'http://www.w3.org/2001/XMLSchema#';
        $this->_sparql .= sprintf('xsd:integer(?%s)%s%s', $field, $op, $filter['value']);
      }
      else if (is_float($filter['value'])) {
        $prefixes['xsd'] = 'http://www.w3.org/2001/XMLSchema#';
        $this->_sparql .= sprintf('xsd:double(?%s)%s%s', $field, $op, $filter['value']);
      }
      else {
        $this->_sparql .= sprintf('?%s%s%s', $field, $op, $filter['value']);
      }
      $this->_sparql .= ').';
    }

    foreach ($join_groups as $join_group => $join_group_properties) {
      $this->_sparql .= ' ?' . $join_group;
      $done_first = FALSE;
      foreach ($join_group_properties as $join_group_property) {
        if ($done_first) $this->_sparql .= ';';
        $this->_sparql .= sprintf(' <%s> ?%s_%s', $this->_rmap[$join_group_property], $join_group, $join_group_property);
        $done_first = TRUE;
      }
      $this->_sparql .= '.';
    }

    $this->_sparql .= ' }';

    if (count($this->_orders) > 0) {
      $done_order_by_token = FALSE;
      foreach ($this->_orders as $order) {
        if (in_array($order['field'], $this->_fields)) {
          if (!$done_order_by_token) {
            $this->_sparql .= ' order by';
            $done_order_by_token = TRUE;
          }
          if (strtolower($order['ordering']) == 'desc') {
            $this->_sparql .= sprintf(' DESC(?%s)', $order['field']);
          }
          else {
            $this->_sparql .= sprintf(' ?%s', $order['field']);
          }
        }
      }
    }
    if ($this->_limit && is_int($this->_limit) && $this->_limit > 0) {
      $this->_sparql .= ' limit ' . $this->_limit;
    }
    if ($this->_offset && is_int($this->_offset) && $this->_offset > 0) {
      $this->_sparql .= ' offset ' . $this->_offset;
    }

    $header = '';
      foreach ($prefixes as $prefix => $uri) {
      $header .= 'prefix ' . $prefix . ': <' . $uri . '> ';
    }
    $this->_sparql = $header . $this->_sparql;
    return $this->_sparql;
  }


  /**
   * Runs the constructed query and returns the results as an instance of DataTableResult
   */
  function get() {
    $store = new Store($this->_store_uri, $this->_credentials, $this->_request_factory);
    $ss = $store->get_sparql_service();
    $query = $this->get_sparql();
    $response = $ss->query($query, 'json');

    if ($response->is_success()) {
      return new DataTableResult($response->body, $this->_subject);
    }
    else {
      //trigger_error("SPARQL query failed with HTTP result " . $response->status_code. " and body " . $response->to_string(), E_USER_WARNING);
      return new DataTableResult('{"head": {"vars": [ ] } , "results": { "bindings": [] } }', $this->_subject);
    }

  }


  /**
   * Sets the value of a field for use with the insert() method. The first parameter is required and specifies the field name to assign the value to (which must be a mapped short name). The second parameter is also required and specifies the new value for the field. Optionally a third parameter can be supplied to specify the type of the value, one of 'literal', 'uri' or 'bnode'. This will default to 'literal'. If the third parameter is 'literal', two further optional parameters may be supplied to specify the language or datatype of the value.
   *
   * Set value of 'name' to be the literal 'chocolate':
   *
   * <code language="php">
   * $dt->set('name', 'chocolate');
   * </code>
   *
   * Set value of 'name' to be the literal 'chocolate' with language code 'en':
   *
   * <code language="php">
   * $dt->set('name', 'chocolate', 'literal', 'en');
   * </code>
   *
   * Set value of 'age' to be the literal '34' with datatype of xsd:integer:
   *
   * <code language="php">
   * $dt->set('age', '34', 'literal', null, 'http://www.w3.org/2001/XMLSchema#integer');
   * </code>
   *
   * Set value of 'father' to be the URI 'http://example.org/bob':
   *
   * <code language="php">
   * $dt->set('name', 'http://example.org/bob', 'uri');
   * </code>
   */
  function set($field, $value, $type=null, $lang=null, $dt=null) {
    if ($field === '_uri') {
      if ($value) {
        $this->_subject= $value;
      }
    }
    else {
      if (is_bool($value)) {
        $field_value = $value === TRUE ? 'true' : 'false';
        $this->_data[$field] = array('type' => 'literal', 'value' => $field_value, 'lang' => null, 'datatype' => 'http://www.w3.org/2001/XMLSchema#boolean');
      }
      else {
        $this->_data[$field] = array('type' => $type, 'value' => $value, 'lang' => $lang, 'datatype' => $dt);
      }
      //$this->_fields[] = $field;
      $this->_optionals[] = array($field);
    }
    return $this;
  }

  /**
   * Specifies default metadata for a field. These will be used by the set() method to set values for type and datatype for the specified field. The first parameter is required and specifies the field name (which must be a mapped short name). The second parameter is also required and specifies the type of the field, one of 'literal', 'uri' or 'bnode'. If the second parameter is 'literal' then a third optional parameter can be supplied which specifies a default datatype URI for the field.
   *
   * Use of this method can simplify and clarify code using set() and insert()
   *
   * Note: Values for type and datatype supplied via the set() method will override any default values set using this method.
   *
   * Set the default type for the 'name' field to be literal:
   *
   * <code language="php">
   * $dt->set('name', 'literal');
   * </code>
   *
   * Set the default datatype for the 'created' field to be xsd:dateTime:
   *
   * <code language="php">
   * $dt->set('created', 'literal', 'http://www.w3.org/2001/XMLSchema#dateTime');
   * </code>
   */
  function set_field_defaults($field, $type, $datatype = null) {
    $this->_field_defaults[$field] = array('type' => $type, 'datatype' => $datatype);
  }


  function get_insert_graph($type_list = '') {

    if ($this->_subject !== null) {
      $s = $this->_subject;
    }
    else {
      $s = '_:a1';
    }

    $g = $this->get_data_as_graph($s);

    $type_list = trim($type_list);
    $types = explode(',', $type_list);
    foreach ($types as $type) {
      $type = trim($type);
      if (strlen($type) > 0) {
        $g->add_resource_triple($s, RDF_TYPE, $this->_rmap[$type] );
      }
    }
    return $g;
  }

  function get_data_as_graph($s) {
    $g = new SimpleGraph();

    foreach ($this->_data as $field => $field_info) {
      if ($field !== '_uri' && $field_info['value'] !== null) {
        $type = $field_info['type'];
        if ($type === null && array_key_exists($field, $this->_field_defaults) && array_key_exists('type', $this->_field_defaults[$field]) && $this->_field_defaults[$field]['type'] !== null) {
          $type = $this->_field_defaults[$field]['type'];
        }
        if ($type === null) {
          $type = 'literal';
        }

        if ($type === 'literal') {

          $dt = $field_info['datatype'];
          if ($dt === null && array_key_exists($field, $this->_field_defaults) && array_key_exists('datatype', $this->_field_defaults[$field]) && $this->_field_defaults[$field]['datatype'] !== null) {
            $dt = $this->_field_defaults[$field]['datatype'];
          }

          $g->add_literal_triple($s, $this->_rmap[$field], $field_info['value'], $field_info['lang'], $dt );
        }
        else if ($type === 'uri') {
          $g->add_resource_triple($s, $this->_rmap[$field], $field_info['value'] );
        }
        else if ($type === 'bnode') {
          $g->add_resource_triple($s, $this->_rmap[$field], '_:' . $field_info['value'] );
        }
      }
    }

    return $g;
  }

  /**
   * Inserts data into a platform store. It optionally takes a single parameter which is a comma separated list of types (which must be mapped short names). These are added as rdf:type properties for the inserted resource. If multiple types are specified then multiple rdf:types will be added.
   *
   * Note that this method is in beta: it has been tested but there may be unusual corner cases that could result in data corruption
   *
   * Insert a new resource description for something with a name of "scooby" and a type of http://example.org/person:
   *
   * <code language="php">
   * $dt = new DataTable('http://api.talis.com/stores/mystore');
   * $dt->map('http://example.org/name', 'name');
   * $dt->map('http://example.org/person', 'person');
   * $dt->set('name', 'scooby');
   * $response = $dt->insert('person');
   * </code>
   *
   * @return HttpResponse
   */
  function insert($type_list = '') {
    $store = new Store($this->_store_uri, $this->_credentials, $this->_request_factory);
    $mb = $store->get_metabox();
    $g = $this->get_insert_graph($type_list);
    $response = $mb->submit_turtle( $g->to_turtle() );
  }

  function get_differences($query_results) {
    $res = $query_results;
    $g_before = new SimpleGraph();
    $g_after = new SimpleGraph();

    $row_count = $res->num_rows();
    $node_index = 0;

    $diffs = array();


    if ($row_count === 0) {
      if ($this->_subject) {
        $s = $this->_subject;
      }
      else {
        $s = "_:s";
      }
      $g_after = $this->get_data_as_graph($s);
      $index = $g_after->get_index();
      if (array_key_exists($s, $index)) {
        $additions = $index[$s];
      }
      else {
        $additions = array();
      }
      $diffs[$s] = array( 'additions' => $additions, 'removals' => array());

    }
    else {
      $subjects = array();

      for ($i = 0; $i < $row_count; $i++) {
        $row = $res->row_array($i);
        $rowdata = $res->rowdata($i);
        if (array_key_exists('_uri', $row)) {
          $s = $row['_uri'];
        }
        else {
          if ($this->_subject) {
            $s = $this->_subject;
          }
          else {
            $s = "_:s";
          }
        }

        $subjects[] = $s;
        foreach ($this->_data as $field => $field_info) {
          if (array_key_exists($field, $row)) {
            $p = $this->_rmap[$field];

            if ($rowdata[$field]['type'] === 'literal') {
              $g_before->add_literal_triple($s, $p, $row[$field], $rowdata[$field]['lang'], $rowdata[$field]['datatype']);
            }
            else if ($rowdata[$field]['type'] === 'uri') {
              $g_before->add_resource_triple($s, $p, $row[$field] );
            }
            else if ($rowdata[$field]['type'] === 'bnode') {
              $g_before->add_resource_triple($s, $p, '_:'.$row[$field] );
            }
          }
        }
      }


      if (count($subjects) > 0) {
        $subjects = array_unique($subjects);
        foreach ($subjects as $s) {
          $g_after->add_graph( $this->get_data_as_graph($s) );
        }
      }

      if ($g_after->is_empty()) {
        $additions = array();
        if ($g_before->is_empty()) {
          $removals = array();
        }
        else {
          $removals = $g_before->get_index();
        }
      }
      else if ($g_before->is_empty()) {
        $additions = $g_after->get_index();
        $removals = array();
      }
      else {
        $removals = SimpleGraph::diff($g_before->get_index(), $g_after->get_index());
        $additions = SimpleGraph::diff($g_after->get_index(), $g_before->get_index());
      }


      foreach ($subjects as $s) {
        $diff = array( 'additions' => array(), 'removals' => array());
        if (array_key_exists($s, $additions)) {
          $diff['additions'] = $additions[$s];
        }
        if (array_key_exists($s, $removals)) {
          $diff['removals'] = $removals[$s];
        }
        $diffs[$s] = $diff;
      }

    }

    return $diffs;
  }

  /**
   * Get the changeset that would be applied by the update method.
   *
   * @return ChangeSet
   */
  function get_update_changeset() {
    $store = new Store($this->_store_uri, $this->_credentials, $this->_request_factory);

    $query = $this->get_sparql();
    $res = $this->get();

    $cs = new SimpleGraph();
    $diffs = $this->get_differences($res);
    $node_index = 0;
    foreach ($diffs as $s => $diff_info) {

      $removals = $diff_info['removals'];
      $additions = $diff_info['additions'];

      $cs_subj = '_:cs' . $node_index++;
      $cs->add_resource_triple($cs_subj, RDF_TYPE, CS_CHANGESET);
      $cs->add_resource_triple($cs_subj, CS_SUBJECTOFCHANGE, $s);
      $cs->add_literal_triple($cs_subj, CS_CHANGEREASON, "Update from DataTable");
      $cs->add_literal_triple($cs_subj, CS_CREATEDDATE, gmdate(DATE_ATOM));
      $cs->add_literal_triple($cs_subj, CS_CREATORNAME, "Moriarty DataTable");

      if (count($removals) > 0) {
        foreach ($removals as $p => $p_list) {
          foreach ($p_list as $p_info) {
            $node = '_:r' . $node_index;
            $cs->add_resource_triple($cs_subj, CS_REMOVAL, $node);
            $cs->add_resource_triple($node, RDF_TYPE, RDF_STATEMENT);
            $cs->add_resource_triple($node, RDF_SUBJECT, $s);
            $cs->add_resource_triple($node, RDF_PREDICATE, $p);
            if ($p_info['type'] === 'literal')  {
              $dt = array_key_exists('datatype', $p_info) ? $p_info['datatype'] : null;
              $lang = array_key_exists('lang', $p_info) ? $p_info['lang'] : null;
              $cs->add_literal_triple($node, RDF_OBJECT, $p_info['value'], $lang, $dt);
            }
            else {
              $cs->add_resource_triple($node, RDF_OBJECT, $p_info['value']);
            }
            $node_index++;
          }
        }
      }

      if (count($additions) > 0) {
        foreach ($additions as $p => $p_list) {
          foreach ($p_list as $p_info) {
            $node = '_:a' . $node_index;
            $cs->add_resource_triple($cs_subj, CS_ADDITION, $node);
            $cs->add_resource_triple($node, RDF_TYPE, RDF_STATEMENT);
            $cs->add_resource_triple($node, RDF_SUBJECT, $s);
            $cs->add_resource_triple($node, RDF_PREDICATE, $p);
            if ($p_info['type'] === 'literal')  {
              $dt = array_key_exists('datatype', $p_info) ? $p_info['datatype'] : null;
              $lang = array_key_exists('lang', $p_info) ? $p_info['lang'] : null;
              $cs->add_literal_triple($node, RDF_OBJECT, $p_info['value'], $lang, $dt);
            }
            else {
              $cs->add_resource_triple($node, RDF_OBJECT, $p_info['value']);
            }
            $node_index++;
          }
        }
      }
    }
    return $cs;
  }

  /**
   * Updates data in a platform store.
   *
   * Note that this method is in beta: it has been tested but there may be unusual corner cases that could result in data corruption
   *
   * Update the resource description for anything with a name of "shaggy" to have a name of "scooby":
   *
   * <code language="php">
   * $dt = new DataTable('http://api.talis.com/stores/mystore');
   * $dt->map('http://example.org/name', 'name');
   * $dt->set('name', 'scooby');
   * $dt->where('name', 'shaggy');
   * $response = $dt->update();
   * </code>
   *
   * The special variable "!_uri" can be used to refer to a specific resource.
   *
   * Update the resource description for http://example.com/thing to have a name of "scooby"
   *
   * <code language="php">
   * $dt = new DataTable('http://api.talis.com/stores/mystore');
   * $dt->map('http://example.org/name', 'name');
   * $dt->set('name', 'scooby');
   * $dt->where('_uri', 'http://example.com/thing');
   * $response = $dt->update();
   * </code>
   *
   * @return HttpResponse
   */
  function update() {
    $cs= $this->get_update_changeset();

    $changesets = $cs->get_subjects_of_type(CS_CHANGESET);
    $store = new Store($this->_store_uri, $this->_credentials, $this->_request_factory);
    $mb = $store->get_metabox();

    if (count($changesets) > 0 && count($cs->get_resource_triple_values($changesets[0], CS_REMOVAL)) > 0) {
      //printf("<p><strong>Posting a changeset</strong></p><pre>%s</pre>", $cs->to_turtle());
      return $mb->apply_changeset_rdfxml( $cs->to_rdfxml() );
    }
    else {
      $g = $this->get_insert_graph('');
      //printf("<p><strong>Submitting RDF</strong></p><pre>%s</pre>", $g->to_turtle());
      return $mb->submit_turtle( $g->to_turtle() );
    }
  }

}
?>