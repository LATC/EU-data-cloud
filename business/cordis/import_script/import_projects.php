<?php
$username="root";
$password="Traxdata1";
$database="cordis";
$file="damagedxml.txt";
//open file with RCN
//$data = fopen ();
$data = file_get_contents(".\Cordis.htm","r");
$pattern = ("/<tr>\s*<td[^>]*>.*?<\/td>\s*<td[^>]*>.*?<\/td>\s*<td[^>]*>.*?<\/td>\s*<td[^>]*>.*?<\/td>\s*<td[^>]*>.*?<\/td>\s*<td[^>]*>.*?<\/td>\s*<td[^>]*>\s*([0-9]*)\s*/s");
preg_match_all($pattern, $data, $RCN);
$test = $RCN[1];
// Get hits and use RCN to load a document for each project
foreach ($test as $child)
{
$ch2 = curl_init();
//establish connection to Document-Search
$URL = "http://cordis.europa.eu/newsearch/print.cfm"; //. $child;
curl_setopt($ch2, CURLOPT_URL, $URL);
curl_setopt($ch2, CURLOPT_HEADER, 0);
curl_setopt($ch2, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch2, CURLOPT_FOLLOWLOCATION, TRUE);
curl_setopt($ch2, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US) AppleWebKit/534.13 (KHTML, like Gecko) Chrome/9.0.597.98 Safari/534.13");
curl_setopt($ch2, CURLOPT_POST, true);
curl_setopt($ch2, CURLOPT_POSTFIELDS, "page=docview&collection=EN_PROJ&position=0&reference=95562");
curl_setopt($ch2, CURLOPT_TIMEOUT, 120);
curl_setopt($ch2, CURLOPT_CONNECTTIMEOUT, 120);
curl_setopt($ch2, CURLOPT_AUTOREFERER, true);
curl_setopt($ch2, CURLOPT_COOKIEFILE, './cookie.txt');
curl_setopt($ch2, CURLOPT_COOKIEJAR, './cookie.txt');
$data2 = curl_exec($ch2);
test;
// For invalid result write RCN in file and continue with next Project
if (!$data2)
{ 
    $fh = fopen($file, "w") or die("can't open file");
    fwrite($fh, $RCN);
    fclose($fh);
    continue;
}
//Set variables
preg_match_all('/id="tag_status">(.*)<\/span>/', $data2, $status);
preg_match_all('/id="tag_contracttype">(.*)<\/span>/', $data2, $contract);
preg_match_all('/id="tag_programmeacronym">(.*)<\/span>/', $data2, $programme);
preg_match_all('/id="tag_startdate">(.*)<\/span>/', $data2, $start_date);
preg_match_all('/id="tag_enddate">(.*)<\/span>/', $data2, $end_date);
preg_match_all('/id="tag_duration">(.*)<\/span>/', $data2, $duration);
preg_match_all('/id="tag_cost">(.*)<\/span>/', $data2, $cost);
preg_match_all('/id="tag_funding">(.*)<\/span>/', $data2, $funding);
preg_match_all('/id="tag_titleHL">(.*)<\/span>/', $data2, $title);
preg_match_all('/id="tag_projectacronym">(.*)<\/span>/', $data2, $acronym);
preg_match_all('/id="tag_organizationname">(.*)<\/span>/', $data2, $organization_name);
// TO-DO: Split Address
preg_match_all('/id="tag_address">(.*)<\/span>/', $data2, $address);
preg_match_all('/id="tag_contact">(.*)<\/span>/', $data2, $contact);
//TO-DO: Split Subjects
preg_match_all('/id="tag_subject">(.*)<\/span>/', $data2, $subjectindexcode);
preg_match_all('/id="tag_url">(.*)<\/span>/', $data2, $contact_url);
preg_match_all('/id="tag_objective">(.*)<\/span>/', $data2, $objective);
preg_match_all('/id="tag_achievements">(.*)<\/span>/', $data2, $achievements);
preg_match_all('/id="tag_generalinfo">(.*)<\/span>/', $data2, $information);
preg_match_all('/id="tag_orgtype">(.*)<\/span>/', $data2, $org_type);
//TO-DO: Split Subprogrammes
preg_match_all('/id="tag_subprogramme">(.*)<\/span>/', $data2, $subprogramme);
preg_match_all('/id="tag_participants">(.*)<\/span>/', $data2, $participants);
//Connect to local database
mysql_connect(localhost,$username,$password) or die("Unable to connect to database");
//Clean Strings so that they can be put in database
$objective_clean = mysql_real_escape_string($objective[0]);
$achievements_clean = mysql_real_escape_string($achievements[0]);
$information_clean = mysql_real_escape_string($information[0]);
mysql_select_db($database) or die("Unable to select database");
$query = "INSERT INTO projects (projectsref,
m,title,url,startdate,duration,enddate,achievements,information,objectives,cost,funding,status,Programmeacronym,Contract) VALUES('$child','$acronym','$title','$URL','$start_date','$duration','$end_date','$achievements_clean','$information_clean','$objective_clean','$cost','$funding','$status','$programme','$contract')";
mysql_query($query);
$org_name = mysql_real_escape_string($organization_name);
$query2 = "INSERT INTO organization (name,size,type,address,city,department,postcode,region,url) VALUES ('$org_name','','$org_type[0]','$address','$city','','','$region','$contact_url[0]')";
mysql_query($query2);
$organization_id  = mysql_insert_id();
$query3 = "INSERT INTO person (name,address,city,department,postcode,region,email,fax,telephone,organizationID) VALUES ('$contact','$address','$city','','','$region','','','','$organization_id')";
mysql_query($query3);
$person_ID = mysql_insert_id();
$query4 = "INSERT INTO employee (personID,organizationID) VALUES ('$person_ID','$organization_id')";
mysql_query($query4);
$query5 = "INSERT INTO primecontractor (projetsref,personID) VALUES ('$RCN','$person_ID')";
mysql_query($query5);
$query6 = "INSERT INTO projectsubjets (projectsref,subjetsID) VALUES ('$RCN','$subjectindexcode')";
mysql_query($query6);
mysql_close();
//Get variables from other participants (if exist)
if ($participants !== false )
{
    $count = 0;
    foreach ($participicants as $item)
    {
        $metadata_name = $xml2->document->metadatagroup->item[$count]->xpath("metadata[@name='organization']");
        $metadata_contact = $xml2->document->metadatagroup->item[$count]->xpath("metadata[@name='contact']");
        $metadata_tel = $xml2->document->metadatagroup->item[$count]->xpath("metadata[@name='tel']");
        $metadata_fax = $xml2->document->metadatagroup->item[$count]->xpath("metadata[@name='fax']");
        $metadata_email = $xml2->document->metadatagroup->item[$count]->xpath("metadata[@name='email']");
        $metadata_url = $xml2->document->metadatagroup->item[$count]->xpath("metadata[@name='url']");
        $metadata_address = $xml2->document->metadatagroup->item[$count]->xpath("metadata[@name='address']");
        $metadata_region = $xml2->document->metadatagroup->item[$count]->xpath("metadata[@name='region']");
        $metadata_type = $xml2->document->metadatagroup->item[$count]->xpath("metadata[@name='organizationtype']");
        mysql_connect(localhost,$username,$password) or die("Unable to connect to database");
        mysql_select_db($database) or die( "Unable to select database");
        $metadata_name_clear = mysql_real_escape_string($metadata_name[0]);
        $metadata_contact_clear = mysql_real_escape_string($metadata_contact[0]);
        $metadata_email_clear = mysql_real_escape_string($metadata_url);
        $query6 = "INSERT INTO organization (name,size,type,address,city,department,postcode,region,url) VALUES ('$metadata_name_clear','','$metadata_type[0]','$metadata_address','','','','','$metadata_region','$metadata_url')";
        mysql_query($query6);
        $metadata_organization_id  = mysql_insert_id();
        $query7 = "INSERT INTO person (name,address,city,department,postcode,region,email,fax,telephone,OrganizationID) VALUES ('$metadata_contact_clear','$metadata_address[0]','','','','$metadata_region[0],'$metadata_email_clear','$metadata_fax[0]','$metadata_tel[0]','$metadata_organization_id')";
        mysql_query($query7);
        $metadata_person_id = mysql_insert_id();
        $query8 = "INSERT into emplyoee (personID,organizationID) VALUES ('$metadata_person_id','$metadata_organization_id')";
        mysql_query($query8);
        $query9 = "INSERT INTO contractor (projetsref,personID) VALUES ('$RCN','$metadata_person_id')";
        mysqpl_query($query9);
        mysql_close();
        $count ++;
    }
}
}
?>