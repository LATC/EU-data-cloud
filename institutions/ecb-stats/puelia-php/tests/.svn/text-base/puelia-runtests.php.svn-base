<?php
if (!defined('PHPUnit_MAIN_METHOD')) {
  define('PHPUnit_MAIN_METHOD', 'LinkedDataAPI_AllTests::main');
}

require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'lda.inc.php';
require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/TextUI/TestRunner.php';

require_once 'pueliagraph.test.php';
require_once 'configgraph.test.php';
require_once 'linkeddataapi.test.php';
require_once 'sparqlwriter.test.php';
require_once 'vocabularygraph.test.php';
require_once 'linkeddataapigraph.test.php';
require_once 'linkeddataapirequest.test.php';

define('Example_List_Uri', 'http://example.com/#ListOfProperties');
define('testEndpointUri', "http://example.com/#unitTestEndpoint");
define('gc_config', 'http://puelia-php.googlecode.com/svn/trunk/documents/config.ttl#');



class LinkedDataApi_AllTests
{
    public static function main()
    {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }

    public static function suite()
    {
    	ini_set('memory_limit', '512M');
    	
        $suite = new PHPUnit_Framework_TestSuite('Linked Data Api Tests');
    
        $suite->addTestSuite('PueliaGraphTest');
        $suite->addTestSuite('ConfigGraphTest');
        $suite->addTestSuite('VocabularyGraphTest');
        $suite->addTestSuite('LinkedDataApiTest');
        $suite->addTestSuite('LinkedDataApiGraphTest');
        $suite->addTestSuite('SparqlWriterTest');
        $suite->addTestSuite('LinkedDataApiRequestTest');

        return $suite;
    }
}

if (PHPUnit_MAIN_METHOD == 'LinkedDataAPI_AllTests::main') {
    LinkedDataAPI_AllTests::main();
}

?>
