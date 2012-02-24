<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'moriarty.inc.php';

class HttpCache {
  var $_directory;
  var $_options;

  function __construct($options = array()) {
    $this->_options = $options;
    if (! array_key_exists('caching', $this->_options)) {
      $this->_options['caching'] = TRUE;
    }
    $this->_directory = $options['directory'];
    if (substr($this->_directory, -1) != DIRECTORY_SEPARATOR) {
      $this->_directory .= DIRECTORY_SEPARATOR;
    }

  }


  function save($data, $id, $tags= array(), $specificLifetime = FALSE, $priority = 0) {
    if ($this->_options['caching'] === FALSE) return TRUE;
    $filename = $this->_directory . $id;
    $fp = fopen($filename, 'w');
    if ($fp) {
      fwrite($fp,serialize($specificLifetime) . "\n");
      fwrite($fp,serialize($data));
      fclose($fp);
      chmod($filename, 0777);
      return TRUE;
    }
    return FALSE;
  }

  function load($id, $doNotTestCacheValidity = FALSE, $doNotUnserialize = FALSE) {
    if ($this->_options['caching'] === FALSE) return FALSE;
    $filename = $this->_directory . $id;
    if ( file_exists($filename)) {
      $content = file_get_contents($filename);
      if ($content !== FALSE ) {
        list($specificLifetime_ser , $response_ser) = explode("\n", $content, 2);
        $specificLifetime= unserialize($specificLifetime_ser);
        if (! $doNotTestCacheValidity) {
          if ( time() - filectime($filename) > $specificLifetime ) {
            $this->remove($id);
            return FALSE;
          }
        }
        $response = unserialize($response_ser);
        return $response;
      }
    }
    return FALSE;
  }

  function remove($id) {
    if ($this->_options['caching'] === FALSE) return TRUE;

    $filename = $this->_directory . $id;
    if ( file_exists($filename)) {
      unlink($filename);
    }

    return TRUE;
  }


  function test($id) {
    if ($this->_options['caching'] === FALSE) return FALSE;
    $filename = $this->_directory . $id;
    return file_exists($filename);
  }

}
?>