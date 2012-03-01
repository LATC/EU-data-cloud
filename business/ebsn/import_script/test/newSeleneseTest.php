<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */


require_once 'PHPUnit/Extensions/SeleniumTestCase.php';

class businessTest extends PHPUnit_Extensions_SeleniumTestCase
{
  protected function setUp()
  {
    $this->setBrowser("*firefox");
    $this->setBrowserUrl("http://ec.europa.eu/");
  }

  public function business()
  {
    //open search page
    $this->open("/enterprise/e-bsn/ebusiness-solutions-guide/detailledSearch.do");
    $this->select("name=countryId", "label=Belgien");
    $this->click("css=input.submit");
    $this->waitForPageToLoad("30000");
    $this->click("css=a > b");
    $this->waitForPageToLoad("30000");
    $location_product = $this->getLocation();
    preg_match("/[^\?]*\?productId=(.*)/s", $location_product,$product_ID);
    $test = $this->getHtmlSource();
    $file_properties = "C:\business\product\\".$product_ID.".html";
    $handle_properties = fopen($file_properties, 'wb');
    fwrite($handle_properties, $test);
    $this->click("css=a > b");
    $this->waitForPageToLoad("30000");
    $location_producer = $this->getLocation();
    preg_match("/[^\?]*\?producerId=(.*)/s", $location_producer,$producer_ID);
    $test_2 = $this->getHtmlSource();
    $file_properties = "C:\business\producer\\".$producer_ID.".html";
    $handle_properties = fopen($file_properties, 'wb');
    fwrite($handle_properties, $test_2);
    $this->click("link=Zurück");
    $this->waitForPageToLoad("30000");
    $this->click("link=Zurück");
    $this->waitForPageToLoad("30000");
    $this->click("//div[@id='content_main']/table[3]/tbody/tr[7]/td/a/b");
    $this->waitForPageToLoad("30000");
        $location_product = $this->getLocation();
    preg_match("/[^\?]*\?productId=(.*)/s", $location_product,$product_ID);
    $test = $this->getHtmlSource();
    $file_properties = "C:\business\product\\".$product_ID.".html";
    $handle_properties = fopen($file_properties, 'wb');
    fwrite($handle_properties, $test);
    $this->click("css=a > b");
    $this->waitForPageToLoad("30000");
    $location_producer = $this->getLocation();
    preg_match("/[^\?]*\?producerId=(.*)/s", $location_producer,$producer_ID);
    $test_2 = $this->getHtmlSource();
    $file_properties = "C:\business\producer\\".$producer_ID.".html";
    $handle_properties = fopen($file_properties, 'wb');
    fwrite($handle_properties, $test_2);
    $this->click("link=Zurück");
    $this->waitForPageToLoad("30000");
    $this->click("//div[@id='content_main']/table[3]/tbody/tr[11]/td/a/b");
    $this->waitForPageToLoad("30000");
        $location_product = $this->getLocation();
    preg_match("/[^\?]*\?productId=(.*)/s", $location_product,$product_ID);
    $test = $this->getHtmlSource();
    $file_properties = "C:\business\product\\".$product_ID.".html";
    $handle_properties = fopen($file_properties, 'wb');
    fwrite($handle_properties, $test);
    $this->click("css=a > b");
    $this->waitForPageToLoad("30000");
    $location_producer = $this->getLocation();
    preg_match("/[^\?]*\?producerId=(.*)/s", $location_producer,$producer_ID);
    $test_2 = $this->getHtmlSource();
    $file_properties = "C:\business\producer\\".$producer_ID.".html";
    $handle_properties = fopen($file_properties, 'wb');
    fwrite($handle_properties, $test_2);
    $this->click("link=Zurück");
    $this->waitForPageToLoad("30000");
    $this->click("//div[@id='content_main']/table[3]/tbody/tr[15]/td/a/b");
    $this->waitForPageToLoad("30000");
        $location_product = $this->getLocation();
    preg_match("/[^\?]*\?productId=(.*)/s", $location_product,$product_ID);
    $test = $this->getHtmlSource();
    $file_properties = "C:\business\product\\".$product_ID.".html";
    $handle_properties = fopen($file_properties, 'wb');
    fwrite($handle_properties, $test);
    $this->click("css=a > b");
    $this->waitForPageToLoad("30000");
    $location_producer = $this->getLocation();
    preg_match("/[^\?]*\?producerId=(.*)/s", $location_producer,$producer_ID);
    $test_2 = $this->getHtmlSource();
    $file_properties = "C:\business\producer\\".$producer_ID.".html";
    $handle_properties = fopen($file_properties, 'wb');
    fwrite($handle_properties, $test_2);
    $this->click("link=Zurück");
    $this->waitForPageToLoad("30000");
    $this->click("//div[@id='content_main']/table[3]/tbody/tr[19]/td/a/b");
    $this->waitForPageToLoad("30000");
        $location_product = $this->getLocation();
    preg_match("/[^\?]*\?productId=(.*)/s", $location_product,$product_ID);
    $test = $this->getHtmlSource();
    $file_properties = "C:\business\product\\".$product_ID.".html";
    $handle_properties = fopen($file_properties, 'wb');
    fwrite($handle_properties, $test);
    $this->click("css=a > b");
    $this->waitForPageToLoad("30000");
    $location_producer = $this->getLocation();
    preg_match("/[^\?]*\?producerId=(.*)/s", $location_producer,$producer_ID);
    $test_2 = $this->getHtmlSource();
    $file_properties = "C:\business\producer\\".$producer_ID.".html";
    $handle_properties = fopen($file_properties, 'wb');
    fwrite($handle_properties, $test_2);
    $this->click("link=Zurück");
    $this->waitForPageToLoad("30000");
    $this->click("//div[@id='content_main']/table[3]/tbody/tr[23]/td/a/b");
    $this->waitForPageToLoad("30000");
        $location_product = $this->getLocation();
    preg_match("/[^\?]*\?productId=(.*)/s", $location_product,$product_ID);
    $test = $this->getHtmlSource();
    $file_properties = "C:\business\product\\".$product_ID.".html";
    $handle_properties = fopen($file_properties, 'wb');
    fwrite($handle_properties, $test);
    $this->click("css=a > b");
    $this->waitForPageToLoad("30000");
    $location_producer = $this->getLocation();
    preg_match("/[^\?]*\?producerId=(.*)/s", $location_producer,$producer_ID);
    $test_2 = $this->getHtmlSource();
    $file_properties = "C:\business\producer\\".$producer_ID.".html";
    $handle_properties = fopen($file_properties, 'wb');
    fwrite($handle_properties, $test_2);
    $this->click("link=Zurück");
    $this->waitForPageToLoad("30000");
    $this->click("//div[@id='content_main']/table[3]/tbody/tr[27]/td/a/b");
    $this->waitForPageToLoad("30000");
        $location_product = $this->getLocation();
    preg_match("/[^\?]*\?productId=(.*)/s", $location_product,$product_ID);
    $test = $this->getHtmlSource();
    $file_properties = "C:\business\product\\".$product_ID.".html";
    $handle_properties = fopen($file_properties, 'wb');
    fwrite($handle_properties, $test);
    $this->click("css=a > b");
    $this->waitForPageToLoad("30000");
    $location_producer = $this->getLocation();
    preg_match("/[^\?]*\?producerId=(.*)/s", $location_producer,$producer_ID);
    $test_2 = $this->getHtmlSource();
    $file_properties = "C:\business\producer\\".$producer_ID.".html";
    $handle_properties = fopen($file_properties, 'wb');
    fwrite($handle_properties, $test_2);
    $this->click("link=Zurück");
    $this->waitForPageToLoad("30000");
    $this->click("//div[@id='content_main']/table[3]/tbody/tr[31]/td/a/b");
        $location_product = $this->getLocation();
    preg_match("/[^\?]*\?productId=(.*)/s", $location_product,$product_ID);
    $test = $this->getHtmlSource();
    $file_properties = "C:\business\product\\".$product_ID.".html";
    $handle_properties = fopen($file_properties, 'wb');
    fwrite($handle_properties, $test);
    $this->click("css=a > b");
    $this->waitForPageToLoad("30000");
    $location_producer = $this->getLocation();
    preg_match("/[^\?]*\?producerId=(.*)/s", $location_producer,$producer_ID);
    $test_2 = $this->getHtmlSource();
    $file_properties = "C:\business\producer\\".$producer_ID.".html";
    $handle_properties = fopen($file_properties, 'wb');
    fwrite($handle_properties, $test_2);
    $this->click("link=Zurück");
    $this->waitForPageToLoad("30000");
    $this->click("//div[@id='content_main']/table[3]/tbody/tr[35]/td/a/b");
    $this->waitForPageToLoad("30000");
        $location_product = $this->getLocation();
    preg_match("/[^\?]*\?productId=(.*)/s", $location_product,$product_ID);
    $test = $this->getHtmlSource();
    $file_properties = "C:\business\product\\".$product_ID.".html";
    $handle_properties = fopen($file_properties, 'wb');
    fwrite($handle_properties, $test);
    $this->click("css=a > b");
    $this->waitForPageToLoad("30000");
    $location_producer = $this->getLocation();
    preg_match("/[^\?]*\?producerId=(.*)/s", $location_producer,$producer_ID);
    $test_2 = $this->getHtmlSource();
    $file_properties = "C:\business\producer\\".$producer_ID.".html";
    $handle_properties = fopen($file_properties, 'wb');
    fwrite($handle_properties, $test_2);
    $this->click("link=Zurück");
    $this->waitForPageToLoad("30000");
    $this->click("//div[@id='content_main']/table[3]/tbody/tr[39]/td/a/b");
    $this->waitForPageToLoad("30000");
    $location_product = $this->getLocation();
    preg_match("/[^\?]*\?productId=(.*)/s", $location_product,$product_ID);
    $test = $this->getHtmlSource();
    $file_properties = "C:\business\product\\".$product_ID.".html";
    $handle_properties = fopen($file_properties, 'wb');
    fwrite($handle_properties, $test);
    $this->click("css=a > b");
    $this->waitForPageToLoad("30000");
    $location_producer = $this->getLocation();
    preg_match("/[^\?]*\?producerId=(.*)/s", $location_producer,$producer_ID);
    $test_2 = $this->getHtmlSource();
    $file_properties = "C:\business\producer\\".$producer_ID.".html";
    $handle_properties = fopen($file_properties, 'wb');
    fwrite($handle_properties, $test_2);
    $this->click("link=Zurück");
    $this->waitForPageToLoad("30000");
    
  }
}
?>