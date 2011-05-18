<?php
// was: 248,581

$count = 0;
$username = "root";
$password = "Traxdata1";
$database = "cordis";
$tabelle = "organization";
mysql_connect(localhost,$username,$password) or die("Unable to connect to database");
mysql_select_db($database) or die("Unable to select database");
$sql = "SELECT name, address, city, department, postcode, region, country, organizationID, COUNT( * ) AS counter
    FROM $tabelle
    GROUP BY LOWER(CONVERT(name USING latin1)), LOWER(CONVERT(address USING latin1)), LOWER(CONVERT(city USING latin1)), LOWER(CONVERT(department USING latin1)), LOWER(CONVERT(postcode USING latin1)), LOWER(CONVERT(region USING latin1)), LOWER(CONVERT(country USING latin1)) 
    HAVING COUNT( * ) >1";
$result = mysql_query($sql);
if(!$result) {
    echo mysql_error();
    die();
}

while($row = mysql_fetch_array($result)) {
    $get_all = "SELECT organizationID FROM $tabelle 
            WHERE 
            LOWER(CONVERT(name USING latin1)) = LOWER('".str_replace("'","\'",$row[name])."') 
            AND LOWER(CONVERT(address USING latin1)) = LOWER('".str_replace("'","\'",$row[address])."') 
            AND LOWER(CONVERT(city USING latin1)) = LOWER('".str_replace("'","\'",$row[city])."') 
            AND LOWER(CONVERT(department USING latin1)) = LOWER('".str_replace("'","\'",$row[department])."')
            AND LOWER(CONVERT(postcode USING latin1)) = LOWER('".str_replace("'","\'",$row[postcode])."')
            AND LOWER(CONVERT(region USING latin1)) = LOWER('".str_replace("'","\'",$row[region])."')
            AND LOWER(CONVERT(country USING latin1)) = LOWER('".str_replace("'","\'",$row[country])."')";
    //echo $get_all;
    $organizationIDs = mysql_query($get_all);
    if (!$organizationIDs) {
        echo mysql_error();
        die();
    }
    //echo mysql_num_rows($organizationIDs);
    $organizations = array();
    while($row1 = mysql_fetch_array($organizationIDs)) {
        $organizations[] = $row1[organizationID];
    }
    $minOrganizationId = min($organizations);
    echo "kept: ".$minOrganizationId.", deleted: ";
    foreach ($organizations as $organization) {
        $update = "UPDATE person 
            SET organizationID = '".$minOrganizationId."'
            WHERE organizationID =  $organization
        ";
        if (!mysql_query($update)) {
            echo mysql_error();
            die();
        }
        if ($organization != $minOrganizationId) {
            $del = "DELETE FROM $tabelle
            WHERE organizationID = $organization";
            if (!mysql_query($del)) {
                echo mysql_error();
                die();
            }
            $count++;
            echo $organization." ";
        }
    }
    echo "\n";
}
echo "Anzahl der gelöschten Reihen: $count";
?>