<?php
// Session check aur start
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Global constants link kiya taake BASE_URL har jagah accessible ho
if (file_exists(__DIR__ . "/../config/constants.php")) {
    require_once __DIR__ . "/../config/constants.php";
}

// Fallback if constant is missing
if (!defined('BASE_URL')) {
    define('BASE_URL', '/utility%20store%20management/');
}

// Current page ka naam nikalne ke liye taake active class auto lag sake
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Utility Store Management System</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            display: flex;
            background-color: #f8f9fa;
            margin: 0;
            min-height: 100vh;
        }
        /* Sidebar Styling */
        .sidebar {
            width: 250px;
            background-color: #1a252f;
            color: #fff;
            min-height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            padding-top: 20px;
        }
        .sidebar h2 {
            text-align: center;
            font-size: 22px;
            margin-bottom: 30px;
            font-weight: bold;
        }
        .sidebar a {
            display: block;
            color: #b8c7ce;
            padding: 12px 20px;
            text-decoration: none;
            transition: all 0.3s;
            font-size: 15px;
        }
        .sidebar a:hover, .sidebar a.active {
            background-color: #111a22;
            color: #fff;
            border-left: 4px solid #1abc9c;
        }
        /* Main Content Styling */
        .main {
            margin-left: 250px;
            width: calc(100% - 250px);
            padding: 20px;
        }
        .topbar {
            background-color: #fff;
            padding: 15px 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
    </style>
</head>
<body>

<!-- 💻 Professional Absolute Sidebar Links Links -->
<div class="sidebar">
    <h2>🏪 Utility Store</h2>
    <a href="<?= BASE_URL ?>admin/dashboard.php" class="<?= $current_page=='dashboard.php'?'active':'' ?>">🏠 Dashboard</a>
    <a href="<?= BASE_URL ?>admin/categories.php" class="<?= $current_page=='categories.php'?'active':'' ?>">📂 Categories</a>
    <a href="<?= BASE_URL ?>admin/brands.php" class="<?= $current_page=='brands.php'?'active':'' ?>">🏷️ Brands</a>
    <a href="<?= BASE_URL ?>admin/products.php" class="<?= $current_page=='products.php'?'active':'' ?>">📦 Products</a>
    <a href="<?= BASE_URL ?>admin/inventory.php" class="<?= $current_page=='inventory.php'?'active':'' ?>">📊 Inventory</a>
    <a href="<?= BASE_URL ?>admin/reports.php" class="<?= $current_page=='reports.php'?'active':'' ?>">📑 Reports</a>
</div>

<!-- Main Content Window -->
<div class="main">
    <!-- Topbar Area -->
    <div class="topbar">
        <div>
            <span class="fw-bold">👋 Welcome, <?= isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Admin' ?></span>
        </div>
        <div>
            <a href="<?= BASE_URL ?>logout.php" class="btn btn-danger btn-sm px-3 fw-bold">Logout</a>
        </div>
    </div>