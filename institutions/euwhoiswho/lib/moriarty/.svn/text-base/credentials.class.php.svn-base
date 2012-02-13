<?php
/**
 * Represents a username and password pair for authenticating against secure services.
 */
class Credentials {
  private $username;
  private $password;

  /**
   * Construct a new instance of this class
   * @param string username the user name to use when authenticating
   * @param string password the password to use when authenticating
   */
  function __construct($username, $password) {
    $this->username = $username;
    $this->password = $password;
  }
  
  /**
   * Obtain the username and password combined for authentication.
   * @return string
   */
  function get_auth() {
    return $this->username . ':' . $this->password;
  }

  /**
   * Obtain the username 
   * @return string
   */
  function get_username() {
    return $this->username;
  }

  /**
   * Obtain the password 
   * @return string
   */
  function get_password() {
    return $this->password;
  }

}
?>