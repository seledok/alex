<?php
// Version
define('VERSION', '3.0.2.0');

// Configuration
if (is_file('../config.php')) {
    require_once('../config.php');
}

// Startup
require_once(DIR_SYSTEM . 'startup.php');

error_reporting(E_ALL | E_STRICT) ;
ini_set('display_errors', 'On');

$db = new DB(DB_DRIVER, DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE, DB_PORT);