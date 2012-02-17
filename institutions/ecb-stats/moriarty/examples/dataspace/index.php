<?php
/*
 * Copy this file into the root of your linked data website
 */

// Change the path to your moriarty installation
define('MORIARTY_DIR', 'path/to/moriarty' . DIRECTORY_SEPARATOR);

// Change the path to your ARC installation
define('MORIARTY_ARC_DIR', 'path/to/arc_2008_11_18' . DIRECTORY_SEPARATOR);

// Change the path to a writeable directory that can be used for cache files
define('MORIARTY_HTTP_CACHE_DIR', '/tmp');



// Change the following to suit your website
// regex is a regex that matches the URIs that you are using on your website
// store is the URI of the Talis Platform store your data is held in
// template is the filename of a template file to use
$uri_map[] = array( 'regex' => '^http://example.com/',
                    'store' => 'http://api.talis.com/stores/mystore',
                    'template' => 'plain.tmpl.html'
                    );

// You can add as many mappings as you like
#$uri_map[] = array( 'regex' => '^http://other.example.com/',
#                    'store' => 'http://api.talis.com/stores/mystore',
#                    'template' => 'plain.tmpl.html'
#                    );

// this loads and runs the dataspace script
require_once(MORIARTY_DIR . 'examples/dataspace/dataspace.php');
