<?php

if(
    (!is_dir(PUELIA_LOG_DIR) AND @!mkdir(PUELIA_LOG_DIR, 0777)) OR
    (defined('MORIARTY_HTTP_CACHE_DIR') AND !is_dir(MORIARTY_HTTP_CACHE_DIR) AND @!mkdir(MORIARTY_HTTP_CACHE_DIR,0777))
   OR
 (  defined("PUELIA_CACHE_DIR") AND PUELIA_CACHE_DIR AND !is_dir(PUELIA_CACHE_DIR) AND @!mkdir(PUELIA_CACHE_DIR,0777))
    ){
       require 'views/errors/installation-error.html';
       exit;
   }


?>