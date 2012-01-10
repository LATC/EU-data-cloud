<?php
$username="root";
$password="";
$database="competition";
// I first grapped all pages and wrote it into links.csv
$filename = "C:\competition\links.csv";
$file = fopen($filename, 'r');
            $i = 0;
            while (($lineArray = fgetcsv($file, 4000, ";")) !== FALSE) {
                for ($j=0; $j<count($lineArray); $j++) {
                    $data2DArray[$i][$j] = $lineArray[$j];
                }
         
                $i++; 
            }
$company_match_array = array();
$case_array = array();
$j=0;

foreach ($data2DArray as $data)
{
    //$url = $data[0];
    $url = "http://ec.europa.eu/competition/elojade/isef/case_details.cfm?proc_code=2_M_1001";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $website = curl_exec($ch);
    // Wenn URL mit 1 anfängt, dann Antitrust / Cartel, 2 = Merger, 3 = State AID
    if(strpos($url,"proc_code=2")!==false)
    {
        preg_match("/=2_(.*)/s", $url, $case_number);
        $case_number_clean = preg_replace("/\//", "\\/s", $case_number[1]);
        $case_number_clean = preg_replace("/_/", ".", $case_number_clean);
        //$title = rtrim($title);
        preg_match("/strong>(.*)strong>/isU", $website, $companies);
        $companies_split = preg_split("/\s*\/\s/", $companies[1]);
        $j=0;
        foreach ($companies_split as $company)
        {
        preg_match_all("/ClassLink\"\s>([^<]*)/s", $company, $match);
        foreach ($match[1] as $matching_company)
        {
        $company_trim = rtrim($matching_company);
        $company_match_array[$j] = $company_trim;
        }
        $j++;           
        }
        
        preg_match("/Notification on\:<[^>]*>\s*<[^>]*>([^<]*)/s", $website, $notification);
        preg_match("/Provisional deadline\:<[^>]*>\s*<[^>]*>(\d\d.\d\d.\d\d\d\d)(.*)<\/td>/isU",$website, $deadline);
        $deadline_text = trim($deadline[2]);
        preg_match("/Prior publication in Official Journal\:(.*)<\/tr>/isU", $website, $publication);
        preg_match_all("/<a\shref=\"([^\"]*)/s", $publication[1], $publication_link);
        $publication_split = preg_split("/Of\s/", $publication[1]);
        $publication_date = preg_replace("/<[^>]*>/", "", $publication_split[1]);
        $publication_date = trim($publication_date);
        preg_match("/<a[^>]*>([^<]*)/s", $publication[1], $publication_link_text);
        preg_match_all("/href=\"index.cfm\?fuseaction=dsp_result&nace_code=[^\"]*\"\s*[^>]*>\s*([^<]*)<[^>]*>\s*-\s([^<]*)/s", $website, $nace);
        $o = 0;
        foreach ($nace[2] as $nace_text)
        {
        $nace[2][$o] = trim($nace_text);
        $o++;
        }
        
        preg_match("/Regulation\:<[^>]*>\s*<[^>]*>([^<]*)/s", $website, $regulation);
        preg_match("/Decisi[^<]*<[^>]*>(.*)<\/tr>/isU", $website, $decisions_2);
        $date = "\d\d.\d\d.\d\d\d\d:";
        preg_match_all($date, $decisions_2,$decision_date);
        $decisions_split = preg_split($date, $decisions_2);
        $k =0;
        foreach ($decisions_split as $decision_art)
        {
            
            $decisions_art[$k] = trim($decision_art);
            $k++;
        }
        $k = 0;
        $l = 1;
        $m = 0;
        foreach ($decisions[1] as $decision_date)
        {
            $pattern = '/'.$decision_date."\:(.*)".$decisions[1][$l].'/s';
            preg_match($pattern, $decisions_2[1], $decision_text[$m]);
            preg_match_all("/<a\shref=\"(http:\/\/**.europa.eu[^\"]*)/s", $decision_text[$m][1], $decision_document_link[$m]);
            preg_match_all("/<a\shref=\"(http:\/\/eur-lex.europa.eu[^\"]*)/s", $decision_text[$m][1], $decision_journal_link[$m]);
            $decision_text[$m] = preg_replace ("/\&nbsp\;/", "", $decision_text[$m]);
            $decision_text[$m] = preg_replace("/<[^>]*>/", "", $decision_text[$m]);
            $l++;
            $m++;
        }
        preg_match("/Other case related information\(s\)\:(.*)tr>/isU", $website, $related);
        preg_match("/<a\shref=\"([^\"]*)/s", $related[1], $related_links);
        $related_text = preg_replace("/<[^>]*>/", "", $related[1]);
        $related_text = trim($related_text);
        $related_text = preg_replace ("/\&nbsp\;/", " ",$related_text);
        preg_match_all("/Relation with other case\(s\)\:<[^>]*>\s*<[^>]*>\s*([^<]*)/s", $website, $related_cases);
        foreach ($related_cases[1] as $case)
        {
            $test = preg_match("/none/s", $case);
                    if ($test != TRUE)
                    {
                        $case_array[$o] = $case;
                        $o++;
                    }
        }
        preg_match("/Related link\(s\)\:<[^>]*>\s*<[^>]*>\s*([^<]*)/s", $website, $links_related);
        rtrim($links_related[1][0]);
        //  format date in xsd:date
        preg_match("/(\d\d).(\d\d).(\d\d\d\d)", $notification[1], $notification_match);
        $notification[1] = $notification_match[3]."-".$notification_match[2]."-".$notification_match[1];
        preg_match("/(\d\d).(\d\d).(\d\d\d\d)", $deadline[1], $deadline_match);
        $deadline[1] = $deadline_match[3]."-".$deadline_match[2]."-".$deadline_match[1];
        preg_match("/(\d\d).(\d\d).(\d\d\d\d)", $publication_date, $publication_date_match);
        $publication_date = $publication_date_match[3]."-".$publication_date_match[2]."-".$publication_date_match[1];
        $q = 0;
        foreach ($decision_date as $d_date)
        {
            preg_match("/(\d\d).(\d\d).(\d\d\d\d)", $d_date, $d_date_match);
            $decision_date[$q] = $d_date_match[3]."-".$d_date_match[2]."-".$d_date_match[1];
            $q++;
        }
        // write into DB
        mysql_connect('localhost',$username,$password) or die("Unable to connect to database");
        mysql_select_db($database) or die("Unable to select database");
        $query_0 = "SELECT ID from merger WHERE ID = '".$case_number_clean."'";
        $select_ID = mysql_query($query_0);
        $select_ID_a = mysql_fetch_array($select_ID);
        if($case_number_clean !="" AND $select_ID_a==false)
        {
        $query_1 = "INSERT into merger (ID, notification, provisional_deadline, deadline_text, regulation, other_case_related_information_link, other_case_related_information_text, related_links, original_link)
            VALUES('".$case_number_clean."','".$notification[1]."','".$deadline[1]."','".$deadline_text."','".$regulation[1]."','".$related_links[1]."','".$related_text."','".$links_related[1]."','".$url."')";
        
        mysql_query($query_1);
        echo mysql_errno() . ": " . mysql_error()."in Merger:".$case_number_clean."\n";
        }
        foreach ($case_array as $case_match)
        {
            $query_2 = "INSERT into merger_relationship(ID_1, ID_2)
                VALUES('".$case_number_clean."', '".$case_match."')";
            mysql_query($query_2);
            echo mysql_errno() . ": " . mysql_error()."in Merger_Relationship:".$case_number_clean." ".$case_match."\n";
        }
        foreach ($company_match_array as $company_value)
        {
        $query_3 = "SELECT ID from company WHERE name = '".$company_value."'";
        $select_company = mysql_query($query_3);
        $select_company_a = mysql_fetch_array($select_company);
        if ($company_value !="" AND $select_company_a==FALSE)
            {
            $query_4 = "INSERT INTO company (name) VALUES ('".$company_value."')";
            mysql_query($query_4);
            $company_id  = mysql_insert_id();
            }
         else {
                $company_id = $select_company_a[ID];
              }
        $query_5 = "INSERT INTO merger_company (merger_ID, company_ID) VALUES
            ('".$case_number_clean."','".$company_id."')";
        mysql_query($query_5);
        echo mysql_errno() . ": " . mysql_error()."in Merger_Company:".$case_number_clean." ".$company_id."\n";
        }
        $n = 0;
        foreach ($decision_date as $date_decision)
        {
            $query_6 = "INSERT INTO decision(date, description, document_link, journal_link, article) VALUES
                ('".$date_decision."','".$decision_text[$n][1]."','".$decision_document_link[$n][1][0]."','".$decision_journal_link[$n][1][0]."','".$decision_art."')";
            mysql_query($query_6);
            $decision_id  = mysql_insert_id();
            echo mysql_errno() . ": " . mysql_error()."in Decision:".$decision_ID."\n";
            $query_7 = "INSERT INTO merger_decision (merger_ID, decision_ID)
                VALUES ('".$case_number_clean."','".$decision_id."')";
            mysql_query($query_7);
            $n++;
        }
        $p = 0;
        foreach ($nace[1] as $nace_code)
        {
        $query_8 = "SELECT nace_code FROM economic_activity WHERE nace_code = '".$nace_code."'";
        $select_nace_code = mysql_query($query_8);
        $select_nace_code_a = mysql_fetch_array($select_nace_code);
        if ($nace_code != "" AND $select_nace_code_a==FALSE)
        {
            $query_9 = "INSERT INTO economic_activity (nace_code, name) VALUES
                ('".$nace_code."','".$nace[2][$p]."')";
            mysql_query($query_9);
             echo mysql_errno() . ": " . mysql_error()."in economic_activity:".$nace_code." from ".$case_number_clean."\n";
            $query_10 = "INSERT INTO merger_economic_activity (merger_ID, economic_activity_nace_code) 
                VALUES ('".$case_number_clean."','".$nace_code."')";
            mysql_query($query_10);
            echo mysql_errno() . ": " . mysql_error()."in merger_economic_activity:".$nace_code." from ".$case_number_clean."\n";
        }
        else
        {
            $query_11 = "INSERT INTO merger_economic_activity (merger_ID, economic_activity_nace_code) VALUES
                ('".$case_number_clean."','".$nace_code."')";
            mysql_query($query_11);
        }
        $p++;
        }
        $query_12 = "INSERT INTO publication (date, link, text) VALUES 
            ('".$publication_date."','".$publication_link[1][0]."','".$publication_link_text[1]."')";
        mysql_query($query_12);
        echo mysql_errno() . ": " . mysql_error()."in publication:".$publication_link[1][0]."\n";
        $publication_ID = mysql_insert_id();
        $query_13 = "INSERT INTO merger_publication VALUES ('".$case_number_clean."','".$publication_ID."')";
        mysql_query($query_13);
        echo mysql_errno() . ": " . mysql_error()."in merger_publication:".$publication_ID."\n";
                
    }
    if(strpos($url,"proc_code=1")!==false)
    {
        preg_match("/=1_(.*)/s", $url, $case_number);
        preg_replace("/\//", "\\/", $case_number[1]);
        $match = "/<strong>\s*".$case_number[1]."*\s*([^<]*)/s";
        preg_match($match, $website, $title);
        $title = rtrim($title[1]);
        preg_match("/nace_code=[^\"]*\"[^>]*>([^<]*)<\/a>\s*-\s*([^<]*)/s", $website, $nace_code );
        preg_match("/<table class=\"events\">(.*)<\/table>/isU",$website, $events );
        preg_match_all ("/eventsTdDate\">([^<]*)/s",$events[1],$event_dates);
        preg_match_all ("/eventsTdDocType\">([^<]*)/s",$events[1],$event_doctype);
        preg_match_all ("/eventsTdDoc\">\s*([^<]*)/s",$events[1],$event_description);
        preg_match_all ("/<a\shref=\"([^\"]*)/s",$events[1],$events_url);
        preg_match("/Companies:<\/td>\s*<[^>]*>(.*)<\/td>/isU",$website, $companies );
        $companies_split = preg_split("/\s\/\s/", $companies[1]);
        $j=0;
        foreach ($companies_split as $company)
        {
        preg_match_all("/<a[^>]*>([^<]*)/s", $company, $match);
        $match_company = $match[1][0]." ".$match[1][1]." ".$match[1][2]." ".$match[1][3]." ".$match[1][4];
        $match_company = trim($match_company);
        $company_match_array[$j] = $match_company;
        $j++;
        }
        // write into DB
        mysql_connect('localhost',$username,$password) or die("Unable to connect to database");
        mysql_select_db($database) or die("Unable to select database");
        $query_0 = "INSERT INTO cartel_antitrust(ID,original_link,title) VALUES ('".$case_number[1]."','".$url."','".$title."')";
        mysql_query($query_0);
        echo mysql_errno() . ": " . mysql_error()."in cartel_antitrust:".$case_number[1]."\n";
        foreach ($company_match_array as $company_value)
        {
        $query_1 = "SELECT ID from company WHERE name = '".$company_value."'";
        $select_company = mysql_query($query_1);
        $select_company_a = mysql_fetch_array($select_company);
        if ($company_value !="" AND $select_company_a==FALSE)
            {
            $query_2 = "INSERT INTO company (name) VALUES ('".$company_value."')";
            mysql_query($query_2);
            $company_id  = mysql_insert_id();
            }
         else {
                $company_id = $select_company_a[ID];
              }
        $query_3 = "INSERT INTO cartel_company (cartel_antitrust_ID, company_ID) VALUES
            ('".$case_number[1]."','".$company_id."')";
        mysql_query($query_3);
        echo mysql_errno() . ": " . mysql_error()."in Merger_Company:".$case_number[1]." ".$company_id."\n";
        }
        $n = 0;
        $query_4 = "SELECT nace_code FROM economic_activity WHERE nace_code = '".$nace_code[1]."'";
        $select_nace_code = mysql_query($query_4);
        $select_nace_code_a = mysql_fetch_array($select_nace_code);
        if ($nace_code[1] != "" AND $select_nace_code_a==FALSE)
        {
            $query_5 = "INSERT INTO economic_activity (nace_code, name) VALUES
                ('".$nace_code[1]."','".$nace_code[2]."')";
            mysql_query($query_5);
             echo mysql_errno() . ": " . mysql_error()."in economic_activity:".$nace_code[1]." from ".$case_number[1]."\n";
            $query_6 = "INSERT INTO cartel_economic_activity (cartel_antitrust_ID, economic_activity_nace_code) 
                VALUES ('".$case_number[1]."','".$nace_code[1]."')";
            mysql_query($query_6);
            echo mysql_errno() . ": " . mysql_error()."in merger_economic_activity:".$nace_code[1]." from ".$case_number[1]."\n";
        }
        else
        {
            $query_7 = "INSERT INTO cartel_economic_activity (cartel_antitrust_ID, economic_activity_nace_code) VALUES
                ('".$case_number[1]."','".$nace_code[1]."')";
            mysql_query($query_7);
        }
        $k = 0;
        foreach($event_doctype[1] as $event)
        {
            $query_8 = "INSERT INTO event(date,document_type,document_link,description) VALUES ('".$event_dates[1][$k]."','".$event."','".$events_url[1][$k]."','".$event_description[1][$k]."')";
            mysql_query($query_8);
            $event_id  = mysql_insert_id();
            echo mysql_errno() . ": " . mysql_error()."in event: ".$event_id." from ".$case_number[1]."\n";
            $query_9 = "INSERT INTO cartel_event VALUES('".$case_number[1]."','".$event_id."')";
            mysql_query($query_9);
            $k++;
        }
    }
    if(strpos($url,"proc_code=3")!==false)
    {
        preg_match("/=3_(.*)/s", $url,$case_number);
        $case_number_clean = preg_replace("/\//", "\\/", $case_number[1]);
        $check = preg_match("/SA/s", $case_number_clean);
        if ($check == TRUE)
        {
            $case_number_clean = preg_replace("/_/", ".", $case_number_clean);

        }
        else
        {
            $case_number_clean = preg_replace("/_/", "\/", $case_number_clean);
        }
        $match = "/<strong>\s*".$case_number_clean."*\s*([^<]*)/s";
        preg_match($match, $website, $title);
        $title = trim($title[1]);
        preg_match("/Member State:<[^>]*>\s*<[^>]*>([^<]*)/s", $website, $state);
        preg_match("/Primary Objective:<[^>]*>\s*<[^>]*>([^<]*)/s", $website, $primary_objective);
        $check_region = preg_match("/Region:<\/td>\s*<[^>]*>(.*)<\/td>/isU",$website, $regions_match);
        preg_match_all("/\s*([^<]*)<br>/s", $regions_match[1], $regions);
        preg_match("/Legal basis primary:<\/td>\s*<[^>]*>(.*)<\/td>/isU",$website, $legal_basis_primary_match);
        preg_match_all("/\s*([^<]*)<br>/s", $legal_basis_primary_match[1], $legal_basis_primary);
        preg_match("/Legal basis secondary:<\/td>\s*<[^>]*>(.*)<\/td>/isU",$website, $legal_basis_secondary_match);
        preg_match_all("/\s*([^<]*)<br>/s", $legal_basis_secondary_match[1], $legal_basis_secondary);
        preg_match("/Sector:<[^>]*>\s*<[^>]*>\s*([^-]*)-\s([^<]*)/s",$website,$nace);
        $nace_code = trim($nace[1]);
        preg_match("/Aid instrument:<[^>]*>\s*<[^>]*>([^<]*)/s",$website, $aid_instrument); //trim aid[1]
        $aid_instrument = trim($aid_instrument[1]);
        preg_match("/Case Type:<[^>]*>\s*<[^>]*>([^<]*)/s",$website, $case_type);
        preg_match("/Duration:<[^>]*>\s*<[^>]*>from\s(\d\d.\d\d.\d\d\d\d)\sto\s(\d\d.\d\d.\d\d\d\d)/s",$website, $duration_from_to);
        $check_until = preg_match("/Duration:<[^>]*>\s*<[^>]*>until\s(\d\d.\d\d.\d\d\d\d)/s",$website, $duration_until);
        if ($check_until == TRUE)
        {
            $end = $duration_until;
        }
        else
        {
            $beginning = $duration_from_to[1];
            $end = $duration_from_to[2];
        }
        preg_match("/Notification or Registration Date:<[^>]*>\s*<[^>]*>([^<]*)/s", $website, $notification_date);
        $notification_date[1] = trim($notification_date[1]);
        preg_match("/DG Responsible:<[^>]*>\s*<[^>]*>([^<]*)/s", $website, $responsible);
        preg_match_all("/Related Cases:<[^>]*>\s*<[^>]*>\s*<[^>]*>([^<]*)/s", $website, $related_cases);
        $o = 0;
        foreach ($related_cases[1] as $case)
        {
            $test = preg_match("/none/s", $case);
                    if ($test != TRUE)
                    {
                        $case_array[$o] = $case;
                        $o++;
                    }
        }
        preg_match_all("/DG Responsible:<[^>]*>\s*<[^>]*>[^<]*<[^>]*>\s*<[^>]*>\s*<[^>]*><[^>]*><[^>]*><[^>]*><[^>]*>\s*<[^>]*>\s*<[^>]*><[^>]*>\s*<[^>]*><[^>]*>([^<]*)/s", $website, $related_cases_alternative);
        $p = 0;
        foreach ($related_cases_alternative[1] as $case_alternative)
        {
            $test = preg_match("/none/s", $case_alternative);
                    if ($test != TRUE)
                    {
                        $case_array_alternative[$o] = $case_alternative;
                        $p++;
                    }
        }
        $check_decision = preg_match("/Decision\son\s(\d\d.\d\d.\d\d\d\d):\s*<[^>]*>\s*<[^>]*>(.*)<\/td>/isU", $website, $decision);
        preg_match_all("/([^<]*)<br>/s",$decision[2],$decision_date_text_array);
        $q = 0;
        foreach ($decision_date_text_array[1] as $decision_date_text)
        {
            $decision_date_text_array[1][$q] = trim($decision_date_text);
            $q++;
        }
        preg_match("/Decision Text:\s<[^>]*>\s*<[^>]*>\s*([^<]*)<[^>]*>(.*)<\/td>/isU", $website, $decision_text);
        $decision_text[1] = trim($decision_text[1]);
        preg_match("/<a\shref=\"([^\"]*)[^>]*>([^<]*)/s", $decision_text[2], $decision_text_link_language);
        preg_match("/Press release:<[^>]*>\s*<[^>]*>\s*<a\shref\s=\"([^\"]*)\"[^>]*>([^<]*)/s", $website, $press_release);
        preg_match("/Publication\s*on\s(\d\d.\d\d.\d\d\d\d):\s*<[^>]*>\s*<[^>]*>\s*[^<]*<a\shref=\"([^\"]*)[^>]*>\s*([^\<]*)/s", $website, $publication);
        $publication[3] = trim($publication[3]);
        $check_summary = preg_match("/Summary\sInfo\sForm:<[^>]*>\s*<[^>]*>\s*<[^>]*>\s*<a\shref=\"([^\"]*)/s", $website, $summary);
        $check_objective = preg_match("/Objective\(s\):<[^>]*>\s*<[^>]*>\s*([^<]*)/s", $website, $objective);
         // write into DB
        mysql_connect('localhost',$username,$password) or die("Unable to connect to database");
        mysql_select_db($database) or die("Unable to select database");
        $title = mysql_escape_string($title);
        $query_0 = "SELECT ID FROM country WHERE name = '".$state[1]."'";
        $select_country_code = mysql_query($query_0);
        $select_country_code_a = mysql_fetch_array($select_country_code);
        if ($state[1] != "" AND $select_country_code_a==FALSE)
        {
            $query_1 = "INSERT INTO country(name) VALUES ('".$state[1]."')";
            mysql_query($query_1);
            $country_id = mysql_insert_id();
        }
        else
        {
            $country_id = $select_country_code_a[ID];
        }
        $query_2 = "SELECT nace_code FROM economic_activity WHERE nace_code = '".$nace_code."'";
        $select_nace_code = mysql_query($query_2);
        $select_nace_code_a = mysql_fetch_array($select_nace_code);
        if ($nace_code != "" AND $select_nace_code_a==FALSE)
        {
            $query_3 = "INSERT INTO economic_activity (nace_code, name) VALUES
                ('".$nace_code."','".$nace[2]."')";
            mysql_query($query_3);
            echo mysql_errno() . ": " . mysql_error()."in economic_activity:".$nace_code." from ".$case_number[1]."\n";
        }
        $query_4 = "INSERT INTO state_aid (ID,title,country_ID,primary_objective,sector,aid_instrument,case_type,beginning,end,notification_date,dg_responsible,press_release_text,press_release_link,publication_date,publication_link,publication_text,original_url) VALUES 
            ('".$case_number_clean."','".$title."','".$country_id."','".$primary_objective[1]."','".$nace_code."','".$aid_instrument."','".$case_type[1]."','".$beginning."','".$end."','".$notification_date[1]."','".$responsible[1]."','".$press_release[2]."','".$press_release[1]."','".$publication[1]."','".$publication[2]."','".$publication[3]."','".$url."')";
        mysql_query($query_4);
        echo mysql_errno() . ": " . mysql_error()."in state_aid:".$case_number_clean."\n";
        foreach ($case_array_alternative as $case_my)
        {
            $query_5 = "INSERT INTO state_aid_relation VALUES ('".$case_number_clean."','".$case_my."')";
            mysql_query($query_5);
            echo mysql_errno() . ": " . mysql_error()."in state_aid_relation:".$case_number_clean." ".$case_my."\n";
        }
        if ($check_decision != FALSE)
        {
        foreach ($decision_date_text_array[1] as $decision_my)
        {
            $query_6 = "INSERT INTO decision(date,description) VALUES ('".$decision[1]."','".$decision_my."')";
            mysql_query($query_6);
            $decision_ID = mysql_insert_id();
            echo mysql_errno() . ": " . mysql_error()."in decision:".$decision_my."\n";
        }
        $query_7 = "INSERT INTO state_aid_decision VALUES ('".$case_number_clean."','".$decision_ID."')";
        mysql_query($query_7);
        echo mysql_errno() . ": " . mysql_error()."in state_aid_decision:".$case_number_clean." ".$decision_ID."\n";
        }
        foreach ($legal_basis_primary[1] as $legal_basis_primary_my)
        {
            $query_8 = "INSERT INTO state_aid_legal_basis_primary VALUES ('".$case_number_clean."','".$legal_basis_primary_my."')";
            mysql_query($query_8);
        }
        foreach ($legal_basis_secondary[1] as $legal_basis_secondary_my)
        {
            $query_9 = "INSERT INTO state_aid_legal_basis_secondary VALUES ('".$case_number_clean."','".$legal_basis_secondary_my."')";
            mysql_query($query_9);
        }
        if ($check_region != FALSE)
        {
        foreach ($regions[1] as $region)
        {
            $query_10 = "SELECT ID FROM region WHERE name = '".$region."' AND country_ID = '".$country_id."'";  
            $select_region_code = mysql_query($query_10);
            $select_region_code_a = mysql_fetch_array($select_region_code);
                if ($region != "" AND $select_region_code_a==FALSE)
                    {
                        $query_11 = "INSERT INTO region(name,country_ID) VALUES ('".$region."','".$country_id."')";
                        mysql_query($query_11);
                        $region_id = mysql_insert_id();
                     }
                else
                    {
                         $region_id = $select_region_code_a[ID];
                    }
            $query_12 = "INSERT INTO state_aid_region VALUES ('".$case_number_clean."','".$region_id."')";
            mysql_query($query_12);
        }
        }
        if ($check_objective != FALSE)
        {
            $query_13 = "UPDATE state_aid SET objective = '".$objective[1]."' WHERE ID = '".$case_number_clean."'";
            mysql_query($query_13);
        }
        if ($check_summary != FALSE)
        {
            $query_14 = "UPDATE state_aid SET summary = '".$summary[1]."' WHERE ID = '".$case_number_clean."'";
            mysql_query($query_14);
        }
        
    }
        unset ($aid_instrument);
        unset ($case_array);
        unset ($case_match);
        unset ($case_number);
        unset ($case_number_clean);
        unset ($ch);
        unset ($companies);
        unset ($companies_split);
        unset ($company);
        unset ($company_id);
        unset ($company_match_array);
        unset ($company_trim);
        unset ($company_value);
        unset ($date_decision);
        unset ($deadline);
        unset ($deadline_text);
        unset ($decision_ID);
        unset ($decision_art);
        unset ($decision_date);
        unset ($decision_date_text);
        unset ($decision_document_link);
        unset ($decision_id);
        unset ($decision_journal_link);
        unset ($decision_text);
        unset ($decision_text_link_language);
        unset ($decisions);
        unset ($decisions_2);
        unset ($decisions_art);
        unset ($duration_from_to);
        unset ($duration_until);
        unset ($event_dates);
        unset ($event_description);
        unset ($event_doctype);
        unset ($events);
        unset ($events_url);
        unset ($i);
        unset ($j);
        unset ($k);
        unset ($l);
        unset ($legal_basis_primary);
        unset ($legal_basis_primary_match);
        unset ($legal_basis_secondary);
        unset ($legal_basis_secondary_match);
        unset ($m);
        unset ($match);
        unset ($matching_company);
        unset ($n);
        unset ($nace);
        unset ($nace_code);
        unset ($nace_text);
        unset ($notification);
        unset ($o);
        unset ($objective);
        unset ($press_release);
        unset ($publication);
        unset ($publication_ID);
        unset ($publication_date);
        unset ($publication_link);
        unset ($publication_link_text);
        unset ($publication_split);
        unset ($query_0);
        unset ($query_1);
        unset ($query_2);
        unset ($query_3);
        unset ($query_4);
        unset ($query_5);
        unset ($query_6);
        unset ($query_7);
        unset ($query_8);
        unset ($query_9);
        unset ($query_10);
        unset ($query_11);
        unset ($query_12);
        unset ($query_13);
        unset ($regulation);
        unset ($related);
        unset ($related_cases);
        unset ($related_links);
        unset ($related_text);
        unset ($select_ID);
        unset ($select_ID_a);
        unset ($select_company);
        unset ($select_company_a);
        unset ($select_nace_code);
        unset ($select_nace_code_a);
        unset ($state);
        unset ($summary);
        unset ($test);
        unset ($title);
        unset ($website);
}

?>
