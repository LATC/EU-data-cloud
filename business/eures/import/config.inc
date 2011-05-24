<?php

$server = 'localhost';
$user = 'root';
$password = '1234';
$database = 'eures';

$conn = mysql_connect($server,$user,$password);
mysql_query("CREATE DATABASE IF NOT EXISTS $database") or die (mysql_error());
mysql_select_db($database, $conn);
mysql_set_charset("utf8");
$JOBS_DIR = 'file:///var/www/EU-data-cloud/business/eures/import/jobs/';
$DIR = '/var/www/EU-data-cloud/business/eures/import/';

//create_tables($database);
function create_tables($database)
{
	$sql = file_get_contents('database/source.sql');
	mysql_query($sql);
	$sql = file_get_contents('database/country.sql');
	mysql_query($sql);
	$sql = file_get_contents('database/isco.sql');
	mysql_query($sql);
	$sql = file_get_contents('database/language.sql');
	mysql_query($sql);
	$sql = file_get_contents('database/language_level.sql');
	mysql_query($sql);
	$sql = file_get_contents('database/create');
	mysql_query($sql);
}

?>
