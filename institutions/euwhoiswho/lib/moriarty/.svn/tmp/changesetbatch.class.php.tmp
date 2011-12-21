<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'constants.inc.php';
require_once MORIARTY_ARC_DIR . "ARC2.php";
require_once MORIARTY_DIR . "changeset.class.php";

/**
 * Represents a batch of changesets. Can be used to create changesets based on the difference between two graphs.
 * @see http://n2.talis.com/wiki/Metabox#Batch_Updating
 */


class ChangeSetBatch  {

      var $a;
	  var $utils;
	  var $before;
	  var $after;
	  var $subjectIndex = array();
	  var $__index = array();

	  function __construct($a = array()) {
		$this->a = $a;
		/* parse the before and after graphs if necessary*/
		foreach(array('before','after') as $rdf){
			if(!is_array($a[$rdf]) AND !empty($a[$rdf])){
				$parser = ARC2::getRDFParser();
				$parser->parse(false, $a[$rdf]);
				$a[$rdf] = $parser->getSimpleIndex(0);
			}
			$this->$rdf = ($a[$rdf])? $a[$rdf] : array();
		}

	  }

	  function ChangeSetBatch ($a = array()) {
	    $this->__construct($a);
	  }

	  function __init() {
		$csIndex = array();
		$CSNS = 'http://purl.org/vocab/changeset/schema#';
		$utils = ARC2::getComponent('ARC2_IndexUtilsPlugin');

		// Get the triples to be added
		$additions = !empty($this->before)? $utils->diff($this->after, $this->before) : $this->after;
		//Get the triples to be removed
		$removals = !empty($this->after)? $utils->diff($this->before, $this->after) : $this->before;
		// Get an array of all the subject uris
		$subjectIndex = array_merge(array_keys($this->before), array_keys($this->after));

		// Get the metadata for all the changesets
		$date  = (!empty($this->a['createdDate']))? $this->a['createdDate'] : date(DATE_ATOM);
		$creator  = (!empty($this->a['creatorName']))? $this->a['creatorName'] : 'Talis ChangeSet Builder plugin';
		$reason  = (!empty($this->a['changeReason']))? $this->a['changeReason'] : 'Change using Talis ChangeSet Builder plugin';


		// for every subject uri, create a new changeset
		$n = count($subjectIndex);

		for ($i=0 ; $i < $n; $i++) { 
			$csID = '_:cs'.$i;
			$csIndex[$subjectIndex[$i]] = $csID;
			$this->addT($csID, 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', $CSNS.'ChangeSet', 'uri');
			$this->addT($csID, $CSNS.'subjectOfChange', $subjectIndex[$i], 'uri');
			$this->addT($csID, $CSNS.'createdDate', $date, 'literal');
			$this->addT($csID, $CSNS.'creatorName', $creator, 'literal');
			$this->addT($csID, $CSNS.'changeReason', $reason, 'literal');

			/* add extra user-given properties to each changeset*/					
			if(!empty($this->a['properties'])){
				foreach ($this->a['properties'] as $p => $objs) $this->addT($csID, $p, $objs);
			}
		}
		/*iterate through the triples to be added, 
		reifying them, 
		and linking to the Statements from the appropriate changeset
		*/
		$reifiedAdditions = $utils->reify($additions, 'Add');
		foreach($reifiedAdditions as $nodeID => $props){
			$subject = $props['http://www.w3.org/1999/02/22-rdf-syntax-ns#subject'][0]['value'];
			$csID = $csIndex[$subject];
			$this->addT($csID, $CSNS.'addition', $nodeID, 'bnode');

			// if dc:source is given in the instantiating arguments, add it to the statement as provenance
				if(isset($this->a['http://purl.org/dc/terms/source'])){
				$this->addT($nodeID, 'http://purl.org/dc/terms/source', $this->a['http://purl.org/dc/terms/source'], 'uri');
				}



		}

		/*iterate through the triples to be removed, 
		reifying them, 
		and linking to the Statements from the appropriate changeset
		*/


		$reifiedRemovals = $utils->reify($removals, 'Remove');

		foreach($reifiedRemovals as $nodeID => $props){
			$subject = $props['http://www.w3.org/1999/02/22-rdf-syntax-ns#subject'][0]['value'];
			$csID = $csIndex[$subject];
			$this->addT($csID, $CSNS.'removal', $nodeID, 'bnode');
		}

		foreach($this->__index as $uri => $props){
			if(
					!isset($props[$CSNS.'removal']) 
					AND 
					!isset($props[$CSNS.'addition'])
					){
						unset($this->__index[$uri]);
				}

		}
		$this->__index = $utils->merge($this->__index, $reifiedAdditions, $reifiedRemovals);		

			    parent::__init();

	  }

/**
 * addT
 * adds a triple to the internal simpleIndex holding all the changesets and statements
 * @return void
 * @author Keith
 **/		
	  function addT($s, $p, $o, $o_type='bnode'){
		if(is_array($o) AND isset($o[0]['type'])){
			foreach($o as $obj){ 
				$this->addT($s, $p, $obj ); 
			}
		}else {
			$obj = !is_array($o)? array('value' => $o, 'type'=> $o_type) : $o ;
			$this->__index[$s][$p][]=$obj;
		}
	  }

	  function toRDFXML(){
		$ser = ARC2::getRDFXMLSerializer();
		return $ser->getSerializedIndex($this->__index);
	  }

	  function to_rdfxml(){
		return $this->toRDFXML();
	}

	function hasChanges(){
		return !empty($this->__index);
	}
}
?>
