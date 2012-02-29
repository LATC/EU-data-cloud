<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'moriarty.inc.php';

/**
 * EXPERIMENTAL: Represents a path selecting nodes from a simple graph.
 * Based on http://www.w3.org/2005/04/fresnel-info/fsl/
 *
 * @package GraphPath
 */
class GraphPath {

  private $_path = array();
  private $_errors = array();
  function __construct($path) {
    $this->_path = $this->_parse_path($path);
  }

  /**
   * Evaluate the path against a graph
   * @param SimpleGraph g
   * @return array nodes that match the path
   */
  function select(&$g, $trace = FALSE) {
    if ($trace) print "GraphPath: Selecting all subjects in graph\n";
    $candidates = array();
    $index = $g->get_index();
    foreach (array_keys($index) as $subject) {
      $candidates[] = $g->make_resource_array($subject);
    }
    $ret = $this->_path->select($candidates, $g, NULL, TRUE, $trace);
    if ($trace && $this->has_errors()) {
      foreach ($this->_errors as $error) {
        print "ERROR: " . $error . "\n";
      }
    }
    return $ret;
  }
  
  /**
   * Evaluate the path against a graph from a given context node
   * @param SimpleGraph g
   * @return array nodes that match the path
   */
  function match(&$g, $subject, $trace = FALSE) {
    $candidates = array($g->make_resource_array($subject));
    if (count($this->_path->select($candidates, $g, NULL, TRUE, $trace) > 0) ) {
      return TRUE;
    }
    else {
      return FALSE;
    }
  }  

  /**
   * @access private
   */
  function add_error($err) {
    if (!in_array($err, $this->_errors)) {
      $this->_errors[] = $err;
    }
  }

  function has_errors() {
    return (count($this->_errors) > 0);
  }

  function get_errors() {
    return $this->_errors;
  }


  /**
   * @access private
   */
  private function _parse_path($v) {
    list($step, $v) = $this->m_locationpath($v);
    return $step;
  }

  /**
   * @access private
   */
  function m($re, $v, $options = 'si') {
    return preg_match("/^\s*" . $re . "(.*)$/" . $options, $v, $m) ? $m : false;
  }

  /**
   * @access private
   */
  function m_split($pattern, $v) {
    if ($r = $this->m($pattern, $v)) {
      return array($r[1], $r[2]);
    }
    return array(false, $v);
  }

  /**
   * @access private
   */
  function m_locationpath($v) {
    $steps = array();
    if ((list($r, $v) = $this->m_step($v)) && $r) {
      $steps[] = $r;

      while ((list($r, $v) = $this->m_slash($v)) && $r) {
        if ((list($r, $v) = $this->m_step($v)) && $r) {
          $steps[] = $r;
        }
      }
    }

    return array(new LocPath($steps), $v);
  }

  /**
   * @access private
   */
  function m_step($v) {
    if ((list($r, $v) = $this->m_test($v)) && $r) {
      return array($r, $v);
    }
    if ((list($r, $v) = $this->m_literal($v)) && $r) {
      return array($r, $v);
    }
    if ((list($r, $v) = $this->m_textfunction($v)) && $r) {
      return array($r, $v);
    }
    return array(false, $v);
  }

  /**
   * @access private
   */
  function m_test($v) {
    list($axis, $v) = $this->m_axis($v);

    $selector= '';
    if ($r = $this->m('(\*)', $v)) {
      $selector = new WildCardMatcher();
      $v = $r[2];
    }
    else if ($r = $this->m('([a-z0-9_]+:[a-z0-9_]+)', $v)) {
      $selector = new TypeMatcher($r[1]);
      $v = $r[2];
    }
    else {
      return array(false, $v);
    }

    $filters = array();
    while ((list($r, $v) = $this->m_openbracket($v)) && $r) {
      if ((list($r, $v) = $this->m_orexpr($v)) && $r) {
        $filters[] = $r;
      }
      list($r_br, $v) = $this->m_closebracket($v);
    }

    return array(new StepMatcher($selector, $axis, $filters), $v);
  }

  /**
   * @access private
   */
  function m_orexpr($v) {
    if ((list($r, $v) = $this->m_andexpr($v)) && $r) {
      $left = $r;
      if ((list($r, $v) = $this->m_split('(\s+or\s+)', $v)) && $r) {
        if ((list($r, $v) = $this->m_andexpr($v)) && $r) {
          return array(new OrExpr($left, $r), $v);
        }
      }
      else {
        return array(new OrExpr($left), $v);
      }
    }
    return array(false, $v);
  }

  /**
   * @access private
   */
  function m_andexpr($v) {
    if ((list($r, $v) = $this->m_compexpr($v)) && $r) {
      $left = $r;
      if ((list($r, $v) = $this->m_split('(\s+and\s+)', $v)) && $r) {
        if ((list($r, $v) = $this->m_andexpr($v)) && $r) {
          return array(new AndExpr($left, $r), $v);
        }
      }
      else {
        return array(new AndExpr($left), $v);
      }
    }
    return array(false, $v);
  }


  /**
   * @access private
   */
  function m_compexpr($v) {
    if ((list($r, $v) = $this->m_unaryexpr($v)) && $r) {
      $left = $r;
      if ((list($r, $v) = $this->m_operator($v)) && $r) {
        $op = $r;
        if ((list($r, $v) = $this->m_unaryexpr($v)) && $r) {
          return array(new CompExpr($left, $op, $r), $v);
        }
      }
      else {
        return array(new CompExpr($left), $v);
      }
    }
    return array(false, $v);
  }

  /**
   * @access private
   */
  function m_unaryexpr($v) {
    if ((list($r, $v) = $this->m_functioncall($v)) && $r) {
      return array($r, $v);
    }
    if ((list($r, $v) = $this->m_literalgenerator($v)) && $r) {
      return array($r, $v);
    }
    if ((list($r, $v) = $this->m_numbergenerator($v)) && $r) {
      return array($r, $v);
    }
    if ((list($r, $v) = $this->m_booleangenerator($v)) && $r) {
      return array($r, $v);
    }
    if ((list($r, $v) = $this->m_split('(\.)', $v)) && $r) {
      return array(new SelfGenerator(), $v);
    }
    if ((list($r, $v) = $this->m_locationpath($v)) && $r) {
      return array($r, $v);
    }
    return array(false, $v);
  }


  /**
   * @access private
   */
  function m_literalgenerator($v) {
    if ((list($r, $v) = $this->m_string($v)) && $r !== FALSE) {
      return array(new LiteralGenerator($r), $v);
    }
    return array(false, $v);
  }

  /**
   * @access private
   */
  function m_numbergenerator($v) {
    if ((list($r, $v) = $this->m_split('([0-9]+)', $v)) && $r) {
      return array(new NumberGenerator($r), $v);
    }
    return array(false, $v);
  }

  /**
   * @access private
   */
  function m_booleangenerator($v) {
    if ((list($r, $v) = $this->m_split('(true\(\))', $v)) && $r) {
      return array(new BooleanGenerator(TRUE), $v);
    }
    if ((list($r, $v) = $this->m_split('(false\(\))', $v)) && $r) {
      return array(new BooleanGenerator(FALSE), $v);
    }
    return array(false, $v);
  }


  /**
   * @access private
   */
  function m_literal($v) {
    if ((list($r, $v) = $this->m_string($v)) && $r !== FALSE) {
      return array(new LiteralMatcher($r), $v);
    }
    return array(false, $v);
   }


  /**
   * @access private
   */
  function m_number($v) {
    if ((list($r, $v) = $this->m_split('([0-9]+)', $v)) && $r) {
      return array(new NumberMatcher($r), $v);
    }
    return array(false, $v);
  }


  /**
   * @access private
   */
  function m_textfunction($v) {
    if ((list($r, $v) = $this->m_split('(text\(\))', $v)) && $r) {
      return array(new AnyLiteralMatcher($r), $v);
    }
    return array(false, $v);
  }

  /**
   * @access private
   */
  function m_operator($v) {
    return $this->m_split('(=)', $v);
  }


  /**
   * @access private
   */
  function m_functioncall($v) {
    if ((list($r, $v) = $this->m_split('(count|local-name|namespace-uri|uri|literal-value|literal-dt|exp|string-length|normalize-space|boolean)\(', $v)) && $r) {
      $function = $r;
      if ((list($r, $v) = $this->m_oneargument($v)) && $r) {
        $arg = $r;
        if ((list($r, $v) = $this->m_split('(\))', $v)) && $r) {
          if ($function == 'count') {
            return array(new CountFunction($arg), $v);
          }
          else if ($function == 'local-name') {
            return array(new LocalNameFunction($arg), $v);
          }
          else if ($function == 'namespace-uri') {
            return array(new NamespaceUriFunction($arg), $v);
          }
          else if ($function == 'uri') {
            return array(new UriFunction($arg), $v);
          }
          else if ($function == 'literal-value') {
            return array(new LiteralValueFunction($arg), $v);
          }
          else if ($function == 'literal-dt') {
            return array(new LiteralDtFunction($arg), $v);
          }
          else if ($function == 'exp') {
            return array(new ExpFunction($arg), $v);
          }
          else if ($function == 'string-length') {
            return array(new StringLengthFunction($arg), $v);
          }
          else if ($function == 'normalize-space') {
            return array(new NormalizeSpaceFunction($arg), $v);
          }
          else if ($function == 'boolean') {
            return array(new BooleanFunction($arg), $v);
          }
        }
      }
    }
    return array(false, $v);
  }

  /**
   * @access private
   */
  function m_oneargument($v) {
    if ((list($r, $v) = $this->m_unaryexpr($v)) && $r) {
      return array($r, $v);
    }
    return array(false, $v);
  }

  /**
   * @access private
   */
  function m_string($v) {

    if ((list($r, $v) = $this->m_split('(".*")', $v)) && $r) {
      return array(substr($r, 1, strlen($r) - 2), $v);
    }
    if ((list($r, $v) = $this->m_split('(\\\'.*\\\')', $v)) && $r) {
      return array(substr($r, 1, strlen($r) - 2), $v);
    }

    return array(false, $v);
  }

  /**
   * @access private
   */
  function m_slash($v) {
    return $this->m_split('(\/)', $v);
  }

  /**
   * @access private
   */
  function m_openbracket($v) {
    return $this->m_split('(\[)', $v);
  }


  /**
   * @access private
   */
  function m_closebracket($v) {
    return $this->m_split('(\])', $v);
  }


  /**
   * @access private
   */
  function m_axis($v) {
    return $this->m_split('(in|out)::', $v);
  }

  /**
   * @access private
   */
  function to_string() {
    return $this->_path->to_string();
  }

}


/**
 * @access private
 */
class LocPath {
  var $_steps = array();

  function __construct($steps) {
    $this->_steps = $steps;
  }

  function select($candidates, &$g, $context, $distinct = FALSE, $trace = FALSE) {
    if ($trace) print "Path: " . $this->to_string() ."\n";

    $selected = array();
    for ($i = 0; $i < count($this->_steps); $i++) {
      $selected = array();
      $step = $this->_steps[$i];
      if ($trace) print "Path: Filtering " . count($candidates) . " candidates using " . $step->to_string() . "\n";
      foreach ($candidates as $candidate) {
        if ( $step->matches($candidate, $g, $context, $trace) ) {
          $selected[] = $candidate;
        }
      }
      if ($trace) print "Path: " . count($selected) . " resources passed the filter\n";
      if ( $i < count($this->_steps) - 2) {
        // get a distinct list of candidates (an optimisation)
        $candidates = $this->get_candidates($selected, $g, TRUE, $trace);
      }
      else if ( $i == count($this->_steps) - 2) {
        // next step is last so get candidates including duplicates
        $candidates = $this->get_candidates($selected, $g, $distinct, $trace);
      }
    }

    return $selected;
  }

  function get_candidates($resources, &$g, $distinct = TRUE, $trace = FALSE) {
    $candidates = array();
    foreach ($resources as $resource) {
      if ($resource['type'] != 'literal') {
        if (isset($resource['node'])) {
          if ($trace) print "Path: Selecting nodes that are values of " . $resource['value'] . "\n";
          $candidates = array_merge($candidates, $this->get_nodes($resource, $g));
        }
        else {
          if ($trace) print "Path: Selecting arcs that are properties of " . $resource['value'] . "\n";
          $candidates = array_merge($candidates, $this->get_arcs($resource, $g, $distinct));
        }
      }
    }

    if ($trace) print "Path: Selected " . count($candidates) . " candidates\n";
    return $candidates;
  }



  function get_arcs(&$node, &$g, $distinct = TRUE) {
    $arcs = array();
    $properties = $g->get_subject_properties($node['value'], $distinct);
    foreach ($properties as $property_uri) {
      $info = $g->make_resource_array($property_uri);
      $info['node'] = $node['value'];
      $arcs[] = $info;
    }
    return $arcs;
  }

  function get_nodes(&$arc, &$g) {
    return $g->get_subject_property_values($arc['node'], $arc['value']);
  }

  function to_string() {
    $ret = '';
    if (count($this->_steps) > 0 ) {
      $ret = $this->_steps[0]->to_string();
      for ($i = 1; $i < count($this->_steps); $i++) {
        $ret .= '/' . $this->_steps[$i]->to_string();
      }
    }
    return $ret;
  }
}


/**
 * @access private
 */
class TypeMatcher {
  var $_type = null;
  function __construct($type) {
    $this->_type = $type;
  }

  function matches($candidate, &$g, $context, $trace = FALSE) {
    if ($trace) print "TypeMatcher: Testing " . $candidate['value'] . " using "  . $this->to_string() . "\n";

    $matches = FALSE;

    $test_uri = $g->qname_to_uri($this->_type);
    if ( $test_uri != null) {

      if (isset($candidate['node'])) {
        // We are testing an arc
        if ($trace) print "TypeMatcher: Testing to see if " . $candidate['value'] . " is same as " . $test_uri . "\n";
        if ($candidate['value'] == $test_uri) {
          $matches = TRUE;
        }
      }
      else {
        // We are testing a node
        if ($trace) print "TypeMatcher: Testing to see if " . $candidate['value'] . " has type of " . $test_uri . "\n";
        if ($g->has_resource_triple($candidate['value'], RDF_TYPE, $test_uri) ) {
          $matches = TRUE;
        }
      }
    }
    if ($trace) print "TypeMatcher: " . ( $matches ? 'MATCHED' : 'NO MATCH') . " using " . $this->to_string() . "\n";

    return $matches;

  }


  function to_string() {
    return $this->_type;
  }

}

/**
 * @access private
 */
class WildCardMatcher {

  function matches($candidate, &$g, $context, $trace = FALSE) {
    if ($trace) print "WildCardMatcher: Testing " . $candidate['value'] . " using "  . $this->to_string() . "\n";
    return TRUE;
  }

  function to_string() {
    return '*';
  }
}

/**
 * @access private
 */
class LiteralMatcher {
  var $_text = '';
  var $_dt = '';
  function __construct($text,$dt = null) {
    $this->_text = $text;
    $this->_dt = $dt;
  }

  function matches($candidate, &$g, $context, $trace = FALSE) {
    if ($trace) print "LiteralMatcher: Testing " . $candidate['value'] . " using "  . $this->to_string() . "\n";
    $matches = FALSE;

    if ($trace) print "LiteralMatcher: Testing to see if " . $candidate['value'] . " is same as " . $this->_text . "\n";
    if ($candidate['type'] == 'literal' && $candidate['value'] == $this->_text) {
      if ($trace) print "LiteralMatcher: It is, adding " . $candidate['value'] . " to selected queue\n";
      $matches = TRUE;
    }

    if ($trace) print "LiteralMatcher: " . ( $matches ? 'MATCHED' : 'NO MATCH') . " using " . $this->to_string() . "\n";
    return $matches;
  }

  function to_string() {
    $ret = "'" . $this->_text. "'";
    return $ret;
  }
}

/**
 * @access private
 */
class NumberMatcher {
  var $_number = '';
  function __construct($number_text) {
    $this->_number = $number_text;
  }

  function matches($candidate, &$g, $context, $trace = FALSE) {
    if ($trace) print "NumberMatcher: Testing " . $candidate['value'] . " using "  . $this->to_string() . "\n";
    $matches = FALSE;

    if ($trace) print "NumberMatcher: Testing to see if " . $candidate['value'] . " equals " . $this->_text . "\n";
    if ($candidate['type'] == 'literal' && is_numeric($candidate['value']) && $candidate['value'] == $this->_number) {
      $matches = TRUE;
    }

    if ($trace) print "NumberMatcher: " . ( $matches ? 'MATCHED' : 'NO MATCH') . " using " . $this->to_string() . "\n";
    return $matches;
  }

  function to_string() {
    $ret = "'" . $this->_text. "'";
    return $ret;
  }
}

/**
 * @access private
 */
class AnyLiteralMatcher {
  function __construct() {

  }

  function matches($candidate, &$g, $context, $trace = FALSE) {
    if ($trace) print "AnyLiteralMatcher: Testing " . $candidate['value'] . " using "  . $this->to_string() . "\n";
    $matches = FALSE;

    if ($candidate['type'] == 'literal') {
      $matches = TRUE;
    }

    if ($trace) print "AnyLiteralMatcher: " . ( $matches ? 'MATCHED' : 'NO MATCH') . " using " . $this->to_string() . "\n";
    return $matches;
  }

  function to_string() {
    $ret = "text()";
    return $ret;
  }
}

/**
 * @access private
 */
class StepMatcher {
  var $_selector = '';
  var $_axis = '';
  var $_filters = array();
  function __construct($selector, $axis, $filters) {
    $this->_selector = $selector;
    $this->_axis = $axis;
    $this->_filters = $filters;
  }

  function matches($candidate, &$g, $context, $trace = FALSE) {
    if ($trace) print "StepMatcher: Matching " . ( $candidate ? $candidate['value'] : 'null') . " using " . $this->to_string() . "\n";
    $matches = FALSE;
    if ( $this->_selector->matches($candidate, $g, $context, $trace) ) {
      if (count($this->_filters) == 0) {
        $matches = TRUE;
      }
      else {
        if ($trace) print "StepMatcher: Iterating through all filters\n";
        $filter_passes = 0;

        $filter_resources = $this->get_candidates(array($candidate), $g, $trace);
        foreach ( $this->_filters as $filter) {
          if ($trace) print "StepMatcher: Trying " . $filter->to_string() . "\n";
          if ($filter->matches($filter_resources, $g, $candidate, $trace)) {
            $filter_passes++;
          }
        }
        if ( $filter_passes == count($this->_filters)) {
          $matches = TRUE;
        }
      }
    }

    if ($trace) print "StepMatcher: " . ( $matches ? 'MATCHED' : 'NO MATCH') . " using " . $this->to_string() . "\n";
    return $matches;
  }

  function get_candidates($resources, &$g, $trace = FALSE) {
    $candidates = array();
    foreach ($resources as $resource) {
      if ($resource['type'] != 'literal') {
        if (isset($resource['node'])) {
          if ($trace) print "Path: Selecting nodes that are values of " . $resource['value'] . "\n";
          $candidates = array_merge($candidates, $this->get_nodes($resource, $g));
        }
        else {
          if ($trace) print "Path: Selecting arcs that are properties of " . $resource['value'] . "\n";
          $candidates = array_merge($candidates, $this->get_arcs($resource, $g));
        }
      }
    }

    if ($trace) print "Path: Selected " . count($candidates) . " candidates\n";
    return $candidates;
  }



  function get_arcs(&$node, &$g) {
    $arcs = array();
    $properties = $g->get_subject_properties($node['value'], FALSE);
    foreach ($properties as $property_uri) {
      $info = $g->make_resource_array($property_uri);
      $info['node'] = $node['value'];
      $arcs[] = $info;
    }
    return $arcs;
  }

  function get_nodes(&$arc, &$g) {
    return $g->get_subject_property_values($arc['node'], $arc['value'], $g);
  }



  function to_string() {
    $ret = '';
    if ($this->_axis && $this->_axis != 'out') {
      $ret .= $this->_axis . '::';
    }
    $ret .= $this->_selector->to_string();
    foreach ( $this->_filters as $filter) {
      $ret .= "[" . $filter->to_string() . "]";
    }
    return $ret;
  }

}


/**
 * @access private
 */
class OrExpr {
  var $_left = null;
  var $_right = null;
  function __construct($left, $right = null) {
    $this->_left = $left;
    $this->_right = $right;
  }

  function matches($candidates, &$g, $context, $trace = FALSE) {
    $match = FALSE;


    if ($this->_left->matches($candidates, $g, $context, $trace)) {
      $match = TRUE;
    }
    else if ($this->_right && $this->_right->matches($candidates, $g, $context, $trace)) {
      $match = TRUE;
    }
    if ($trace && $this->_right) print "OrExpr: " . ( $match ? 'MATCHED' : 'NO MATCH') . "\n";
    return $match;
  }

  function to_string() {
    $ret = $this->_left->to_string();
    if ($this->_right) $ret .= ' or ' . $this->_right->to_string();
    return $ret;
  }

}

/**
 * @access private
 */
class AndExpr {
  var $_left = null;
  var $_right = null;

  function __construct($left, $right = null) {
    $this->_left = $left;
    $this->_right = $right;
  }

  function matches($candidates, &$g, $context, $trace = FALSE) {
    $match = FALSE;

    if ($this->_left->matches($candidates, $g, $context, $trace)) {
      $match = FALSE;
      if ($this->_right) {
        if ($this->_right->matches($candidates, $g, $context, $trace)) {
          $match = TRUE;
        }
      }
      else {
        $match = TRUE;
      }
    }

    if ($trace && $this->_right) print "AndExpr: " . ( $match ? 'MATCHED' : 'NO MATCH') . "\n";
    return $match;
  }

  function to_string() {
    $ret = $this->_left->to_string();
    if ($this->_right) $ret .= ' and ' . $this->_right->to_string();
    return $ret;
  }

}


/**
 * @access private
 */
class CompExpr {
  var $_left = null;
  var $_operator = null;
  var $_right = null;
  function __construct($left, $operator = null, $right = null) {
    $this->_left = $left;
    $this->_operator = $operator;
    $this->_right = $right;
  }

  function matches($candidates, &$g, $context, $trace = FALSE) {
    $matches = FALSE;

    if ($trace) print "CompExpr: Selecting resources using left of " . $this->_left->to_string() . "\n";
    $selected = $this->_left->select($candidates, $g, $context, $trace);

    if ( $this->_operator && $this->_right) {
      if ($trace) print "CompExpr: Selecting resources using right of " . $this->_right->to_string() . "\n";
      $selected_right = $this->_right->select($candidates, $g, $context, $trace);

      if (is_array($selected) ) {
        if ($trace) print "CompExpr: Left of comparison selected a set of " . count($selected) . " resources\n";
        if (is_array($selected_right)) {
          if ($trace) print "CompExpr: Right of comparison selected " . count($selected_right) . " resources\n";
          $matches = $this->compare_list_to_list($selected, $selected_right);
        }
        else if (is_bool($selected_right)) {
          if ($trace) print "CompExpr: Right of comparison selected a boolean of value " . $selected_right . "\n";
          $matches = $this->compare_list_to_boolean($selected, $selected_right);
        }
        else if (is_numeric($selected_right)) {
          if ($trace) print "CompExpr: Right of comparison selected a number of value " . $selected_right . "\n";
          $matches = $this->compare_list_to_numeric($selected, $selected_right);
        }
        else if (is_string($selected_right)) {
          if ($trace) print "CompExpr: Right of comparison selected a string of value " . $selected_right . "\n";
          $matches = $this->compare_list_to_string($selected, $selected_right);
        }
      }
      else if (is_bool($selected)) {
        if ($trace) print "CompExpr: Left of comparison selected a boolean of value " . $selected . "\n";

        if (is_array($selected_right)) {
          if ($trace) print "CompExpr: Right of comparison selected a set of " . count($selected_right) . " resources\n";
          $matches = $this->compare_list_to_boolean($selected_right, $selected);
        }
        else if (is_bool($selected_right)) {
          if ($trace) print "CompExpr: Right of comparison selected a boolean of value " . $selected_right . "\n";
          if ( ($selected_right && $selected) || (!$selected_right && !$selected)) {
            $matches = TRUE;
          }
        }
        else if (is_numeric($selected_right)) {
          if ($trace) print "CompExpr: Right of comparison selected a number of value " . $selected_right . "\n";
          // TODO
        }
        else if (is_string($selected_right)) {
          if ($trace) print "CompExpr: Right of comparison selected a string of value " . $selected_right . "\n";
          $matches = $this->compare_boolean_to_string($selected, $selected_right);
        }
      }
      else if (is_numeric($selected)) {
        if ($trace) print "CompExpr: Left of comparison selected a number of value " . $selected . "\n";
        if (is_array($selected_right)) {
          if ($trace) print "CompExpr: Right of comparison selected a set of " . count($selected_right) . " resources\n";
          $matches = $this->compare_list_to_numeric($selected_right, $selected);
        }
        else if (is_bool($selected_right)) {
          if ($trace) print "CompExpr: Right of comparison selected a boolean of value " . $selected_right . "\n";
          // TODO
        }
        else if (is_numeric($selected_right)) {
          if ($trace) print "CompExpr: Right of comparison selected a number of value " . $selected_right . "\n";
          $matches = ($selected == $selected_right);
        }
        else if (is_string($selected_right)) {
          if ($trace) print "CompExpr: Right of comparison selected a string of value " . $selected_right . "\n";
          // TODO
        }
      }
      else if (is_string($selected)) {
        if ($trace) print "CompExpr: Left of comparison selected a string of value " . $selected_right . "\n";
        if (is_array($selected_right)) {
          if ($trace) print "CompExpr: Right of comparison selected a set of " . count($selected_right) . " resources\n";
          $matches = $this->compare_list_to_string($selected_right, $selected);
        }
        else if (is_bool($selected_right)) {
          if ($trace) print "CompExpr: Right of comparison selected a boolean of value " . $selected_right . "\n";
          $matches = $this->compare_boolean_to_string($selected_right, $selected);
        }
        else if (is_numeric($selected_right)) {
          if ($trace) print "CompExpr: Right of comparison selected a number of value " . $selected_right . "\n";
          // TODO
        }
        else if (is_string($selected_right)) {
          if ($trace) print "CompExpr: Right of comparison selected a string of value " . $selected_right . "\n";
          $matches = ($selected == $selected_right);
        }
      }

    }
    else {
      $matches = Converter::to_boolean($selected);
    }

    if ($trace) print "CompExpr: " . ( $matches ? 'MATCHED' : 'NO MATCH') . " using " . $this->to_string() . "\n";
    return $matches;
  }

  function compare_list_to_list($list1, $list2) {
    if (count($list1) > 0 && count($list2) > 0) {
      if ($this->_operator == '=') {
        foreach ($list1 as $selected_resource) {
          if (in_array($selected_resource, $list2)) {
            return TRUE;
          }
        }
      }
    }
    return FALSE;
  }

  function compare_list_to_boolean($list, $boolean) {
    $list_bool = Converter::to_boolean($list);
    return ( ($boolean && $list_bool) || (!$boolean && !$list_bool));
  }

  function compare_list_to_numeric($list, $numeric) {
    foreach ($list as $resource) {
      if ($resource['type'] == 'literal' && is_numeric($resource['value']) ) {
        if ($resource['value'] == $numeric) {
          return TRUE;
        }
      }
    }
    return false;
  }

  function compare_list_to_string($list, $string) {
    foreach ($list as $resource) {
      if ($resource['type'] == 'literal') {
        if ($resource['value'] == $string) {
          return TRUE;
        }
      }
    }
    return false;
  }

  function compare_boolean_to_string($boolean, $string) {
    $string_bool = Converter::to_boolean($string);
    return ( ($boolean && $string_bool) || (!$boolean && !$string_bool));
  }


  function to_string() {
    $ret = $this->_left->to_string();
    if ( $this->_operator && $this->_right) {
      $ret .= ' ' . $this->_operator . ' ' . $this->_right->to_string();
    }
    return $ret;
  }

}


/**
 * @access private
 */
class LiteralGenerator {
  var $_text = '';
  var $_dt = '';
  function __construct($text,$dt = null) {
    $this->_text = $text;
    $this->_dt = $dt;
  }

  function select($candidates, &$g, $context, $trace = FALSE) {
    //return $this->_text;
    return array( array('type'=>'literal', 'value'=>$this->_text));
  }

  function to_string() {
    $ret = "'" . $this->_text. "'";
    return $ret;
  }
}


/**
 * @access private
 */
class NumberGenerator {
  var $_number = '';
  function __construct($number_text) {
    $this->_number = $number_text;
  }

  function select($candidates, &$g, $context, $trace = FALSE) {
    return $this->_number;
  }

  function to_string() {
    $ret = "'" . $this->_number. "'";
    return $ret;
  }
}

/**
 * @access private
 */
class SelfGenerator {

  function select($candidates, &$g, $context, $trace = FALSE) {
    return array($context);
  }

  function to_string() {
    return '.';
  }
}

/**
 * @access private
 */
class BooleanGenerator {
  var $_value = '';
  function __construct($value) {
    $this->_value = $value;
  }

  function select($candidates, &$g, $context, $trace = FALSE) {
    return $this->_value;
  }

  function to_string() {
    return ($this->_value ? 'true()' : 'false()');
  }
}


/**
 * @access private
 */
class CountFunction {
  var $_arg;

  function __construct($arg) {
    $this->_arg = $arg;
  }


  function select($candidates, &$g, $context, $trace = FALSE) {
    if ($trace) print "CountFunction: Counting number of resources selected by " . $this->_arg->to_string() . "\n";
    $selected = $this->_arg->select($candidates, $g, $context, $trace);
    if ($trace) print "CountFunction: Selected " . count($selected) . " resources\n";
    return array(array('type'=>'literal', 'value'=> count($selected)));
  }

  function to_string() {
    return 'count(' .$this->_arg->to_string() . ')';
  }
}


/**
 * @access private
 */
class LocalNameFunction {
  var $_arg;

  function __construct($arg) {
    $this->_arg = $arg;
  }

  function select($candidates, &$g, $context, $trace = FALSE) {
    $selected = $this->_arg->select($candidates, $g, $context, $trace);
    if (count($selected) > 0) {
      if ( $selected[0]['type'] == 'uri') {
        if ($trace) print "LocalNameFunction: Determining local name of " . $selected[0]['value'] . "\n";
        if (preg_match('~^(.*[\/\#])([a-z0-9\-\_]+)$~i', $selected[0]['value'], $m)) {
          if ($trace) print "LocalNameFunction: Selected local name of " . $m[2] . "\n";
          return array(array('type'=>'literal', 'value'=> $m[2]));
        }
      }
    }
    return array();
  }

  function to_string() {
    return 'local-name(' .$this->_arg->to_string() . ')';
  }
}

/**
 * @access private
 */
class NamespaceUriFunction {
  var $_arg;

  function __construct($arg) {
    $this->_arg = $arg;
  }

  function select($candidates, &$g, $context, $trace = FALSE) {
    $selected = $this->_arg->select($candidates, $g, $context, $trace);
    if (count($selected) > 0) {
      if ( $selected[0]['type'] == 'uri') {
        if ($trace) print "NamespaceUriFunction: Determining namespace URI of " . $selected[0]['value'] . "\n";
        if (preg_match('~^(.*[\/\#])([a-z0-9\-\_]+)$~i', $selected[0]['value'], $m)) {
          if ($trace) print "NamespaceUriFunction: Selected namespace URI of " . $m[1] . "\n";
          return array(array('type'=>'literal', 'value'=> $m[1]));
        }
      }
    }
    return array();
  }

  function to_string() {
    return 'namespace-uri(' .$this->_arg->to_string() . ')';
  }
}


/**
 * @access private
 */
class UriFunction {
  var $_arg;

  function __construct($arg) {
    $this->_arg = $arg;
  }

  function select($candidates, &$g, $context, $trace = FALSE) {
    $selected = $this->_arg->select($candidates, $g, $context, $trace);
    if (count($selected) > 0) {
      if ( $selected[0]['type'] == 'uri') {
        if ($trace) print "UriFunction: Selected URI of " . $selected[0]['value'] . "\n";
        return array(array('type'=>'literal', 'value'=> $selected[0]['value']));
      }
    }
    return array();
  }

  function to_string() {
    return 'uri(' .$this->_arg->to_string() . ')';
  }
}

/**
 * @access private
 */
class ExpFunction {
  var $_arg;

  function __construct($arg) {
    $this->_arg = $arg;
  }

  function select($candidates, &$g, $context, $trace = FALSE) {
    $selected = $this->_arg->select($candidates, $g, $context, $trace);
    if ( count($selected) > 0 && $selected[0]['type'] == 'literal') {
      if ($trace) print "ExpFunction: Attempting to expand " . $this->_arg . " to URI\n";
      $uri = $g->qname_to_uri($selected[0]['value']);
      if ($uri != null) {
        if ($trace) print "ExpFunction: Expanded to " . $uri . "\n";
        return array(array('type'=>'literal', 'value'=> $uri));
      }
    }
    if ($trace) print "ExpFunction: Could not expand\n";
    return array();
  }

  function to_string() {
    return 'exp(\'' .$this->_arg->to_string() . '\')';
  }
}


/**
 * @access private
 */
class LiteralValueFunction {
  var $_arg;

  function __construct($arg) {
    $this->_arg = $arg;
  }

  function select($candidates, &$g, $context, $trace = FALSE) {
    if ($trace) print "LiteralValueFunction: Using " . $this->_arg->to_string() . " to determine literal value\n";
    $selected = $this->_arg->select($candidates, $g, $context, $trace);
    if (count($selected) > 0 && isset($selected[0]['node'])) {
      $values = $g->get_subject_property_values($selected[0]['node'], $selected[0]['value']);
      if (count($values) > 0) {
        if ( $values[0]['type'] == 'literal') {
          if ($trace) print "LiteralValueFunction: Selected value of " . $values[0]['value'] . "\n";
          return $values[0]['value'];
        }
      }
    }
    return '';
  }

  function to_string() {
    return 'literal-value(' .$this->_arg->to_string() . ')';
  }
}

/**
 * @access private
 */
class LiteralDtFunction {
  var $_arg;

  function __construct($arg) {
    $this->_arg = $arg;
  }

  function select($candidates, &$g, $context, $trace = FALSE) {
    if ($trace) print "LiteralDtFunction: Using " . $this->_arg->to_string() . " to determine literal datatype\n";
    $selected = $this->_arg->select($candidates, $g, $context, $trace);
    if (count($selected) > 0 && isset($selected[0]['node'])) {
      $values = $g->get_subject_property_values($selected[0]['node'], $selected[0]['value']);
      if (count($values) > 0) {
        if ( $values[0]['type'] == 'literal' && isset($values[0]['datatype'])) {
          if ($trace) print "LiteralDtFunction: Selected datatype of " . $values[0]['datatype'] . "\n";
          return $values[0]['datatype'];
        }
      }
    }
    return '';
  }

  function to_string() {
    return 'literal-dt(' .$this->_arg->to_string() . ')';
  }
}


/**
 * @access private
 */
class StringLengthFunction {
  var $_arg;

  function __construct($arg) {
    $this->_arg = $arg;
  }

  function select($candidates, &$g, $context, $trace = FALSE) {
    if ($trace) print "StringLengthFunction: Finding string length of " . $this->_arg->to_string() . "\n";
    $selected = $this->_arg->select($candidates, $g, $context, $trace);
    if (is_string($selected)) {
      if ($trace) print "StringLengthFunction: String length of " . $selected . " is " .  strlen($selected) . "\n";
      return strlen($selected);
    }
    else {
      $this->add_error($this->to_string() . " expected a string as an argument but did not receive one");
    }
    return array();
  }

  function to_string() {
    return 'string-length(' .$this->_arg->to_string() . ')';
  }
}


/**
 * @access private
 */
class NormalizeSpaceFunction {
  var $_arg;

  function __construct($arg) {
    $this->_arg = $arg;
  }

  function select($candidates, &$g, $context, $trace = FALSE) {
    $selected = $this->_arg->select($candidates, $g, $context, $trace);
    if (is_string($selected)) {
      $val = preg_replace("~\s+~m", ' ', $selected);
      return trim($val);
    }
    else {
      $this->add_error($this->to_string() . " expected a string as an argument but did not receive one");
    }
    return array();
  }

  function to_string() {
    return 'normalize-space(' .$this->_arg->to_string() . ')';
  }
}

/**
 * @access private
 */
class BooleanFunction {
  var $_arg;

  function __construct($arg) {
    $this->_arg = $arg;
  }

  function select($candidates, &$g, $context, $trace = FALSE) {
    $selected = $this->_arg->select($candidates, $g, $context, $trace);
    return Converter::to_boolean($selected);
  }

  function to_string() {
    return 'boolean(' .$this->_arg->to_string() . ')';
  }
}


/**
 * Implements FSL specific conversions
 * @access private
 */
class Converter {
  function to_boolean($arg) {
    if (is_array($arg)) {
      return (count($arg) != 0);
    }
    else if (is_string($arg)) {
      return (strlen($arg) != 0);
    }
  }
}
?>