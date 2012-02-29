<?php
$username="root";
$password="";
$database="competition";
// I first grapped all pages and wrote it into links.csv
$filename = "C:\competition\links_test3.csv";
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
$count_ID = 0;
$count_ID_2 = 0;
$count_ID_3 = 19201;
$decision_article = array();
foreach ($data2DArray as $data)
{
    //$url = $data[0];
    $url = "http://ec.europa.eu/competition/elojade/isef/case_details.cfm?proc_code=3_N293_2008";

    // Wenn URL mit 1 anfängt, dann Antitrust / Cartel, 2 = Merger, 3 = State AID
    if(strpos($url,"proc_code=2")!==false)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0"); 
        curl_setopt($ch, CURLOPT_ENCODING, "" );
        $website = curl_exec($ch);
        preg_match("/=2_(.*)/s", $url, $case_number);
        $case_number_clean = preg_replace("/\//", "\\/s", $case_number[1]);
        $case_number_clean = preg_replace("/_/", ".", $case_number_clean);
        //$title = rtrim($title);
        preg_match("/Prior publication in Official Journal\:(.*)<\/tr>/isU", $website, $publication);
        preg_match_all("/<a\shref=\"([^\"]*)/s", $publication[1], $publication_links);
        $publication_split = preg_split("/Of\s/", $publication[1]);
        $publication_date = preg_replace("/<[^>]*>/", "", $publication_split[1]);
        $publication_date = trim($publication_date);
        preg_match("/<a[^>]*>([^<]*)/s", $publication[1], $publication_link_text);
        //  format date in xsd:date
        preg_match("/(\d\d).(\d\d).(\d\d\d\d)/s", $publication_date, $publication_date_match);
        if (count($publication_date_match) > 3)
        {
        $publication_date = $publication_date_match[3]."-".$publication_date_match[2]."-".$publication_date_match[1];
        $q = 0;
        }        
        // write into DB
        mysql_connect('localhost',$username,$password) or die("Unable to connect to database");
        mysql_select_db($database) or die("Unable to select database");
                foreach ($publication_links[1] as $publication_link){
        $publication_ID = "ME_".$case_number_clean."_".$count_ID_3;
        $query_17 = "INSERT INTO publication (id,date,link,text) VALUES
            ('".$publication_ID."','".$publication_date."','".$publication_link."','".$publication_link_text[1]."')";
        mysql_query($query_17);
        $query_18 = "INSERT INTO merger_publication VALUES ('".$case_number_clean."','".$publication_ID."')";
        mysql_query($query_18);
        $count_ID_3++;
        }
    }
unset ($article);
        unset ($aid_instrument);
        unset ($beginning);
        unset ($beginning_match);
        unset ($case_array);
        unset ($case_match);
        unset ($case_number);
        unset ($case_number_clean);
        unset ($ch);
        unset ($check);
        unset ($check_decision);
        unset ($check_objective);
        unset ($check_press_release);
        unset ($check_region);
        unset ($check_summary);
        unset ($check_until);
        unset ($companies);
        unset ($companies_split);
        unset ($company);
        unset ($company_id);
        unset ($company_match_array);
        unset ($company_trim);
        unset ($company_value);
        unset ($country_id);
        unset ($date_decision);
        unset ($deadline);
        unset ($deadline_text);
        unset ($decision);
        unset ($decision_ID);
        unset ($decision_art);
        unset ($decision_article_array);
        unset ($decision_date);
        unset ($decision_date_match);
        unset ($decision_date_text);
        unset ($decision_document_link);
        unset ($decision_id);
        unset ($decision_journal_link);
        unset ($decision_mission);
        unset ($decision_my);
        unset ($decision_text);
        unset ($decision_text_link_language);
        unset ($decisions);
        unset ($decisions_2);
        unset ($decisions_art);
        unset ($duration_from_to);
        unset ($duration_until);
        unset ($end);
        unset ($end_match);
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
        unset ($legal_basis_secondary_my);
        unset ($m);
        unset ($match);
        unset ($matching_company);
        unset ($n);
        unset ($nace);
        unset ($nace_code);
        unset ($nace_text);
        unset ($notification);
        unset ($notification_date);
        unset ($notification_date_match);
        unset ($o);
        unset ($objective);
        unset ($p);
        unset ($press_release);
        unset ($press_releases);
        unset ($primary_objective);
        unset ($publication);
        unset ($publications);
        unset ($publication_ID);
        unset ($publication_date);
        unset ($publication_date_match);
        unset ($publication_link);
        unset ($publication_link_text);
        unset ($publication_split);
        unset ($q);
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
        unset ($query_14);
        unset ($query_15);
        unset ($query_16);
        unset ($query_17);
        unset ($r);
        unset ($regions);
        unset ($regions_match);
        unset ($regions_cases);
        unset ($regions_cases_alternative);
        unset ($regulation);
        unset ($related);
        unset ($related_cases);
        unset ($related_links);
        unset ($related_text);
        unset ($responsible);
        unset ($s);
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
        unset ($u);
        unset ($website);
        unset ($x);
        unset ($y);
        unset ($z);
}
?>
