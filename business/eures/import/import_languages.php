<?php

include("config.inc.php");
include("functions.php");

date_default_timezone_set("Europe/Dublin");

$conn = mysql_connect($MYSQL_SERVER, $MYSQL_USER, $MYSQL_PASSWORD);
mysql_select_db($MYSQL_DATABASE);

$languages = array();

$openFile = fopen("csv/lexvo.csv", "r");
if (!$openFile){
	echo ("<p>File not found!</p>");
}
else{

  while ($values = fgetcsv ($openFile, 2048, ";")) {
    if (!isset($languages[$values[0]])) {
      $languages[$values[0]] = array();
      $languages[$values[0]]["iso1"] = $values[1];
      $languages[$values[0]]["labels"] = array();
    }
    $languages[$values[0]]["labels"][] = trim($values[2]);
	}
}

fclose($openFile);

foreach($languages as $iso3 => $language_array) {
  mysql_query("INSERT INTO language SET iso639p3 = '$iso3', iso639p1 = '".$language_array["iso1"]."', labels =".db_prep(implode(";", $language_array["labels"]))) or die(mysql_error());
}