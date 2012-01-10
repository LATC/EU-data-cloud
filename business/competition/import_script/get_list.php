<?php

$ids_array = array();
$decision_match_array = array();
$filename = "C:\competition\links.csv";
$j=0;

for ($i=1; $i<=19500; $i=$i+30 )
{
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://ec.europa.eu/competition/elojade/isef/index.cfm?fuseaction=dsp_result&policy_area_id=1,2,3&fromrow='.$i);
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$data = curl_exec($ch);
preg_match_all("/class=\"case\"[^>]*><[^\"]*\"([^\"]*)/s", $data, $ids_match);
preg_match_all("/class=\"decision\" [^>]*>([^<]*)/s", $data, $decision_match);
$ids_array[$i] = $ids_match[1];
$decision_match_array[$i] = $decision_match[1];
$file = fopen($filename, 'a');

for ($j=0; $j<=29; $j++)
{
    fwrite($file, "http://ec.europa.eu/competition/elojade/isef/case_details.cfm?".$ids_match[1][$j].";".$decision_match[1][$j]."; \n");
}
}

fclose($file);


?>
