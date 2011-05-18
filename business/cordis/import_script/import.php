<?php
$username="root";
$password="Traxdata1";
$database="cordis";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://cordis.europa.eu/guidance/sic-codes_en.html');
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$data = curl_exec($ch);
mysql_select_db($database) or die("Unable to select database");
$query2 = mysql_real_escape_string($query);
mysql_query($query2);
mysql_close();
?>