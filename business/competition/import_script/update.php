<?php
$username="root";
$password="";
$database="competition";
$cartel_decisions = array();
$a = 0;
mysql_connect('localhost',$username,$password) or die("Unable to connect to database");
        mysql_select_db($database) or die("Unable to select database");
// decisions

$query_0 = "SELECT * FROM cartel_decision";
$select_IDs = mysql_query($query_0);
while ($row = mysql_fetch_array($select_IDs))
{
     $cartel_decisions[$a] = array($row[0],$row[1]);
     $a++;
}

foreach ($cartel_decisions as $cartel_decision)
{
    $query_1 = "UPDATE decision SET ID = '".$cartel_decision[0]."_".$cartel_decision[1]."' 
        WHERE ID = '".$cartel_decision[1]."'";
    mysql_query($query_1);
    $query_11 = "UPDATE cartel_decision SET decision_ID = '".$cartel_decision[0]."_".$cartel_decision[1]."' 
        WHERE decision_ID = '".$cartel_decision[1]."'";
    mysql_query($query_11);
}

$query_2 = "SELECT * FROM merger_decision";
$select_IDs = mysql_query($query_2);
while ($row = mysql_fetch_array($select_IDs))
{
     $merger_decisions[$a] = array($row[0],$row[1]);
     $a++;
}

foreach ($merger_decisions as $merger_decision)
{
    $query_3 = "UPDATE decision SET ID = '".$merger_decision[0]."_".$merger_decision[1]."' 
        WHERE ID = '".$merger_decision[1]."'";
    mysql_query($query_3);
    $query_31 = "UPDATE merger_decision SET decision_ID = '".$merger_decision[0]."_".$merger_decision[1]."' 
        WHERE decision_ID = '".$merger_decision[1]."'";
    mysql_query($query_31);
}

$query_4 = "SELECT * FROM state_aid_decision";
$select_IDs = mysql_query($query_4);
while ($row = mysql_fetch_array($select_IDs))
{
     $merger_state_aids[$a] = array($row[0],$row[1]);
     $a++;
}

foreach ($merger_state_aids as $merger_state_aid)
{
    $query_5 = "UPDATE decision SET ID = '".$merger_state_aid[0]."_".$merger_state_aid[1]."' 
        WHERE ID = '".$merger_state_aid[1]."'";
    mysql_query($query_5);
    $query_5 = "UPDATE state_aid_decision SET decision_ID = '".$merger_state_aid[0]."_".$merger_state_aid[1]."' 
        WHERE decision_ID = '".$merger_state_aid[1]."'";
    mysql_query($query_5);
}

// press_releases

$query_0 = "SELECT * FROM cartel_press_release";
$select_IDs = mysql_query($query_0);
while ($row = mysql_fetch_array($select_IDs))
{
     $cartel_press_releases[$a] = array($row[0],$row[1]);
     $a++;
}

foreach ($cartel_press_releases as $cartel_press_release)
{
    $query_1 = "UPDATE press_release SET ID = '".$cartel_press_release[0]."_".$cartel_press_release[1]."' 
        WHERE ID = '".$cartel_press_release[1]."'";
    mysql_query($query_1);
    $query_11 = "UPDATE cartel_press_release SET press_release_ID = '".$cartel_press_release[0]."_".$cartel_press_release[1]."' 
        WHERE press_release_ID = '".$cartel_press_release[1]."'";
    mysql_query($query_11);
}

$query_2 = "SELECT * FROM merger_press_release";
$select_IDs = mysql_query($query_2);
while ($row = mysql_fetch_array($select_IDs))
{
     $merger_press_releases[$a] = array($row[0],$row[1]);
     $a++;
}

foreach ($merger_press_releases as $merger_press_release)
{
    $query_3 = "UPDATE press_release SET ID = '".$merger_press_release[0]."_".$merger_press_release[1]."' 
        WHERE ID = '".$merger_press_release[1]."'";
    mysql_query($query_3);
    $query_31 = "UPDATE merger_press_release SET press_release_ID = '".$merger_press_release[0]."_".$merger_press_release[1]."' 
        WHERE press_release_ID = '".$merger_press_release[1]."'";
    mysql_query($query_31);
}

$query_4 = "SELECT * FROM state_aid_press_release";
$select_IDs = mysql_query($query_4);
while ($row = mysql_fetch_array($select_IDs))
{
     $merger_state_aids[$a] = array($row[0],$row[1]);
     $a++;
}

foreach ($merger_state_aids as $merger_state_aid)
{
    $query_5 = "UPDATE press_release SET ID = '".$merger_state_aid[0]."_".$merger_state_aid[1]."' 
        WHERE ID = '".$merger_state_aid[1]."'";
    mysql_query($query_5);
    $query_5 = "UPDATE state_aid_press_release SET press_release_ID = '".$merger_state_aid[0]."_".$merger_state_aid[1]."' 
        WHERE press_release_ID = '".$merger_state_aid[1]."'";
    mysql_query($query_5);
}

// publications


$query_2 = "SELECT * FROM merger_publication";
$select_IDs = mysql_query($query_2);
while ($row = mysql_fetch_array($select_IDs))
{
     $merger_publications[$a] = array($row[0],$row[1]);
     $a++;
}

foreach ($merger_publications as $merger_publication)
{
    $query_3 = "UPDATE publication SET ID = '".$merger_publication[0]."_".$merger_publication[1]."' 
        WHERE ID = '".$merger_publication[1]."'";
    mysql_query($query_3);
    $query_31 = "UPDATE merger_publication SET publication_ID = '".$merger_publication[0]."_".$merger_publication[1]."' 
        WHERE publication_ID = '".$merger_publication[1]."'";
    mysql_query($query_31);
}

$query_4 = "SELECT * FROM state_aid_publication";
$select_IDs = mysql_query($query_4);
while ($row = mysql_fetch_array($select_IDs))
{
     $merger_state_aids[$a] = array($row[0],$row[1]);
     $a++;
}

foreach ($merger_state_aids as $merger_state_aid)
{
    $query_5 = "UPDATE publication SET ID = '".$merger_state_aid[0]."_".$merger_state_aid[1]."' 
        WHERE ID = '".$merger_state_aid[1]."'";
    mysql_query($query_5);
    $query_5 = "UPDATE state_aid_publication SET publication_ID = '".$merger_state_aid[0]."_".$merger_state_aid[1]."' 
        WHERE publication_ID = '".$merger_state_aid[1]."'";
    mysql_query($query_5);
}

?>
