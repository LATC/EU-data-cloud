<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */


require_once 'PHPUnit/Extensions/SeleniumTestCase.php';

/**
 * Description of newSeleneseTest1
 *
 * @author Uli
 */
class newSeleneseTest1 extends PHPUnit_Extensions_SeleniumTestCase {

    function setUp() {
$this->setBrowser("*firefox");
    $this->setBrowserUrl("http://ec.europa.eu/");
    }

    function testMyTestCase() {
    $this->open("/enterprise/e-bsn/ebusiness-solutions-guide/detailledSearch.do");
    $this->click("css=#linkBoxLanguage > a");
    $this->click("link=Englisch (en)");
    $this->waitForPageToLoad("30000");
    $country = "Germany";
    $this->select("name=countryId", "label=".$country);
    $this->click("css=input.submit");
    // only for german
    sleep(120);
    $this->waitForPageToLoad("30000");
    $source = $this->getHtmlSource();
    preg_match("/(\d*) results on your search:/s", $source, $number_of_links);
    $this->click("css=a > b");
    $this->waitForPageToLoad("30000");
    $location_product = $this->getLocation();
    preg_match("/[^\?]*\?productId=(.*)/s", $location_product,$product_ID);
    $test = $this->getHtmlSource();
    $file_properties = "C:\business\\product\\".$product_ID[1].".html";
    $handle_properties = fopen($file_properties, 'wb');
    fwrite($handle_properties, $test);
    $this->click("css=a > b");
    $this->waitForPageToLoad("30000");
    $location_producer = $this->getLocation();
    preg_match("/[^\?]*\?producerId=(.*)/s", $location_producer,$producer_ID);
    $test_2 = $this->getHtmlSource();
    $file_properties = "C:\business\\producer\\".$producer_ID[1].".html";
    $handle_properties = fopen($file_properties, 'wb');
    fwrite($handle_properties, $test_2);
    $file_properties_2  = "C:\business\\relation.csv";
    $handle_properties_2 = fopen($file_properties_2, 'a');
    fwrite($handle_properties_2, $product_ID[1].";".$producer_ID[1].";".$country."\n");
    fclose($handle_properties_2);
    $this->click("link=Back");
    $this->waitForPageToLoad("30000");
    $this->click("link=Back");
    $this->waitForPageToLoad("30000");
    for ($i=7;$i<=39;$i=$i+4)
    {
    $this->click("//div[@id='content_main']/table[3]/tbody/tr[".$i."]/td/a/b");
    $this->waitForPageToLoad("30000");
        $location_product = $this->getLocation();
    preg_match("/[^\?]*\?productId=(.*)/s", $location_product,$product_ID);
    $test = $this->getHtmlSource();
    $file_properties = "C:\business\\product\\".$product_ID[1].".html";
    $handle_properties = fopen($file_properties, 'wb');
    fwrite($handle_properties, $test);
    $this->click("css=a > b");
    $this->waitForPageToLoad("30000");
    $location_producer = $this->getLocation();
    preg_match("/[^\?]*\?producerId=(.*)/s", $location_producer,$producer_ID);
    $test_2 = $this->getHtmlSource();
    $file_properties = "C:\business\\producer\\".$producer_ID[1].".html";
    $handle_properties = fopen($file_properties, 'wb');
    fwrite($handle_properties, $test_2);
        $file_properties_2  = "C:\business\\relation.csv";
    $handle_properties_2 = fopen($file_properties_2, 'a');
    fwrite($handle_properties_2, $product_ID[1].";".$producer_ID[1].";".$country."\n");
    fclose($handle_properties_2);
    $this->click("link=Back");
    $this->waitForPageToLoad("30000");
    $this->click("link=Back");
    $this->waitForPageToLoad("30000");
    $max_number = $number_of_links[1] / 10;
    $max_number_of_links = ceil($max_number);
    }
    for ($j=2;$j<=$max_number_of_links;$j++)
    {
        $this->click("link=".$j);
        $this->waitForPageToLoad("30000");
        $this->click("css=a > b");
    $this->waitForPageToLoad("30000");
    $location_product = $this->getLocation();
    preg_match("/[^\?]*\?productId=(.*)/s", $location_product,$product_ID);
    $test = $this->getHtmlSource();
    $file_properties = "C:\business\\product\\".$product_ID[1].".html";
    $handle_properties = fopen($file_properties, 'wb');
    fwrite($handle_properties, $test);
    $this->click("css=a > b");
    $this->waitForPageToLoad("30000");
    $location_producer = $this->getLocation();
    preg_match("/[^\?]*\?producerId=(.*)/s", $location_producer,$producer_ID);
    $test_2 = $this->getHtmlSource();
    $file_properties = "C:\business\\producer\\".$producer_ID[1].".html";
    $handle_properties = fopen($file_properties, 'wb');
    fwrite($handle_properties, $test_2);
    $file_properties_2  = "C:\business\\relation.csv";
    $handle_properties_2 = fopen($file_properties_2, 'a');
    fwrite($handle_properties_2, $product_ID[1].";".$producer_ID[1].";".$country."\n");
    fclose($handle_properties_2);
    $this->click("link=Back");
    $this->waitForPageToLoad("30000");
    $this->click("link=Back");
    $this->waitForPageToLoad("30000");
    for ($i=7;$i<=39;$i=$i+4)
    {
    $this->click("//div[@id='content_main']/table[3]/tbody/tr[".$i."]/td/a/b");
    $this->waitForPageToLoad("30000");
        $location_product = $this->getLocation();
    preg_match("/[^\?]*\?productId=(.*)/s", $location_product,$product_ID);
    $test = $this->getHtmlSource();
    $file_properties = "C:\business\\product\\".$product_ID[1].".html";
    $handle_properties = fopen($file_properties, 'wb');
    fwrite($handle_properties, $test);
    $this->click("css=a > b");
    $this->waitForPageToLoad("30000");
    $location_producer = $this->getLocation();
    preg_match("/[^\?]*\?producerId=(.*)/s", $location_producer,$producer_ID);
    $test_2 = $this->getHtmlSource();
    $file_properties = "C:\business\\producer\\".$producer_ID[1].".html";
    $handle_properties = fopen($file_properties, 'wb');
    fwrite($handle_properties, $test_2);
        $file_properties_2  = "C:\business\\relation.csv";
    $handle_properties_2 = fopen($file_properties_2, 'a');
    fwrite($handle_properties_2, $product_ID[1].";".$producer_ID[1].";".$country."\n");
    fclose($handle_properties_2);
    $this->click("link=Back");
    $this->waitForPageToLoad("30000");
    $this->click("link=Back");
    $this->waitForPageToLoad("30000");
    }
    }
    }

}

?>