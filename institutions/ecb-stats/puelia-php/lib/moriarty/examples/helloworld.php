<?php
// CHANGE THESE TO POINT TO YOUR INSTALLATIONS OF MORIARTY AND ARC
define('MORIARTY_DIR', '../');
define('MORIARTY_ARC_DIR', '../../../web/lib/arc_2008_11_18/');


require_once MORIARTY_DIR . 'moriarty.inc.php';
require_once MORIARTY_DIR . 'store.class.php';

$store = new Store('http://api.talis.com/stores/ukbib');
$contentbox = $store->get_contentbox();

$results = $contentbox->search_to_resource_list("feynman", 10, 0);
echo '<h1>' . $results->title . '</h1>';
foreach ($results->items as $item) {
  echo '<p><a href="' . $item[RSS_LINK][0] . '">' . $item[RSS_TITLE][0] . '</a></p>';
}
?>