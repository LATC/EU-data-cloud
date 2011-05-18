<?php
######################################
# PHP scraper
# Scraps the URLs for EURES jobs
######################################

ini_set('max_execution_time', 0);
//ini_set('memory_limit', '-1');

require 'scraperwiki/scraperwiki.php';

require 'scraperwiki/simple_html_dom.php';

//$country = array ('AT', 'BG','CY','CZ','DK','EE','FI','FR','DE','GR','HU','IS','IR','IT','LV','LI','LT','LU','MT','NL','NO','PL','PT','RO','SK','SI','ES','SE','CH','UK','BE');
$country = array('MT');

$page_size = 99;

$day = "01";

$month = "01";

$year = "1975";

/*
$day = date("d") - 1;

$month = date("m");

$year = date("Y");
*/

$isco = array (1, 11, 111, 1110, 112, 1120, 113, 1130, 114, 1141,1142,1143,12,121,1210,122,1221,1222,1223,1224,1225,1226,1227,1228,1229,123,1231,1232,1233,1234,1235,1236,1237,1238,1239,13,131,1311,1312,1313,1314,1315,1316,1317,1318,
1319,2,21,211,2111,2112,2113,2114,212,2121,2122,213,2131,2132,2139,214,2141,2142,2143,2144,2145,2146,2147,2148,2149,22,221,2211,2212,2213,222,2221,2222,2223,2224,2229,223,2230,23,231,2310,232,2320,233,2331,2332,234,
2340,235,2351,2352,2359,24,241,2411,2412,2419,242,2421,2422,2429,243,2431,2432,244,2441,2442,2443,2444,2445,2446,245,2451,2452,2453,2454,2455,246,2460,25,3,31,311,3111,3112,3113,3114,3115,3116,3117,3118,3119,312,3121,
3122,3123,313,3131,3132,3133,3139,314,3141,3142,3143,3144,3145,315,3151,3152,32,321,3211,3212,3213,322,3221,3222,3223,3224,3225,3226,3227,3228,3229,323,3231,3232,324,3241,3242,33,331,3310,332,3320,333,3330,334,3340,
34,341,3411,3412,3413,3414,3415,3416,3417,3419,342,3421,3422,3423,3429,343,3431,3432,3433,3434,3439,344,3441,3442,3443,3444,3449,345,3450,346,3460,347,3471,3472,3473,3474,3475,348,3480,4,41,411,4111,4112,4113,4114,
4115,412,4121,4122,413,4131,4132,4133,414,4141,4142,4143,4144,419,4190,42,421,4211,4212,4213,4214,4215,422,4221,4222,4223,43,44,45,5,51,511,5111,5112,5113,512,5121,5122,5123,513,5131,5132,5133,5139,514,5141,5142,
5143,5149,515,5151,5152,516,5161,5162,5163,5169,52,521,5210,522,5220,523,5230,6,61,611,6111,6112,6113,6114,612,6121,6122,6123,6124,6129,613,6130,614,6141,6142,615,6151,6152,6153,6154,62,621,6210,7,71,711,7111,7112,
7113,712,7121,7122,7123,7124,7129,713,7131,7132,7133,7134,7135,7136,7137,714,7141,7142,7143,72,721,7211,7212,7213,7214,7215,7216,722,7221,7222,7223,7224,723,7231,7232,7233,724,7241,7242,7243,7244,7245,73,731,7311,
7312,7313,732,7321,7322,7323,7324,733,7331,7332,734,7341,7342,7343,7344,7345,7346,74,741,7411,7412,7413,7414,7415,7416,742,7421,7422,7423,7424,743,7431,7432,7433,7434,7435,7436,7437,744,7441,7442,75,751,7510,752,
7520,79,8,81,811,8111,8112,8113,812,8121,8122,8123,8124,813,8131,8139,814,8141,8142,8143,815,8151,8152,8153,8154,8155,8159,816,8161,8162,8163,817,8171,8172,82,821,8211,8212,822,8221,8222,8223,8224,8229,823,8231,8232,
824,8240,825,8251,8252,8253,826,8261,8262,8263,8264,8265,8266,8269,827,8271,8272,8273,8274,8275,8276,8277,8278,8279,828,8281,8282,8283,8284,8285,8286,829,8290,83,831,8311,8312,832,8321,8322,8323,8324,833,8331,
8332,8333,8334,834,8340,9,91,911,9111,9112,9113,912,9120,913,9131,9132,9133,914,9141,9142,915,9151,9152,9153,916,9161,9162,92,921,9211,9212,9213,93,931,9311,9312,9313,932,9321,9322,933,9331,9332,9333,0,1,11,110);

/*

Belgium BE: 103392 job(s)

France FR: 48423 job(s)

Spain ES: 324 job(s)

Austria AT: 34808 job(s)

Cyprus CY: 1156 job(s)

Czech Republic CZ: 14984 job(s)

Denmark DK: 4131 job(s)

Germany DE: 377403 job(s)

Greece GR: 689 job(s)

Hungary HU: 3049 job(s)

Iceland IS: 164 job(s)

Ireland IR: 2853 job(s)

Italy IT: 1872 job(s)

Latvia LV: 48 job(s)

Liechtenstein LI: 149 job(s)

Lithuania LT: 1163 job(s)

Luxembourg LU: 411 job(s)

Netherlands NL: 38770 job(s)

Norway NO: 10078 job(s)

Poland PL: 12269 job(s)

Portugal PT: 1995 job(s)

Romania RO: 84 job(s)

Slovenia SI: 1124 job(s)

Sweden SE: 27126 job(s)

Switzerland CH: 2399 job(s)

United Kingdom UK: 374057 job(s)

Bulgaria BG: 28 job(s)

Estonia EE: 10 job(s)

Finland FI: 16593 job(s)

Malta MT: 6 job(s)

Slovakia SK: 2311 job(s)

*/

//Loop all the countries in the array
for ($i=0; $i < sizeof($country); $i++)
{
	
	$thisdir = getcwd();

	$jobs_dir = $thisdir ."/jobs";	
	//Creates the directory for jobs	
	if (!file_exists($jobs_dir))
	{
		if(mkdir($jobs_dir, 0777))
		{
			echo "Directory for jobs has been created successfully.";
		}
		else
		{
			echo "Failed to create directory.";
		} 
	}
	else
	{
		echo "Directory jobs already exists.";
	}

	$country_dir = $thisdir ."/jobs/".$country[$i];
	if (!file_exists($country_dir))
	{
		//Creates the directory for country
		if(mkdir($country_dir, 0777)){
			echo "Directory for country has been created successfully.";
		}
		else
		{
			echo "Failed to create directory.";
		} 
	}
	else
	{
		echo "Directory for this country already exists.";
	}

	$date = date ("Y-m-d");

	//Uses ISCO code in the search for countries with many jobs to avoid breaking script
	if ($country[$i] == 'DE' || $country[$i] == 'UK' || $country[$i] == 'BE' || $country[$i] == 'AT' || $country[$i] == 'FR' || $country[$i] == 'NL' || $country[$i] == 'SE')
	{ 
		for ($j=0; $j < sizeof($isco); $j++)
		{
			$url_first = "http://ec.europa.eu/eures/eures-searchengine/servlet/BrowseCountryJVsServlet?lg=EN&isco=".$isco[$j]."&country=".$country[$i]."&multipleRegions=%25&date=".$day."%2F".$month."%2F".$year."&title=&durex=&exp=&qual=&pageSize=".$page_size."&totalCount=999999999&startIndexes=0-1o1-1o2-1I0-2o1-30o2-1I0-3o1-59o2-1I0-4o1-88o2-1I&page=1";

			scraper($url_first, $country[$i]);
		}
	}
	//Uses only the country at the search
	else
	{
		$url_first = "http://ec.europa.eu/eures/eures-searchengine/servlet/BrowseCountryJVsServlet?lg=EN&isco=&country=".$country[$i]."&multipleRegions=%25&date=".$day."%2F".$month."%2F".$year."&title=&durex=&exp=&qual=&pageSize=".$page_size."&totalCount=999999999&startIndexes=0-1o1-1o2-1I0-2o1-30o2-1I0-3o1-59o2-1I0-4o1-88o2-1I&page=1";	

		scraper($url_first, $country[$i]);
	}
	
}

//Scraps the search page for URLs and other data (source, description) 
function scraper($url_search, $country_id)
{
	unset($url_next_size,$url_next,$has_next,$thisdir,$url_first,$date);

	$scraper_date = date("Y-m-d");

	$has_next = false;
	$url_size = strlen($url_search);

	$base_url = "http://ec.europa.eu/eures/eures-searchengine/servlet";
	$scraper_hour = date("H:i:s");
	
	$html = scraperwiki::scrape($url_search);  

	$dom = new simple_html_dom();
	$dom->load($html);

	//Gets each job result
	foreach($dom->find('table[class=JResult]') as $result)
	{
		//Gets the job URL
		foreach($result->find('td[class=JRTitle] a') as $job_page)
		{
			$chars = explode("'",$job_page->onclick);

			$url_job_unique = substr($chars[1], 1);
 
			$url_job = $base_url.$url_job_unique;

			$url_id = strstr($url_job, 'uniqueJvId=');

			$url_id = str_replace('uniqueJvId=',"",$url_id);

			$url_job_unique = str_replace('/ShowJvServlet?lg=EN&serviceUri=',"",$url_job_unique);

			$url_job_unique_slashless = str_replace('/',"*",$url_job_unique);

			echo "JOB: " .$url_job . "<br />";
		};

		$file = 'jobs/'.$country_id.'/'.$url_job_unique_slashless.'.html';
		if (!file_exists($file))
		{
			//Gets the job description and source
			foreach($result->find('th') as $data)
			{   
				$text = trim($data->plaintext);

				if ($text == 'Description:')
				{
					$description = trim($data->next_sibling()->plaintext);
					echo "DESCRIPTION: " .$description. "<br />";    
				}

				if ($text == 'Source:')
				{
					$source = trim($data->next_sibling()->plaintext);
					echo "SOURCE: " .$source. "<br /><br />";   
				}
			}

			//Gets the HTML from the Job
			$html_job = scraperwiki::scrape($url_job);

			//Saves the search data in a CSV file
			$fp = fopen('jobs/'.$country_id.'_'.$scraper_date.'.csv', 'a+');
			$list = array (
			    array($url_job, $url_id, $url_job_unique, $url_job_unique_slashless,$description, $source,$url_search,$country_id,$scraper_date,$scraper_hour)
			);

			foreach ($list as $fields) {
			    fputcsv($fp, $fields);
			}
			fclose($fp);

			//Saves the HTML in a file
			$fh = fopen($file, 'w');
			fwrite($fh,'<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />');
			fwrite($fh,$html_job);
			fwrite($fh,'<div id=url_job>'.$url_job.'</div>');
			fwrite($fh,'<div id=country_id>'.$country_id.'</div>');
			fwrite($fh,'<div id=description>'.$description.'</div>');
			fwrite($fh,'<div id=source>'.$source.'</div>');
			fwrite($fh,'<div id=url_id>'.$url_id.'</div>');
			fwrite($fh,'<div id=url_job_unique>'.$url_job_unique.'</div>');
			fwrite($fh,'<div id=url_job_unique_slashless>'.$url_job_unique_slashless.'</div>');
			fwrite($fh,'<div id=url_search>'.$url_search.'</div>');
			fwrite($fh,'<div id=scraper_date>'.$scraper_date.'</div>');
			fwrite($fh,'<div id=scraper_hour>'.$scraper_hour.'</div>');
			fclose($fh);
		}
		else
		{
			echo "Job already extracted.";
		}	
	}

	//Gets the next search page
	foreach($dom->find('div[class=prevNext] a') as $next_page)
	{
		$text = $next_page->plaintext;

		if ($text == "Next page")
		{
			$url_next = substr($next_page->href, 1);

			$url_next = $base_url.$url_next;

			$has_next = true;

			print "<br /><br />NEXT: " . $url_next . "<br /><br />";
		}   
		
	};

	unset($html,$dom,$result,$job_page,$data,$next_page,$text,$url_id,$url_job,$description,$source,$source_id,$url_search,$html_job,$scraper_date,$scraper_hour,$url_job_unique,$url_job_unique_slashless,$chars,$fh,$list,$file);

	//Calls the next search page to scrap 	
	if ($has_next == true){
		if ($url_size <= 4101)
		{
			sleep(1); //waits before the next extraction
			scraper($url_next, $country_id);
		}
		else
		{
			echo "Page URL size is to big.";
		}
	}
	else
	{
		echo "No more pages to scrap.";
	}
	
}

?>
