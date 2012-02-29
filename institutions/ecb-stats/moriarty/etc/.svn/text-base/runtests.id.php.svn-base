<?php
ini_set('error_reporting', E_ALL);

define('MORIARTY_DIR',dirname(dirname(__FILE__)). DIRECTORY_SEPARATOR);
define('MORIARTY_ARC_DIR',"/home/iand/web/lib/arc_2008_11_18/");
define('PAGET_DIR',"/home/iand/web/lib/paget/");
define('MORIARTY_PHPUNIT_DIR',"/home/iand/wip/moriarty-dev/PHPUnit-3.3.0/");

ini_set('include_path',
  ini_get('include_path')
  .PATH_SEPARATOR.MORIARTY_DIR
  .PATH_SEPARATOR.MORIARTY_ARC_DIR
  .PATH_SEPARATOR.MORIARTY_PHPUNIT_DIR
  .PATH_SEPARATOR.PAGET_DIR
);


if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'Combined_AllTests::main');
}


require_once MORIARTY_DIR . '/tests/runtests.php';
require_once PAGET_DIR . '/tests/runtests.php';
//require_once OPENVOCAB_DIR . '/tests/runtests.php';


class Combined_AllTests
{
    public static function main()
    {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }

    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('Combined Tests');

        $suite->addTestSuite(Moriarty_AllTests::suite());
        $suite->addTestSuite(Paget_AllTests::suite());
 //       $suite->addTestSuite(OpenVocab_AllTests::suite());

        return $suite;
    }
}

if (PHPUnit_MAIN_METHOD == 'Combined_AllTests::main') {
    Combined_AllTests::main();
}

?>
