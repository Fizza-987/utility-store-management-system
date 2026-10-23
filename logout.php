<?php
// Session config load karein (agar file maujood ho), warna session khud start karein
if (is_file(__DIR__ . '/config/session.php')) {
    require_once __DIR__ . '/config/session.php';
}
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Session ke saare variables clear karein
$_SESSION = array();

// Session cookie destroy karein
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

session_destroy();

// Relative redirect: project folder ke naam par depend nahi karta
header("Location: login.php");
exit();