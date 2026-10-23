<?php
// Session aur Constants ko lazmi include karna hai
require_once __DIR__ . "/../config/session.php";
require_once __DIR__ . "/../config/constants.php";

// 1. Check login function (jo requireLogin ke andar use ho raha hai)
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// 2. Redirect if not logged in
function requireLogin() {
    if (!isLoggedIn()) {
        // Hamesha project ke main login page par bhejega, path kharab nahi hoga
        header("Location: " . BASE_URL . "login.php");
        exit();
    }
}

// 3. Logout function (agar aapko project mein kahin chahiye ho)
function logout() {
    $_SESSION = array();
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
    header("Location: " . BASE_URL . "login.php");
    exit();
}
?>