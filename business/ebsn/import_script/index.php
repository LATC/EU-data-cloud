<?php
$username="root";
$password="";
$database="business";
$location_csv = "C:\business\\relation.csv";
$handle = fopen($location_csv, "r");
while ($columns = fgetcsv($handle,1000,";"))
    {
        //getting data from product
        $file_product = "C:\business\\product\\".$columns[0].".html";
        $handle_product = file_get_contents($file_product);
        $handle_product = utf8_decode($handle_product);
        preg_match("/<tr style=\"background-color:\srgb\(206, 221, 243\)\;\" valign=\"top\">(.*)<\/tr>/isU", $handle_product, $match_1);
        preg_match("/<td><b>([^<]*)/s", $match_1[1],$title);
        preg_match_all("/<img alt=\"([^\"]*)\"\ssrc=\"images\/sme-top\.gif\"/s", $match_1[1], $kmu);
        preg_match("/<td>(\w[^<]*)/s",$match_1[1],$categories);
        $category_array = explode(", ", $categories[1]);
        preg_match_all("/<img alt=\"([^\"]*)\"\ssrc=\"images\/star\.gif\"/s", $match_1[1], $evaluation);
        preg_match("/<img alt=\"([^\"]*)\"\ssrc=\"images\/price1\.gif\"/s", $match_1[1], $price);
        preg_match("/<tr style=\"background-color: rgb\(238, 238, 238\);\">\s*<[^>]*>([^<]*)/s", $handle_product, $description);
        preg_match("/Information Provider:[^>]*>\s*[^>]*>([^<]*)/s", $handle_product, $information_provider);
        preg_match("/Product Version:[^>]*>\s*[^>]*>([^<]*)/s", $handle_product, $product_version);
        preg_match("/Keywords:[^>]*>\s*[^>]*>([^<]*)/s", $handle_product, $keywords);
        $keywords_array = explode(", ", $keywords[1]);
        preg_match("/Web Link:[^>]*>\s*[^>]*>[^<]*<a href=\"([^\"]*)/s", $handle_product, $weblink);
        preg_match("/Licensing Model:[^>]*>\s*[^>]*>([^<]*)/s", $handle_product, $license);
        $license_array = explode(", ", $license[1]);
        preg_match("/Target Industry Sectors:[^>]*>\s*[^>]*>([^<]*)/s", $handle_product, $target_sectors);
        $target_sectors_array = explode(", ", $target_sectors[1]);
        preg_match("/User Interface:[^>]*>\s*[^>]*>([^<]*)/s", $handle_product, $interface);
        $interface_array = explode(", ",$interface[1]);
        preg_match("/Reference Customer General:[^>]*>\s*[^>]*>([^<]*)/s",$handle_product, $reference_customers);
        preg_match("/Price Indication:[^>]*>\s*[^>]*>([^<]*)/s", $handle_product, $price_indication);
        preg_match_all("/Product Related Services:[^>]*>\s*[^>]*>([^<]*)/s", $handle_product, $product_related_services);
        preg_match("/Reference Customers SMEs:[^>]*>\s*[^>]*>([^<]*)/s", $handle_product, $reference_customers_smes);
        preg_match("/Standards Supported:[^>]*>\s*[^>]*>([^<]*)/s", $handle_product, $supported_standards);
        preg_match("/Compatibility Operation Systems:[^>]*>\s*[^>]*>([^<]*)/s", $handle_product, $supported_os);
        $supported_os_array = explode(", ",$supported_os[1]);
        preg_match("/Compatibility Database Systems:[^>]*>\s*[^>]*>([^<]*)/s", $handle_product, $supported_db);
        $supported_db_array = explode(", ",$supported_db[1]);
        preg_match("/Benefits:[^>]*>\s*[^>]*>([^<]*)/s",$handle_product, $benefit);
        $benefit_array = explode(", ", $benefit[1]);
        preg_match("/Review:[^>]*>\s*[^>]*>\s*[^>]*>([^\<]*)/s", $handle_product, $evaluation_text);
        $evaluation_array = explode("\n",$evaluation_text[1]);
        $product_original_url = "http://ec.europa.eu/enterprise/e-bsn/ebusiness-solutions-guide/showProductDetails.do?productId=".$columns[0];
        
        //getting data from producer
        
        $file_producer = "C:\business\\producer\\".$columns[1].".html";
        $handle_producer = file_get_contents($file_producer);
        preg_match("/<tr style=\"background-color: rgb\(206, 221, 243\)\;\" valign=\"top\">\s*<td>(.*)<\/tr>/isU",$handle_producer, $match_2);
        preg_match("/<b>([^<]*)/s", $match_2[1],$company_name);
        preg_match_all("/<td>([^<]*)/s", $match_2[1], $several_things);
        $company_category_array = explode(", ", $several_things[1][0]);
        preg_match("/Company Description:[^>]*>\s*[^>]*>([^<]*)/s", $handle_producer, $company_description);
        preg_match("/Offering Description:[^>]*>\s*[^>]*>([^<]*)/s", $handle_producer, $offer_description);
        preg_match("/Address Data:[^>]*>\s*[^>]*>([^<]*)/s", $handle_producer, $company_address);
        preg_match("/Contact Sales:[^>]*>\s*[^>]*>([^<]*)/s", $handle_producer, $company_contact);
        preg_match("/Web Link:[^>]*>\s*[^>]*>[^<]*<a href=\"([^\"]*)/s", $handle_producer, $company_website);
        preg_match("/Keywords:<[^>]*>\s*[^>]*>([^<]*)/s", $handle_producer, $company_keywords);
        $company_keywords_array = explode(", ", $company_keywords[1]);
        preg_match("/Employees Worldwide:<[^>]*>\s*[^>]*>([^<]*)/s", $handle_producer, $employees_international);
        preg_match("/Employees Country:<[^>]*>\s*[^>]*>([^<]*)/s", $handle_producer, $employees_national);
        preg_match("/Countries Active:<[^>]*>\s*[^>]*>([^<]*)/s", $handle_producer, $active_countries);
        $active_countries_array = explode(", ", $active_countries[1]);
        preg_match("/Founding Year:/s",$handle_producer ,$founding_year);
        preg_match("/Partnership Products:<[^>]*>\s*[^>]*>([^<]*)/s", $handle_producer, $partnership_products);
        $partnership_products_array = explode("\n", $partnership_products[1]);
        preg_match("/Partnership Services:<[^>]*>\s*[^>]*>([^<]*)/s", $handle_producer, $partnership_services);
        $partnership_services_array = explode("\n", $partnership_services[1]);
        preg_match("/Cetifications:<[^>]*>\s*[^>]*>([^<]*)/s", $handle_producer, $certificates);
        $certification_array = explode("\n", $certificates[1]);
        preg_match("/Reference Customer General:<[^>]*>\s*[^>]*>([^<]*)/s", $handle_producer, $company_reference_customers_general);
        preg_match("/Reference Customer SMEs:<[^>]*>\s*[^>]*>([^<]*)/s", $handle_producer, $company_reference_customers_smes);
        preg_match("/Addressed Customer Size \(Products\):<[^>]*>\s*[^>]*>([^<]*)/s", $handle_producer, $company_customer_size_products);
        $company_customer_size_products_array = explode(", ", $company_customer_size_products[1]);
        preg_match("/Addressed Customer Size \(Services\):<[^>]*>\s*[^>]*>([^<]*)/s", $handle_producer, $company_customer_size_services);
        $company_customer_size_services_array = explode(", ", $company_customer_size_services[1]);
        preg_match("/Industry Branches Customers:<[^>]*>\s*[^>]*>([^<]*)/s", $handle_producer, $industry_branches_customers);
        $industry_branches_customers_array = explode(", ", $industry_branches_customers[1]);
        preg_match("/Product Categories:<[^>]*>\s*[^>]*>([^<]*)/s", $handle_producer, $company_product_categories);
        $company_product_categories_array = explode(", ", $company_product_categories[1]);
        preg_match("/Service Categories:<[^>]*>\s*[^>]*>([^<]*)/s", $handle_producer, $company_service_categories);
        $company_service_categories_array = explode(", ", $company_service_categories[1]);
        // connect to database
        mysql_connect('localhost',$username,$password) or die("Unable to connect to database");
        mysql_select_db($database) or die("Unable to select database");
        
        // Set Values
        
        $values_1 = "INSERT INTO employee VALUES ('1','1-9'),('2','10-49'),('3','50-250'),('4','>250')";
        mysql_query($values_1);
        
        // Clean Data
        
        $description[1] = mysql_real_escape_string($description[1]);
        $company_description[1] = mysql_real_escape_string($company_description[1]);
        $offer_description[1] = mysql_real_escape_string($offer_description[1]);
        $company_address[1] = mysql_real_escape_string($company_address[1]);
        $i = 0;
        foreach ($keywords_array as $kw)
        {
            $keywords_array[$i] = mysql_real_escape_string($keywords_array[$i]);
            $i++;
        }
        $product_related_services[1][1] = mysql_real_escape_string($product_related_services[1][1]);
        $k = 0;
        foreach ($partnership_products_array as $pa)
        {
            $partnership_products_array[$k] = mysql_real_escape_string($partnership_products_array[$k]);
            $k++;
        }
        $l = 0;
        foreach ($partnership_services_array as $ps)
        {
            $partnership_services_array[$l] = mysql_real_escape_string($partnership_services_array[$l]);
            $l++;
        }
        
        
        // let's roll
        
        $query_1 = "INSERT INTO product VALUES ('".$columns[0]."','".$title[1]."','".$price[1]."','".$description[1]."','".$information_provider[1]."','".$product_version[1]."','".$weblink[1]."','".$reference_customers[1]."','".$price_indication[1]."','".$reference_customers_smes[1]."','".$supported_standards[1]."','".$product_original_url."','".$evaluation[1][0]."','".$product_related_services[1][1]."')";
        mysql_query($query_1);
        echo mysql_errno() . ": " . mysql_error()."in product:".$columns[0]."\n";
        $j = 1;
        foreach ($kmu[1] as $kmu_fit) 
        {
            $query_2 = "INSERT INTO product_kmu_fit VALUES
                ('".$columns[0]."','".$j."','".$kmu_fit."')";
            mysql_query($query_2);
            echo mysql_errno() . ": " . mysql_error()."in kmu_fit:".$kmu_fit."\n";
            $j++;
        }
        foreach ($license_array as $license_value)
        {
        $query_3 = "SELECT ID FROM license_model WHERE name = '".$license_value."'";  
            $select_license_model = mysql_query($query_3);
            $select_license_model_a = mysql_fetch_array($select_license_model);
                if ($license_value != "" AND $select_license_model_a==FALSE)
                    {
                        $query_4 = "INSERT INTO license_model(name) VALUES ('".$license_value."')";
                        mysql_query($query_4);
                        $license_model_id = mysql_insert_id();
                        echo mysql_errno() . ": " . mysql_error()."in product:".$columns[0]."\n";
                     }
                else
                    {
                         $license_model_id = $select_license_model_a['ID'];
                    }
                    if ($license_value != ""){
            $query_5 = "INSERT INTO product_license_model VALUES ('".$columns[0]."','".$license_model_id."')";
            mysql_query($query_5);
                    }
        }
        foreach ($benefit_array as $benefit_value)
        {
        $query_6 = "SELECT ID FROM benefit WHERE name = '".$benefit_value."'";  
            $select_benefit = mysql_query($query_6);
            $select_benefit_a = mysql_fetch_array($select_benefit);
                if ($benefit_value != "" AND $select_benefit_a==FALSE)
                    {
                        $query_7 = "INSERT INTO benefit(name) VALUES ('".$benefit_value."')";
                        mysql_query($query_7);
                        $benefit_id = mysql_insert_id();
                        echo mysql_errno() . ": " . mysql_error()."in product:".$columns[0]."\n";
                     }
                else
                    {
                         $benefit_id = $select_benefit_a['ID'];
                    }
                    if ($benefit_value != "")
                    {
            $query_8 = "INSERT INTO product_benefit VALUES ('".$columns[0]."','".$benefit_id."')";
            mysql_query($query_8);
                    }
        }
        foreach ($supported_os_array as $supported_os_value)
        {
        $query_9 = "SELECT ID FROM operation_system WHERE name = '".$supported_os_value."'";  
            $select_operation_system = mysql_query($query_9);
            $select_operation_system_a = mysql_fetch_array($select_operation_system);
                if ($supported_os_value != "" AND $select_operation_system_a==FALSE)
                    {
                        $query_10 = "INSERT INTO operation_system(name) VALUES ('".$supported_os_value."')";
                        mysql_query($query_10);
                        $supported_os_id = mysql_insert_id();
                        echo mysql_errno() . ": " . mysql_error()."in product:".$columns[0]."\n";
                     }
                else
                    {
                         $supported_os_id = $select_operation_system_a['ID'];
                    }
                    if ($supported_os_value != "")
                    {
            $query_11 = "INSERT INTO product_operation_system VALUES ('".$columns[0]."','".$supported_os_id."')";
            mysql_query($query_11);
                    }
        }
    foreach ($supported_db_array as $supported_db_value)
        {
        $query_12 = "SELECT ID FROM database_system WHERE name = '".$supported_db_value."'";  
            $select_database_system = mysql_query($query_12);
            $select_database_system_a = mysql_fetch_array($select_database_system);
                if ($supported_db_value != "" AND $select_database_system_a==FALSE)
                    {
                        $query_13 = "INSERT INTO database_system(name) VALUES ('".$supported_db_value."')";
                        mysql_query($query_13);
                        $supported_db_id = mysql_insert_id();
                        echo mysql_errno() . ": " . mysql_error()."in product:".$columns[0]."\n";
                     }
                else
                    {
                         $supported_db_id = $select_database_system_a['ID'];
                    }
                    if ($supported_db_value != "")
                    {
            $query_14 = "INSERT INTO product_database_system VALUES ('".$columns[0]."','".$supported_db_id."')";
            mysql_query($query_14);
        }
        }
     foreach ($interface_array as $interface_value)
        {
        $query_15 = "SELECT ID FROM user_interface WHERE name = '".$interface_value."'";  
            $select_interface = mysql_query($query_15);
            $select_interface_a = mysql_fetch_array($select_interface);
                if ($interface_value != "" AND $select_interface_a==FALSE)
                    {
                        $query_16 = "INSERT INTO user_interface(name) VALUES ('".$interface_value."')";
                        mysql_query($query_16);
                        $interface_id = mysql_insert_id();
                        echo mysql_errno() . ": " . mysql_error()."in product:".$columns[0]."\n";
                     }
                else
                    {
                         $interface_id = $select_interface_a['ID'];
                    }
                    if ($interface_value != "")
                    {
            $query_17 = "INSERT INTO product_user_interface VALUES ('".$columns[0]."','".$interface_id."')";
            mysql_query($query_17);
                    }
        }
        $query_18 = "SELECT ID FROM country WHERE name = '".$columns[2]."'";  
            $select_active_countries = mysql_query($query_18);
            $select_active_countries_a = mysql_fetch_array($select_active_countries);
                if ($columns[2] != "" AND $select_active_countries_a==FALSE)
                    {
                        $query_19 = "INSERT INTO country(name) VALUES ('".$columns[2]."')";
                        mysql_query($query_19);
                        $country_id = mysql_insert_id();
                        echo mysql_errno() . ": " . mysql_error()."in product:".$columns[0]."\n";
                     }
                else
                    {
                         $country_id = $select_active_countries_a['ID'];
                    }
                    if ($columns[2] != "")
                    {
            $query_20 = "INSERT INTO product_country VALUES ('".$columns[0]."','".$country_id."')";
            mysql_query($query_20);
                    }
    foreach ($category_array as $category_value)
        {
        $query_21 = "SELECT ID FROM category WHERE name = '".$category_value."'";  
            $select_category = mysql_query($query_21);
            $select_category_a = mysql_fetch_array($select_category);
                if ($category_value != "" AND $select_category_a==FALSE)
                    {
                        $query_22 = "INSERT INTO category(name) VALUES ('".$category_value."')";
                        mysql_query($query_22);
                        $category_id = mysql_insert_id();
                        echo mysql_errno() . ": " . mysql_error()."in product:".$columns[0]."\n";
                     }
                else
                    {
                         $category_id = $select_category_a['ID'];
                    }
                    if ($category_value != "")
                    {
            $query_23 = "INSERT INTO product_category VALUES ('".$columns[0]."','".$category_id."')";
            mysql_query($query_23);
                    }
        }       
    foreach ($target_sectors_array as $target_sectors_value)
        {
        $query_24 = "SELECT ID FROM industry_branch WHERE name = '".$target_sectors_value."'";  
            $select_target_sector = mysql_query($query_24);
            $select_target_sector_a = mysql_fetch_array($select_target_sector);
                if ($target_sectors_value != "" AND $select_target_sector_a==FALSE)
                    {
                        $query_25 = "INSERT INTO industry_branch (name) VALUES ('".$target_sectors_value."')";
                        mysql_query($query_25);
                        $target_sectors_id = mysql_insert_id();
                        echo mysql_errno() . ": " . mysql_error()."in product:".$columns[0]."\n";
                     }
                else
                    {
                         $target_sectors_id = $select_target_sector_a['ID'];
                    }
                    if ($target_sectors_value != "")
                    {
            $query_26 = "INSERT INTO product_industry_branch VALUES ('".$columns[0]."','".$target_sectors_id."')";
            mysql_query($query_26);
                    }
        }
    foreach ($keywords_array as $keywords_value)
        {
        $query_27 = "SELECT ID FROM keyword WHERE name = '".$keywords_value."'";  
            $select_keywords = mysql_query($query_27);
            $select_keywords_a = mysql_fetch_array($select_keywords);
                if ($keywords_value != "" AND $select_keywords_a==FALSE)
                    {
                        $query_28 = "INSERT INTO keyword (name) VALUES ('".$keywords_value."')";
                        mysql_query($query_28);
                        $keywords_id = mysql_insert_id();
                        echo mysql_errno() . ": " . mysql_error()."in product:".$columns[0]."\n";
                     }
                else
                    {
                         $keywords_id = $select_target_sector_a['ID'];
                    }
                    if ($keywords_value != "")
                    {
            $query_29 = "INSERT INTO product_keyword VALUES ('".$columns[0]."','".$keywords_id."')";
            mysql_query($query_29);
                    }
        }           
      
// Queries for producers
        $query_30_0 = "SELECT ID FROM producer WHERE ID = '".$columns[1]."'";
        $select_producer = mysql_query($query_30_0);
            $select_producer_a = mysql_fetch_array($select_producer);
                if ($select_producer_a==FALSE)
                {
        $query_30 = "INSERT INTO producer VALUES ('".$columns[1]."','".$company_name[1]."','".$several_things[1][1]."','".$several_things[1][2]."','".$company_description[1]."','".$offer_description[1]."','".$company_address[1]."','".$company_contact[1]."','".$company_website[1]."','".$employees_international[1]."','".$employees_national[1]."','".$founding_year[1]."','".$company_reference_customers_smes[1]."','".$company_reference_customers_general[1]."')";
        mysql_query($query_30);
        echo mysql_errno() . ": " . mysql_error()."in producer:".$columns[1]."\n";
                }
        foreach ($active_countries_array as $active_countries_value)
        {
        $query_31 = "SELECT ID FROM country WHERE name = '".$active_countries_value."'";  
            $select_active_countries = mysql_query($query_31);
            $select_active_countries_a = mysql_fetch_array($select_active_countries);
                if ($active_countries_value != "" AND $select_active_countries_a==FALSE)
                    {
                        $query_32 = "INSERT INTO country(name) VALUES ('".$active_countries_value."')";
                        mysql_query($query_32);
                        $country_id = mysql_insert_id();
                        echo mysql_errno() . ": " . mysql_error()."in producer:".$columns[1]."\n";
                     }
                else
                    {
                         $country_id = $select_active_countries_a['ID'];
                    }
                    if ($active_countries_value != "")
                    {
            $query_33 = "INSERT INTO producer_country VALUES ('".$columns[1]."','".$country_id."')";
            mysql_query($query_33);
                    }
        }
        foreach ($company_service_categories_array as $company_service_categories_value)
        {
        $query_34 = "SELECT ID FROM service WHERE name = '".$company_service_categories_value."'";  
            $select_company_service_categories = mysql_query($query_34);
            $select_company_service_categories_a = mysql_fetch_array($select_company_service_categories);
                if ($company_service_categories_value != "" AND $select_company_service_categories_a==FALSE)
                    {
                        $query_35 = "INSERT INTO service(name) VALUES ('".$company_service_categories_value."')";
                        mysql_query($query_35);
                        $service_id = mysql_insert_id();
                        echo mysql_errno() . ": " . mysql_error()."in producer:".$columns[1]."\n";
                     }
                else
                    {
                         $service_id = $select_company_service_categories_a['ID'];
                    }
                    if ($company_service_categories_value != "")
                    {
            $query_36 = "INSERT INTO producer_service VALUES ('".$columns[1]."','".$service_id."')";
            mysql_query($query_36);
                    }
        }
        foreach ($company_product_categories_array as $company_product_categories_value)
        {
        $query_37 = "SELECT ID FROM category WHERE name = '".$company_product_categories_value."'";  
            $select_company_product_categories = mysql_query($query_37);
            $select_company_product_categories_a = mysql_fetch_array($select_company_product_categories);
                if ($company_product_categories_value != "" AND $select_company_product_categories_a==FALSE)
                    {
                        $query_38 = "INSERT INTO category(name) VALUES ('".$company_product_categories_value."')";
                        mysql_query($query_38);
                        $company_product_categories_id = mysql_insert_id();
                        echo mysql_errno() . ": " . mysql_error()."in producer:".$columns[1]."\n";
                     }
                else
                    {
                         $company_product_categories_id = $select_company_product_categories_a['ID'];
                    }
                    if ($company_product_categories_value != "")
                    {
            $query_39 = "INSERT INTO producer_product_category VALUES ('".$columns[1]."','".$company_product_categories_id."')";
            mysql_query($query_39);
                    }
        }
        foreach ($company_category_array as $company_category_value)
        {
        $query_40 = "SELECT ID FROM category WHERE name = '".$company_category_value."'";  
            $select_company_category = mysql_query($query_40);
            $select_company_category_a = mysql_fetch_array($select_company_category);
                if ($company_category_value != "" AND $select_company_category_a==FALSE)
                    {
                        $query_41 = "INSERT INTO category(name) VALUES ('".$company_category_value."')";
                        mysql_query($query_41);
                        $company_category_id = mysql_insert_id();
                        echo mysql_errno() . ": " . mysql_error()."in producer:".$columns[1]."\n";
                     }
                else
                    {
                         $company_category_id = $select_company_category_a['ID'];
                    }
                     if ($company_category_value != "")
                     {
            $query_42 = "INSERT INTO producer_category VALUES ('".$columns[1]."','".$company_category_id."')";
            mysql_query($query_42);
                     }
        }
        foreach ($company_keywords_array as $company_keywords_value)
        {
        $query_43 = "SELECT ID FROM keyword WHERE name = '".$company_keywords_value."'";  
            $select_company_keywords = mysql_query($query_43);
            $select_company_keywords_a = mysql_fetch_array($select_company_keywords);
                if ($company_keywords_value != "" AND $select_company_keywords_a==FALSE)
                    {
                        $query_44 = "INSERT INTO keyword (name) VALUES ('".$company_keywords_value."')";
                        mysql_query($query_44);
                        $company_keywords_id = mysql_insert_id();
                        echo mysql_errno() . ": " . mysql_error()."in producer:".$columns[1]."\n";
                     }
                else
                    {
                         $company_keywords_id = $select_company_keywords_a['ID'];
                    }
                    if ($company_keywords_value != "")
                    {
            $query_45 = "INSERT INTO producer_keyword VALUES ('".$columns[1]."','".$company_keywords_id."')";
            mysql_query($query_45);
                    }
        }        
        foreach ($industry_branches_customers_array as $industry_branches_customers_value)
        {
        $query_46 = "SELECT ID FROM industry_branch WHERE name = '".$industry_branches_customers_value."'";  
            $select_industry_branches = mysql_query($query_46);
            $select_industry_branches_a = mysql_fetch_array($select_industry_branches);
                if ($industry_branches_customers_value != "" AND $select_industry_branches_a==FALSE)
                    {
                        $query_47 = "INSERT INTO industry_branch (name) VALUES ('".$industry_branches_customers_value."')";
                        mysql_query($query_47);
                        $industry_branches_customers_id = mysql_insert_id();
                        echo mysql_errno() . ": " . mysql_error()."in producer:".$columns[1]."\n";
                     }
                else
                    {
                         $industry_branches_customers_id = $select_industry_branches_a['ID'];
                    }
                    if ($industry_branches_customers_value != "")
                    {
            $query_48 = "INSERT INTO producer_industry_branch VALUES ('".$columns[1]."','".$industry_branches_customers_id."')";
            mysql_query($query_48);
                    }
        }
        foreach ($company_customer_size_services_array as $company_customer_size_services_value)
        {
        $query_49 = "SELECT ID FROM employee WHERE name = '".$company_customer_size_services_value."'";  
            $select_size_value = mysql_query($query_49);
            $select_size_value_a = mysql_fetch_array($select_size_value);
                if ($company_customer_size_services_value != "" AND $select_size_value_a==FALSE)
                    {
                        $query_50 = "INSERT INTO employee (name) VALUES ('".$company_customer_size_services_value."')";
                        mysql_query($query_50);
                        $size_value_id = mysql_insert_id();
                        echo mysql_errno() . ": " . mysql_error()."in producer:".$columns[1]."\n";
                     }
                else
                    {
                         $size_value_id = $select_size_value_a['ID'];
                    }
                    if ($company_customer_size_services_value != "")
                    {
            $query_51 = "INSERT INTO producer_customer_size_service VALUES ('".$columns[1]."','".$size_value_id."')";
            mysql_query($query_51);
                    }
        }
        foreach ($company_customer_size_products_array as $company_customer_size_products_value)
        {
        $query_52 = "SELECT ID FROM employee WHERE name = '".$company_customer_size_products_value."'";  
            $select_size_product_value = mysql_query($query_52);
            $select_size_product_value_a = mysql_fetch_array($select_size_product_value);
                if ($company_customer_size_products_value != "" AND $select_size_product_value_a==FALSE)
                    {
                        $query_53 = "INSERT INTO employee (name) VALUES ('".$company_customer_size_products_value."')";
                        mysql_query($query_53);
                        $size_product_value_id = mysql_insert_id();
                        echo mysql_errno() . ": " . mysql_error()."in producer:".$columns[1]."\n";
                     }
                else
                    {
                         $size_product_value_id = $select_size_product_value_a['ID'];
                    }
                     if ($company_customer_size_products_value != "")
                     {
            $query_54 = "INSERT INTO producer_customer_size_product VALUES ('".$columns[1]."','".$size_product_value_id."')";
            mysql_query($query_54);
                     }
        }
        foreach ($partnership_products_array as $partnership_products_value)
        {
        $query_55 = "SELECT ID FROM partnership_product WHERE name = '".$partnership_products_value."'";  
            $select_partnership_product = mysql_query($query_55);
            $select_partnership_product_a = mysql_fetch_array($select_partnership_product);
                if ($partnership_products_value != "" AND $select_partnership_product_a==FALSE)
                    {
                        $query_56 = "INSERT INTO partnership_product (name) VALUES ('".$partnership_products_value."')";
                        mysql_query($query_56);
                        $partnership_products_id = mysql_insert_id();
                        echo mysql_errno() . ": " . mysql_error()."in producer:".$columns[1]."\n";
                     }
                else
                    {
                         $partnership_products_id = $select_partnership_product_a['ID'];
                    }
                    if ($partnership_products_value != "")
                    {
            $query_57 = "INSERT INTO producer_partnership_product VALUES ('".$columns[1]."','".$partnership_products_id."')";
            mysql_query($query_57);
                    }
        }
        foreach ($partnership_services_array as $partnership_services_value)
        {
        $query_58 = "SELECT ID FROM partnership_service WHERE name = '".$partnership_services_value."'";  
            $select_partnership_service = mysql_query($query_58);
            $select_partnership_service_a = mysql_fetch_array($select_partnership_service);
                if ($partnership_services_value != "" AND $select_partnership_service_a==FALSE)
                    {
                        $query_59 = "INSERT INTO partnership_service (name) VALUES ('".$partnership_services_value."')";
                        mysql_query($query_59);
                        $partnership_services_id = mysql_insert_id();
                        echo mysql_errno() . ": " . mysql_error()."in producer:".$columns[1]."\n";
                     }
                else
                    {
                         $partnership_services_id = $select_partnership_service_a['ID'];
                    }
            if ($partnership_services_value != "")
            {
                    $query_60 = "INSERT INTO producer_partnership_service VALUES ('".$columns[1]."','".$partnership_services_id."')";
            mysql_query($query_60);
            }
        }
        foreach ($certification_array as $certification_value)
        {
        $query_61 = "SELECT ID FROM certification WHERE name = '".$certification_value."'";  
            $select_certification = mysql_query($query_61);
            $select_certification_a = mysql_fetch_array($select_certification);
                if ($certification_value != "" AND $select_certification_a==FALSE)
                    {
                        $query_62 = "INSERT INTO certification (name) VALUES ('".$certification_value."')";
                        mysql_query($query_62);
                        $certification_id = mysql_insert_id();
                        echo mysql_errno() . ": " . mysql_error()."in producer:".$columns[1]."\n";
                     }
                else
                    {
                         $certification_id = $select_certification_a['ID'];
                    }
                    if ($certification_value != "")
                    {
            $query_63 = "INSERT INTO producer_certification VALUES ('".$columns[1]."','".$certification_id."')";
            mysql_query($query_63);
                    }
        }
        $query_64 = "INSERT INTO product_producer VALUES ('".$columns[0]."','".$columns[1]."')";
        mysql_query($query_64);
        echo mysql_errno() . ": " . mysql_error()."in product_producer:".$columns[0]." ".$columns[1]."\n";
        unset($active_countries);
        unset($active_countries_array);
        unset($active_countries_value);
        unset($benefit);
        unset($benefit_array);
        unset($benefit_id);
        unset($benefit_value);
        unset($categories);
        unset($category_array);
        unset($category_id);
        unset($category_value);
        unset($certificates);
        unset($certification_array);
        unset($certification_id);
        unset($certification_value);
        unset($columns);
        unset($company_address);
        unset($company_category_array);
        unset($company_category_id);
        unset($company_category_value);
        unset($company_contact);
        unset($company_customer_size_products);
        unset($company_customer_size_products_array);
        unset($company_customer_size_products_value);
        unset($company_customer_size_services);
        unset($company_customer_size_services_array);
        unset($company_customer_size_services_value);
        unset($company_description);
        unset($company_keywords);
        unset($company_keywords_array);
        unset($company_keywords_id);
        unset($company_keywords_value);
        unset($company_name);
        unset($company_product_categories);
        unset($company_product_categories_array);
        unset($company_product_categories_id);
        unset($company_product_categories_value);
        
    }
        ?>