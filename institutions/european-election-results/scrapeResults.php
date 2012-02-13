<?php
// Script to scrape EU election results data as loaded into http://kasabi.com/dataset/european-election-results
// path to moriarty and arc libraries on your server
define('MORIARTY_DIR', 'lib/moriarty/');
define('MORIARTY_ARC_DIR', 'lib/arc/');

define('PROJECT_ROOT', dirname(__FILE__).'/' );

date_default_timezone_set("Europe/London"); // stops warning from arc2 bubbling up in simple_html_dom and causing latter to fail.

require 'lib/simplehtmldom/simple_html_dom.php';
// use Moriarty code to do stuff
require_once MORIARTY_DIR . 'moriarty.inc.php';
require_once MORIARTY_DIR . 'simplegraph.class.php';

class Election_Results {
    var $PARTYNAMES;
    var $dom;
    var $countryCodes;
    var $data;
    var $observationId;

    function __CONSTRUCT($URL){
        $this->dom = new simple_html_dom();
        if(!$this->dom->load_file($URL)){
            throw new Exception("The document could not be loaded in entirety!");
        }
        $this->writeLog("Initiated for URL {$URL}\n");
        $this->PARTYNAMES = $this->getPartyNames();
    }

    function parseSeatsByPartyByStateTables(){
    # this will get both the number of seats and percentage of vote tables.
        $tables = array();
        foreach($this->dom->find("table[@summary='Seats by political group in each Member State']") as $data){
            
            #get the political groupings
            foreach($data->find("th[@scope='col'] abbr") as $k => $abb ){
                $record[$k] = array(
                    'PoliticalGroupAbbr' => $abb->plaintext, 
                    'PoliticalGroupName' => $abb->title
                );
            }

            
            #get total seats by party by country
            foreach($data->find("tr.countryglobal") as $v){

                $country = $v->find("th img");
                $countryName = $country[0]->alt;

                $country = $v->find("th");
                $countryCode = strtoupper(preg_replace("/.*country_/","",$country[0]->id));

                $this->countryCodes[$countryName] = $countryCode ;
                $this->countryCodes[$countryCode] = $countryName ;

                foreach($v->find("td") as $k => $v2){
                    if($k % 2){ # only even keys (others are blank cells)
                        $record[($k/2)][$countryCode]['seatCount'] = $v2->plaintext;
                    }
                }
            }

            #get seats by national party by country
            foreach($data->find("tr") as $v){
                if($v->class == 'firsttr'){
                    #echo "ignoring 'firsttr'\n";
                }
                else if($v->class == 'countryglobal'){
                    #echo "ignoring 'countryglobal'\n";
                }
                else {
                    #echo "usable row\n";
                    
                    $cells = $v->children();
                    $flag=false;
                    foreach($cells as $k => $v2){ // iterate through cells looking for counts per party
                        if($v2->tag == 'th'){
                            $np = $v2->plaintext ;
                        }
                        if($v2->tag == 'td'){
                            if($v2->class == 'tdabbr'){ 
                                $cc = $v2->plaintext;
                                $flag = true ;
                            }else{
                                $val = $v2->plaintext ;
                            }
                        }
                        // if the row starts with a cell that contains the country code, adjust our counting accordingly.
                        if((($k%2) == 0 )&& $flag==true && $np != '' && $val != ''){
                            #echo "$k => $cc => $np => $val\n";
                            
                            $record[(($k-1)/2)][$cc][$np] = $val ;
                            
                            $np = ''; 
                            $val= '';
                        // get the data from the rows, skipping every other (blank) cell.
                        }else if (($k%2)!=0 && $flag==false && $np != '' && $val != ''){
                            #echo "$k => $cc => $np => $val\n";
                            
                            $record[($k/2)][$cc][$np] = $val ;
                            
                            $np = ''; 
                            $val= '';
                        }
                    }
                }
            }
            // each table is added to a numerically indexed array.
            $this->data[] = $record;    
        }
    }

    # get the names of the parties and their abbreviations
    function getPartyNames(){
        $this->writeLog("Getting Party Names");
        //echo print_r($this->dom,true);
        try {
            $list = $this->dom->find("div.legend li");

        }catch(Exception $e){
            echo "Caught exception: ".$e->getMessage();
            echo $e->getTraceAsString();
        }
         foreach($list as $data){
            $abbr = $data->children(0)->plaintext;
            $name = explode(":",$data->plaintext);
            array_shift($name);
            $name = join(":",$name );
            $partyNames[$abbr]=$name;

            }

        #print_r($partyNames);
        return $partyNames;
    }

    function lookupPartyName($abbr){
        return $this->PARTYNAMES[$abbr];
    }
    
    function getPartyURI($base, $id){
        $uri = $base."national_party/".$this->uri_safe($id);
        
        return $uri;
    }

    function lookupCountry($val){
        return $this->countryCodes[$val];
    }

    // get data as the seat counts
    function getSeatsByPartyByStateTableCounts(){
        return $this->data[0];
    }
    
    // get the data as percentge of all seats.
    function getSeatsByPartyByStateTablePercentages(){
        return $this->data[1];
    }

    // get an observation id
    function getObservationId(){
        return ++$this->observationId;
    }

    function uri_safe($val){
        $val = preg_replace("/ /", "", $val);
        $val = preg_replace("/[^A-Za-z0-9]/", "_", $val);
        $val = preg_replace("/_+/", "_", $val);
        $val = strtolower($val);
        return $val;
    }

    function writeLog($msg){
        echo $msg."\n";
    }
}



$ee = new Election_Results("http://www.europarl.europa.eu/parliament/archive/elections2009/en/national_parties_en_txt.html");
$ee->parseSeatsByPartyByStateTables();

print_r($electionCountData);

//echo $ee->lookupCountry("BE");

$data_base_uri = "http://data.kasabi.com/dataset/european-election-results/";
$schema_base_uri = "http://data.kasabi.com/dataset/european-election-results/def/";

# build a graph
$graph = new SimpleGraph();
//$def = new SimpleGraph() ;
$graph->add_turtle(file_get_contents(PROJECT_ROOT.'defs/eu-dataset-definition.ttl'));

$graph->set_namespace_mapping('eedef', $schema_base_uri);
$graph->set_namespace_mapping('ee', $data_base_uri);
$graph->set_namespace_mapping('qb', 'http://purl.org/linked-data/cube#');
$graph->set_namespace_mapping('dct', 'http://purl.org/dc/terms/');

$electionPercentageData = $ee->getSeatsByPartyByStateTablePercentages();

// seats won counts
foreach ($ee->getSeatsByPartyByStateTableCounts() as $k1 => $v ){

    foreach($v as $k2 => $val){
        
        if($k2 == 'PoliticalGroupAbbr'){
            // add political groups
            $political_group = $data_base_uri."political_group/".$ee->uri_safe($v['PoliticalGroupAbbr']);
            $graph->add_resource_triple($political_group,  $graph->qname_to_uri("rdf:type"), $schema_base_uri."PoliticalGroup");
            $graph->add_literal_triple($political_group,  $graph->qname_to_uri("rdfs:label"), html_entity_decode($v['PoliticalGroupName'],ENT_QUOTES));
            $graph->add_literal_triple($political_group,  $graph->qname_to_uri("dct:identifier"), html_entity_decode($v['PoliticalGroupAbbr'],ENT_QUOTES));
        
        }
        if(preg_match( "/^[A-Z][A-Z]$/", $k2 )){
            // add country
            $country_name = $ee->lookupCountry($k2);
            $country_code = $k2;
            $country_uri =  $data_base_uri."country/".$ee->uri_safe($country_code);
            $graph->add_resource_triple($country_uri ,  $graph->qname_to_uri("rdf:type"), $schema_base_uri."Country");
            $graph->add_literal_triple($country_uri ,  $graph->qname_to_uri("rdfs:label"), $country_name);
            $graph->add_literal_triple($country_uri ,  $graph->qname_to_uri("dct:identifier"), $country_code);

            // add results per country
            foreach($val as $party => $count){
                if ($party == 'seatCount'){
                    // total count for the political group
                    $totalCount = $count;
                    $totalPercentage = $electionPercentageData[$k1][$k2][$party] ;

                    $observation =  $data_base_uri."2009/political_groups/observation/".$ee->getObservationId();
                    $graph->add_resource_triple($observation, $graph->qname_to_uri('rdf:type'),  $graph->qname_to_uri('eedef:ElectionResult'));
                    $graph->add_resource_triple($observation, $graph->qname_to_uri('eedef:year'), 'http://reference.data.gov.uk/id/year/2009');
                    $graph->add_resource_triple($observation, $graph->qname_to_uri('eedef:votingCountry'), $country_uri);
                    $graph->add_resource_triple($observation, $graph->qname_to_uri('eedef:politicalGroup'), $political_group);
                    $graph->add_literal_triple($observation,  $graph->qname_to_uri('eedef:seatsWon') , $totalCount );
                    $graph->add_literal_triple($observation,  $graph->qname_to_uri('eedef:percentageOfSeatsWon') , $totalPercentage );
                    $graph->add_resource_triple($observation, $graph->qname_to_uri('qb:dataset') , $data_base_uri.'2009/political_groups/seats_won');

                }
                else{
                    // seats won by the national parties
                    $obs2 =   $data_base_uri."2009/national_parties/observation/".$ee->getObservationId();
                    $graph->add_resource_triple($obs2, $graph->qname_to_uri('rdf:type'),  $graph->qname_to_uri('eedef:ElectionResult'));
                    $graph->add_resource_triple($obs2, $graph->qname_to_uri('eedef:year'), 'http://reference.data.gov.uk/id/year/2009');
                    $graph->add_resource_triple($obs2, $graph->qname_to_uri('eedef:votingCountry'), $country_uri);
                    $graph->add_resource_triple($obs2, $graph->qname_to_uri('eedef:nationalParty'), $ee->getPartyURI($data_base_uri, $party));
                    $graph->add_literal_triple($obs2, $graph->qname_to_uri('eedef:seatsWon') , $count );
                    $graph->add_literal_triple($obs2, $graph->qname_to_uri('eedef:percentageOfSeatsWon') , $electionPercentageData[$k1][$k2][$party] );
                    $graph->add_resource_triple($obs2, $graph->qname_to_uri('qb:dataset') ,  $data_base_uri.'2009/political_groups/seats_won');


                    $graph->add_resource_triple($ee->getPartyURI($data_base_uri , $party),  $graph->qname_to_uri('rdf:type'),  $graph->qname_to_uri('eedef:NationalParty'));
                    $graph->add_resource_triple($ee->getPartyURI($data_base_uri , $party),  $graph->qname_to_uri('rdfs:label'), html_entity_decode($ee->lookupPartyName($party),ENT_QUOTES ));


                }
            }

        }
    }
}

echo $graph->to_turtle();
//echo $def->to_turtle();

file_put_contents(PROJECT_ROOT."rdf/election-results.nt", $graph->to_ntriples());


// TODO
// link political groups to national parties. (columns in the original HTML)
// do we need slices?
//

/*
 * country specific scrapings
 *
 *
 */


// http://www.europarl.europa.eu/parliament/archive/elections2009/en/belgium_en_txt.html
// 





?>
