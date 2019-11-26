<?php
// Version
define('VERSION', '3.0.2.0');

// Configuration
if (is_file('../config.php')) {
    require_once('../config.php');
}

// Startup
require_once(DIR_SYSTEM . 'startup.php');