<?php

function insert_address($address)
{	
	global $country_code;

	$address = db_prep($address);  
	$lowercase_address = strtolower($address);
	
	$sql = mysql_query("SELECT id FROM geo WHERE LOWER(address) = $lowercase_address") or die (mysql_error());
	//$sql = mysql_query("SELECT id FROM geo WHERE LOWER(address) = $lowercase_address AND country_code= '$country_code'") or die (mysql_error());

	if ((mysql_num_rows($sql) == 0) && ($address <> NULL))
	{
		mysql_query("INSERT INTO geo SET address = $address, country_code= '$country_code'") or die (mysql_error());	
	}
	else
	{
		echo "Address already extracted." . PHP_EOL;
	}            
}

function create_job_id()
{
	global $url_id;
	global $source;

	$sql = mysql_query("SELECT country_code,local_id FROM source WHERE name = ".db_prep($source));
	$row = mysql_fetch_array($sql);		
	$country_code = $row[0];
	$local_id = $row[1];
	
	$unique_id = $country_code."_".$local_id."_".$url_id;

	return $unique_id;
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
			country_code =".db_prep($address_array['country_code']).",
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

function format_hour($value)
{
	global $job_id;
	
	global $source_id;

	$hours['max'] = NULL;
	$hours['min'] = NULL;

	preg_match_all('/([0-9]+[.]?[0-9]*)/',$value,$matches);
	if (sizeof($matches[0]) > 1)
	{
		$hours['min'] = $matches[0][0];

		if ($matches[0][1] > $hours['min'])
		{
		   $hours['max'] = $matches[0][1];
		   $hours['max'] = preg_replace("/[,:]/", ".", $hours['max']);
		}
	}
	elseif (sizeof($matches[0]) == 1)
	{
		$hours['min'] = $matches[0][0];
	}
	else
	{
		write_log('logs/hours_per_week_errors.log',"$value\t$country_code\t$job_id\t$source_id\n");
	}
	$hours['min'] = preg_replace("/[,:]/", ".", $hours['min']);
	return $hours;
}

function translate($text) 
{
	$text = urlencode($text);
	$key = "AIzaSyDCCOqsDiY-Q0x5MQvMawqfDDhMaFL5sp0";
	 
	$trans = @file_get_contents("https://www.googleapis.com/language/translate/v2?key={$key}&q={$text}&target=en");

	$json = json_decode($trans, true);

	return $json['data']['translations'][0]['translatedText'];
}

function split_required_languages($required_languages)
{
	global $job_id;

	$iso639p3 = NULL;
	$ilr_level = NULL;

	$required_languages = preg_replace("/[\(\)]/","",$required_languages);	
	$split_language1 = preg_split("/[,;]/",$required_languages);

	foreach ($split_language1 as $spl1)
	{
		$split_language2 = preg_split("/[-\/]/",$spl1);
		if (isset($split_language2[0]))
		{
			$language = trim($split_language2[0]);
			//$iso639p3 = import_language_level($iso639p3);
		}
		if (isset($split_language2[1]))
		{
			$language_level = trim($split_language2[1]);
			//$ilr_level = import_language_level($language_level);
		}
		//mysql_query("INSERT INTO job_language SET job_id = '$job_id', iso639p3 ='$iso639p3', ilr_level =".db_prep($ilr_level)) or die (mysql_error());
	}
}

function import_language_level($language_level)
{
	//1. thing you need in that function: lanuage level, country, language
	global $iso639p3;
	//for test: $iso639p3 = 'ita';

	global $job_id;
	//for test: $job_id = 9;

	global $country_code;

	global $source_id;

	//2. lower case the language level
	$lowercase_language_level = strtolower($language_level);

	//3. check if column for language exists
	$sql = mysql_query("SHOW COLUMNS FROM language_level like '$iso639p3'");

	//4. otherwise create it	
	if ((mysql_num_rows($sql) == 0))
	{
		mysql_query("ALTER TABLE language_level ADD $iso639p3 SET('$lowercase_language_level') NOT NULL") or die (mysql_error());
	}

	//5. look up the language level in the table language_level for the language or English
	$sql = mysql_query("SELECT ilr_level FROM language_level WHERE $iso639p3 LIKE '%$lowercase_language_level%' OR eng LIKE '%$lowercase_language_level%'") or die (mysql_error());
	
	//6. if found: insert ilr_level and iso639p3 in the job_language table
	if ((mysql_num_rows($sql) <> 0))
	{
		$row = mysql_fetch_object($sql);
		$ilr_level = $row->ilr_level;
		
		return $ilr_level;	
	}
	//7. if not found:
	else
	{
             	//3. look up that language level label in google translate API (to English)
		//4. lower case the result
		$language_level_translate = translate($lowercase_language_level);
		//5. look up result in table language_level this english label (only in the english column)
		$sql = mysql_query("SELECT ilr_level FROM language_level WHERE eng LIKE '%$language_level_translate%'") or die(mysql_error());
		//6. if found:
		if ((mysql_num_rows($sql) <> 0))
		{
			$row = mysql_fetch_object($sql);	
			$ilr_level = $row->ilr_level;

			//FIXME! Hack by Lucas to ALTER TABLE FOR SET DATA TYPE TO CONCATENATE VALUES
			$sql_set = mysql_query("SHOW COLUMNS FROM language_level LIKE '$iso639p3'");
			$row_set = mysql_fetch_object($sql_set);	
			$set = $row_set->Type;
			$set = str_replace(")","",$set);
			mysql_query("ALTER TABLE language_level CHANGE $iso639p3 $iso639p3 $set,'$lowercase_language_level')") or die (mysql_error());

			//2. insert language level label original (but lower case) in that column
			mysql_query("UPDATE language_level SET $iso639p3 = CONCAT($iso639p3,',$lowercase_language_level') WHERE ilr_level = '$ilr_level'") or die (mysql_error());

			//1. (create if not exists) & open a log file called language_levels_added.log
			//open_log('language_levels_added.log');
			//3. write in language_levels_added.log, tab separated:- level original w/o lower case,- language,- translation found in google translate in english,- country,- job_id (uniquejvid!),- source_id
			write_log('logs/language_levels_added.log',"$language_level\t$iso639p3\t$language_level_translate\t$country_code\t$job_id\t$source_id\n");	

			//1. write language_level_id in job table
	
			return $ilr_level;		
		}
		//7. if not found:		
		else
		{
			//2. (create if not exists) & open a log file called language_levels_errors.log
			//open_log('language_levels_errors.log');
			//3. write in language_levels_errors.log, tab separated:- level original w/o lower case,- language,- translation found in google translate in english,- country,- job_id (uniquejvid!),- source_id
			write_log('logs/language_levels_errors.log',"$language_level\t$iso639p3\t$language_level_translate\t$country_code\t$job_id\t$source_id\n");
			return NULL;
		}
	}			
}

function import_language($language)
{
	//1. Thing you need in that function: language, country, job_id, source_id
	global $job_id;

	global $source_id;

	global $country_code;

	//2. Lower case the EURES language
	$lowercase_language = strtolower($language);

	//3. Select iso639p3 from language table where EURES language = LEXVO label
	$sql = mysql_query("SELECT iso639p3 FROM language WHERE labels LIKE'%$lowercase_language%'") or die (mysql_error());

	//4. If found return the iso639p3
	if ((mysql_num_rows($sql) <> 0))
	{
		$row = mysql_fetch_object($sql);
		$iso639p3 = $row->iso639p3;

		return $iso639p3;
	}
	//5. If not found	
	else
	{
		//1. look up that language in google translate API
		$language_translate = translate($lowercase_language);
		//2. look up result in table language this english label for iso639p3
		$sql = mysql_query("SELECT iso639p3 FROM language_code WHERE labels LIKE'%$language_translate%'") or die (mysql_error());
		//3. if found:
		if ((mysql_num_rows($sql) <> 0))
		{
			$row = mysql_fetch_object($sql);
			$iso639p3 = $row->iso639p3;

			//a. write in language_added.log
			write_log('logs/languages_added.log',"$language\t$iso639p3\t$language_translate\t$country_code\t$job_id\t$source_id\n");

			//b. return the iso639p3			
			return $iso639p3;		
		}
		else
		{
			//4. if not found write in language_errors.log
			write_log('logs/languages_errors.log',"$language\t$iso639p3\t$language_translate\t$country_code\t$job_id\t$source_id\n");
			return NULL;
		}
	}
}

function write_log($file,$text)
{
	if (!file_exists('logs'))
	{
		if(mkdir('logs', 0777))
		{
			echo "Directory for logs has been created successfully.";
		}
		else
		{
			echo "Failed to create directory.";
		} 
	}
	else
	{
		echo "Directory logs already exists.";
	}

	if( $fh = @fopen($file,'a+'))
	{
		fputs($fh, $text, strlen($text));
		fclose($fh);
		return(true);
	}
	else
	{
		return(false);
	}
}

function db_prep($data)
// Basic prep function - trims and escapes data
{
	if (isset($data) and $data != '')
	{
		$prepped = "'" . mysql_real_escape_string(trim($data)) . "'";
	}
	else 
	{
		$prepped = "NULL";
	}
	return $prepped;
}

function addhttp($url) {	
	if (!preg_match("~^(?:f|ht)tps?://~i", $url)) 
	{
		$url = "http://" . $url;
	}
	return $url;
}

function insert_name($table,$name_table)
{
// Insert name on the small tables and returns the id
	if($name_table <> '' && $name_table <> '0' && $name_table <> '.' && $name_table <> '**')
		mysql_query("INSERT INTO ".$table." SET name =".db_prep($name_table));
	$sql = mysql_query("SELECT id FROM ".$table." WHERE name =".db_prep($name_table));				
	$row = mysql_fetch_array($sql);		
	return $row[0];
}

function select_id($query)
{
// Select id based on the query or returns empty
	$sql = mysql_query($query);		
	$cont = mysql_num_rows($sql);
	if ($cont > 0)
	{
		$row = mysql_fetch_array($sql);
		$id = $row[0];
		return $id;
	}
	elseif ($cont == 0)
	{
		return '';	
	}
}

function format_salary($value) {
	global $job_id;

	$salary['amount'] = NULL;
	$salary['currency'] = NULL;
	$salary['period'] = NULL;

	$number = strtoupper($value);
	$period = array('STUNDE', 'MONATLICH', 'STD. LOHN', 'PER HOUR', 'PER D', 'PER DAY', 'PER ANNUM', 'PA', 'PER WEEK', 'PW', 'PH', 'PRO STUNDE','P.A.');
	$currency = array('€', 'EURO', 'GBP','SFR.','FR.');

	$number = str_replace("-","0",$number);

	$number = str_replace("'","",$number);

	if ($number == '0.00')
		return NULL;

	foreach ($currency as &$cur) 
	{
		if (preg_match('/'.$cur.'/',$number))
		{
			$salary ['currency'] = trim($cur);
			$number = str_replace ($cur,"",$number);
		}
	}

	foreach ($period as &$per) 
	{
		if (preg_match('/'.$per.'/',$number))
		{
			$salary ['period'] = trim($per);
			$number = str_replace ($per,"",$number);
		}
	} 

	if (!preg_match('/[A-Z_%-]/',$number)){
		$amount = preg_replace('/[^\d.]+/', '', $number);
		$salary['amount'] = sprintf('%01.2f', $amount);
	}
	else
	{
		write_log('logs/currency_errors.log',"$value\t$job_id\n");		
	}
	return $salary;
}

?>
