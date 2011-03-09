<?php
$username="root";
$password="Traxdata1";
$database="cordis";
$file="damagedxml.txt";
//establish connection to CORDIS REST API
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://cordis.europa.eu/newsearch/SearchServlet?action=query&ENGINE_ID=CORDIS_ENGINE_ID&SEARCH_TYPE_ID=CORDIS_SEARCH_ID&Collection=EN_PROG');
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$data = curl_exec($ch);
// Save data as xml
$xml = simplexml_load_string($data);
// For invalid xml write RCN in file and continue with next Project
if (!$xml) 
    {
     $fh = fopen($file, "w") or die("can't open file");
    fwrite($fh, $RCN);
    fclose($fh);
    continue;
    }
// Get hits and use RCN to load a document for each project
foreach ($xml->responsedata->hit as $child)
{
$RCN = $child->RCN;
$ch2 = curl_init();
//establish connection to Document-Search
$URL = "http://cordis.europa.eu/newsearch/SearchServlet?action=read&DOC_FETCHER_ID=PROGRAMMES_FETCHER_ID&DOC_ID=" . $RCN;
curl_setopt($ch2, CURLOPT_URL, $URL);
curl_setopt($ch2, CURLOPT_HEADER, 0);
curl_setopt($ch2, CURLOPT_RETURNTRANSFER, 1);
$data2 = curl_exec($ch2);        
$xml2 = simplexml_load_string($data2, 'SimpleXMLElement', LIBXML_NOCDATA);
// For invalid xml write RCN in file and continue with next Project
if (!$xml2)
{ 
    $fh = fopen($file, "w") or die("can't open file");
    fwrite($fh, $RCN);
    fclose($fh);
    continue;
}

//Set variables
$acronym = $xml2->document->xpath("//metadata[@name='tag_acronym']");
$title = $xml2->document->xpath("//metadata[@name='tag_title']");
$shorttitle = $xml2->document->xpath("//metadata[@name='tag_short_title']");
$startdate = $xml2->document->xpath("//metadata[@name='tag_startdate']");
$duration = $xml2->document->xpath("//metadata[@name='tag_duration']");
$enddate = $xml2->document->xpath("//metadata[@name='tag_enddate']");
$information = $xml2->document->xpath("//metadata[@name='tag_generalinfo']");
$subdivision = $xml2->document->xpath("//metadata[@name='tag_subdivision']");
$objectives = $xml2->document->xpath("//metadata[@name='tag_objectives']");
$implementation = $xml2->document->xpath("//metadata[@name='tag_implementation']");
$num_projects = $xml2->document->xpath("//metadata[@name='tag_numproj']");
$leg_reference = $xml2->document->xpath("//metadata[@name='tag_legreference']");
$leg_date = $xml2->document->xpath("//metadata[@name='tag_legdate']");
$country = $xml2->document->xpath("//metadata[@name='tag_country']");
$status = $xml2->document->xpath("//metadata[@name='tag_status']");
$funding = $xml2->document->xpath("//metadata[@name='tag_funding']");
$framework = $xml2->document->xpath("//metadata[@name='tag_framework']");
$type = $xml2->document->xpath("//metadata[@name='tag_type']");
$next = $xml2->document->xpath("//metadata[@name='tag_next']");
$prev = $xml2->document->xpath("//metadata[@name='tag_prev']");
$organization = $xml2->document->xpath("//metadata[@name='tag_organization']");
$revisions = $xml2->document->xpath("//metadata[@name='tag_revisions']");
$journal_ref = $xml2->document->xpath("//metadata[@name='tag_journalreference']");
$journal_date = $xml2->document->xpath("//metadata[@name='tag_journaldate']");
//Connect to local database
mysql_connect(localhost,$username,$password) or die("Unable to connect to database");
$information_clean = mysql_real_escape_string($information[0]);
$subdivision_clean = mysql_real_escape_string($subdivision[0]);
$objectives_clean = mysql_real_escape_string($objectives[0]);
$implementation_clean = mysql_real_escape_string($implementation);
$revisions_clean = mysql_real_escape_string($revisions);
$query = "INSERT INTO programmes (acronym,title,shorttitle,startdate,duration,enddate,information,subdivision,revisions,objectives,implementation,numberprojects,leg_ref,leg_date,country,status,funding,framework,type,organization,journal_ref,journal_date) VALUES ('$acronym[0]','$title[0]','$shorttitle[0]','$startdate[0]','$duration[0]','$enddate[0]','$information_clean','$subdivision_clean','$revisions_clean','$objectives_clean','$implementation_clean','$num_projects','$leg_reference[0]','$leg_date[0]','$country[0]','$status[0]','$funding[0]','$framework[0]','$type[0]','$organization[0]','$journal_ref[0]','$journal_date[0]')";
mysql_query($query);
$query2 = "INSERT INTO programmerelations VALUES ('$acronym[0]','$next[0]','1')";
mysql_query($query2);
$query3 = "INSERT INTO programmerelations VALUES ('$acronym[0]','$prev[0]','2')";
mysql_query($query3);
//Clean Strings so that they can be put in database
mysql_close();
//Get variables from other participants (if exist)
}
?>