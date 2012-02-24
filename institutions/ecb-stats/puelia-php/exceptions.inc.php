<?php
class ConfigGraphException extends Exception {

  function __construct($message){
    $this->message = 'Configuration Error: '.$message;
  }

}
class UnknownPropertyException extends Exception {}
?>
