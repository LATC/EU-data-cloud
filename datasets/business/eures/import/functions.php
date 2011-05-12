<?php

function db_prep($data)
// Basic prep function - trims and escapes data
{
   if (isset($data) and $data != ''){
      $prepped = "'" . mysql_real_escape_string(trim($data)) . "'";
   }
   else {
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
		mysql_query("INSERT INTO ".$table." SET name ='$name_table'");
	$sql = mysql_query("SELECT id FROM ".$table." WHERE name = '$name_table'");				
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

function format_currency($number) {
	$number = strtoupper($number);
	$period = array('STUNDE', 'MONATLICH', 'STD. LOHN', 'PER HOUR', 'PER D', 'PER DAY', 'PER ANNUM', ' PA ', 'PER WEEK');
	$currency = array('€', 'EURO', 'GBP');

	if ($number == '0.00')
		return NULL;

	foreach ($currency as &$value) {
	    if (preg_match('/'.$value.'/',$number))
		$salary ['currency'] = trim($value);
		$new_number = str_replace ($value,"",$number);
	}
	unset($value);


	foreach ($period as &$value2) {
	    if (preg_match('/'.$value2.'/',$number))
		$salary ['period'] = trim($value2);
		$new_number = str_replace ($value2,"",$new_number);
	} 
	unset($value2); 
	
	

	if (!preg_match('/[A-Z_%-]/',$new_number)){
		$amount = preg_replace('/[^\d.]+/', '', $new_number);
		$salary['amount'] = sprintf('%01.2f', $amount);
	}
	else
		$salary['amount'] = $number;
	
	return $salary;
}

function format_hour($number) {
	if ($number == '0' || $number == '.')
		return NULL;

	if (!preg_match('/[A-Za-z-%]/',$number))
	{
		$number = str_replace(":", '.', $number);
		$number = str_replace(",", '.', $number);
		$number = str_replace(";", '.', $number);
		$number = sprintf('%01.2f', $number);
	}
	return $number;
}

?>
