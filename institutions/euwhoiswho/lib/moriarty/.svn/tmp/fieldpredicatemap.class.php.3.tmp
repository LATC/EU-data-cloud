<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'moriarty.inc.php';
require_once MORIARTY_DIR . 'networkresource.class.php';

/**
 * Represents a field/predicate map
 * @see http://n2.talis.com/wiki/Field_Predicate_Map
 */
class FieldPredicateMap extends NetworkResource {

  /**
   * Create a new instance of this class
   * @param string uri URI of the field/predicate map
   * @param Credentials credentials the credentials to use for authenticated requests (optional)
   */ 
  function __construct($uri, $credentials = null) {
    parent::__construct($uri, $credentials);
  }

  /**
   * Add a mapping between a predicate URI and a short name. Returns the URI of the new mapping.
   * @param string p the URI of the predicate to map
   * @param string name the short name to assign to the predicate
   * @param string analyzer the URI of the analyzer to apply to this predicate (optional)
   * @return string
   */
  function add_mapping($p, $name, $analyzer = null) {
    $mapping_uri = $this->uri . '#' . $name;
    $this->add_resource_triple( $this->uri, FRM_MAPPEDDATATYPEPROPERTY, $mapping_uri);
    $this->add_resource_triple( $mapping_uri, FRM_PROPERTY, $p);
    $this->add_literal_triple( $mapping_uri, FRM_NAME, $name);
    if ( $analyzer ) {
      $this->add_resource_triple( $mapping_uri, BF_ANALYZER, $analyzer);
    }
    return $mapping_uri;
  }

  /**
   * Remove a mapping between a predicate URI and a short name.
   * @param string p the URI of the predicate being mapped
   * @param string name the short name assigned to the predicate
   */
  function remove_mapping($p, $name) {
    $index = $this->get_index();
    foreach ($index[$this->uri][FRM_MAPPEDDATATYPEPROPERTY] as $mapping) {
      if (($mapping['type'] == 'uri' || $mapping['type'] == 'bnode') && isset($index[$mapping['value']]) ) {
        $candidate_mapping_uri = $mapping['value'];
        foreach ( $index[$candidate_mapping_uri][FRM_PROPERTY] as $mapped_property_info) {
          if ( ($mapped_property_info['type'] == 'uri' || $mapped_property_info['type'] == 'bnode') && $mapped_property_info['value'] == $p) {
            foreach ( $index[$candidate_mapping_uri][FRM_NAME] as $mapped_name_info) {
              if ( ($mapped_name_info['type'] != 'uri' && $mapped_name_info['type'] != 'bnode') && $mapped_name_info['value'] == $name) {
                $this->remove_resource_triple( $this->uri, FRM_MAPPEDDATATYPEPROPERTY, $candidate_mapping_uri);
                $this->remove_triples_about($candidate_mapping_uri);
              }
            }
          }
        }
      }
    }
  }

  /**
   * Copies the mappings and other properties into new field/predicate map.
   * Any URIs that are prefixed by the source field/predicate map's URI will be converted to
   * be prefixed with this field/predicate map's URI
   *
   * For example<br/>
   *   http://example.org/source/fpmaps/1#name<br/>
   * Would become<br/>
   *   http://example.org/destination/fpmaps/1#name<br/>
   *
   * @return FieldPredicateMap
   * @author Ian Davis
   **/
  function copy_to($new_uri) {
    $res = new FieldPredicateMap($new_uri, $this->credentials);
    $index = $this->get_index();

    foreach ($index as $uri => $uri_info) {
      $subject_uri = preg_replace('/^' . preg_quote($this->uri, '/') . '(.*)$/', $res->uri . '$1', $uri);
      foreach ($uri_info as $res_property_uri => $res_property_values) {
        foreach ($res_property_values as $res_property_info) {
          if ( $res_property_info['type'] == 'uri') {
            $value_uri = preg_replace('/^' . preg_quote($this->uri, '/') . '(.+)$/', $res->uri . '$1', $res_property_info['value']);
            $res->add_resource_triple( $subject_uri, $res_property_uri, $value_uri );
          }
          elseif ( $res_property_info['type'] == 'bnode') {
            $res->add_resource_triple( $subject_uri, $res_property_uri, $res_property_info['value'] );
          }
          else {
            $res->add_literal_triple( $subject_uri, $res_property_uri, $res_property_info['value'] );
          }
        }
      }
    }
    return $res;

  }
}
?>