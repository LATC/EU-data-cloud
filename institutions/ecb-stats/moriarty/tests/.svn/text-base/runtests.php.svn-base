<?php
if (!defined('PHPUnit_MAIN_METHOD')) {
  define('PHPUnit_MAIN_METHOD', 'Moriarty_AllTests::main');
}

// use environment variable to specify location of ARC2
$moriarty_arc_dir = getenv("MORIARTY_ARC_DIR");
if(!empty($moriarty_arc_dir)) {
	define("MORIARTY_ARC_DIR", $moriarty_arc_dir);
}

require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'constants.inc.php';
require_once 'PHPUnit/TextUI/TestRunner.php';

require_once MORIARTY_TEST_DIR . 'fakehttprequest.class.php';
require_once MORIARTY_TEST_DIR . 'fakerequestfactory.class.php';

require_once MORIARTY_TEST_DIR . 'changeset.test.php';
require_once MORIARTY_TEST_DIR . 'store.test.php';
require_once MORIARTY_TEST_DIR . 'metabox.test.php';
require_once MORIARTY_TEST_DIR . 'sparqlservice.test.php';
require_once MORIARTY_TEST_DIR . 'fieldpredicatemap.test.php';
require_once MORIARTY_TEST_DIR . 'valuepool.test.php';
require_once MORIARTY_TEST_DIR . 'httprequest.test.php';
require_once MORIARTY_TEST_DIR . 'httpresponse.test.php';
require_once MORIARTY_TEST_DIR . 'credentials.test.php';
require_once MORIARTY_TEST_DIR . 'privategraph.test.php';
require_once MORIARTY_TEST_DIR . 'contentbox.test.php';
require_once MORIARTY_TEST_DIR . 'multisparqlservice.test.php';
require_once MORIARTY_TEST_DIR . 'jobqueue.test.php';
require_once MORIARTY_TEST_DIR . 'simplegraph.test.php';
require_once MORIARTY_TEST_DIR . 'config.test.php';
require_once MORIARTY_TEST_DIR . 'storecollection.test.php';
require_once MORIARTY_TEST_DIR . 'networkresource.test.php';
require_once MORIARTY_TEST_DIR . 'queryprofile.test.php';
require_once MORIARTY_TEST_DIR . 'storegroupconfig.test.php';
require_once MORIARTY_TEST_DIR . 'httpcache.test.php';
require_once MORIARTY_TEST_DIR . 'facetservice.test.php';
require_once MORIARTY_TEST_DIR . 'rollback.test.php';
require_once MORIARTY_TEST_DIR . 'snapshots.test.php';
require_once MORIARTY_TEST_DIR . 'augmentservice.test.php';
require_once MORIARTY_TEST_DIR . 'graphpath.test.php';
require_once MORIARTY_TEST_DIR . 'oaiservice.test.php';
require_once MORIARTY_TEST_DIR . 'curlhttpclient.test.php';
require_once MORIARTY_TEST_DIR . 'union.test.php';
require_once MORIARTY_TEST_DIR . 'labeller.test.php';
require_once MORIARTY_TEST_DIR . 'datatable.test.php';
require_once MORIARTY_TEST_DIR . 'datatableresult.test.php';

error_reporting(E_ALL && ~E_STRICT);

function exceptions_error_handler($severity, $message, $filename, $lineno) {
  if (error_reporting() == 0) {
    return;
  }
  if (error_reporting() & $severity) {
    throw new ErrorException($message, 0, $severity, $filename, $lineno);
  }
}
set_error_handler('exceptions_error_handler');

function debug_exception_handler($ex) {
  echo "Error : ".$ex->getMessage()."\n";
  echo "Code : ".$ex->getCode()."\n";
  echo "File : ".$ex->getFile()."\n";
  echo "Line : ".$ex->getLine()."\n";
  echo $ex->getTraceAsString()."\n";
  exit;
}
set_exception_handler('debug_exception_handler');


class Moriarty_AllTests
{
    public static function main()
    {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }

    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('Moriarty Framework Tests');

        $suite->addTestSuite('StoreTest');
        $suite->addTestSuite('SparqlServiceTest');
        $suite->addTestSuite('FieldPredicateMapTest');
        $suite->addTestSuite('ChangesetTest');
        $suite->addTestSuite('CredentialsTest');
        $suite->addTestSuite('ValuePoolTest');
        $suite->addTestSuite('HttpRequestTest');
        $suite->addTestSuite('HttpResponseTest');
        $suite->addTestSuite('PrivateGraphTest');
        $suite->addTestSuite('MetaboxTest');
        $suite->addTestSuite('ContentboxTest');
        $suite->addTestSuite('MultiSparqlServiceTest');
        $suite->addTestSuite('JobQueueTest');
        $suite->addTestSuite('SimpleGraphTest');
        $suite->addTestSuite('ConfigTest');
        $suite->addTestSuite('StoreCollectionTest');
        $suite->addTestSuite('NetworkResourceTest');
        $suite->addTestSuite('QueryProfileTest');
//        $suite->addTestSuite('HttpCacheTest');
        $suite->addTestSuite('FacetServiceTest');
        $suite->addTestSuite('SnapshotsTest');
        $suite->addTestSuite('RollbackTest');
        $suite->addTestSuite('AugmentServiceTest');
        $suite->addTestSuite('GraphPathTest');
        $suite->addTestSuite('OAIServiceTest');
//        $suite->addTestSuite('CurlHttpClientTest');
        $suite->addTestSuite('UnionTest');
        $suite->addTestSuite('LabellerTest');
        $suite->addTestSuite('DataTableTest');
        $suite->addTestSuite('DataTableResultTest');

        return $suite;
    }
}

if (PHPUnit_MAIN_METHOD == 'Moriarty_AllTests::main') {
    Moriarty_AllTests::main();
}

?>
