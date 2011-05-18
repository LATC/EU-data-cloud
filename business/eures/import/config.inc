<?php

$conn = mysql_connect("localhost","root","1234");
mysql_select_db("TEST_EURES", $conn);
mysql_set_charset("utf8");

$JOBS_DIR = 'file:///var/www/latc3/datasets/business/eures/import/jobs/';

?>
