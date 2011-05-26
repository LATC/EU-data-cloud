<?php
include ("config.inc");
include ("functions.php");

$openFile = fopen("csv/isco.csv", "r");
if (!$openFile){
	echo ("<p>File not found!</p>");
}
else{
	$row = 0;
	while ($values = fgetcsv ($openFile, 2048, ";")) {
		if ($row >= 0){
			$minor_code = NULL;
			$submajor_code = NULL;
			$major_code = NULL;
			switch(strlen ($values[0])) {
				case 4: $minor_code = substr($values[0], 0, 3);$submajor_code= substr($values[0], 0, 2);$major_code = substr($values[0], 0, 1);break;
				case 3: $submajor_code= substr($values[0], 0, 2);$major_code = substr($values[0], 0, 1);break;
				case 2: $major_code = substr($values[0], 0, 1);break;
			}
			$sql = mysql_query("INSERT INTO isco SET code = '$values[0]', name = '$values[1]', major_code =".db_prep($major_code).", submajor_code =".db_prep($submajor_code).", minor_code = ".db_prep($minor_code)) or die(mysql_error());
		}
		$row++;
	}
}
fclose($openFile);

?>
