<?php
// Is file ka kaam sirf secure tarike se session start karna hai
if (session_status() === PHP_SESSION_NONE) {
    // Session cookies ko secure karne ke liye settings
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    
    session_start();
}

// Session regeneration (Security ke liye taake session hijack na ho)
if (!isset($_SESSION['last_regeneration'])) {
    session_regenerate_id(true);
    $_SESSION['last_regeneration'] = time();
} else {
    $interval = 60 * 30; // 30 minutes
    if (time() - $_SESSION['last_regeneration'] > $interval) {
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = time();
    }
}
?>