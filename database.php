<?php
// Error display strictly on karne ke liye
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

// Session check aur start
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database Credentials
$host = "localhost";
$dbname = "utility_store_management";
$username = "root";
$password = "";

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Database Connection failed: " . $e->getMessage());
}

// Global Base URL Configuration
if (!defined('BASE_URL')) {
    define('BASE_URL', '/utility%20store%20management/');
}
?>