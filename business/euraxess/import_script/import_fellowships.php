<?php
$timestamp = time();
$date = date("d-m-Y",($timestamp));

$username="root";
$password="Traxdata1";
$database="euraxess";
//$ids_array = array();
$query_array = array();
$affected_array = array();

//Connect to local database
mysql_connect(localhost,$username,$password) or die("Unable to connect to database");
$create_database = "CREATE DATABASE `euraxess`;";
mysql_query($create_database);
mysql_select_db($database) or die("Unable to select database");
$create_fellowship = "CREATE TABLE IF NOT EXISTS `fellowship` (
  `ID` int(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `summary` blob NOT NULL,
  `criteria` blob NOT NULL,
  `selection` blob NOT NULL,
  `awards_per_year` int(11) NOT NULL,
  `annual_budget` int(11) NOT NULL,
  `frequency` varchar(11) NOT NULL,
  `int_mobility_req` varchar(255) NOT NULL,
  `eligible_country_residence` varchar(255) NOT NULL,
  `eligible_country_fellows` varchar(255) NOT NULL,
  `eligible_nationality_fellows` varchar(255) NOT NULL,
  `career_stage_ID` int(255) NOT NULL,
  `social_security` varchar(255) NOT NULL,
  `amount_per_fellowship` varchar(255) NOT NULL,
  `currency` varchar(255) NOT NULL,
  `covers_salary` varchar(255) NOT NULL,
  `covers_subsistence` varchar(255) NOT NULL,
  `covers_research_costs` varchar(255) NOT NULL,
  `max_duration` varchar(255) NOT NULL,
  `framework_programme_ID` int(255) NOT NULL,
  `company_ID` int(255) NOT NULL,
  `original_url` varchar(255) NOT NULL,
  `date_posted` date NOT NULL,
  `application_deadline` date NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;";
mysql_query($create_fellowship);
$create_fellowship_research_fields = "CREATE TABLE IF NOT EXISTS `fellowship_research_fields` (
  `fellowship_ID` int(255) NOT NULL,
  `research_field_ID` int(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;";
mysql_query($create_fellowship_research_fields);

$create_fellowship_destination_countries = "CREATE TABLE IF NOT EXISTS `fellowship_destination_countries` (
  `fellowship_ID` int(255) NOT NULL,
  `country_ID` int(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;";
mysql_query($create_fellowship_destination_countries);

$create_fellowship_countries_residence = "CREATE TABLE IF NOT EXISTS `fellowship_countries_residence` (
  `fellowship_ID` int(255) NOT NULL,
  `country_ID` int(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;";
mysql_query($create_fellowship_countries_residence);

$create_country = "CREATE TABLE IF NOT EXISTS `country` (
  `ID` int(255) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;";
mysql_query($create_country);

$create_fellowship_nationalities = "CREATE TABLE IF NOT EXISTS `fellowship_nationalities` (
  `fellowship_ID` int(255) NOT NULL,
  `country_ID` int(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;";
mysql_query($create_fellowship_nationalities);

$create_fellowship_websites = "CREATE TABLE IF NOT EXISTS `fellowship_websites` (
  `fellowship_ID` int(255) NOT NULL,
  `website` varchar(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;";
mysql_query($create_fellowship_websites);

$create_fellowship_career_stage = "CREATE TABLE IF NOT EXISTS `fellowship_career_stage` (
  `fellowship_ID` int(255) NOT NULL,
  `career_stage_ID` int(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;";
mysql_query($create_fellowship_career_stage);

$alter_statistics = "ALTER TABLE statistics add (country int(255) NOT NULL, fellowship int(255) NOT NULL, fellowship_research_fields int(255) NOT NULL, fellowship_destination_countries int(255) NOT NULL, fellowship_websites int(255) NOT NULL, fellowship_countries_residence int(255) NOT NULL, fellowship_nationality int(255) NOT NULL, fellowship_career_stage int(255) NOT NULL);
    ";
mysql_query($alter_statistics);
$query_statistics_before = "SELECT * FROM `statistics` WHERE ID = (SELECT MAX(ID) FROM statistics)";
$statistics = mysql_query($query_statistics_before);
$statistics_a = mysql_fetch_array($statistics);

//aktuellste Seite

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://ec.europa.eu/euraxess/index.cfm/jobs/fgSearch');
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//curl_setopt($ch, CURLOPT_USERAGENT, '[Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.2) Gecko/20070219 Firefox/2.0.0.2")]');
//establishing connection
$data_fellowships = curl_exec($ch);
$data_fellowships = utf8_decode($data_fellowships);

// get number of results

$pattern_results = ("/<div class=\"subtitle\">\s*([\d]*)/s");
preg_match_all($pattern_results, $data_fellowships, $pattern_results_match);
$results = intval($pattern_results_match[1][0]);
$number_of_pages = ceil($results/15);
//hier mal nachschauen, ob das so klappt
$ids_array = array($number_of_pages);
$post_dates_array = array($number_of_pages);
// get ids

for ($i=1; $i <=$number_of_pages; $i +=1){
    $ch2 = curl_init();
    $url = 'http://ec.europa.eu/euraxess/index.cfm/jobs/fgSearch/page/'.$i;
    curl_setopt($ch2, CURLOPT_URL, $url);
curl_setopt($ch2, CURLOPT_HEADER, 0);
curl_setopt($ch2, CURLOPT_RETURNTRANSFER, 1);
//curl_setopt($ch, CURLOPT_USERAGENT, '[Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.2) Gecko/20070219 Firefox/2.0.0.2")]');
//establishing connection
$fellowship_ids = curl_exec($ch2);
$fellowship_ids = utf8_decode($fellowship_ids);
$pattern_ids = ("/<a href=\"index.cfm\/jobs\/fgDetails\/([\d]*)/s");
preg_match_all($pattern_ids, $fellowship_ids, $pattern_ids_match);
$ids = array($pattern_ids_match[1]);
$pattern_post_date = ("/<span class=\"time\"><[^>]*>Post date: <[^>]*>([^\s]*)/s");
preg_match_all($pattern_post_date, $fellowship_ids, $pattern_post_date_match);
$post_dates = array($pattern_post_date_match[1]);

$ids_array = array_merge($ids_array, $ids);
$post_dates_array = array_merge($post_dates_array, $post_dates);
}

    for ($j=1;$j<=$ids_array[0];$j +=1){
        $k = 0;
        foreach ($ids_array[$j] as $fellowship_id){

            $original_url = 'http://ec.europa.eu/euraxess/index.cfm/jobs/fgDetails/'.$fellowship_id;
            $ch3 = curl_init();
curl_setopt($ch3, CURLOPT_URL, $original_url);
curl_setopt($ch3, CURLOPT_HEADER, 0);
curl_setopt($ch3, CURLOPT_RETURNTRANSFER, 1);
//curl_setopt($ch, CURLOPT_USERAGENT, '[Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.2) Gecko/20070219 Firefox/2.0.0.2")]');
//establishing connection
$data = curl_exec($ch3);
$data = utf8_decode($data);
//matching title

//check if image is in title
$pattern_image = ("/<div[^>]*>\s*<strong>[^>]*>[^>]*>\s*<h1>\s*<i[^>]*>\s*/s");
$imagecheck = preg_match_all($pattern_image, $data, $image_check);

if($imagecheck==1){
$pattern_title = ("/<div[^>]*>\s*<strong>[^>]*>[^>]*>\s*<h1>\s*<i[^>]*>\s*([^<]*)\n/s");
preg_match_all($pattern_title, $data, $pattern_title_match);
$title = $pattern_title_match[1][0];

}
else
    {
$pattern_title = ("/<div[^>]*>\s*<strong>[^>]*>[^>]*>\s*<h1>\s*<?i?[^>]*?>?\s*([^<]*)\n/s");
preg_match_all($pattern_title, $data, $pattern_title_match);
$title = $pattern_title_match[1][0];
}

//matching summary
$pattern_summary = ("/<\/button>\s*<\/div>\s*<p>([^~]*?)<\/p>/s");
preg_match_all($pattern_summary, $data, $pattern_summary_match);
$summary = $pattern_summary_match[1][0];

//matching description
$pattern_criteria = ("/<h3>Eligibility criteria<\/h3>\s*<p>([^~]*?)<\/p>/s");
preg_match_all($pattern_criteria, $data, $pattern_criteria_match);
$criteria = $pattern_criteria_match[1];

//matching selection process
$pattern_selection = ("/<h3>Selection process<\/h3>\s*<p>([^~]*?)<\/p>/s");
preg_match_all($pattern_selection, $data, $pattern_selection_match);
$selection = $pattern_selection_match[1];

//matching research_fields
$pattern_research_fields = ("/<h3>Research Fields<\/h3>\s*<p>\s*([^~]*?<\/p>)/s");
preg_match_all($pattern_research_fields, $data, $pattern_research_fields_match);
$pattern_research_fields2 = ("/\s*([^\<]*)\s?<[^>]*>/s");
preg_match_all($pattern_research_fields2, $pattern_research_fields_match[1][0] , $research_fields);
//// dividing research fields (if possible)
//$pattern_divide = ("/([^\s]*)\s*-\s*([^\n]*)<b/s");
//preg_match_all($pattern_divide, $pattern_research_fields_match[1][0], $divide_match);
//
//if(isset($divide_match[1][0])==TRUE){
//    $research_fields = $divide_match;
//}
//
//

//matching awards_per_year
$pattern_awards = ("/Number of awards per year<[^>]*><[^>]*>([^<]*)/s");
preg_match_all($pattern_awards,$data,$pattern_awards_match);
$awards = $pattern_awards_match[1];

//matching annual_budget
$pattern_budget = ("/Annual budget<[^>]*><[^>]*>([^<]*)/s");
preg_match_all($pattern_budget,$data,$pattern_budget_match);
$budget = $pattern_budget_match[1];

//matching frequency_calls
$pattern_frequency_calls = ("/Frequency of calls<[^>]*><[^>]*>([^<]*)/s");
preg_match_all($pattern_frequency_calls,$data,$pattern_frequency_calls_match);
$frequency_calls = $pattern_frequency_calls_match[1];

//matching international_mobility
$pattern_international_mobility = ("/International mobility required \?<[^>]*><[^>]*>([^<]*)/s");
preg_match_all($pattern_international_mobility,$data,$pattern_international_mobility_match);
$international_mobility = $pattern_international_mobility_match[1];

//matching eligible_country_residence
$pattern_eligible_country_residence = ("/Eligibility of fellows\: country\/ies of residence<[^>]*><[^>]*>([^<]*)/s");
preg_match_all($pattern_eligible_country_residence,$data,$pattern_eligible_country_residence_match);
$eligible_country_residence =  $pattern_eligible_country_residence_match[1];
$eligible_country_residence = ltrim($eligible_country_residence[0]);

$eligible_country_residence_array = explode(",", $eligible_country_residence);

if ($eligible_country_residence_array != NULL){
    foreach ($eligible_country_residence_array as $key =>$value)
        {
            $eligible_country_residence_array[$key] = ltrim($value);
        }
    $eligible_country_residence = $eligible_country_residence_array;
}

//matching Eligibility of fellows: nationality/ies
$pattern_eligible_nationality_fellows = ("/Eligibility of fellows\: nationality\/ies<[^>]*><[^>]*>([^<]*)/s");
preg_match_all($pattern_eligible_nationality_fellows,$data,$pattern_eligible_nationality_fellows_match);
$eligible_nationality_fellows = $pattern_eligible_nationality_fellows_match[1];
$eligible_nationality_fellows = ltrim($eligible_nationality_fellows[0]);

$eligible_nationalites_array = explode(",", $eligible_nationality_fellows);

if ($eligible_nationalites_array != NULL){
    foreach ($eligible_nationalites_array as $key =>$value)
        {
            $eligible_nationalites_array[$key] = ltrim($value);
        }
    $eligible_nationality_fellows = $eligible_nationalites_array;
}



// matching Website of Fellowship Programme
$pattern_website = ("/Website of Fellowship Programme<\/[^>]*><[^>]*><[^>]*>([^<]*)<[^>]*>[^<]*<[^>]*>([^<]*)/s");
preg_match_all($pattern_website,$data,$pattern_website_match);
$website = $pattern_website_match[1];


//matching career_stage
$pattern_career_stage = ("/Career Stage<[^>]*><[^>]*>([^<]*)/s");
preg_match_all($pattern_career_stage, $data, $pattern_career_stage_match);
$career_stage = $pattern_career_stage_match[1];
$career_stage = ltrim($career_stage[0]);

$career_stage_array = explode(",", $career_stage);

if ($career_stage_array != NULL){
    $career_stage = $career_stage_array;
}

//matching social security
$pattern_social_security = ("/Employment contract with full social security<[^>]*><[^>]*>([^<]*)/s");
preg_match_all($pattern_social_security, $data, $pattern_social_security_match);
$social_security = $pattern_social_security_match[1];

//matching amount_per_fellowship
$pattern_amount_per_fellowship = ("/Total amount per fellowship per year<[^>]*><[^>]*>([^<]*)/s");
preg_match_all($pattern_amount_per_fellowship, $data, $pattern_amount_per_fellowship_match);
$amount_per_fellowship = $pattern_amount_per_fellowship_match[1];

// matching currency
$pattern_currency = ("/Currency<[^>]*><[^>]*>([^<]*)/s");
preg_match_all($pattern_currency, $data, $pattern_currency_match);
$currency = $pattern_currency_match[1];

// matching covers_salary
$pattern_covers_salary = ("/Covers salary<[^>]*><[^>]*>([^<]*)/s");
preg_match_all($pattern_covers_salary, $data, $pattern_covers_salary_match);
$covers_salary = $pattern_covers_salary_match[1];

// matching covers_subsistence
$pattern_covers_subsistence = ("/Covers travel and subsistence<[^>]*><[^>]*>([^<]*)/s");
preg_match_all($pattern_covers_subsistence, $data, $pattern_covers_subsistence_match);
$covers_subsistence = $pattern_covers_subsistence_match[1];

// matching covers_research
$pattern_covers_research = ("/Covers research costs<[^>]*><[^>]*>([^<]*)/s");
preg_match_all($pattern_covers_research, $data, $pattern_covers_research_match);
$covers_research = $pattern_covers_research_match[1];

//matching duration of fellowship

$pattern_max_duration = ("/Maximum duration of fellowship<[^>]*><[^>]*>([^<]*)/s");
preg_match_all($pattern_max_duration, $data, $pattern_max_duration_match);
$max_duration = $pattern_max_duration_match[1];

//matching framework_programme
$pattern_framework_programme = ("/EU Research Framework Programme<[^>]*>\s*<p[^>]*>\s*([^\n]*)\s*<\/p>/s");
preg_match_all($pattern_framework_programme, $data, $pattern_framework_programme_match);
$framework_programme = $pattern_framework_programme_match[1];

//matching organisation
$pattern_company = ("/Funding organisation \/ Contacts<[^>]*>\s*<[^>]*>([^<]*)<[^>]*>\s*<[^>]*>\s*([^~]*?)<\/p>/s");
preg_match_all($pattern_company, $data, $pattern_company_match);
$company = $pattern_company_match[1];

foreach ($company as $key =>$value)
{
$company[$key] = rtrim($value);
}

// get phone_number(s)
$pattern_phone_number = ("/Phones<[^>]*>([^<]*)/s");
preg_match_all($pattern_phone_number, $pattern_company_match[2][0], $pattern_phone_number_match);
//get fax_number
//$pattern_fax_number = ("/fax\s*([^<]*)/s");
//preg_match_all($pattern_fax_number, $pattern_company_match[2][0], $pattern_fax_number_match);
//get email
$pattern_email = ("/E-mails\<[^>]*><[^>]*>([^<]*)</s");
preg_match_all($pattern_email, $pattern_company_match[2][0], $pattern_email_match);
//get website
$pattern_website = ("/<A[^>]*>([^<]*)/s");
preg_match_all($pattern_website, $pattern_company_match[2][0], $pattern_website_match);

foreach ($pattern_phone_number_match as $phone_number){
$pattern_company_match_clean = str_replace($phone_number, "", $pattern_company_match[2][0]);
}
//$pattern_company_match_clean = str_replace($pattern_fax_number_match[0], "", $pattern_company_match_clean);
$pattern_company_match_clean = str_replace($pattern_email_match[0], "", $pattern_company_match_clean);
$pattern_company_match_clean = str_replace($pattern_website_match[0], "", $pattern_company_match_clean);
$company_info = $pattern_company_match_clean;

//matching Application Deadline
$pattern_application_deadline = ("/Application deadline<[^>]*>\s*<p[^>]*>\s*([^\n]*)\s*<\/p>/s");
preg_match_all($pattern_application_deadline, $data, $pattern_application_deadline_match);
$application_deadline_wrong_format = $pattern_application_deadline_match[1];
preg_match_all("/(\d*)\/(\d*)\/(\d*)/s",$application_deadline_wrong_format[0],$application_deadline_right_format);
$application_deadline = ($application_deadline_right_format[3][0]."-".$application_deadline_right_format[2][0]."-".$application_deadline_right_format[1][0]);

// matching Eligible destination country/ies for fellows

$pattern_eligible_country_fellows = ("/Eligible destination country\/ies for fellows<[^>]*><[^>]*>([^<]*)/s");
preg_match_all($pattern_eligible_country_fellows,$data,$pattern_eligible_country_fellows_match);
$eligible_country_fellows = $pattern_eligible_country_fellows_match[1];
$eligible_country_fellows = ltrim($eligible_country_fellows[0]);

$eligible_country_fellows_array = explode(",", $eligible_country_fellows);


if ($eligible_country_fellows_array != NULL){
    foreach ($eligible_country_fellows_array as $key =>$value)
        {
            $eligible_country_fellows_array[$key] = ltrim($value);
        }
    $eligible_country_fellows = $eligible_country_fellows_array;
}

//date posted

preg_match_all("/(\d*)\/(\d*)\/(\d*)/s",$post_dates_array[$j][$k],$date_posted_right_format);
$date_posted = ($date_posted_right_format[3][0]."-".$date_posted_right_format[2][0]."-".$date_posted_right_format[1][0]);

//Clean the data

//remove whitespaces of website

//$website = preg_replace("/\s*/s","",$pattern_website_match[1][0]);


//search for empty arrays only in research fields
foreach($research_fields[1] as $key => $value) {
    $research_fields[1][$key] = rtrim($value);
  if($value == "") {
    unset($research_fields[1][$key]);
  }
}

//search for empty arrays in websites

foreach($website as $key => $value){
    if($value == "") {
    unset($website[$key]);
}

//remove whitespace from summary
$summary = ltrim($summary);

$search = array("<br />", "/a>","</A>","<br/>","'");
$replace = array(" \n ","",""," \n "."\'");
$values = array($title,$summary,$criteria,$selection,$research_fields[1],$awards,$budget,$frequency_calls,$international_mobility,$eligible_country_residence,$eligible_nationality_fellows,$website,$career_stage,$social_security,$amount_per_fellowship,$currency,$covers_salary,$covers_subsistence,$covers_research,$max_duration,$framework_programme,$company,$pattern_phone_number_match[1],$pattern_email_match[1],$pattern_website_match,$company_info,$application_deadline,$eligible_country_fellows,$date_posted);
foreach($values as $key =>$value ){
    $values[$key] = str_replace($search, $replace, $value);
}

//Connect to local database
mysql_connect(localhost,$username,$password) or die("Unable to connect to database");
mysql_select_db($database) or die("Unable to select database");

$query = "INSERT INTO fellowship (ID,title, summary, criteria,selection,awards_per_year,annual_budget,frequency,int_mobility_req,social_security,amount_per_fellowship,currency,covers_salary,covers_subsistence,covers_research_costs,max_duration,original_url,date_posted,application_deadline)
VALUES('".$fellowship_id."','$values[0]','".$values[1]."','".$values[2][0]."','".$values[3][0]."','".$values[5][0]."','".$values[6][0]."','".$values[7][0]."','".$values[8][0]."','".$values[13][0]."','".$values[14][0]."','".$values[15][0]."','".$values[16][0]."','".$values[17][0]."','".$values[18][0]."','".$values[19][0]."','".$original_url."','".$date_posted."','".$values[26]."')
";
mysql_query($query);
echo mysql_errno() . ": " . mysql_error()."in Fellowship_ID:".$fellowship_id. "\n";


//fill table company

//proof if company is already in the database
$query1 = "SELECT ID FROM company WHERE name = '".$values[21][0]."' AND website = '".$values[24][1][0]."'
    ";
$select_company = mysql_query($query1);
$select_company_a = mysql_fetch_array($select_company);

if ($values[21][0] !="" AND $select_company_a==FALSE){
$query2 = "INSERT INTO company (name,address,email,website)
    VALUES('".$values[21][0]."','".$values[25]."','".$values[23][0]."','".$values[24][1][0]."')
";
mysql_query($query2);
$company_id  = mysql_insert_id();
echo mysql_errno() . ": " . mysql_error()."in Company_ID:".$company_id. "\n";

}
 else {
    $company_id = $select_company_a[ID];
}

if ($values[22][0] !=''){
$query_phone = "INSERT INTO company_phone VALUES ('$company_id','".$values[22][0]."')";
mysql_query($query_phone);
}

$query3 = "UPDATE fellowship SET company_ID = '$company_id' WHERE ID = '".$fellowship_id."'
";
mysql_query($query3);

//websites in schleife
foreach ($values[11] as $website){
    $query41 = "SELECT website FROM fellowship_websites WHERE website = '".$website."' AND fellowship_ID = '".$fellowship_id."'
    ";
$select_website = mysql_query($query41);
$select_website_a = mysql_fetch_array($select_website);

if ($select_website_a==FALSE){
$query4 = " INSERT INTO fellowship_websites (fellowship_ID,website)
    VALUES ('$fellowship_id','$website')";
mysql_query($query4);
}
}

//career_stage
foreach ($values[12] as $career_stage){
$query6 = "SELECT ID FROM career_stage
    WHERE name ='$career_stage'
    ";
$select_career = mysql_query($query6);
$select_career_a = mysql_fetch_array($select_career);

if ($select_career_a==FALSE){
    $query7 = "INSERT INTO career_stage (name)
        VALUES ('$career_stage')
        ";
    mysql_query($query7);
    $career_stage_ID  = mysql_insert_id();
    $query8 = "INSERT INTO fellowship_career_stage VALUES ('".$fellowship_id."','$career_stage_ID') 
        ";
    mysql_query($query8);
}
else{
    $query9 = "UPDATE fellowship SET career_stage_ID = '$select_career_a[ID]' WHERE ID = '".$fellowship_id."'
        ";
    mysql_query($query9);
}
}

//framework programme

if ($values[20][0]!=NULL){
$query21= "SELECT ID FROM framework_programme
    WHERE name ='".$values[20][0]."'

";
$select_framework_programme = mysql_query($query21);
$select_framework_programme_a = mysql_fetch_array($select_framework_programme);

if ($select_framework_programme_a==FALSE){
    $query22 = "INSERT INTO framework_programme (name)
        VALUES ('".$values[20][0]."')
    ";
    mysql_query($query22);
    $framework_programme_ID  = mysql_insert_id();
    $query23 = "UPDATE fellowship SET framework_programme_ID  = '$framework_programme_ID ' WHERE ID = '".$fellowship_id."'";
    mysql_query($query23);
}
else{
    $query24 = "UPDATE fellowship SET framework_programme_ID  = '$select_framework_programme_a[ID]' WHERE ID = '".$fellowship_id."'"
        ;
    mysql_query($query24);
}
}

//research fields

foreach ($values[4] as $research_field){
$query30 = "SELECT ID FROM research_field
    WHERE name ='$research_field'

";
$select_research_field = mysql_query($query30);
$select_research_field_a = mysql_fetch_array($select_research_field);

if ($select_research_field_a==FALSE){
    $query31 = "INSERT INTO research_field (name)
        VALUES ('$research_field')
        ";
    mysql_query($query31);
    $research_field_ID  = mysql_insert_id();
    $query32 = "INSERT INTO fellowship_research_fields VALUES ('".$fellowship_id."','$research_field_ID')";
    mysql_query($query32);
}
else{
    $query33 = "INSERT INTO fellowship_research_fields VALUES ('".$fellowship_id."',$select_research_field_a[ID])
    ";
    mysql_query($query33);
}
}

//eligible_country_fellows

foreach($values[27] as $country)
{
  $query34 = "SELECT ID FROM country
    WHERE name ='$country'
  ";  
    $select_country = mysql_query($query34);
    $select_country_a = mysql_fetch_array($select_country);
  if ($select_country_a==FALSE){
      $query35 = "INSERT INTO country (name) 
          VALUES ('$country')";
      mysql_query($query35);
      $country_ID = mysql_insert_id();
      $query36 = "INSERT INTO fellowship_destination_countries VALUES ('".$fellowship_id."','$country_ID')";
      mysql_query($query36);
  }
  else{
      $query37 = "INSERT INTO fellowship_destination_countries VALUES ('".$fellowship_id."','$select_country_a[ID]')";
      mysql_query($query37);
  }
}

//eligible_country_residence

foreach($values[9] as $residence)
{
  $query38 = "SELECT ID FROM country
    WHERE name ='$residence'
  ";  
    $select_residence = mysql_query($query38);
    $select_residence_a = mysql_fetch_array($select_residence);
  if ($select_residence_a==FALSE){
      $query39 = "INSERT INTO country (name) 
          VALUES ('$residence')";
      mysql_query($query39);
      $country_ID = mysql_insert_id();
      $query40 = "INSERT INTO fellowship_countries_residence VALUES ('".$fellowship_id."','$country_ID')";
      mysql_query($query40);
  }
  else{
      $query41 = "INSERT INTO fellowship_countries_residence VALUES ('".$fellowship_id."','$select_residence_a[ID]')";
      mysql_query($query41);
  }
}

//eligible_nationality_fellows 

foreach($values[10] as $nationality)
{
  $query42 = "SELECT ID FROM country
    WHERE name ='$nationality'
  ";  
    $select_nationality = mysql_query($query42);
    $select_nationality_a = mysql_fetch_array($select_nationality);
  if ($select_nationality_a==FALSE){
      $query43 = "INSERT INTO country (name) 
          VALUES ('$nationality')";
      mysql_query($query43);
      $country_ID = mysql_insert_id();
      $query44 = "INSERT INTO fellowship_nationalities VALUES ('".$fellowship_id."','$country_ID')";
      mysql_query($query44);
  }
  else{
      $query45 = "INSERT INTO fellowship_nationalities VALUES ('".$fellowship_id."','$select_nationality_a[ID]')";
      mysql_query($query45);
  }
}

    }
    $k ++;
    }
}
// Count actual number of rows

$count_1 = "SELECT COUNT(ID) FROM career_stage";
$count_2 = "SELECT COUNT(ID) FROM company";
$count_3 = "SELECT COUNT(company_ID) FROM company_phone";
$count_4 = "SELECT COUNT(ID) FROM contract_type";
$count_5 = "SELECT COUNT(ID) FROM degree";
$count_6 = "SELECT COUNT(ID) FROM degree_field";
$count_7 = "SELECT COUNT(ID) FROM degree_level";
$count_8 = "SELECT COUNT(ID) FROM framework_programme";
$count_9 = "SELECT COUNT(ID) FROM job";
$count_10 = "SELECT COUNT(job_ID) FROM job_career_stage";
$count_11 = "SELECT COUNT(job_ID) FROM job_degree";
$count_12 = "SELECT COUNT(job_ID) FROM job_required_languages";
$count_13 = "SELECT COUNT(job_ID) FROM job_requirements";
$count_14 = "SELECT COUNT(job_ID) FROM job_research_fields";
$count_15 = "SELECT COUNT(iso639p3) FROM language";
$count_16 = "SELECT COUNT(ilr_level) FROM language_level";
$count_17 = "SELECT COUNT(ID) FROM research_field";
$count_18 = "SELECT COUNT(job_ID) FROM todo_job_required_languages";
$count_19 = "SELECT COUNT(ID) FROM country";
$count_20 = "SELECT COUNT(ID) FROM fellowship";
$count_21 = "SELECT COUNT(fellowship_ID) FROM fellowship_destination_countries";
$count_22 = "SELECT COUNT(fellowship_ID) FROM fellowship_research_fields";
$count_23 = "SELECT COUNT(fellowship_ID) FROM fellowship_websites";
$count_24 = "SELECT COUNT(fellowship_ID) FROM fellowship_countries_residence";
$count_25 = "SELECT COUNT(fellowship_ID) FROM fellowship_nationalities";
$count_26 = "SELECT COUNT(fellowship_ID) FROM fellowship_career_stage";

$count_array = array($count_1,$count_2,$count_3,$count_4,$count_5,$count_6,$count_7,$count_8,$count_9,$count_10,$count_11,$count_12,$count_13,$count_14,$count_15,$count_16,$count_17,$count_18,$count_19,$count_20,$count_21,$count_22,$count_23,$count_24,$count_25,$count_26);
//Connect to local database
mysql_connect(localhost,$username,$password) or die("Unable to connect to database");
mysql_select_db($database) or die("Unable to select database");


foreach ($count_array as $count)
{
    $count_rows = mysql_query($count);
    $count_rows_a = mysql_fetch_array($count_rows);
    $query_array = array_merge($query_array,$count_rows_a);
}

$query_statistics = "INSERT INTO statistics(timestamp,career_stage,company,company_phone,contract_type,degree,degree_field,degree_level,framework_programme,job,job_career_stage,job_degree,job_required_languages,job_requirements,job_research_fields,language,language_level,research_field,todo_job_required_languages,country,fellowship,fellowship_destination_countries,fellowship_research_fields,fellowship_websites,fellowship_countries_residence,fellowship_nationality,fellowship_career_stage)
    VALUES ('".$date."','".$query_array[0]."','".$query_array[1]."','".$query_array[2]."','".$query_array[3]."','".$query_array[4]."','".$query_array[5]."','".$query_array[6]."','".$query_array[7]."','".$query_array[8]."','".$query_array[9]."','".$query_array[10]."','".$query_array[11]."','".$query_array[12]."','".$query_array[13]."','".$query_array[14]."','".$query_array[15]."','".$query_array[16]."','".$query_array[17]."','".$query_array[18]."','".$query_array[19]."','".$query_array[20]."','".$query_array[21]."','".$query_array[22]."','".$query_array[23]."','".$query_array[24]."','".$query_array[25]."')
        ";
mysql_query($query_statistics);
 $r = 2;
for ($s=0;$s<=26;$s+=1){

    $affected = $query_array[$s]-$statistics_a[$r];
    array_push($affected_array,$affected);
    $r ++;
}

echo "affected rows (career stage): ".$affected_array[0]."\n";
echo "affected rows (company): ".$affected_array[1]."\n";
echo "affected rows (company phone): ".$affected_array[2]."\n";
echo "affected rows (contract type): ".$affected_array[3]."\n";
echo "affected rows (degree): ".$affected_array[4]."\n";
echo "affected rows (degree field): ".$affected_array[5]."\n";
echo "affected rows (degree level): ".$affected_array[6]."\n";
echo "affected rows (framework programme): ".$affected_array[7]."\n";
echo "affected rows (job): ".$affected_array[8]."\n";
echo "affected rows (job career stage): ".$affected_array[9]."\n";
echo "affected rows (job degree): ".$affected_array[10]."\n";
echo "affected rows (job required language): ".$affected_array[11]."\n";
echo "affected rows (job requirements): ".$affected_array[12]."\n";
echo "affected rows (job research field): ".$affected_array[13]."\n";
echo "affected rows (language): ".$affected_array[14]."\n";
echo "affected rows (language level): ".$affected_array[15]."\n";
echo "affected rows (research fields): ".$affected_array[16]."\n";
echo "affected rows (todo job required language): ".$affected_array[17]."\n";
echo "affected rows (country): ".$affected_array[18]."\n";
echo "affected rows (fellowship): ".$affected_array[19]."\n";
echo "affected rows (fellowship_destination_countries): ".$affected_array[20]."\n";
echo "affected rows (fellowship_research_fields): ".$affected_array[21]."\n";
echo "affected rows (fellowship_websites): ".$affected_array[22]."\n";
echo "affected rows (fellowship_countries_residence): ".$affected_array[23]."\n";
echo "affected rows (fellowship_nationality): ".$affected_array[24]."\n";
echo "affected rows (fellowship_career_stage): ".$affected_array[25]."\n";


mysql_close();
?>
