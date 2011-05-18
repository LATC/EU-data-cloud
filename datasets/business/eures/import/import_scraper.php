<?php
######################################
# PHP scraper
# Scraps data from EURES jobs
######################################

ini_set('max_execution_time', 0);

ini_set('memory_limit', '-1');

include("config.php");

require 'scraperwiki/scraperwiki.php';

require 'scraperwiki/simple_html_dom.php';

include 'geocoderParser/GGeocoderParserLib.v1.php';


function insert_address($country)
{	
	$dir = '/var/www/latc/datasets/business/eures/import/jobs/'.$country;
	if ($handle = opendir($dir)) 
	{
		while (false !== ($file = readdir($handle))) 
		{
			$html = scraperwiki::scrape("http://localhost/latc/datasets/business/eures/import/jobs/".$country."/".$file);

			$dom = new simple_html_dom();
			$dom->load($html);
			
			foreach($dom->find('th') as $data)
			{  

				$text = trim($data->plaintext);  

				if($text == 'Address:') 
				{
					$value = trim($data->next_sibling()->plaintext);

					$address = db_prep($value);  

					echo "ADDRESS: ".$address."<br />";

					$sql = mysql_query("SELECT id FROM geo WHERE LCASE(address) = $address") or die (mysql_error());

					$cont = mysql_num_rows($sql);

					if ($cont == 0 && $address <> NULL)
					{
						mysql_query("INSERT INTO geo SET address = $address") or die (mysql_error());	
					}
					else
					{
						echo "Address already extracted.<br /><br />";
					}               
				}
			}
		}
		closedir($handle);
	}
	else
	{
		echo "Unable to open directory.";
	}
}

function update_address()
{
	$sql = mysql_query("SELECT address FROM geo WHERE formatted_address IS NULL LIMIT 0, 1000") or die (mysql_error()); 
	//improve WHERE

	while($row = mysql_fetch_array($sql))
	{
		$address = $row[0];	
		$address_array = get_geocoder_address($address);
		$query = "UPDATE geo SET
			formatted_address =".db_prep($address_array['formatted_address']).",
			country_id =".db_prep($address_array['country_id']).",
			administrative_area =".db_prep($address_array['administrative_area_level_1']).",
			subadministrative_area =".db_prep($address_array['administrative_area_level_2']).",
			locality =".db_prep($address_array['locality']).",
			route =".db_prep($address_array['route']).",
			street_number =".db_prep($address_array['street_number']).",
			postal_code =".db_prep($address_array['postal_code']).",
			latitude =".db_prep($address_array['latitude']).",
			longitude =".db_prep($address_array['longitude']).",
			lat_southwest =".db_prep($address_array['viewport_lat_southwest']).",
			lng_southwest =".db_prep($address_array['viewport_lng_southwest']).",
			lat_northeast =".db_prep($address_array['viewport_lat_northeast']).",
			lng_northeast =".db_prep($address_array['viewport_lng_northeast'])."
		WHERE address =".db_prep($address);
		mysql_query($query) or die (mysql_error());		
	}
}

function db_prep($data)
{
   if (isset($data) and $data != ''){
	$prepped = "'" . mysql_real_escape_string(trim($data)) . "'";
   }
   else {
      $prepped = "NULL";
   }
   return $prepped;
}

insert_address("DE");

//update_address();

?>

