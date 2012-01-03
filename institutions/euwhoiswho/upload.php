<?php
define('MORIARTY_ARC_DIR', 'arc/');
require 'inc.php';
require 'credentials.inc.php';
require_once 'moriarty/credentials.class.php';

function report($r){
  var_dump($r->status_code);
  if($r->is_success()===false){
    var_dump($r);
    die;
  }
}
$void = new SimpleGraph();
$void->add_turtle(file_get_contents('void.ttl'));
$void->add_literal_triple(WHOISWHO, DCT.'modified', date('c'), false, XSDT.'dateTime');
$graph = new Graph('http://api.talis.com/stores/euwhoiswho/meta', new Credentials(STORE_USER, STORE_PASS));
$graph->mirror_from_uri(WHOISWHO, $void->to_json());
$graph->submit_ntriples_in_batches_from_file('roles.nt', 500, 'report');
$graph->submit_ntriples_in_batches_from_file('all.nt', 500, 'report');

?>
