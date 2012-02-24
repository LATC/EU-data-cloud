<?php
require_once 'graphs/vocabularygraph.class.php';
class SparqlWriter {
    
    private $_config;
    private $_request;
    
    var $_unknownPropertiesFromRequestParameter = array();
    var $_unknownPropertiesFromConfig = array();
    
    function __construct($config, $request){
        $this->_config = $config;
        $this->_request = $request;
    }
    
    function addPrefixesToQuery($query){
    	  $prefixesString='';	
        $prefixes = $this->getConfigGraph()->getPrefixesFromLoadedTurtle();
	  preg_match_all('/([a-zA-Z_\-]+)\:[a-zA-Z0-9_\-]+/', $query, $matches);
	  foreach($matches[1] as $prefix){
		  if(isset($prefixes[$prefix])){
			$ns = $prefixes[$prefix];
			$prefixesString.="PREFIX {$prefix}: <{$ns}>\n";
			unset($prefixes[$prefix]);
		  }
	  }
	  return $prefixesString.$query;
    }
    
    function getLimit(){
        $maxPageSize = $this->getConfigGraph()->getMaxPageSize();
        $requestedPageSize = $this->_request->getParam('_pageSize');
        $endpointDefaultPageSize = $this->getConfigGraph()->getEndpointDefaultPageSize();
        $apiDefaultPageSize = $this->getConfigGraph()->getApiDefaultPageSize();
        if($maxPageSize && $requestedPageSize > $maxPageSize) return $apiDefaultPageSize;
        else if($requestedPageSize) return $requestedPageSize;
        else if($endpointDefaultPageSize) return $endpointDefaultPageSize;
        else if($apiDefaultPageSize) return $apiDefaultPageSize;
        else return 10;
    }
    
    function getDefaultSelectLangs(){
        $requestedDefaultLangs = $this->_request->getParam('_lang');
        $endpointDefaultLangs = $this->getConfigGraph()->getEndpointDefaultLangs();
        $apiDefaultLangs = $this->getConfigGraph()->getApiDefaultLangs();
        if ($requestedDefaultLangs) return explode(',', $requestedDefaultLangs);
        else if($endpointDefaultLangs) return explode(',', $endpointDefaultLangs);
        else if($apiDefaultLangs) return explode(',', $apiDefaultLangs);
        else return null;
    }
    
    function getSelectTemplate(){
        if($select = $this->_request->getParam('_select')){
            return $select;
        } else {
            return $this->getConfigGraph()->getSelectQuery();
        }
    }
    
    function getExplicitSelectQuery(){
        if($template = $this->getSelectTemplate()){
            $limit = $this->getLimit();
            $offset = $this->getOffset();

            $bindings = $this->getConfigGraph()->getAllProcessedVariableBindings();
            return $this->fillQueryTemplate($template, $bindings)." LIMIT {$limit} OFFSET {$offset}";            
        } else {
            return false;
        }
        
    }



    function variableBindingToSparqlTerm($props, $propertyUri=false){
        if(isset($props['type']) AND $props['type'] == RDFS.'Resource'){
            $sparqlVal = "<{$props['value']}>";
        } else {
            $sparqlVal = '"""'.$props['value'].'"""';
            if(isset($props['lang'])){
                $sparqlVal.='@'.$props['lang'];
            } else if(isset($props['datatype'])){
                $sparqlVal.='^^<'.$props['datatype'].'>';
            } else if(isset($props['type'])){
                $sparqlVal.='^^<'.$props['type'].'>';
            } else {
               $sparqlVal = $this->addDatatypeOrLangToLiteral($sparqlVal, $propertyUri);
               $sparqlVal = $sparqlVal[0]; 
            }
        }
        return $sparqlVal;
    }
    
    function getFromClause(){
      $graphNames = '';
        foreach($this->getConfigGraph()->getSparqlEndpointGraphs() as $graphUri){
          $graphNames.="FROM <{$graphUri}>\n";
        }
      return $graphNames;
    }
    
    function filterValueToSparqlTerm($val, $langs, $propertyUri){
        $varNames = $this->getConfigGraph()->variableNamesInValue($val);
        $bindings = $this->getConfigGraph()->getAllProcessedVariableBindings();
        if($varNames){
            foreach($varNames as $varName){
                if(isset($bindings[$varName])){
                    $binding = $bindings[$varName];
                    return array($this->variableBindingToSparqlTerm($binding, $propertyUri));
                } else {
                    throw new ConfigGraphException("The variable {$varName} has no binding");
                }
            }
        } else if($uri = $this->getConfigGraph()->getUriForVocabPropertyLabel($val)){
            $namespaces = $this->getConfigGraph()->getPrefixesFromLoadedTurtle();
            return array($this->qnameOrUri($uri, $namespaces));
        } else {
            $literal =  '"""'.$val.'"""';
            return $this->addDatatypeOrLangToLiteral($literal, $propertyUri, $langs);
        }
    }
    
    function addDatatypeOrLangToLiteral($literal, $propertyUri=false, $langs=null){
        if($propertyUri){
            if($propertyRange = $this->getConfigGraph()->getVocabPropertyRange($propertyUri) AND $propertyRange!=RDFS_LITERAL){
                $literal .= '^^<'.$propertyRange.'>';
                return array($literal);
            } else {
                return $this->addLangToLiteral($literal, $langs);
            }
        } else {
          return $this->addLangToLiteral($literal, $langs);
        }
    }
    
    function addLangToLiteral($literal, $langs){
        if ($langs){
          $literals = array();
          foreach($langs as $lang) {
            $literals[] = $literal.'@'.$lang;
          }
          return $literals;
        } else {
          return array($literal);
        }
    }
    
    function fillQueryTemplate($template, $bindings){
        foreach($bindings as $name => $props){
            $sparqlVal = $this->variableBindingToSparqlTerm($props);
            $sparqlVar = '?'.$name;
            logDebug("SPARQL Variable binding: {$sparqlVal} = {$sparqlVar}");
            //replace all variables with values 
            //(but not variables that simply start with this variable name)
            $template = preg_replace('/\\'.$sparqlVar.'([^_a-zA-Z0-9])/', $sparqlVal.'$1', $template); # the \ is to escape the ? and needs \\ because it is escape char in php ...
        }
        return $template;
    }
    
    function getGroupGraphPattern(){
        $whereRequestParam              =  $this->_request->getParam('_where');
        $selectorConfigWhereProperty    = $this->getConfigGraph()->getSelectWhere();    
        $bindings = $this->getConfigGraph()->getAllProcessedVariableBindings();
        $selectorConfigWhereProperty = $this->fillQueryTemplate($selectorConfigWhereProperty, $bindings);
        if(!empty($whereRequestParam)) $whereRequestParam = '{'.$whereRequestParam.'}'; 
        if(!empty($selectorConfigWhereProperty)) $selectorConfigWhereProperty = '{'.$selectorConfigWhereProperty.'}'; 
        $GGP = "{$whereRequestParam}\n{$selectorConfigWhereProperty}\n ";
        $filter = implode( '&', $this->getConfigGraph()->getAllFilters());
        foreach($this->_request->getUnreservedParams() as $k => $v){
            list($k, $v) = array(urlencode($k), urlencode($v));
            $filter.="&{$k}={$v}";
        }
        logDebug("Filter is: {$filter}");
        $params = queryStringToParams($filter);
        $langs = array();
        foreach($params as $k => $v) {
          if (strpos($k, 'lang-') === 0) {
            $langs[substr($k, 5)] = $v;
            unset($params[$k]);
          }
        }
        $GGP .= $this->paramsToSparql($params, $langs);
        
        $GGP = trim($GGP);
        if(empty($GGP)){
            $GGP = "\n  ?item ?property ?value .";
        }

        return $GGP;
    }
    
    function getGeneratedSelectQuery(){
        $GroupGraphPattern = $this->getGroupGraphPattern();
        $order = $this->getOrderBy();
        $limit = $this->getLimit();
        $offset = $this->getOffset();
        $fromClause = $this->getFromClause();
        $query = <<<_SPARQL_
SELECT DISTINCT ?item
{$fromClause}
WHERE {
{$GroupGraphPattern}
{$order['graphConditions']}
}
{$order['orderBy']} 
LIMIT {$limit} 
OFFSET {$offset}
_SPARQL_;
        return $this->addPrefixesToQuery($query);
    }
    
    function paramsToSparql($paramsArray, $langArray=array()){
        $sparql = '';
        $filters = '';
        $namespaces = $this->getConfigGraph()->getPrefixesFromLoadedTurtle();
        $rdfsLabelQnameOrUri = $this->qnameOrUri(RDFS_LABEL, $namespaces);
        $defaultLangs = $this->getDefaultSelectLangs();
        foreach($paramsArray as $k => $v){
            
            $prefix = $this->prefixFromParamName($k);
            $propertiesList = $this->mapParamNameToProperties($k);
            $propertyNames = $this->paramNameToPropertyNames($k);
            $counter=0;
            $name = $propertyNames[0];
            $varName = $name;
            $nextVarName = '';
            $propUri = $propertiesList[$name];
            $propQnameOrUri = $this->qnameOrUri($propUri, $namespaces);
            $lastPropUri = array_pop(array_values($propertiesList));
            $langs = array_key_exists($k, $langArray) ? array($langArray[$k]) : $defaultLangs;
            $processedFilterValues = $this->filterValueToSparqlTerm($v, $langs, $lastPropUri);
            $nValues = count($processedFilterValues);
            
            if(count($propertyNames) > 1){
                 $sparql.= "\n  ?item {$propQnameOrUri} ?{$name} . ";
            }

            foreach($propertiesList as $name => $propUri){
                if(isset($propertyNames[$counter+1]) OR count($propertyNames)==1){ //if this ISN'T the last property or is the only property
                    if(count($propertyNames)==1){
                        $varName = 'item';
                        $nextName = $propertyNames[0];
                        $nextVarName = $nextName;
                    } else {
                        $nextName = $propertyNames[$counter+1];
                        $nextVarName = $varName.'_'.$nextName;
                    } 
                    
                    $nextProp = $propertiesList[$nextName];
                    $nextPropQnameOrUri = $this->qnameOrUri($nextProp, $namespaces);
                      
                     //need to cast $nextVarName to compare it with $processedFilterValue
                     $castNextVarName = $this->castOrderByVariable($nextVarName, $nextProp);                      
                                        
                    if ( (($counter+2) == count($propertyNames) OR count($propertyNames)==1)){ //if last item or only item
                        if (!$prefix) {
                          if ($nValues > 1) {
                            foreach($processedFilterValues as $position => $processedFilterValue) {
                              if ($position) {
                                $sparql .= "\n  UNION";
                              }
                              $sparql.="\n  { ?{$varName} {$nextPropQnameOrUri} {$processedFilterValue} . }";
                            }
                          } else {
                            $processedFilterValue = $processedFilterValues[0];
                            $sparql .= "\n  ?{$varName} {$nextPropQnameOrUri} {$processedFilterValue} . ";
                          }
                        } else if($prefix=='min') {
                            $sparql.="\n  ?{$varName} {$nextPropQnameOrUri} ?{$nextVarName} . \n  FILTER (?{$nextVarName} >= {$processedFilterValues[0]})"; 
                        } else if($prefix=='max') {
                            $sparql.="\n  ?{$varName} {$nextPropQnameOrUri} ?{$nextVarName} . \n  FILTER (?{$nextVarName} <= {$processedFilterValues[0]})";
                        } else if($prefix == 'minEx') {
                            $sparql.="\n  ?{$varName} {$nextPropQnameOrUri} ?{$nextVarName} . \n  FILTER (?{$nextVarName} > {$processedFilterValues[0]})";
                        } else if($prefix == 'maxEx') {
                            $sparql.="\n  ?{$varName} {$nextPropQnameOrUri} ?{$nextVarName} . \n  FILTER (?{$nextVarName} < {$processedFilterValues[0]})";
                        } else if($prefix == 'name') {
                            $sparql.="\n  ?{$varName} {$nextPropQnameOrUri} ?{$nextVarName} .\n";
                            foreach($processedFilterValues as $position => $processedFilterValue) {
                              if ($nValues > 1) {
                                $sparql.="\n  {";
                              }
                              $sparql.="\n  ?{$nextVarName} {$rdfsLabelQnameOrUri} {$processedFilterValue} . ";
                              if ($nValues > 1) {
                                $sparql.="\n  } ";
                                if ($position + 1 < $nValues) {
                                  $sparql.="\n UNION ";
                                }
                              }
                            }
                        } else if($prefix == 'exists') {
                              if($v=="true"){
                                  $sparql.="\n  ?{$varName} {$nextPropQnameOrUri} [] . ";                                               
                              } else {
                                  $sparql.="\n  OPTIONAL { \n    ?{$varName} {$nextPropQnameOrUri} ?{$nextVarName} . \n  } \n  FILTER (!bound(?{$nextVarName})) ";                                               
                              }
                        } 
                    } else {
                        $sparql.="\n  ?{$varName} {$nextPropQnameOrUri} ?{$nextVarName} . ";
                    }
                    $varName = $nextVarName;
                } 
                $counter++;
            }

        
        }
        return  $sparql;
    }
    
    
    function qnameOrUri($uri, $prefixes) {
        $hash = strpos($uri, '#');
        if (!$hash) {
            $parts = explode('/', $uri);
            $localPart = $parts[count($parts) - 1];
            $namespace = substr($uri, 0, strlen($uri) - strlen($localPart));
        } else {
            $localPart = substr($uri, $hash + 1);
            $namespace = substr($uri, 0, $hash + 1);
        }
        foreach ($prefixes as $prefix=>$ns) {
          if ($ns == $namespace) {
            return $prefix.':'.$localPart;
          }
        }
        return '<'.$uri.'>';
    }
    
    function paramNameToPropertyNames($name){
        #remove min-/max-
        $nameArray = $this->splitPrefixAndName($name);
        $name = $nameArray['name'];
        #split on dot
        $splitNames = explode('.', $name);
        return $splitNames;
    }
    
    function mapParamNameToProperties($name){
        $splitNames = $this->paramNameToPropertyNames($name);
        $list = array();
        foreach($splitNames as $sn){
            $uri = $this->getConfigGraph()->getUriForVocabPropertyLabel($sn);
            $list[$sn] = $uri;
        }
        return $list;
    }
    
    function splitPrefixAndName($name){
        $prefixes = array('min', 'max', 'minEx', 'maxEx', 'name', 'exists', 'lang', 'true', 'false');
        foreach($prefixes as $prefix){
            if(strpos($name, $prefix.'-')===0){
                $name =  substr($name, strlen($prefix.'-'));
                return array(
                    'name' => $name,
                    'prefix' => $prefix,
                    );
            }
        }
        return array('name' => $name, 'prefix' => false);
    }
    
    function prefixFromParamName($name){
        $a = $this->splitPrefixAndName($name);
        return $a['prefix'];
    }
    
    function getOffset(){
        $pageNo = $this->_request->getPage();
        return ($pageNo - 1 ) * $this->getLimit();
    }
    
    function getUnknownPropertiesFromRequest(){
        if($this->hasUnknownPropertiesFromRequest()){
            return $this->_unknownPropertiesFromRequestParameter;
        } else {
            return false;
        }
    }

    function getUnknownPropertiesFromConfig(){
        if($this->hasUnknownPropertiesFromConfig()){
            return $this->_unknownPropertiesFromConfig;
        } else {
            return false;
        }
    }
    
    function hasUnknownPropertiesFromRequest(){
        
        if(!empty($this->_unknownPropertiesFromRequestParameter)){
            return true;
        }
        foreach($this->_request->getUnreservedParams() as $k => $v){
            $propertyNames = $this->paramNameToPropertyNames($k);
            $propertyNamesWithUris = $this->mapParamNameToProperties($k);
            foreach($propertyNames as $pn){
                  if(empty($propertyNamesWithUris[$pn])){
                        $this->_unknownPropertiesFromRequestParameter[]=$pn;
                    }
            }
        }
        $this->getOrderBy();

        try{
            $chain = $this->getConfigGraph()->getRequestPropertyChainArray();
        } catch (UnknownPropertyException $e){
            $this->_unknownPropertiesFromRequestParameter[]=$e->getMessage();
        }

        if(!empty($this->_unknownPropertiesFromRequestParameter)){
            return true;
        }

        return false;
    }
    
    function hasUnknownPropertiesFromConfig($viewerUri=false){

        if(!empty($this->_unknownPropertiesFromConfig)){
              return true;
        }
        
        $filters = $this->getConfigGraph()->getAllFilters();
        foreach($filters as $filter){
            $paramsArray = queryStringToParams($filter);
            foreach(array_keys($paramsArray) as $paramName){
                $propertyNames = $this->paramNameToPropertyNames($paramName);
                $propertyNamesWithUris = $this->mapParamNameToProperties($paramName);
                foreach($propertyNames as $pn){
                    if(empty($propertyNamesWithUris[$pn])){
                        $this->_unknownPropertiesFromConfig[]=$pn;
                    }
                }
            }
            
        }
        $this->getOrderBy();
 
        if($viewerUri){
        try{
                $chain = $this->getConfigGraph()->getViewerDisplayPropertiesValueAsPropertyChainArray($viewerUri);
            } catch (Exception $e){
                $this->_unknownPropertiesFromConfig[]=$e->getMessage();
            }
        }
        if(!empty($this->_unknownPropertiesFromConfig)){
              return true;
        }
        
        return false;
    }
    
    
    function getOrderBy(){
        $graphConditions = false;
        $orderBy = false;
        if($orderByRequestParam = $this->_request->getParam('_orderBy')){
            $orderBy = 'ORDER BY '.$orderByRequestParam;
        } else if($sort = $this->_request->getParam('_sort')){
            return $this->sortToOrderBy($sort, 'request');
        } else if($orderByConfig = $this->getConfigGraph()->getOrderBy()){
            $bindings = $this->getConfigGraph()->getAllProcessedVariableBindings();
            $orderByConfig = $this->fillQueryTemplate($orderByConfig, $bindings);
            $orderBy = 'ORDER BY '.$orderByConfig;
        } else if($sort = $this->getConfigGraph()->getSort()){
            return $this->sortToOrderBy($sort, 'config');
        }
        return array(
            'graphConditions' => $graphConditions,
            'orderBy' => $orderBy,
        );
    }
    
    function sortToOrderBy($sort, $source){
        $sortPropNames = explode(',',$sort);
        $propertyLists = array();
        foreach($sortPropNames as $sortName){
            $ascOrDesc = ($sortName[0]=='-')? 'DESC' : 'ASC';
            $sortName = ltrim($sortName, '-');
            $propertyLists[]= array(
                    'sort-order' => $ascOrDesc,
                    'property-list'=> $this->mapParamNameToProperties($sortName),
                );
        }
        
        foreach($propertyLists as $propertyList){
            $properties = $propertyList['property-list'];
            foreach($properties as $name => $uri){
              if(!$uri){
                    if($source == 'request') $this->_unknownPropertiesFromRequestParameter[]=$name;
                    else if($source == 'config') $this->_unknownPropertiesFromConfig[]=$name;
                    else throw new Exception("source parameter for sortToOrderBy must be 'request' or 'config'");
                }    
            }           
        } 
        return $this->propertyNameListToOrderBySparql($propertyLists);
    }
    
    
    function propertyNameListToOrderBySparql($propertyLists){
        $namespaces = $this->getConfigGraph()->getPrefixesFromLoadedTurtle();
        $sparql = '';
        $orderBy = "ORDER BY ";
        $variableNames = array();
        foreach($propertyLists as $propertiesListHash){
            $propertiesList = $propertiesListHash['property-list'];
            $sortOrder = $propertiesListHash['sort-order'];
            $propertyNames = array_keys($propertiesList);
            $counter=0;
            $name = $propertyNames[0];
            $varName = $name;
            $propUri = $propertiesList[$name];
            $propQnameOrUri = $this->qnameOrUri($propUri, $namespaces);
            $sparql.= "\n  ?item {$propQnameOrUri} ?{$name} .";
            $variableNames[$name] = $propUri;
            foreach($propertiesList as $name => $propUri){
                 if(isset($propertyNames[$counter+1])){ //if this ISN'T the last property
                     $nextName = $propertyNames[$counter+1];
                 } else if (count($propertyNames) ==1){
                     $orderBy.= $sortOrder.'(?'.$name.') ';
                     $varName = 'item';
                     $nextName = $propertyNames[0];
                 }
                 
                 $nextProp = $propertiesList[$nextName];
                 $nextPropQnameOrUri = $this->qnameOrUri($nextProp, $namespaces);
                 $nextVarName = $varName.'_'.$nextName;

                 if ( ($counter+1) < count($propertyNames)  ){ //if not last item
                     $sparql.="\n  ?{$varName} {$nextPropQnameOrUri} ?{$nextVarName} .";  
                     $variableNames[$nextVarName] = $nextProp; 
                 }
                 
                 if ( ($counter+2) == count($propertyNames) ){
                     //if this is the last property in the chain, add to the order by
                     $orderBy.= $sortOrder.'(?'.$nextVarName.') ';
                 }
                 
                 $varName = $nextVarName;
                 $counter++;
             }
            
        }
        return array('graphConditions' => $sparql, 'orderBy' => $orderBy); 
    }
    
    
    function getSelectQueryForUriList(){
        if($query = $this->getExplicitSelectQuery()){
            return $this->addPrefixesToQuery($query);
        } else {
            return $this->getGeneratedSelectQuery();
        }
    }    
    
    function castOrderByVariable($varName, $propertyUri){
        $xsdDatatypes = array(
            XSD."integer"  ,
            XSD."int"  ,
            XSD."decimal"  ,
            XSD."float"    ,
            XSD."double"   ,
            XSD."string"   ,
            XSD."boolean"  ,
            XSD."dateTime" ,
            );
        if($propertyRange = $this->getConfigGraph()->getVocabPropertyRange($propertyUri) AND in_array($propertyRange, $xsdDatatypes)){
            return "<{$propertyRange}>(?{$varName})";
        } else {
            return "?{$varName}";
        }
    }    
            
    function getViewQueryForUri($uri, $viewerUri){
        return $this->getViewQueryForUriList(array($uri), $viewerUri);
    }
    
    function getViewQueryForUriList($uriList, $viewerUri){
        
        $fromClause = $this->getFromClause();
        
        if(($template = $this->_request->getParam('_template') OR $template = $this->_config->getViewerTemplate($viewerUri)) AND !empty($template)){
                  $uriSetFilter = "FILTER( ?item = <http://puelia.example.org/fake-uri/x> ";
                  foreach($uriList as $describeUri){
                      $uriSetFilter.= "|| ?item = <{$describeUri}> \n";
                  }
                  $uriSetFilter.= ")\n";
                  return $this->addPrefixesToQuery("CONSTRUCT { {$template}  } {$fromClause} WHERE { {$template} {$uriSetFilter} }");
                  /* 
                    FILTER doesn't work so well with all triplestores, could do it by adding incrementers to every variable in the pattern which increment for ever loop of the URI list. If do so, it would be good to change the propertypath->sparql code to map to a plain pattern which is then passed to the same code as this is, to add the incrementers 
                    
                  */
        } else if($viewerUri==API.'describeViewer' AND strlen($this->_request->getParam('_properties')) === 0 ){
            return 'DESCRIBE <'.implode('> <', $uriList).'>'.$fromClause;
        } else {
            $namespaces = $this->getConfigGraph()->getPrefixesFromLoadedTurtle();
            $conditionsGraph = '';
            $whereGraph = '';
            $chains = $this->getViewerPropertyChains($viewerUri);            
            $props = array();
            foreach($chains as $chain) {
              $props =  $this->mapPropertyChainToStructure($chain, $props);
            }
            foreach ($uriList as $position => $uri) {
              if ($position) {
                $whereGraph .= " UNION\n";
              }
              $conditionsGraph .= "\n    # constructing properties of {$uri} \n";
              $whereGraph .= "\n  # identifying properties of {$uri} \n";
              $counter = 0;
              foreach ($props as $prop => $substruct) {
                if ($counter) {
                  $whereGraph .= "UNION {\n";
                } else {
                  $whereGraph .= "  {\n";
                }
                $propvar = $substruct['var'] . '_' . $position;
                $invProps = false;
            
                if ($prop == API.'allProperties') {
                  $triple = "    <{$uri}> {$propvar}_prop {$propvar} .\n";
                } else {
                  if($invProps = $this->getConfigGraph()->getInverseOfProperty($prop)){
                    $inverseTriple="#Inverse Mappings \n\n";
                    foreach($invProps as $no => $invProp){
                      $invPropQnameOrUri = $this->qnameOrUri($invProp, $namespaces);
                      $inverseTriple.= "{\n   {$propvar} {$invPropQnameOrUri} <{$uri}>  . \n ";
                      if (array_key_exists('props', $substruct)) {
                        $inverseTriple .= $this->mapPropertyStructureToWhereGraph($substruct, $position, $namespaces);
                      }
                      $inverseTriple .= "}"; 
                      if($no!=(count($invProps)-1)){
                        $inverseTriple.= " UNION ";
                      }
                    }
                }
                 
                  $propQnameOrUri = $this->qnameOrUri($prop, $namespaces);
                  $triple = "    <{$uri}> {$propQnameOrUri} {$propvar} .\n";
                }
                
                $whereGraph .= ($invProps)? $inverseTriple :  $triple;
                $conditionsGraph .= $triple;
                if (array_key_exists('props', $substruct)) {
                  if(!$invProps) $whereGraph .= $this->mapPropertyStructureToWhereGraph($substruct, $position, $namespaces);
                  $conditionsGraph .= $this->mapPropertyStructureToConstructGraph($substruct, $position, $namespaces);
                }
                $whereGraph .= "  } ";
                $counter += 1;
              }
            }
            
            return $this->addPrefixesToQuery("CONSTRUCT { {$conditionsGraph}} $fromClause WHERE { {$whereGraph}\n}\n");
            
        }
        
    }
    
    function mapPropertyStructureToWhereGraph($structure, $uriPosition, $namespaces) {
      $var = $structure['var'];
      $props = $structure['props'];
      $graph = '';
      foreach($props as $prop => $substruct) {
        $propvar = $substruct['var'] . '_' . $uriPosition;
        if ($prop == API.'allProperties') {
          $graph .= "    OPTIONAL { {$var}_{$uriPosition} {$propvar}_prop {$propvar} .";          
        } else {
          $propQnameOrUri = $this->qnameOrUri($prop, $namespaces);

                  if($invProps = $this->getConfigGraph()->getInverseOfProperty($prop)){
                    $inverseTriple="#Inverse Mappings \n\n";
                    foreach($invProps as $no => $invProp){
                      $invPropQnameOrUri = $this->qnameOrUri($invProp, $namespaces);
                      $graph .= "\n OPTIONAL { {$propvar} {$invPropQnameOrUri} {$var}_{$uriPosition} .";
                      if (array_key_exists('props', $substruct)) {
                        $graph .= $this->mapPropertyStructureToWhereGraph($substruct, $uriPosition, $namespaces);
                      }
                      $graph .= "}"; 
                    }
                }
            $graph .= "    OPTIONAL { {$var}_{$uriPosition} {$propQnameOrUri} {$propvar} .";
        }
        if (array_key_exists('props', $substruct)) {
          $graph .= $this->mapPropertyStructureToWhereGraph($substruct, $uriPosition, $namespaces);
        }
        $graph .= " }\n";
      }
      return $graph;
    }
    
    function mapPropertyStructureToConstructGraph($structure, $uriPosition, $namespaces) {
      $var = $structure['var'];
      $props = $structure['props'];
      $graph = '';
      foreach($props as $prop => $substruct) {
        $propvar = $substruct['var'] . '_' . $uriPosition;
        if ($prop == API.'allProperties') {
          $graph .= "    {$var}_{$uriPosition} {$propvar}_prop {$propvar} .\n";
        } else {
          $propQnameOrUri = $this->qnameOrUri($prop, $namespaces);
/*          if($invProp = $this->getConfigGraph()->getInverseOfProperty($prop)){
              $invPropQnameOrUri = $this->qnameOrUri($invPropQnameOrUri, $namespaces);
              $graph .= "\n  {$propvar} {$propQnameOrUri} {$var}_{$uriPosition} . \n # inverse property mapping \n";
          } 
 */
          $graph .= "    {$var}_{$uriPosition} {$propQnameOrUri} {$propvar} .\n";
        }
        if (array_key_exists('props', $substruct)) {
          $graph .= $this->mapPropertyStructureToConstructGraph($substruct, $uriPosition, $namespaces);
        }
      }
      return $graph;
    }
    
    /*
    Creating a structure that looks like:
    array(
      "var" => "?s",
      "props" => array(
                   rdfs:label => array("var" => "?var_1"),
                   org:reportsTo => array(
                                      "var" => "?var_2",
                                      "props" => array(
                                         rdfs:label => array("var" => "?var_2_1")
                                      )
                                    )
                 )
    )
    */
    function mapPropertyChainToStructure($chain, $structure, $varbase = '?var') {
      $prop = array_shift($chain);
      if (array_key_exists($prop, $structure)) {
        $varbase = $structure[$prop]['var'];
      } else {
        $varbase = $varbase . '_' . (count($structure) + 1);
        $structure[$prop] = array('var' => $varbase, 'props' => array());
      }
      if (count($chain) != 0) {
        $structure[$prop]['props'] = $this->mapPropertyChainToStructure($chain, $structure[$prop]['props'], $varbase);
      }
      return $structure;
    }
    
    function getViewerPropertyChains($viewerUri){
        return array_merge($this->getConfigGraph()->getRequestPropertyChainArray(), $this->getConfigGraph()->getViewerDisplayPropertiesValueAsPropertyChainArray($viewerUri), $this->getConfigGraph()->getAllViewerPropertyChains($viewerUri));
    }
    
    function getConfigGraph(){
        return $this->_config;
    }
    
}
?>
