<?php

/**
 * Represents a set of results returned from a DataTable query.
 */
class DataTableResult {
  var $_fields = array();
  var $_results = array();
  var $_rowdata = array();

  function __construct($data, $uri = null) {
    $results = json_decode($data, true);

    $this->_fields = $results['head']['vars'];
    foreach ($results['results']['bindings'] as $binding) {
      $row = array();
      $rowdata = array();
      foreach ($this->_fields as $field) {
        if (array_key_exists($field, $binding)) {
          $row[$field] = $binding[$field]['value'];
          if (array_key_exists('type', $binding[$field]) ) {
            if ($binding[$field]['type'] === 'typed-literal') {
              $rowdata[$field]['type'] = 'literal';
            }
            else {
              $rowdata[$field]['type'] = $binding[$field]['type'];
            }
          }
          else {
            $rowdata[$field]['type'] = null;
          }

          if (array_key_exists('datatype', $binding[$field]) ) {
            $rowdata[$field]['datatype'] = $binding[$field]['datatype'];
          }
          else {
            $rowdata[$field]['datatype'] = null;
          }

          if (array_key_exists('xml:lang', $binding[$field]) ) {
            $rowdata[$field]['lang'] = $binding[$field]['xml:lang'];
          }
          else {
            $rowdata[$field]['lang'] = null;
          }
        }
        else {
          $row[$field] = null;
          $rowdata[$field]['type'] = 'unknown';
        }
      }

      if ($uri) {
        $row['_uri'] = $uri;
        $rowdata['_uri']['type'] = 'uri';
      }

      $this->_results[] = $row;
      $this->_rowdata[] = $rowdata;
    }

    if ($uri) {
      $this->_fields[] = '_uri';
    }
  }

  /**
   * Returns the number of rows in the result set.
   */
  function num_rows() {
    return count($this->_results);
  }

  /**
   * Returns the number of fields in the result set.
   */
  function num_fields() {
    return count($this->_fields);
  }

  /**
   * Returns the query result as an array of objects, or an empty array on any failure. Each field in the original query is mapped to an object variable.
   *
   * For example:
   *
   * <code language="php">
   * $dt = new DataTable('http://api.talis.com/stores/mystore');
   * $dt->map('http://xmlns.com/foaf/0.1/name', 'name');
   * $dt->map('http://xmlns.com/foaf/0.1/nick', 'nick');
   *    * $dt->select('name,nick')->limit(10);
   * $res = $dt->get();
   * foreach ($res->result() as $row) {
   *    echo $row->name;
   *    echo $row->nick;
   * }
   * </code>
   */
  function result() {
    $results = array();
    foreach ($this->_results as $result) {
      $results[] = (object)$result;
    }
    return $results;
  }

  /**
   * Returns the query result as an array of associative arrays, or an empty array on any failure. The keys and values of the associative array correspond with the fields and values in the results.
   *
   * For example:
   *
   * <code language="php">
   * $dt->select('name,nick')->limit(10);
   * $res = $dt->get();
   *
   * foreach ($res->result_array() as $row) {
   *    echo $row['name'];
   *    echo $row['nick'];
   * }
   *</code>
   */
  function result_array() {
    return $this->_results;
  }

  /**
   * Returns an associative array containing a single result row. The $index parameter is optional and defaults to zero.
   */
  function row_array($index=0) {
    return $this->_results[$index];
  }
  function row($index=0) {
    return (object)$this->_results[$index];
  }

  /**
   * Returns an associative array containing metadata about the values in the specified row. The array keys correspond to field names and the values to another associative array containing the metadata for that field's value. The $index parameter is optional and defaults to zero.
   *
   * The following metadata keys are available:
   *
   * <ul>
   * <li>type - the type of the field's value, one of "uri", "bnode" or "literal"</li>
   * <li>datatype - the datatype of a literal value, or null if no datatype was detected</li>
   * <li>lang - the language code of a literal value, or null if no language was detected</li>
   * </ul>
   * For example:
   *
   * <code language="php">
   * $dt->select('name,nick')->limit(10);
   * $res = $dt->get();
   * $rowdata = $res->rowdata(5);
   * echo $rowdata['name']['type'];
   * echo $rowdata['name']['lang'];
   * echo $rowdata['name']['datatype'];
   * </code>
   */
  function rowdata($index=0) {
    return $this->_rowdata[$index];
  }

  /**
   * Returns a tabular string representation of the results.
   */
  function to_string() {
    $ret = '';
    $col_widths = array();
    foreach ($this->_fields as $field) {
      $col_widths[$field] = strlen($field);
    }
    foreach ($this->_results as $result) {
      foreach ($this->_fields as $field) {
        if (! is_null($result[$field])) {
          if (strlen($result[$field]) > $col_widths[$field]) {
            $col_widths[$field] = strlen($result[$field]);
          }
        }
      }
    }

    $total_width = 0;
    $format_string = '';
    foreach ($this->_fields as $field) {
      $total_width += $col_widths[$field] + 2;
      $ret .= str_pad($field, $col_widths[$field]) . '  ';
    }
    $ret .= "\n";

    $ret .= str_repeat('_', $total_width) . "\n";
    foreach ($this->_results as $result) {
      foreach ($this->_fields as $field) {
        $ret .= str_pad($result[$field], $col_widths[$field]) . '  ';
      }
      $ret .= "\n";
    }

    return $ret;
  }
}
?>