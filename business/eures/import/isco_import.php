<?php
include("config.php");

$openFile = fopen("csv/occupation.csv", "r");
if (!$openFile){
	echo ("<p>File not found!</p>");
}
else{
	$row = 0;
	while ($values = fgetcsv ($openFile, 2048, ";")) {
		if ($row >= 0){
			$sql = mysql_query("INSERT INTO isco SET id = '$values[0]', name = '$values[1]'") or die(mysql_error());
		}
		$row++;
	}
}
fclose($openFile);

?>
