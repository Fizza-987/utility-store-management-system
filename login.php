<?php
// Errors screen par dikhayen taake koi masla chhupe nahi
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$error = "";
$success = "";

// Config files safe tareeqe se load hoti hain.
// Agar koi config file (jaise database.php) crash kare, to login page phir bhi khulega
// aur upar error ka message dikhayega.
foreach (['config/constants.php', 'config/session.php', 'config/database.php'] as $file) {
    $path = __DIR__ . '/' . $file;
    if (is_file($path)) {
        try {
            require_once $path;
        } catch (Throwable $e) {
            $error = "Config file error (" . $file . "): " . $e->getMessage();
        }
    }
}

// Agar session.php ne session start nahi kiya to yahan kar dein
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (!defined('SITE_NAME')) {
    define('SITE_NAME', 'Online Utility Store Management System');
}

// Register page se aane par message
if (isset($_GET['registration']) && $_GET['registration'] === 'success') {
    $success = "Account ban gaya! Ab login karein.";
}

// Form Processing
if (isset($_POST['login'])) {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username !== '' && $password !== '') {
        try {
            if (isset($conn) && $conn instanceof PDO) {
                $stmt = $conn->prepare("SELECT * FROM users WHERE username = :username");
                $stmt->execute([':username' => $username]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($user && password_verify($password, $user['password'])) {
                    session_regenerate_id(true);
                    $_SESSION['user_id'] = $user['user_id'];
                    $_SESSION['username'] = $user['username'];

                    // dashboard.php root folder mein hai (admin folder mein nahi),
                    // aur relative redirect folder ke naam par depend nahi karta
                    header("Location: dashboard.php");
                    exit();
                } else {
                    $error = "Invalid username or password!";
                }
            } elseif ($error === "") {
                $error = "Database connection (\$conn) nahi bani. config/database.php check karein aur XAMPP mein MySQL start karein.";
            }
        } catch (PDOException $e) {
            $error = "Database Error: " . $e->getMessage();
        }
    } else {
        $error = "Please fill in all fields!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?= htmlspecialchars(SITE_NAME) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            font-family: 'Segoe UI', system-ui, sans-serif;
        }
        .login-card {
            border: none;
            border-radius: 24px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            width: 100%;
            max-width: 430px;
            background: #ffffff;
            opacity: 0;
            transform: translateY(20px);
            animation: cardReveal 0.6s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
        }
        @keyframes cardReveal { to { opacity: 1; transform: translateY(0); } }
        .brand-icon { font-size: 46px; color: #2563eb; display: inline-block; animation: floatingShield 2.5s ease-in-out infinite; }
        @keyframes floatingShield { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-6px); } }
        .input-group-text { background-color: #f1f5f9; border: 1px solid #cbd5e1; border-right: none; color: #64748b; border-top-left-radius: 12px; border-bottom-left-radius: 12px; }
        .form-control { border: 1px solid #cbd5e1; border-left: none; border-top-right-radius: 12px; border-bottom-right-radius: 12px; padding: 12px 16px; background-color: #f8fafc; }
        .form-control:focus { box-shadow: none; background-color: #ffffff; border-color: #2563eb; }
        .input-group:focus-within .input-group-text { border-color: #2563eb; color: #2563eb; background-color: #ffffff; }
        .btn-primary { background: #2563eb; border: none; border-radius: 12px; padding: 13px; font-weight: 600; transition: all 0.3s ease; }
        .btn-primary:hover { background: #1d4ed8; box-shadow: 0 10px 20px rgba(37, 99, 235, 0.3); transform: translateY(-2px); }
    </style>
</head>
<body>

<div class="card login-card p-4 mx-3">
    <div class="card-body">
        <div class="text-center mb-4">
            <div class="mb-2"><i class="bi bi-shield-lock-fill brand-icon"></i></div>
            <h3 class="fw-bold text-dark mb-1">Login Panel</h3>
            <p class="text-muted small mb-0 px-3"><?= htmlspecialchars(SITE_NAME) ?></p>
        </div>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success text-center py-2 small border-0 rounded-3 mb-4" role="alert">
                <i class="bi bi-check-circle-fill me-1"></i> <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger text-center py-2 small border-0 rounded-3 mb-4" role="alert" style="background-color: #fef2f2; color: #991b1b;">
                <i class="bi bi-exclamation-triangle-fill me-1"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <div class="mb-3">
                <label for="username" class="form-label small fw-semibold text-secondary">Username</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
                    <input type="text" name="username" id="username" class="form-control" required placeholder="Enter username">
                </div>
            </div>

            <div class="mb-4">
                <label for="password" class="form-label small fw-semibold text-secondary">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                    <input type="password" name="password" id="password" class="form-control" required placeholder="Enter password">
                </div>
            </div>

            <button type="submit" name="login" class="btn btn-primary w-100 d-flex align-items-center justify-content-center gap-2">
                <span>Sign In</span> <i class="bi bi-arrow-right-short fs-5"></i>
            </button>
        </form>

        <p class="text-center small text-muted mt-3 mb-0">New Account? <a href="register.php">Register Here</a></p>
    </div>
</div>

</body>
</html>