<?php

class EUScraper {

  const uriPattern = '';
  var $dom;
  var $xpath= false;
  var $graph = false;
  var $publicInstitutionsGraph = false;
  var $pageUri = false;
  var $request_factory = false;
  var $translate_langs = array('de','fr','nl');

  function __construct($uri, &$publicInstitutionsGraph){
    $this->pageUri = $uri;
    $this->publicInstitutionsGraph = $publicInstitutionsGraph;
    $this->request_factory = new HttpRequestFactory();
    $this->dom = $this->fetchDocFromUrl($uri);
    $this->xpath = new DOMXPath($this->dom);
    $this->graph = new SimpleGraph();
  }

  function translatePageUrlTo($lang){
    $url = preg_replace('/&lang=[a-z]+/','', $this->pageUri);
    return $url.'&lang='.$lang;
  }
  function translateLabelsOnPage($xpath_query){
    foreach($this->translate_langs as $lang){
      $doc = $this->fetchDocFromUrl($this->translatePageUrlTo($lang));
      $this->log_message("{$lang} translations for $this->pageUri");
      $xpath = new DomXpath($doc);
      $graph = new SimpleGraph();
      foreach($xpath->query($xpath_query) as $a){
        $nodeId = $this->getNodeIdFromUrl($a->getAttribute('href'));
        $uri = INST.'institutions/'.$nodeId;
        $this->graph->add_literal_triple($uri, RDFS_LABEL, $a->textContent, $lang);
      }
      echo trim($graph->to_ntriples());
    }
  }

  function fetchDocFromUrl($url){
     $request = $this->request_factory->make('GET', $url);
    $response = $request->execute();
    if(!$response->is_success()){
      $c = 0;
      while(!$response->is_success() AND $c < 2){
        sleep(3);
        $response = $request->execute();
        $c++;    
      }
      if(!$response->is_success()){
        $this->log_message("Failed to get {$url}");
      }
    }
    $dom = new DomDocument();
    @$dom->loadHTML($response->body); 
    return $dom;
  }

  public function getNodeIdFromUrl($url){
    $query = parse_url($url, PHP_URL_QUERY);
    parse_str($query, $params);
    return $params['nodeID'];
  }
  public function getPersonIdFromUrl($url){
    $query = parse_url($url, PHP_URL_QUERY);
    parse_str($query, $params);
    return $params['personID'];
  }

  public function scrape(){}

    public function get_graph(){
      return $this->graph;
    }

  public function add_resource($uri, $type, $label, $label_lang=false){
    
      $this->graph->add_resource_triple($uri, RDF_TYPE, $type);
      $this->graph->add_literal_triple($uri, RDFS_LABEL, trim($label), $label_lang);
  }

  function choose_type($label){
    # Organization
    # FormalOrganization
    # OrganizationalUnit
    # dcmi:Collection
    # ?PoliticalParty ?
    $knowns = array(
      DCMI.'Collection' => array(
        'Agencies and other bodies',
        'Non-attached Members',
        'listed by',
        'Members of',
        'committees',
        'bodies',
        'Delegations',
      ),
      WHOISWHO.'Bank'         =>array( 'Bank'),
      WHOISWHO.'Bureau'       =>array( 'Bureau'),
      WHOISWHO.'Office'       =>array( 'Office'),
      WHOISWHO.'Conference'   =>array( 'Conference'),
      WHOISWHO.'Directorate'  =>array( 'Directorate'),
      WHOISWHO.'DirectorateGeneral' 
                              =>array('Directorate-General', 'Directorate General'),
      WHOISWHO.'Delegation'   =>array( 'Delegation'),
      WHOISWHO.'Council'      =>array( 'Council'),
      WHOISWHO.'Court'        =>array( 'Court'),
      WHOISWHO.'Committee'    =>array( 'Committee'),
      WHOISWHO.'Service'      =>array( 'Service'),
      WHOISWHO.'Ombudsman'    =>array( 'Ombudsman'),
      WHOISWHO.'PoliticalParty' => array(
                                          'Party', 
                                          'Democrats', 
                                          'Conservatives', 
                                          'Greens', 
                                          'United Left', 
                                          'Socialist' , 
                                          'democracy Group',
                                        ),
      WHOISWHO.'Agency' => array('Agency'),
      ORG.'OrganizationalUnit' => array('Unit ', ' Unit'),
      WHOISWHO.'Parliament'   =>array( 'Parliament'),
    );

    foreach($knowns as $type => $keyphrases){
      foreach($keyphrases as $keyphrase){
        if(stristr($label, $keyphrase)){
          return $type;
        }
      }
    }
    return ORG.'FormalOrganization';
  }
 function parseAddressDetails($detailsTextLine, $uri, $recursed=false){
    $address = '';
    $parts = explode(':', $detailsTextLine->nodeValue);
    switch(count($parts)){
      case 1:
          $addressLine = trim($detailsTextLine->nodeValue);
          if(!empty($addressLine)) $address.=$addressLine."\n";
          break;
      case 2: 
          switch(trim($parts[0])){
          case 'Tel':
          case 'phone':
              $phone = trim($parts[1]);
              if(!empty($phone)) $this->graph->add_literal_triple($uri, OV.'phoneNumber', $phone);
              break;
            case 'Fax':
              $this->graph->add_literal_triple($uri, OV.'faxNumber', trim($parts[1]));
              break;
            case 'Email':
              if(preg_match('/.+@.+/', $parts[1])){
                $this->graph->add_resource_triple($uri, FOAF.'mbox', 'mailto:'.trim($parts[1]));
                $this->graph->add_resource_triple($uri, OWL_SAMEAS, 'http://rdfize.com/people/'.sha1(trim($parts[1])));
              }
              break;
            case 'Internet':
              $textVal = $detailsTextLine->nextSibling->nodeValue;
              foreach(explode(';', $textVal) as $url){
                $url = trim($url);
                if(strpos($url, 'www.') === 0) $url = 'http://'.$url;
                $this->graph->add_resource_triple($uri, FOAF.'homepage', trim($url));
              }
              break;
            case 'Bureaux':
            case 'Office':
              $address.=$detailsTextLine->nodeValue;
              break;
            case 'Adresse postale':
              $address.= trim($parts[1]);
              break;
            case 'http' :
              $this->graph->add_resource_triple($uri, FOAF.'homepage', trim($detailsTextLine->nodeValue));
              break;
            default:
              if(preg_match('/[a-zA-Z]+:.+/', $detailsTextLine->nodeValue, $m) AND !$recursed){
                $detailsTextLine->nodeValue = $m[0];
                return $this->parseAddressDetails($detailsTextLine, $uri, 1);
              } else {
                $address.=$detailsTextLine->nodeValue;
                
//                var_dump( array($uri, $parts, $detailsTextLine->nodeValue));
//                die;
              }
              break;
          }
      }
   return $address;
 
 }

  function log_message($message){
     file_put_contents('scraper.log', "\n ".get_class($this)."\t{$this->pageUri}\t{$message}", FILE_APPEND);
  }
}





?>
