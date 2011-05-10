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

?>
