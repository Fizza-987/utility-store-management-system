<?php
// Agar session start nahi hai tou pehle start karein
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// System Name
if (!defined('SITE_NAME')) {
    define("SITE_NAME", "Utility Store Management System");
}

// Absolute Base URL (Folder name space ke sath theek kar diya hai)
if (!defined('BASE_URL')) {
    define("BASE_URL", "http://localhost/utility%20store%20management/");
}

// User Roles Definition
if (!defined('ROLE_ADMIN')) {
    define("ROLE_ADMIN", "admin");
}
if (!defined('ROLE_STAFF')) {
    define("ROLE_STAFF", "staff");
}
if (!defined('ROLE_CUSTOMER')) {
    define("ROLE_CUSTOMER", "customer");
}
?>