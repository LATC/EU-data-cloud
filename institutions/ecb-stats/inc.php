<?php
define('MORIARTY_ARC_DIR', 'arc/');

define('NS', 'http://ecb.publicdata.eu/');
define('KASABI_COUNTRIES', 'http://data.kasasbi.com/dataset/countries/');

require_once 'moriarty/simplegraph.class.php';

function log_message($msg){
  return error_log($msg."\n",3,'errors.log');
}

class Utils {

  const base = 'http://reference.data.gov.uk/';

  public static $geographyCodeMappings = array(
  'IG' => 'intermediate-geography',
  'DZ' => 'datazone',
  'ZN' => 'datazone',
  'CHP' => 'community-health-partnership',
  'CPP' => 'community-planning-partnership',
  'LA' => 'local-authority',
  'HB' => 'health-board',
  'SP' => 'scottish-parliamentary-constituency-2007',
  'P2' => 'scottish-parliamentary-constituency-2011',
  'W2' => 'ward',
  'RC' => 'community-regeneration-community-planning-partnership',
  'RL' => 'community-regeneration-local',
  'MW' => 'multi-member-ward',
  'CH' => 'community-health-partnership',
  'SC' => 'scotland',
  'COA' => '2001-census-output-areas',
  'N2' => 'NUTS2',
  'N3' => 'NUTS3',
  'N4' => 'NUTS4',
  'U6' => '6-fold-urban-rural-classification',
);


  function dateToURI($date){

    $calendarYear = '/^[0-9]{4}$/';
    $calendarYearWithSpaces = '/^[0-9]{4}\W+$/';
    $multiYearSpan = '/^([0-9]{4})-([0-9]{4})$/';
    $multiYearSpanSlash = '/^([0-9]{4})\/([0-9]{4})$/';
    $yearWithMonth = '/^([0-9]{4})M([0-9]{2})$/';
    $yearWithQuarter = '/^([0-9]{4})Q([0-9]{2})$/';
    $governmentYear = '/^([0-9]{4})\/([0-9]{4})/';
    $governmentYearmentYear = '/^([0-9]{4})\/([0-9]{4})/';
    $yearStartingFirstApril = '/^([0-9]{4})\/([0-9]{2})-([0-9]{4})\/([0-9]{2})$/';
    $financialYearWithQuarter = "/^([0-9]{4})\/([0-9]{2})Q([0-9]{2})/";
    $dateTimeDayMonthYearTime = '@^(\d\d)/(\d\d)/(\d\d\d\d) (\d\d:\d\d:\d\d)@';
      
    if(preg_match($calendarYear, $date, $m)){
      return self::base.'id/year/'.$m[0];
    } else if(preg_match($calendarYearWithSpaces,$date, $m)){
      return self::base.'id/year/'.trim($m[0]);
    } else if(preg_match($multiYearSpan, $date, $m)){
      $difference = $m[2] - $m[1];
      return self::base.'id/gregorian-interval/'.$m[1].'-01-01T00:00:00/P'.$difference.'Y';
    }  else if(preg_match($multiYearSpanSlash, $date, $m)){
      $difference = $m[2] - $m[1];
      return self::base.'id/gregorian-interval/'.$m[1].'-01-01T00:00:00/P'.$difference.'Y';
    } else if(preg_match($yearWithMonth, $date, $m)){
      return self::base.'id/month/'.$m[1].'-'.$m[2];
    } else if(preg_match($yearWithQuarter, $date, $m)){
      return self::base.'id/quarter/'.$m[1].'-Q'.ltrim($m[2], '0');
    } else if(preg_match($governmentYear, $date, $m)){
      return self::base.'id/government-year/'.$m[1].'-'.$m[2];
     
    } else if(preg_match($yearStartingFirstApril, $date, $m)){
      $difference =  strtotime($m[3].'/'.$m[4].'/01') - strtotime($m[1].'/'.$m[2].'/01') ;
      $noOfSecondsInHour = 60 * 60 ;
      $difference =  $difference /  $noOfSecondsInHour ;
      return self::base.'id/gregorian-interval/'.$m[1].'-'.$m[2].'-01T00:00:00/PT'.$difference.'H';
      //return self::base.'id/government-interval/'.$m[1].'_'.self::sortDate($m[2]).'-'.$m[3].'_'.self::sortDate($m[4]);
    } else if(preg_match($financialYearWithQuarter, $date, $m)){
      return self::base.'id/government-quarter/'.$m[1].'-'.self::sortDate($m[2]).'/Q'.ltrim($m[3], '0');
    } else if(preg_match($dateTimeDayMonthYearTime, $date, $m)){

      return self::base.'id/gregorian-instant/'.$m[3]."-$m[2]-$m[1]T{$m[4]}";

    } else {
      log_message("$date not recognised as a date");
      return false;
    }

  }

  function sortDate($date){
    if($date > 1900) return $date;
    if($date > 95){
      return 1900 + $date;
    } else {
      return 2000 + $date;
    }
  }
}
?>
