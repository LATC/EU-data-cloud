<?php
######################################
# PHP scraper
# Scrapes data from EURES jobs
######################################

ini_set('max_execution_time', 0);

ini_set('memory_limit', '-1');

include("config.inc");

include("functions.php");

require 'scraperwiki/scraperwiki.php';

require 'scraperwiki/simple_html_dom.php';

include 'geocoderParser/GGeocoderParserLib.v1.php';

function insert_address($address)
{	
	global $country_id;

	$address = db_prep($address);  
	$lowercase_address = strtolower($address);
	
	$sql = mysql_query("SELECT id FROM geo WHERE LOWER(address) = $lowercase_address") or die (mysql_error());
	//$sql = mysql_query("SELECT id FROM geo WHERE LOWER(address) = $lowercase_address AND country_id= '$country_id'") or die (mysql_error());

	if ((mysql_num_rows($sql) == 0) && ($address <> NULL))
	{
		mysql_query("INSERT INTO geo SET address = $address, country_id= '$country_id'") or die (mysql_error());	
	}
	else
	{
		echo "Address already extracted." . PHP_EOL;
	}            
}

function create_unique_id()
{
	global $url_id;
	global $source;

	$sql = mysql_query("SELECT country_code FROM source WHERE name = ".db_prep($source));
	$row = mysql_fetch_array($sql);		
	$country_code = $row[0];
	
	$unique_id = $country_code."_".$url_id;

	return $unique_id;
}

function update_address()
{
	$sql = mysql_query("SELECT address FROM geo WHERE formatted_address IS NULL LIMIT 0, 1000") or die (mysql_error()); 
	//improve WHERE

	while($row = mysql_fetch_array($sql))
	{
		$address = $row[0];	
		$address_array = get_geocoder_address($address);
		$query = "UPDATE geo SET
			formatted_address =".db_prep($address_array['formatted_address']).",
			country_id =".db_prep($address_array['country_id']).",
			administrative_area =".db_prep($address_array['administrative_area_level_1']).",
			subadministrative_area =".db_prep($address_array['administrative_area_level_2']).",
			locality =".db_prep($address_array['locality']).",
			route =".db_prep($address_array['route']).",
			street_number =".db_prep($address_array['street_number']).",
			postal_code =".db_prep($address_array['postal_code']).",
			latitude =".db_prep($address_array['latitude']).",
			longitude =".db_prep($address_array['longitude']).",
			lat_southwest =".db_prep($address_array['viewport_lat_southwest']).",
			lng_southwest =".db_prep($address_array['viewport_lng_southwest']).",
			lat_northeast =".db_prep($address_array['viewport_lat_northeast']).",
			lng_northeast =".db_prep($address_array['viewport_lng_northeast'])."
		WHERE address =".db_prep($address);
		mysql_query($query) or die (mysql_error());		
	}
}

$country_dir = array('GR','HU','IR','IS','IT','LI','LT','LU','LV','MT','NL','NO','UK');

//$country_dir = array('CH');

for ($i=0; $i < sizeof($country_dir); $i++)
{
	$dir = $JOBS_DIR.$country_dir[$i];

	if ($handle = opendir($dir)) {

		while (false !== ($file = readdir($handle))) 
		{
			if (!is_dir($file))
			{	
				$title=NULL;$required_languages=NULL;$starting_date=NULL;$ending_date=NULL;$country=NULL;$region=NULL;$minimum_salary=NULL;$maximum_salary=NULL;$salary_currency=NULL;$salary_tax=NULL;$salary_period=NULL;$hours_per_week=NULL;$contract=NULL;$contract_type=NULL;$contract_hours=NULL;$accommodation_provided=NULL;$relocation_covered=NULL;$meals_included=NULL;$travel_expenses=NULL;$education_skills_required=NULL;$professional_qualifications_required=NULL;$experience_required=NULL;$driving_license_required=NULL;$minimum_age=NULL;$maximum_age=NULL;$name=NULL;$information=NULL;$address=NULL;$phone=NULL;$email=NULL;$fax=NULL;$how_to_apply=NULL;$contact=NULL;$last_date_for_application=NULL;$date_published=NULL;$national_reference=NULL;$last_modification_date=NULL;$nace_code=NULL;$isco_code=NULL;$isco_unit_code=NULL;$isco_minor_code=NULL;$isco_submajor_code=NULL;$isco_major_code=NULL;$number_of_posts=NULL;$other_value=NULL;$eures_reference=NULL;$contract_type_id=NULL;$contract_hours_id=NULL;$education_skills_id=NULL;$experience_id=NULL;$driving_license_id=NULL;$contact_id=NULL;$employer_id=NULL;$job_id=NULL;	$address_array=NULL;$how_to_apply_id=NULL;$title_id=NULL;$homepage=NULL;$dom=NULL;$text=NULL;$data=NULL;$sql=NULL;$query=NULL;$row=NULL;$salary_currency_id=NULL;$salary_period_id=NULL;$salary_tax_id=NULL;$salary=NULL;$minimum_salary_id=NULL;$maximum_salary_id=NULL;$url=NULL;$country_id=NULL;$description=NULL;$source=NULL;$source_id=NULL;$url_id=NULL;$url_search=NULL;$url_scraper_date=NULL;$url_scraper_hour=NULL;$url_job_unique=NULL;$hours_per_week_id=NULL;$region_id=NULL;

				$html = scraperwiki::scrape($dir."/".$file);

				$dom = new simple_html_dom();
				$dom->load($html);

				foreach($dom->find('div[@id=url_job]') as $data)
				{  
					$url = $data->plaintext; 
				}
				foreach($dom->find('div[@id=country_id]') as $data)
				{  
					$country_id = $data->plaintext; 
				}
				foreach($dom->find('div[@id=description]') as $data)
				{  
					$description = $data->plaintext;
				}
				foreach($dom->find('div[@id=source]') as $data)
				{  
					$source = $data->plaintext;
					$source_id = insert_name('source',$source);
				}
				foreach($dom->find('div[@id=url_id]') as $data)
				{  
					$url_id = $data->plaintext;
				}
				foreach($dom->find('div[@id=url_search]') as $data)
				{  
					$url_search = $data->plaintext;
				}
				foreach($dom->find('div[@id=scraper_date]') as $data)
				{  
					$url_scraper_date = $data->plaintext;
				}
				foreach($dom->find('div[@id=scraper_hour]') as $data)
				{  
					$url_scraper_hour = $data->plaintext;
				}
				foreach($dom->find('div[@id=url_job_unique]') as $data)
				{  
					$url_job_unique = $data->plaintext;
				}

				foreach($dom->find('th') as $data)
				{  
					$text = trim($data->plaintext);     

					if ($text <> 'Description:')
						$value = trim($data->next_sibling()->plaintext);			

					//FIXME. HACK by Lucas, to get the data from Maximum salary, since the TH for this data in EURES pages is not well-written, causing the extractor not to work. <th colspan="1">Maximum salary:</td> 
					if (preg_match('/Maximum salary:/', $text)){
						$maximum_salary = str_replace("Maximum salary:","",$text);
						$maximum_salary = trim(str_replace("</td>","",$maximum_salary));
						$salary = format_currency($maximum_salary);
						$maximum_salary = $salary['amount'];
						$maximum_salary_id = insert_name('maximum_salary',$maximum_salary);
					}
					else
					{
						switch($text) {
							//Summary
							case 'Title:': 
								$title = $value;
								$title_id = insert_name('title',$title);
								break;
							case 'Description:':break;
							case 'Required languages:':
								$required_languages = $value;
								$required_languages = str_replace("(","",$required_languages);	
								$required_languages = str_replace(")","",$required_languages);		

								$explode_language1 = explode(",",$required_languages);

								if (sizeof($explode_language1) == 1)
									$explode_language1 = explode(";",$required_languages);	

								$i = 0;

								while ($i < sizeof($explode_language1))
								{
									$id_language = '';
									$id_language_level = '';

									$explode_language2 = explode("-",trim($explode_language1[$i]));
									$language = trim($explode_language2[0]); 

									$language_id = insert_name('language',$language);

									if (sizeof($explode_language1) > 1)
									{
										$language_level = trim($explode_language2[1]);

										$language_level_id = insert_name('language_level',$language_level);
									}
									$i++;
									mysql_query("INSERT INTO job_language SET job_id = '$url_job_unique', language_id ='$language_id', language_level_id ='$language_level_id'");
								}
								break;
							case 'Starting Date:':$starting_date = $value;break;
							case 'Ending date:':$ending_date = $value;break;
							//Geographical Information
							case 'Country:':$country = $value;break;    
							case 'Region:':
								$region = $value;
								if($region <> '' && $region <> '0' && $region <> '.')
									mysql_query("INSERT INTO region SET name ='$value', country_id = '$country_id'");
								$sql = mysql_query("SELECT id FROM region WHERE name = '$region' AND country_id = '$country_id'");				
								$row = mysql_fetch_array($sql);		
								$region_id = $row[0];
								break;
							//Salary / Contract
							case 'Minimum salary:':
								$minimum_salary = $value;
								$salary = format_currency($minimum_salary);
								$minimum_salary = $salary['amount'];
								$minimum_salary_id = insert_name('minimum_salary',$minimum_salary);	
								break;
							case 'Maximum salary:':
								$maximum_salary = $value;
								$salary = format_currency($maximum_salary);
								$maximum_salary = $salary['amount'];
								$maximum_salary_id = insert_name('maximum_salary',$maximum_salary);
								break;
							case 'Salary currency:':
								$salary_currency = $value;
								$salary_currency_id = insert_name('salary_currency',$salary_currency);
								break;
							case 'Salary tax:':
								$salary_tax = $value;
								$salary_tax_id = insert_name('salary_tax',$salary_tax);
								break;
							case 'Salary period:':
								$salary_period = $value;
								$salary_period_id = insert_name('salary_period',$salary_period);
								break;
							case 'Hours per week:':
								$hours_per_week = $value;
								format_hour($hours_per_week);
								$hours_per_week_id = insert_name('hours_per_week',$hours_per_week);
								break;
							case 'Contract type:':
								$contract = $value;
								$contract = str_replace(")","",$contract);

								if (strstr($contract, '('))
									$explode_contract = explode("(",$contract);
								elseif (strstr($contract, ' - '))
									$explode_contract = explode(" - ",$contract);
								else
									$explode_contract = explode("+",$contract);

								$contract_type = trim($explode_contract[0]);
	
								$contract_type_id = insert_name('contract_type',$contract_type);

								if (sizeof($explode_contract) > 1){
									$contract_hours = trim($explode_contract[1]);

									$contract_hours_id = insert_name('contract_hours',$contract_hours);	
								}
								break;
							//Extras
							case 'Accommodation provided:':$accommodation_provided = $value;break;
							case 'Relocation covered:':$relocation_covered = $value;break;
							case 'Meals included:':$meals_included = $value;break;
							case 'Travel expenses:':$travel_expenses = $value;break;
							//Requirements
							case 'Education skills required:':
								$education_skills_required = $value;
								$education_skills_id = insert_name('education_skills',$education_skills_required);
								break;
							case 'Professional qualifications required:': $professional_qualifications_required = $value;break;
							case 'Experience required:':
								$experience_required = $value;
								$experience_id = insert_name('experience',$experience_required);
								break;
							case 'Driving license required:':
								$driving_license_required = $value;
								$driving_license_id = insert_name('driving_license',$driving_license_required);
								break;
							case 'Minimum age:': $minimum_age = $value;break;
							case 'Maximum age:': $maximum_age = $value;break;
							//Employer
							case 'Name:':$name = $value;break;
							case 'Information:':$information = $value;break;
							case 'Address:':
								$address = $value;
								insert_address($address);
								break;                    
							case 'Phone:':$phone = $value;break;
							case 'Email:':$email = $value;break;
							case 'Fax:':$fax = $value;break;
							//Application
							case 'How to apply:':
								$how_to_apply = $value;
								$how_to_apply_id = insert_name('how_to_apply',$how_to_apply);
								break;
							case 'Contact:':$contact = $value;break;         
							case 'Last date for application:':$last_date_for_application = $value;break;
							//Other Information
							case 'Date published:':$date_published = $value;break;
							case 'National reference:':$national_reference = $value;break;
							case 'Eures reference:':$eures_reference = $value;break;
							case 'Last Modification Date:':$last_modification_date = $value;break;
							case 'Nace code:':$nace_code = $value;break;
							case 'ISCO code:':
								$isco_code = $value;
								switch(strlen ($isco_code)) {
									case 4: $isco_unit_code = $isco_code;$isco_minor_code = substr($isco_code, 0, 3);$isco_submajor_code= substr($isco_code, 0, 2);$isco_major_code = substr($isco_code, 0, 1);break;
									case 3: $isco_minor_code = $isco_code;$isco_submajor_code= substr($isco_code, 0, 2);$isco_major_code = substr($isco_code, 0, 1);break;
									case 2: $isco_submajor_code = $isco_code;$isco_major_code = substr($isco_code, 0, 1);break;
									case 1: $isco_major_code = $isco_code;break;
								}						
								break;
							case 'Number of posts:':$number_of_posts = $value;break;
							//FIXME. HACK by Lucas, to get the data from Contact, since the TH for this data in EURES pages is not well-written, causing the extractor not to work. e.g.<th colspan="1>Contact:</th>
							default:$contact = trim(str_replace("</td>","",$text));$contact = str_replace("'","\'",$contact);break; 
						}
					}
				}

				if ($name <> '' || ($address <> '' && $address <> ',  ,'))
				{	

					$query = "SELECT id FROM employer WHERE name = '$name' AND address = '$address' AND country_id = '$country_id'";
	
					$employer_id = select_id($query);

					if ($employer_id == '')
					{
						mysql_query("INSERT INTO employer SET 
							name = '$name',
							homepage = '$homepage',
							address ='$address',
							country_id ='$country_id',
							url = '$url',
							scraper_date = SYSDATE(),	 
							scraper_hour = SYSDATE()"
						); 
						$employer_id = select_id("SELECT LAST_INSERT_ID() FROM employer");
					}
				}

				if ($contact <> '' || $email <> '' || $information <> '' || $phone <> '' || $fax <> '')
				{
					if ($email <> '')
						$query = "SELECT id FROM contact WHERE email = '$email'";
					elseif ($information <> '' && $phone <> '' && $fax <> '') 
						$query = "SELECT id FROM contact WHERE information LIKE '$information' AND fax LIKE '$fax' AND phone LIKE '$phone' AND country_id = '$country_id'";
					elseif ($information <> '' && $phone <> '')  
						$query = "SELECT id FROM contact WHERE information LIKE '$information' AND phone LIKE '$phone' AND country_id = '$country_id'";
					elseif ($information <> '' && $fax <> '') 
						$query = "SELECT id FROM contact WHERE information LIKE '$information' AND fax LIKE '$fax' AND country_id = '$country_id'";
					elseif ($phone <> '' && $fax <> '') 
						$query = "SELECT id FROM contact WHERE phone LIKE '$phone' AND fax LIKE '$fax' AND country_id = '$country_id'";
					else
						$query = "SELECT id FROM contact WHERE email = '$email' AND information LIKE '$information' AND phone LIKE '$phone' AND fax LIKE '$fax' AND country_id = '$country_id'";

					$contact_id = select_id($query);

					if ($contact_id == '')
					{
						mysql_query("INSERT INTO contact SET 
								employer_id = '$employer_id',
								contact = '$contact',
								information = '$information',
								country_id ='$country_id',
								phone = '$phone',
								email='$email',
								fax = '$fax',					
								url = '$url',
								scraper_date = SYSDATE(),	 
								scraper_hour = SYSDATE()"
						);
						$contact_id = select_id("SELECT LAST_INSERT_ID() FROM contact");
					}
				}
			
				//$id_job = create_unique_id();
				
				//id = '$id_job',			
	
				mysql_query("INSERT INTO job SET 
					url = ".db_prep($url).",
					description = ".db_prep($description).",
					employer_id = ".db_prep($employer_id).",
					contact_id = ".db_prep($contact_id).", 
					source_id = ".db_prep($source_id).",  
					url_id = ".db_prep($url_id).",  
					url_search = ".db_prep($url_search).",  
					url_scraper_date = ".db_prep($url_scraper_date).",  
					url_scraper_hour = ".db_prep($url_scraper_hour).",
					url_job_unique = ".db_prep($url_job_unique).",
					title_id = ".db_prep($title_id).",       
					starting_date = STR_TO_DATE('$starting_date', '%d/%m/%Y'),
					ending_date = STR_TO_DATE('$ending_date', '%d/%m/%Y'),
					country_id = ".db_prep($country_id).",  
					region_id = ".db_prep($region_id).",  
					minimum_salary_id = ".db_prep($minimum_salary_id).",  
					maximum_salary_id = ".db_prep($maximum_salary_id).",  
					salary_currency_id = ".db_prep($salary_currency_id).",  
					salary_tax_id = ".db_prep($salary_tax_id).",  
					salary_period_id = ".db_prep($salary_period_id).",   
					hours_per_week_id = ".db_prep($hours_per_week_id).",  
					contract_type_id = ".db_prep($contract_type_id).",   
					contract_hours_id = ".db_prep($contract_hours_id).",   
					accommodation_provided = ".db_prep($accommodation_provided).",   
					relocation_covered = ".db_prep($relocation_covered).",   
					meals_included = ".db_prep($meals_included).",   
					travel_expenses = ".db_prep($travel_expenses).",   
					education_skills_id = ".db_prep($education_skills_id).",   
					professional_qualifications_required = ".db_prep($professional_qualifications_required).",   
					experience_id = ".db_prep($experience_id).",   
					driving_license_id = ".db_prep($driving_license_id).",   
					minimum_age = ".db_prep($minimum_age).",   
					maximum_age = ".db_prep($maximum_age).",   
					how_to_apply_id = ".db_prep($how_to_apply_id).",   
					last_date_for_application = STR_TO_DATE('$last_date_for_application', '%d/%m/%Y'),
					date_published = STR_TO_DATE('$date_published', '%d/%m/%Y'),
					last_modification_date = STR_TO_DATE('$last_modification_date', '%d/%m/%Y'),
					nace_code = ".db_prep($nace_code).",   
					national_reference = ".db_prep($national_reference).",   
					eures_reference = ".db_prep($eures_reference).",   
					isco_code = ".db_prep($isco_code).",   
					isco_unit_code = ".db_prep($isco_unit_code).",   
					isco_minor_code = ".db_prep($isco_minor_code).",   
					isco_submajor_code = ".db_prep($isco_submajor_code).",   
					isco_major_code = ".db_prep($isco_major_code).",   
					number_of_posts = ".db_prep($number_of_posts).",   
					job_scraper_date = SYSDATE(),	 
					job_scraper_hour = SYSDATE()"
				);
				
			
			}
		}

		closedir($handle);
	}
	else
	{
		die ("Directory does not exist: " . $dir);
	}
mysql_query("INSERT INTO update_service SET date = SYSDATE(), hour = SYSDATE(), type='job'");

}

?>
