<?php
require_once 'graphs/pueliagraph.class.php';

class LinkedDataApiGraph extends PueliaGraph {
    
    private $_config;
    private $_usedProperties;
    var $_current_page_uri = false;
    private $_nodeCounter = 1;
    var $_primary_resource=null;
    function __construct($a, $config){
        $this->_config = $config;
        $this->_usedProperties = array();
        date_default_timezone_set('UTC');
        parent::__construct($a);
    }
    

    public function add_rdf($rdf, $base=false){
      $return = parent::add_rdf($rdf, $base);
      foreach($this->get_subjects() as $s){
        $this->remove_property_values($s, 'http://schemas.talis.com/2005/dir/schema#etag');
      }
      return $return;
    }

    private function getNewNodeNumber(){
        return $this->_nodeCounter++;
    }
    
    function getConfigGraph(){
        return $this->_config;
    }
    
    function getVocabularyGraph(){
        return $this->getConfigGraph()->getVocabularyGraph();
    }
    
    function to_simple_json($pageUri){
        $this->_current_page_uri = $pageUri;
        $container = array(
            'format' => 'linked-data-api',
            "version" => "0.2",
            "result" => $this->_resource_to_simple_json_object($pageUri, $pageUri, false, array()),
        );
        $index = $this->get_index();
        $nodeCounter = 1;
        $bnodeIdIndex = array();
        $this->_current_page_uri = false;
        return json_encode($container);
    }
    
    function _resource_to_simple_json_object($uri, $subjectUri, $propertyUri=false, $parentUris){
         if(
                !in_array($uri, $parentUris)
        )
        {
        $parentUris[]=$uri;        
        $index = $this->get_index();
        $properties = $this->get_subject_properties($uri);
        $resource = array();
        if($this->node_is_blank($uri)){
            if(count($this->get_subjects_where_object($uri)) > 1){
                $bnodeNewId = 'node'.$this->getNewNodeNumber();
                $bnodeIdIndex[$uri] = $bnodeNewId;
                $resource['_id'] = $uri;
            }
        } else if(!empty($uri)) {
            $resource['_about'] = $uri;
        }
        sort($properties);
        foreach($properties as $propertyUri){
                $objects = $index[$uri][$propertyUri];
                $jsonPropertyName = $this->get_short_name_for_uri($propertyUri);
                $val = $this->get_simple_json_property_value($objects, $propertyUri, $subjectUri, $parentUris);
                $resource[$jsonPropertyName] = $val;
        }

        if(!empty($resource)) return $resource;
    } else {
        return $uri;
    }
    
}
    
    
    function get_simple_json_property_value($objects, $propertyUri, $subjectUri, $parentUris){

        if(count($objects) > 1 OR $this->propertyIsMultiValued($propertyUri)){
            $returnArray = array();
            foreach($objects as $object){
                $val = $this->map_rdf_value_to_json_value($object, $propertyUri, $subjectUri, $parentUris);
                if($val!==null) $returnArray[]=$val;
            }
            return $returnArray;
        } else {
            return $this->map_rdf_value_to_json_value($objects[0], $propertyUri, $subjectUri, $parentUris);
        }
    }
    
    function map_rdf_value_to_json_value($object, $propertyUri, $subjectUri, $parentUris){
        $target = array();
        
        if($object['type']!=='literal') $subject_properties = $this->get_subject_properties($object['value']);
        
        $xsd_numeric_datatypes = array(
                                XSD.'integer', 
                                XSD.'float',
                                XSD.'decimal',
                                XSD.'double',
                                XSD.'int',
                                XSD.'byte',
                                XSD.'long',
                                XSD.'short',
                                XSD.'negativeInteger',
                                XSD.'nonNegativeInteger',
                                XSD.'nonPositiveInteger',
                                XSD.'positiveInteger',
                                XSD.'unsignedLong',
                                XSD.'unsignedShort',
                                XSD.'unsignedInt',
                                XSD.'unsignedByte',
                            );
        if( $this->getConfigGraph()->get_first_literal($propertyUri, API.'structured')=='true'
        OR
        $this->getVocabularyGraph()->get_first_literal($propertyUri, API.'structured')=='true'){
            $target['_value'] = $object['value'];
            if(!empty($object['lang'])){
                $target['_lang'] = $object['lang'];
            } else if(!empty($object['datatype'])){
                $target['_datatype'] = array_pop(preg_split('@/|#@', $object['datatype']));
            }
            return $target;
        } else if(isset($object['datatype']) AND $object['datatype']==XSD.'boolean'){
            $target= (strtolower($object['value']) == 'true');
        } else if(isset($object['datatype']) AND in_array($object['datatype'], $xsd_numeric_datatypes) AND is_numeric($object['value'])){
            $target = $object['value']+0;
        } else if(isset($object['datatype']) AND $object['datatype'] == XSD.'dateTime'){
            $target = date(DATE_COOKIE, strtotime($object['value']));
        } else if(isset($object['datatype']) AND $object['datatype'] == XSD.'date'){
            $target = date(DATE_W3C, strtotime($object['value']));
        } else if($this->has_resource_triple($object['value'], RDF_TYPE, RDF_LIST)){
            $target = array();
            $listValues = $this->list_to_array($object['value']);
            foreach($listValues as $listUri){
                $val = $this->_resource_to_simple_json_object($listUri, $subjectUri, $propertyUri, $parentUris);
                if($val!==null) $target[]=$val;
            }
        } else if(!empty($subject_properties)){
            $target=$this->_resource_to_simple_json_object($object['value'], $subjectUri, $propertyUri, $parentUris);
            
        } else if($object['type'] =='bnode' AND empty($subject_properties)) {
            $target = new BlankObject();
        } else {
            $target = $object['value'];
        }
        return $target;
    }
    
    function is_legal_short_name($shortName){
        return (!empty($shortName) AND preg_match('/^[a-zA-Z_0-9][a-zA-Z_0-9]+$/', $shortName));
    }
    
    function get_short_name_for_uri($propertyUri){
        if(
            $name = $this->get_first_literal($propertyUri, API.'label')
            OR
            $name = $this->_config->get_first_literal($propertyUri, API.'label')
        ){
             $this->_usedProperties[$name] = $propertyUri;
             return $name;
        } else if($name = $this->get_label($propertyUri) and $this->is_legal_short_name($name)){
            $this->_usedProperties[$name] = $propertyUri;
            return $name;
        }
        
        $mappings = $this->getConfigGraph()->getVocabPropertyLabels();
        $mappings = array_flip($mappings);
        if(isset($mappings[$propertyUri]) AND $this->is_legal_short_name($mappings[$propertyUri])){
            $this->_usedProperties[$mappings[$propertyUri]] = $propertyUri;
            return $mappings[$propertyUri];
        } else {
            preg_match('@^(.+[#/])([^#/]+)$@', $propertyUri, $m);
            $ns = $m[1];
            $localName = $m[2];
            if(!in_array($localName, array_values($mappings)) AND $this->is_legal_short_name($localName)){
                $this->_usedProperties[$localName] = $propertyUri;
                return $localName;
            } else {
                $prefix = $this->get_prefix($ns);
                $name = $prefix.'_'.$localName;
                if($this->is_legal_short_name($name)) {
                  $this->_usedProperties[$name] = $propertyUri;
                  return $name;
                } else {
                    logDebug("{$propertyUri} has no legal short name");
                    $name = preg_replace('/[^a-zA-Z0-9_]/','_', $name);
                    $this->_usedProperties[$name] = $propertyUri;
                    return $name;
                }
            }
        }
    }
    
    function node_is_blank($node){
        return (strpos($node, '_:')===0);
    }
    
    function get_subjects_where_object($object){
        $subjects = array();
        $index = $this->get_index();
        foreach($index as $uri => $properties){
             foreach($properties as $p => $objs){
                 foreach($objs as $o){
                     if($o['type'] == 'uri' && $object == $o['value']){
                         $subjects[]=$uri;
                     }
                 }
             } 
        }
        return $subjects;      
    }
    
    
    function to_simple_xml($pageUri){
        $this->_uris_processed_by_simple_xml = array();
        $this->_current_page_uri = $pageUri;
        $dom = new DOMDocument('1.0', 'utf-8');
        $resultEl = $dom->createElement('result');
        $format = $dom->createAttribute('format');
        $format->appendChild($dom->createTextNode('linked-data-api'));
        $version = $dom->createAttribute('version');
        $version->appendChild($dom->createTextNode('0.2'));
        $resultEl->appendChild($format);
        $resultEl->appendChild($version);   

        $resultEl = $this->write_resource_on_xml_element($dom, $pageUri, $resultEl, array());
        $dom->appendChild($resultEl);
        
        
        $this->_current_page_uri = false;
        return $dom->saveXml();     
    }
    
    function write_resource_on_xml_element(DomDocument $dom, $uri, DomElement $el, $parentUris){

        /* resource ID */
        if($this->node_is_blank($uri)){
            $idAttr = $dom->createAttribute('id');
        } else {
            $idAttr = $dom->createAttribute('href');
        }
        $idAttr->appendChild($dom->createTextNode($uri));
        if(!empty($uri)) $el->appendChild($idAttr);
        $index = $this->get_index();
        $isPrimaryTopicSameAsResult = ($el->tagName == 'primaryTopic' AND count($parentUris) == 1);
        
        if(
            !in_array($uri, $parentUris) ||
            $isPrimaryTopicSameAsResult
            ){
            $resourceProperties = array(
              FOAF.'primaryTopic', 
              FOAF.'isPrimaryTopicOf', 
              API.'definition', 
              DCT.'hasFormat', 
              DCT.'hasVersion'
            );
            $parentUris[]=$uri;
            $properties = $this->get_subject_properties($uri);
            arsort($properties); //producing predictable output
            foreach($properties as $property){
                if (!$isPrimaryTopicSameAsResult || !in_array($property, $resourceProperties)) {
                    $propertyName = $this->get_short_name_for_uri($property);
                    $propEl = $dom->createElement($propertyName);
                    $propValues = $index[$uri][$property];
                    if(count($propValues) > 1 || $this->propertyIsMultiValued($property)){
                        foreach($propValues as $val){
                            $item = $dom->createElement('item');
                            $item = $this->write_rdf_value_to_xml_element($dom, $val, $item, $uri, $property, $parentUris);
                            $propEl->appendChild($item);
                        }
                    } else if (count($propValues) == 1) {
                        $val = $propValues[0];
                        $propEl = $this->write_rdf_value_to_xml_element($dom, $val, $propEl, $uri, $property, $parentUris);
                    }
                    $el->appendChild($propEl);
                }
            }
        }
        return $el;
    }
    
    private function write_rdf_value_to_xml_element(DomDocument $dom, $val, DomElement $el, $s, $p, $parentUris){
        if(in_array($val['type'], array('uri','bnode')) AND $this->resource_is_a_list($val['value'])) {
            $listValues = $this->list_to_array($val['value']);
            foreach($listValues as $itemURI){
                $item = $dom->createElement('item');
                $item = $this->write_resource_on_xml_element($dom, $itemURI, $item, $parentUris);
                $el->appendChild($item);
            }
        }
        else if($val['type']=='literal'){ 
            $el->appendChild($dom->createTextNode($val['value']));
            if(isset($val['lang'])){
                $xmllang = $dom->createAttribute('xml:lang');
                $xmllang->appendChild($dom->createTextNode($val['lang']));
                $el->appendChild($xmllang);
            } else if(isset($val['datatype'])){
                $shortname = $this->get_short_name_for_uri($val['datatype']);
                $datatypeAttr = $dom->createAttribute('datatype');
                $datatypeAttr->appendChild($dom->createTextNode($shortname));
                $el->appendChild($datatypeAttr);
            }
        } else if($val['type'] =='bnode' OR $val['type']=='uri'){
            
            $el = $this->write_resource_on_xml_element($dom, $val['value'], $el, $parentUris);
            
        } 
        return $el;
    }
    
    function get_count_of_id_mentions($id){
        $Count = count($this->get_subjects_where_object($id));
        if($this->has_triples_about($id)) $Count++;
        return $Count;
    }
    
   
  
  function propertyIsMultiValued($propertyUri){
      return (
          $this->getConfigGraph()->has_literal_triple($propertyUri, API.'multiValued', "true")
          OR
          $this->getVocabularyGraph()->has_literal_triple($propertyUri, API.'multiValued', "true")
          );
  }
  
  function resource_is_a_page_list_item($pageUri, $resourceUri){
      $itemsList = $this->get_first_resource($pageUri, API.'items');
      $itemsArray = $this->list_to_array($itemsList);
      return in_array($resourceUri, $itemsArray);
  }
  
  function get_all_predicates(){
      $predicates = array();
      foreach($this->get_index() as $uri => $props){
          foreach($props as $p) $predicates[]=$p;
      }
      $predicates = array_unique($predicates);
      sort($predicates);
      return $predicates;
  }
    
}

class BlankObject {}

?>
