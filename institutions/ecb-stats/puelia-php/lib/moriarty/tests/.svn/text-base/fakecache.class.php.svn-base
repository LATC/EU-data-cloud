<?php
require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'constants.inc.php';
require_once MORIARTY_DIR . 'httpcache.class.php';

class FakeCache extends HttpCache {
  var $_entries = array();
  
  function __construct() {
    $this->_entries = array();
  }

  function save($data, $id, $tags= array(), $specificLifetime = FALSE, $priority = 0) {
    $this->_entries[$id] = $data;
  }

  function load($id, $doNotTestCacheValidity = FALSE, $doNotUnserialize = FALSE) {
    if (array_key_exists($id, $this->_entries)) {
      return $this->_entries[$id];  
    }
    else {
      return null;  
    }
  }
  
  function remove($id) {
    unset($this->_entries[$id]);
  } 


  function test($id) {
    return array_key_exists($id, $this->_entries);
  }
}
?>
