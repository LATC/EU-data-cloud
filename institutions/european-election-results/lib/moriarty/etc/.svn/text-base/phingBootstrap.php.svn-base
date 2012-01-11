<?php
$moriarty_arc_dir = getenv('MORIARTY_ARC_DIR');

if (empty($moriarty_arc_dir))
{
    throw new Exception("Environment variable 'MORIARTY_ARC_DIR' not found - this should point to where your ARC directory is located, ending with the trailing slash");
}

define('MORIARTY_DIR',dirname(dirname(__FILE__)). DIRECTORY_SEPARATOR);
define('MORIARTY_ARC_DIR', $moriarty_arc_dir);

//TODO Probably better to use real mocks for these otherwise they get out of sync with the real class.
require_once MORIARTY_DIR . '/tests/fakehttprequest.class.php';
require_once MORIARTY_DIR . '/tests/fakerequestfactory.class.php';

error_reporting(E_ALL && ~E_STRICT);

ini_set('include_path',
  ini_get('include_path')
  .PATH_SEPARATOR.MORIARTY_DIR
  .PATH_SEPARATOR.MORIARTY_ARC_DIR
);
?>