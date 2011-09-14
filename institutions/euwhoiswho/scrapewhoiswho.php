<?php

#requires
#
#
if(!defined('MORIARTY_ARC_DIR')) define('MORIARTY_ARC_DIR', '../arc/');
define('MORIARTY_HTTP_CACHE_DIR', 'cache/');
define('MORIARTY_ALWAYS_CACHE_EVERYTHING', 1);
require_once '../moriarty/moriarty.inc.php';
require_once '../moriarty/simplegraph.class.php';
require_once '../moriarty/store.class.php';


define('DCT', 'http://purl.org/dc/terms/');
define('DCMI', 'http://purl.org/dc/dcmitype/');
define('ORG', 'http://www.w3.org/ns/org#');
define('WHOISWHO', 'http://euwhoiswho.dataincubator.org/');
define('INST', WHOISWHO.'institutions/');
define('OV', 'http://open.vocab.org/terms/');
define('FOAF', 'http://xmlns.com/foaf/0.1/');

function urlize($i){

  return urlencode(str_replace(' ','_',ucwords(trim($i))));
}


#
$publicInstitutionsGraph = new SimpleGraph(file_get_contents("institutions.publicdata.eu.ttl"));


class EUScraper {

  const uriPattern = '';
  var $xpath= false;
  var $graph = false;
  var $publicInstitutionsGraph = false;
  var $pageUri = false;
  var $request_factory = false;

  function __construct($uri, &$publicInstitutionsGraph){
    $this->pageUri = $uri;
    $this->publicInstitutionsGraph = $publicInstitutionsGraph;
    $this->request_factory = new HttpRequestFactory();
    $request = $this->request_factory->make('GET', $uri);
    $response = $request->execute();
    if(!$response->is_success()){
      throw new Exception("Failed to get {$uri}");
    }
    $dom = new DomDocument();
   @$dom->loadHTML($response->body);
    $this->xpath = new DOMXPath($dom);
    $this->graph = new SimpleGraph();
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
    $knowns = array(
      DCMI.'Collection' => array(
        'Agencies and other bodies',
      ),
    );
  }
 function parseAddressDetails($detailsTextLine, $uri){
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
              $phone = trim($parts[1]);
              if(!empty($phone)) $this->graph->add_literal_triple($uri, OV.'phoneNumber', $phone);
              break;
            case 'Fax':
              $this->graph->add_literal_triple($uri, OV.'faxNumber', trim($parts[1]));
              break;
            case 'Email':
              if(preg_match('/.+@.+/', $parts[1])) $this->graph->add_resource_triple($uri, FOAF.'mbox', 'mailto:'.trim($parts[1]));
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
              $address.=$detailsTextLine->nodeValue;
              break;
            case 'Adresse postale':
              $address.= trim($parts[1]);
              break;
            default:
              var_dump(array($uri, $detailsTextLine->nodeValue));
              break;
          }
      }
   return $address;
 
  }
}

class HierarchicViewScraper extends EUScraper {

  const uriPattern = 'http://europa.eu/whoiswho/public/index.cfm?fuseaction=idea.hierarchy&nodeid=1';

  function scrape(){

    foreach($this->xpath->query('//td/ul/li/a') as $link){
          $webPageUri = str_replace('lang=en','', 'http://europa.eu/whoiswho/public/'.$link->getAttribute('href'));
          $label = $link->textContent;
          if($label == ''){
          
          } else if (!strpos($webPageUri, 'nodeID=18&')){
          $uri = INST.urlize($label);
          $this->add_resource($uri, ORG.'FormalOrganization', $label, 'en-gb');
          $this->graph->add_resource_triple($uri, FOAF_PAGE, $webPageUri);
          $sameAs = array_shift($this->publicInstitutionsGraph->get_subjects_where_literal(DCT.'title', $label)); 
          if($sameAs) $this->graph->add_resource_triple($uri, OWL_SAMEAS, $sameAs);
          $InstitutionScraper = new InstitutionScraper($webPageUri.'&lang=en', $this->publicInstitutionsGraph);
          $InstitutionScraper->setSlugPrefix(urlize($label));
          $InstitutionScraper->scrape($uri, ORG.'hasSubOrganization', ORG.'transitiveSubOrganisationOf');
          $this->graph->add_graph($InstitutionScraper->get_graph());
          } else if (strpos($webPageUri, 'nodeID=18&')) { //Agencies and other bodies
           //scrape agencies
          
          }
      }    
  
  }
 
}

class InstitutionScraper extends EUScraper {


  var $slugPrefix = '';

  function setSlugPrefix($val){
    $this->slugPrefix = $val;
  }

  function getSlugPrefix(){
    return $this->slugPrefix;
  }

  function scrape($linkSubject=false, $linkPredicate=false, $inverseLinkPredicate=false ){
    if($linkSubject){
      $uri = $linkSubject;
      $address = '';
      foreach( $this->xpath->query("//node()[preceding-sibling::h3][following::br]") as $detailsTextLine){
        //    var_dump($detailsTextLine->nodeValue);
            $address.=$this->parseAddressDetails($detailsTextLine, $uri);
        }
        $this->graph->add_literal_triple($uri, OV.'postalAddress', trim($address));
    }
    
    foreach($this->xpath->query("//td/ul/li/a[preceding::h2[text()='Depending entity']]") as $link){
          $webPageUri = str_replace('&lang=en','', 'http://europa.eu/whoiswho/public/'.$link->getAttribute('href'));
          $label = $link->textContent;
          $uri = INST.$this->slugPrefix.'/'.urlize($label);
          $this->add_resource($uri, ORG.'FormalOrganization', $label, 'en-gb');
          $this->graph->add_resource_triple($uri, FOAF_PAGE, $webPageUri);
          $sameAs = array_shift($this->publicInstitutionsGraph->get_subjects_where_literal(DCT.'title', $label)); 
          if($sameAs) $this->graph->add_resource_triple($uri, OWL_SAMEAS, $sameAs);
          if($linkSubject &&$linkPredicate){
            $this->graph->add_resource_triple($linkSubject, $linkPredicate, $uri);
          } 
          if($linkSubject && $inverseLinkPredicate) {
            $this->graph->add_resource_triple($uri, $inverseLinkPredicate, $linkSubject);
          }

//          $InstitutionScraper = new InstitutionScraper($webPageUri, $this->publicInstitutionsGraph);
  //        $InstitutionScraper->setSlugPrefix(urlize($label));
   //       $InstitutionScraper->scrape($uri, ORG.'hasSubOrganization', ORG.'transitiveSubOrganisationOf');
    //      $this->graph->add_graph($InstitutionScraper->get_graph());
      }    
  
  }

}

class EUPersonScraper extends EUScraper {

  
  function scrape(){
      parse_str(parse_url($this->pageUri, PHP_URL_QUERY), $pageQueryParams);
      $personID = $pageQueryParams['personID'];
      $name = trim($this->xpath->query('//tr/td/h3')->item(0)->nodeValue);
      $uri = WHOISWHO.'people/'.$personID.'/'.urlize($name);
      preg_match('/^([A-Z ]+) ([a-zA-Z ]+)$/', $name, $matches);
      $surname= ucwords(strtolower($matches[1]));
      $fullName = trim($matches[2]).' '.$surname;

      $this->add_resource($uri, FOAF.'Person', $fullName );
      $this->graph->add_literal_triple($uri, FOAF.'name', $fullName);

      $address = '';
      foreach( $this->xpath->query('//tr/td[contains(. ,"Tel:")][preceding::h3]/node()') as $node){
        $address.= $this->parseAddressDetails($node, $uri);
      }
      $this->graph->add_literal_triple($uri, OV.'postalAddress', trim($address));

      foreach($this->xpath->query('//table[2]//tr/td/a[contains(@href, "index.cfm?fuseaction=idea.hierarchy&nodeID=")]') as $a){
        $orgBreadcrumbPath = $a->nodeValue;
        $roleLabel = trim($a->parentNode->parentNode->nextSibling->textContent);
        $roleURI = WHOISWHO.'roles/'.urlize($roleLabel);
    //    var_dump($roleLabel);
        $orgURI = INST.str_replace('%2F', '/', urlize(str_replace('; ','/', $orgBreadcrumbPath)));
        $membershipURI = $orgURI.'/memberships/'.$personID;
        $this->add_resource($roleURI, ORG.'Role', $roleLabel, 'en-gb');
        $this->add_resource($membershipURI, ORG.'Membership', $fullName.', '.$roleLabel.' of '.array_pop(explode('; ', $orgBreadcrumbPath)), 'en-gb');
        $this->graph->add_resource_triple($membershipURI, ORG.'member', $uri);
        $this->graph->add_resource_triple($membershipURI, ORG.'organization', $orgURI);
        $this->graph->add_resource_triple($membershipURI, ORG.'role', $roleURI);
//        var_dump($orgURI);
      }
  }
}



class AgenciesAndOtherBodiesScraper extends EUScraper {
  function scrape(){
    foreach($this->xpath->query('//td/table//tr/td/table//tr/td/ul/li/a') as $el){
        $linkText = $el->textContent;
        $linkHref = $el->getAttribute('href');
        $scraper = new AgencyListingScraper('http://europa.eu/whoiswho/public/'.$linkHref, $this->publicInstitutionsGraph);
        $scraper->scrape();
        $this->graph->add_graph($scraper->get_graph());
    }
  }
}


class AgencyListingScraper extends EUScraper {
  function scrape(){
    
    foreach($this->xpath->query('//td/table//tr/td/table//tr/td/ul/li/a') as $el){
        $linkText = $el->textContent;
        $linkHref = $el->getAttribute('href');
        $scraper = new InstitutionScraper('http://europa.eu/whoiswho/public/'.$linkHref, $this->publicInstitutionsGraph);
        $scraper->scrape();
        $this->graph->add_graph($scraper->get_graph());
    }
  }
}



?>
