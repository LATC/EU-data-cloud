<?php
/*
 * GGeocoderParserLib v.1.0
 * GGeocoderParserLib is a PHP library (class) that lets you query Google Geocoder Service via Google Geocoding API and parses the response so it provides a very easy way to handle the JSON data in PHP.
 *
 * Permission is hereby granted, free of charge, to any person to use this Software without restriction,
 * the rights to use, copy, modify, publish and distribute copies of the Software or do whatever.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND. IN NO EVENT SHALL THE AUTHORS
 * BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY ARISING FROM THE USE OF THIS SOFTWARE.
 *
 * Copyright 2010 moohy.com
 * AUTHOR: Narcis Paun
 */
// edit this if it changes over time
define("mooGEOCODING_SERVER", "maps.google.com");
define("mooGEOCODING_SERVER_PATH", "/maps/api/geocode/json");
// !!! no config below this line.................................................

//added by Lucas to return the data for database (this could be better implemented)
function get_geocoder_address($q)
{
	$ggeo = get_ggeocoder_json($q);

	$address['formatted_address']=$ggeo->results['formatted_address'];

	$address['latitude']=$ggeo->results['latitude'];

	$address['longitude']=$ggeo->results['longitude'];

	$address['viewport_lat_southwest']=$ggeo->results['viewport_lat_southwest'];

	$address['viewport_lng_southwest']=$ggeo->results['viewport_lng_southwest'];

	$address['viewport_lat_northeast']=$ggeo->results['viewport_lat_northeast'];

	$address['viewport_lng_northeast']=$ggeo->results['viewport_lng_northeast'];

	$address['country']=$ggeo->find_address_components('country','long_name');

	$address['country_code']=$ggeo->find_address_components('country','short_name');

	$address['administrative_area_level_1']=$ggeo->find_address_components('administrative_area_level_1','long_name');

	$address['administrative_area_level_2']=$ggeo->find_address_components('administrative_area_level_2','long_name');

	$address['locality']=$ggeo->find_address_components('locality','long_name');

	$address['postal_code']=$ggeo->find_address_components('postal_code','long_name');

	$address['route']=$ggeo->find_address_components('route','long_name');

	$address['street_number']=$ggeo->find_address_components('street_number','long_name');

	if (isset($address))
		return $address;
	else
		return FALSE;
}

//sanitization and encoding functions (this could be better implemented)
function moo_sanitize_str_paranoid_mode($data){
	$data = preg_replace("/[^a-zA-Z0-9 ,]/", "", $data);
return $data;
}
function moo_req_encode ($data) {
	$data = urlencode( stripslashes($data) );
return $data;
}

// socketopen based function, if needed could be replaced with CURL etc.
function moo_socket_get($host, $path, $req='', $port = 80) {
	$http_request  = "GET $path$req HTTP/1.0\r\n";
	$http_request .= "Host: $host\r\n";
	//$http_request .= "Content-Type: application/x-www-form-urlencoded;\r\n";
	$http_request .= "Content-Type: text/html\r\n";
	//$http_request .= "Content-Length: " . strlen($req) . "\r\n";
	$http_request .= "User-Agent: GGeocoderParserLib(PHPlib-V1)\r\n";
	$http_request .= "Connection: Close\r\n";
	$http_request .= "\r\n";
	//$http_request .= $req;
	$response = '';
	if( false == ( $fs = @fsockopen($host, $port, $errno, $errstr, 10) ) ) {
		die ('Could not open socket in order to connect');
	}
	fwrite($fs, $http_request);
	while ( !feof($fs) ){
		$response .= fgets($fs, 1500);
	}
	fclose($fs);
return $response;
}

// get G geocoder json from G geocoder service
function get_ggeocoder_json($address='',$latlng='',$language='en',$sensor ='false') {
	$ggeo = new simple_ggeocoder_json_parser($address,$latlng,$language,$sensor);
    return $ggeo;
}

class simple_ggeocoder_json_parser {
    public $status = 'MOOGEOCODING:NONE';
	public $address = '';
	public $header = '';
    public $json = '';
	public $plaintext = '';
    public $results = array();
    public $obj = null;

    function __construct($address='',$latlng='',$language='en',$sensor ='false') {
    	$this->address = $address;
		$this->language = $language;
		$this->status = 'MOOGEOCODING:INIT';
		$this->load($address,$latlng,$language,$sensor);
		$this->parse_default();
    }
	function load($address,$latlng,$language,$sensor){
		//some sanitization and encoding and empty string detection again
		$address=urlencode($address);
		$latlng=moo_req_encode(moo_sanitize_str_paranoid_mode($latlng));
		if ( empty($address) && empty($latlng) ) {
			$this->status = "MOOGEOCODING:UNSUBMITTED,WRONG_ADDRESS"; //something illegal in the string
			return FALSE;
		}
		$server=mooGEOCODING_SERVER;
		$server_path=mooGEOCODING_SERVER_PATH;
		$params='?address='.$address.'&language='.$language.'&sensor='.$sensor;

		$server_response = moo_socket_get($server,$server_path,$params);
		$pos = strpos($server_response, 'OK');
		if ($pos === false) {
			$this->status = "MOOGEOCODING:FAILED";
			return FALSE;
		}
		$this->plaintext = $server_response;
		$pieces = explode("\r\n\r\n", $server_response); // split header/content
		$this->header=$pieces[0];
		$this->json=$pieces[1];
		$this->obj = json_decode($this->json);
		//echo '<pre>'; print_r($obj); echo '<pre>';
		$this->status = "MOOGEOCODING:PASSED";
		return TRUE;
	}
	function parse_default(){
		$this->results['status']=$this->obj->status;
		if($this->results['status']!=='OK'){
			return FALSE;
		}
		$this->results['count']=count($this->obj->results);
		if($this->results['count']<1){
			return FALSE;
		}
		//get some values only from first result, add whatever you may need :)
		$this->results['formatted_address']=$this->obj->results[0]->formatted_address;
		$this->results['latitude']=$this->obj->results[0]->geometry->location->lat;
		$this->results['longitude']=$this->obj->results[0]->geometry->location->lng;
		$this->results['viewport_lat_southwest']=$this->obj->results[0]->geometry->viewport->southwest->lat;
		$this->results['viewport_lng_southwest']=$this->obj->results[0]->geometry->viewport->southwest->lng;
		$this->results['viewport_lat_northeast']=$this->obj->results[0]->geometry->viewport->northeast->lat;
		$this->results['viewport_lng_northeast']=$this->obj->results[0]->geometry->viewport->northeast->lng;
		return TRUE;
	}
	function find_address_components($type,$value) {
		if($this->results['status']!=='OK'){
			return FALSE;
		}
		foreach($this->obj->results[0]->address_components as $k=>$found){
			//echo '<pre>';print_r($found);echo '</pre>';
			if(in_array($type, $found->types)){
				return $found->$value;
			}
		}
		return FALSE;
	}

    function __destruct() {
        $this->clear();
    }
    // clean up memory
    function clear() {
        $this->status = null;
        $this->address = null;
        $this->header = null;
        $this->json = null;
		$this->plaintext = null;
		$this->results = null;
		$this->obj = null;
    }

}