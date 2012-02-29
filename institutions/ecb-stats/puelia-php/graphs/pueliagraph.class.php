<?php
require_once 'lib/moriarty/simplegraph.class.php';
class PueliaGraph extends SimpleGraph {
    
    var $PropertyLabels = false;
    
    function getPropertyLabels(){
        if($this->PropertyLabels) return $this->PropertyLabels;
        
        $PropertyLabels = array();
        $rdf_properties = $this->get_subjects();
        foreach($rdf_properties as $s){
            foreach(
                array_merge(
                    $this->get_literal_triple_values($s, API.'label'), 
                    $this->get_literal_triple_values($s, RDFS_LABEL)
                ) as $label){
                $PropertyLabels[$label]=$s;
            }
        }
        $this->PropertyLabels = $PropertyLabels;
        return $PropertyLabels;
    }
    
    function getUriForPropertyLabel($PropertyLabel){
        $PropertyLabels = $this->getPropertyLabels();
        if(isset($PropertyLabels[$PropertyLabel])){
            return $PropertyLabels[$PropertyLabel];
        } else {
            return false;
        }
    }
    
    function getPropertyRange($uri){
        return $this->get_first_resource($uri, RDFS_RANGE);
    }  
    function list_to_array($listUri){
        $array = array();
        while(!empty($listUri) AND $listUri != RDF.'nil'){
            $array[]=$this->get_first_resource($listUri, RDF_FIRST);
            $listUri = $this->get_first_resource($listUri, RDF_REST);
        }
        return $array;
    }
    
    function resource_is_a_list($uri){
        if($this->has_resource_triple($uri, RDF_TYPE, RDF_LIST)){
            return true;
        } else if($this->get_first_resource($uri, RDF_FIRST)){
            return true;
        } else {
            return false;
        }
    }
    
    function resource_is_first_list($uri){
        $parentLists = $this->get_subjects_where_resource(RDF_REST, $uri);
        return empty($parentLists);
    }
    
    function get_label($uri, $capitalize = false, $use_qnames = FALSE){
        if($label = $this->get_first_literal($uri, RDFS_LABEL)) return $label;
        if($label = $this->get_first_literal($uri, API.'name')) return $label;
        if($label = $this->get_first_literal($uri, API.'label')) return $label;
        else return parent::get_label($uri, $capitalize, $use_qnames);
    }
  
    
}


?>
