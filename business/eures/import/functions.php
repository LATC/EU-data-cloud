<?php

function setup_database($new = false) {
  global $MYSQL_SERVER;
  global $MYSQL_USER;
  global $MYSQL_PASSWORD;
  global $MYSQL_DATABASE;

  echo "= DATABASE SETUP =".PHP_EOL.PHP_EOL;
  $conn = mysql_connect($MYSQL_SERVER, $MYSQL_USER, $MYSQL_PASSWORD);

  if ($new) {
    mysql_query("DROP DATABASE $MYSQL_DATABASE");
    echo "Database $database dropped.".PHP_EOL;
  }

  mysql_query("CREATE DATABASE IF NOT EXISTS $MYSQL_DATABASE") or die (mysql_error());
  echo "Database $MYSQL_DATABASE created.".PHP_EOL;
  mysql_select_db($MYSQL_DATABASE, $conn);
  mysql_set_charset("utf8");
  if ($new) {
      $dir = "database/";
    	if (is_dir($dir)) {
        if ($handle = opendir($dir)) {
          while (false !== ($file = readdir($handle)))
          {
            if (!is_dir($file) && (!endsWith($file, "_inserts.sql")))
            {
              create_table($file);
            }
          }
        }
      }
      insert_languages();
    echo "Tables for $MYSQL_DATABASE created.".PHP_EOL;
  }

}

function insert_languages() {
  $openFile = fopen("csv/lexvo.csv", "r");
  if (!$openFile){
    die("File not found: csv/lexvo.csv");
  }
  else{

    while ($values = fgetcsv ($openFile, 2048, ";")) {
      if (!isset($languages[$values[0]])) {
        $languages[$values[0]] = array();
        $languages[$values[0]]["iso1"] = $values[1];
        $languages[$values[0]]["labels"] = array();
      }
      $language = strtolower(trim($values[2]));
      if (!in_array($language, $languages[$values[0]]["labels"])) {
        $languages[$values[0]]["labels"][] = $language;
      }
    }
  }
  fclose($openFile);
  foreach($languages as $iso3 => $language_array) {
    mysql_query("INSERT INTO language SET iso639p3 = '$iso3', iso639p1 = '".$language_array["iso1"]."', labels =".db_prep(implode(";", $language_array["labels"]))) or die(mysql_error());
  }
  echo "Inserted data for table: language".PHP_EOL;
}

function create_table($file) {
  $table = str_replace(".sql", "", $file);
  $file = "database/".$file;
  if (file_exists($file)) {
    $query = file_get_contents($file);
    mysql_query($query) or die(mysql_error());
    echo "Created table: $table".PHP_EOL;
    $file_inserts = "database/".$table."_inserts.sql";
    if (file_exists($file_inserts)) {
      $query = file_get_contents($file_inserts);
      mysql_query($query) or die(mysql_error());
      echo "Inserted data for table: $table".PHP_EOL;
    }
  } else {
    die ("File not found:".$file);
  }
}



function endsWith($string, $test)
{
  $strlen = strlen($string);
  $testlen = strlen($test);
  if ($testlen > $strlen) return false;
  return substr_compare($string, $test, -$testlen) === 0;
}

function insert_address($address)
{	
	global $country_code;

	$address = db_prep($address);  
	$lowercase_address = strtolower($address);
	
	$query = "SELECT id FROM geo WHERE LOWER(address) = $lowercase_address";	
	//AND country_code= '$country_code'
	
	$sql = mysql_query($query) or die (mysql_error());

	if ((mysql_num_rows($sql) == 0) && ($address <> NULL) && ($address <> ',  ,'))
	{
		mysql_query("INSERT INTO geo SET address = $address, country_code = '$country_code'") or die (mysql_error());
		return mysql_insert_id();
	}
	// echo "Address already extracted." . PHP_EOL;
	$row = mysql_fetch_object($sql);
	return $row->id;          
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
	$sql = mysql_query("SELECT address FROM geo WHERE looked_up IS NULL LIMIT 0, 2500") or die (mysql_error());

	while($row = mysql_fetch_array($sql))
	{
		$address = $row[0];

		$address_array = get_geocoder_address($address);

		mysql_query("UPDATE geo SET looked_up = '1' WHERE address =".db_prep($address)) or die (mysql_error());

		if ($address_array['formatted_address'] <> NULL)
		{
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
		write_log('hours_per_week_errors.log',date("Y-m-d H:i:s")."\t$value\t$country_code\t$job_id\t$source_id\n");
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

	foreach ($split_language1 as &$spl1)
	{
		$split_language2 = preg_split("/[-\/]/",$spl1);
		if (isset($split_language2[0]))
		{
			$language = trim($split_language2[0]);
      if (strlen($language) == 0) {
        write_log("languages_errors.log", array($required_languages, $job_id));
      } else {
        $iso639p3 = import_language($language);
        if ($iso639p3) {
          if (isset($split_language2[1]))
          {
            $language_level = trim($split_language2[1]);
            if (strlen($language_level) > 0) {
              $ilr_level = import_language_level($language_level);
            }
          }
          $query = "INSERT INTO job_language SET job_id = $job_id, iso639p3 = '$iso639p3'";
          if ($ilr_level) {
            $query = $query.", ilr_level = $ilr_level";
          }
          mysql_query($query) or die ($query. " ".mysql_error());
        }
      }
		}
	}
}

function import_language_level($language_level)
{
	global $iso639p3;
	global $job_id;
	global $country_code;
	global $source_id;

	$lowercase_language_level = strtolower($language_level);

  $sql = mysql_query("SHOW COLUMNS FROM language_level");

  while ($row = mysql_fetch_row($sql)) {
    $column = $row[0];
    $query = "SELECT ilr_level, $column FROM language_level WHERE $column LIKE '%$lowercase_language_level%'";
    $sql1 = mysql_query($query);
    while ($row1 = mysql_fetch_row($sql1)) {
      $column_values = explode(",", $row1[1]);
      if (in_array($lowercase_language_level, $column_values)) {
        $ilr_level = $row1[0];
        return $ilr_level;
      }
    }
  }

  $language_level_translate = strtolower(translate($lowercase_language_level));

  $sql = mysql_query("SHOW COLUMNS FROM language_level");

  while ($row = mysql_fetch_row($sql)) {
    $column = $row[0];
    $sql1 = mysql_query("SELECT ilr_level, eng FROM language_level WHERE eng LIKE '%$language_level_translate%'");
    while ($row1 = mysql_fetch_row($sql1)) {
      $column_values = explode(",", $row1[1]);
      if (in_array($language_level_translate, $column_values)) {
        $ilr_level = $row1[0];
        $query = "UPDATE language_level SET labels = CONCAT(labels,';$lowercase_language') WHERE ilr_level = $ilr_level";
        if (mysql_query($query)) {
          write_log('languages__levels_ added.log', array($job_id, $language, $iso639p3, $language_translate, $ilr_level, $country_code));
        } else {
          write_log('language_level_insert_errors.log', array($job_id, $language, $iso639p3, $language_translate, $ilr_level, $country_code, mysql_error()));
        }
        return $ilr_level;
      }
    }
  }
  write_log('language_levels_errors.log', array($job_id, $iso639p3, $language_level, $language_level_translate, $country_code,));
}

function import_language($language)
{
	global $job_id;
	global $source_id;
	global $country_code;

	$lowercase_language = strtolower($language);

	$sql = mysql_query("SELECT iso639p3, labels FROM language WHERE labels LIKE'%$lowercase_language%'");
  while ($row = mysql_fetch_row($sql)) {
    $column_values = explode(";", $row[1]);
    if (in_array($lowercase_language, $column_values)) {
      $iso639p3 = $row[0];
      return $iso639p3;
    }
  }

  $language_translate = strtolower(translate($lowercase_language));

  $sql = mysql_query("SELECT iso639p3, labels FROM language WHERE labels LIKE'%$language_translate%'");
  while ($row = mysql_fetch_row($sql)) {
    $column_values = explode(";", $row[1]);
    if (in_array($language_translate, $column_values)) {
      $iso639p3 = $row[0];
      $query = "UPDATE language SET labels = CONCAT(labels,';$lowercase_language') WHERE iso639p3='$iso639p3'";
      if (mysql_query($query)) {
        write_log('languages_added.log', array($job_id, $language, $iso639p3, $language_translate, $country_code));
      } else {
        write_log('language_insert_errors.log', array($job_id, $language, $iso639p3, $language_translate, $country_code, mysql_error()));
      }
      return $iso639p3;
    }
  }
  write_log('languages_errors.log', array($language, $language_translate, $country_code, $job_id, $source_id));
}

function write_log($file, $text_array)
{
	global $DIR;

  $text = date("Y-m-d H:i:s")."\t".join("\t", $text_array)."\n";
  $dir = "logs/";
  $file = $dir.$file;

	if (!is_dir($dir))
	{
		if(mkdir($dir, 0777))
		{
			echo "Directory for logs has been created successfully.";
		}
		else
		{
			die("Failed to create directory: $dir");
		} 
	}
	
	if( $fh = fopen($file,'a+'))
	{
		fputs($fh, $text, strlen($text));
		fclose($fh);
		return true;
	}
  echo "Couldn't write to log file: $file";
	return false;
}

// Basic prep function - trims and escapes data
function db_prep($data)
{
	if (isset($data) && ($data != ''))
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

// Insert name on the small tables and returns the id
function insert_name($table, $value)
{
	$lowercase_value = db_prep(strtolower($value));

	if($value <> '' && $value <> '0' && $value <> '.' && $value <> '**')
	{
		$query = "SELECT id FROM $table WHERE LOWER(name) = $lowercase_value";
    $sql = mysql_query($query) or die ($query.PHP_EOL."caused: ".mysql_error());
    if (mysql_num_rows($sql) == 0)
		{
			mysql_query("INSERT INTO ".$table." SET name =".db_prep($value));
			$sql = mysql_query($query) or die ($query.PHP_EOL."caused: ".mysql_error());
		}
		$row = mysql_fetch_object($sql);
		return $row->id;
	}
  // TODO throw exception & log
}

// Select id based on the query or returns empty
function select_id($query)
{
	$sql = mysql_query($query) or die($query.PHP_EOL." caused: ".mysql_error());
	if (mysql_num_rows($sql) > 0)
	{
		$row = mysql_fetch_array($sql);
    return $row[0];
	}
}

function format_salary($value) 
{
	global $unique_id;
	global $source_id;

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
		$salary['amount'] = preg_replace('/[^\d.]+/', '', $number);
	}
	else
	{
		write_log('salary_errors.log', array($value, $unique_id, $source_id));
	}
	return $salary;
}

function format_phone($value) 
{
	//needs improve to split with 99252572 -22490440 but not 051-857229
	global $contact_id;
  if (preg_match('/@/',$value))
	{
		$phone_array['email'] = $value;
		return $phone_array ;
	}

	if (preg_match('/\d/',$value))
	{
		$phone = preg_replace('/\+/','00',$value);

		$split_phone = preg_split('/[\/,]/',$phone);

		foreach ($split_phone as $spl)
		{
			$number = preg_replace('/[^\d]/','',$spl);
			$query = "INSERT INTO contact_phone SET contact_id = '$contact_id', number = '$number'";
			mysql_query($query);
		}
	}
	else
	{
		write_log("phone_errors.log", array($value, $contact_id));
		return NULL;
	}
}

function format_fax($value) 
{
	global $unique_id;

  $fax_array = array();

	if (preg_match('/@/',$value))
	{
		$fax_array['email'] = $value;
		return $fax_array;
	}

	if (preg_match('/\d/',$value))
	{
		$fax = preg_replace('/\+/','00',$value);
		$fax_array['fax'] = preg_replace('/[^\d]/','',$fax);
		return $fax_array;
	}
  write_log("fax_errors.log", array($value, $unique_id));
}

function prepare_boolean($value)
{
	if (strtolower($value) == 'yes') {
		return 1;
  }
	if (strtolower($value) == 'no') {
    return 0;
  }
  // TODO treat occurences of this function when neither 0 or 1 are returned
}