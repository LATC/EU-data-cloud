<?php
$username="root";
$password="Traxdata1";
$database="euraxess";

//Connect to local database
mysql_connect(localhost,$username,$password) or die("Unable to connect to database");
mysql_select_db($database) or die("Unable to select database");

$select_1 = "SELECT ID from fellowship where career_stage_ID = 2";
$select_ids_query = mysql_query($select_1);


$rec = array();

while(($select_ids = mysql_fetch_array($select_ids_query)) !== FALSE) {
    $rec[] = $select_ids[0];
}


foreach ($rec as $id)
{
    $check_id = "SELECT ID FROM fellowship WHERE ID = '$id'";
            $check_id_query = mysql_query($check_id);
            $check_check = mysql_fetch_array($check_id_query);
            if ($check_check[ID] != FALSE){
    $query = "UPDATE fellowship SET career_stage_ID = '3' WHERE ID = '$id'";}
}

$select_2 = "SELECT ID from fellowship where career_stage_ID = '4'";
$select_ids_query = mysql_query($select_1);
$select_ids = mysql_fetch_array($select_ids_query);

foreach ($select_ids as $id)
{
    $query = "UPDATE fellowship SET career_stage_ID = '2' WHERE ID = '$id'";
}

mysql_close();
?>