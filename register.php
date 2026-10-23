<?php
// Core database connection file link ho rahi hai
require_once "config/database.php"; 

$message = "";
$messageType = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Form se data utha kar sanitize karna
    $username = trim($_POST['email']); 
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    
    // Professional Schema Fix: Setting a default valid foreign key role_id
    // (Aam taur par 1 Admin ya standard User hota hai jo roles table mein hota hai)
    $role_id = 1; 

    if (!empty($username) && !empty($password) && !empty($confirm_password)) {
        if ($password !== $confirm_password) {
            $message = "Passwords do not match!";
            $messageType = "danger";
        } elseif (strlen($password) < 8) {
            $message = "Password must be at least 8 characters long!";
            $messageType = "danger";
        } else {
            try {
                // Check if username/email already exists
                $check = $conn->prepare("SELECT COUNT(*) FROM Users WHERE username = :username");
                $check->execute([':username' => $username]);
                
                if ($check->fetchColumn() > 0) {
                    $message = "This email/username is already registered!";
                    $messageType = "danger";
                } else {
                    // Modern Secure Password Hashing
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    
                    // FIXED: Including role_id to satisfy the foreign key constraint
                    $query = "INSERT INTO Users (username, password, role_id) VALUES (:username, :password, :role_id)";
                    $stmt = $conn->prepare($query);
                    
                    if ($stmt->execute([
                        ':username' => $username,
                        ':password' => $hashed_password,
                        ':role_id' => $role_id
                    ])) {
                        // Registration success redirect to login
                        header("Location: login.php?registration=success");
                        exit();
                    }
                }
            } catch (PDOException $e) {
                $message = "Database Error: " . $e->getMessage();
                $messageType = "danger";
            }
        }
    } else {
        $message = "All fields are required!";
        $messageType = "danger";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Online Utility Store Management</title>
    
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, Roboto, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #1e3a8a 0%, #0d9488 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        .register-card {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 400px;
            padding: 30px 24px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .brand-header {
            text-align: center;
            margin-bottom: 22px;
        }

        .store-icon {
            font-size: 42px;
            margin-bottom: 8px;
            display: inline-block;
            background: #f0fdf4;
            padding: 10px;
            border-radius: 50%;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }

        .brand-header h1 {
            color: #0f172a;
            font-size: 20px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }

        .brand-header p {
            color: #64748b;
            font-size: 13px;
        }

        .form-group {
            margin-bottom: 12px;
        }

        .form-group label {
            display: block;
            margin-bottom: 4px;
            color: #334155;
            font-weight: 600;
            font-size: 12.5px;
        }

        .form-group input {
            width: 100%;
            padding: 9px 12px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            font-size: 13.5px;
            color: #334155;
            background-color: #ffffff;
            transition: all 0.15s ease;
        }

        .form-group input:focus {
            outline: none;
            border-color: #0d9488;
            box-shadow: 0 0 0 3px rgba(13, 148, 136, 0.15);
        }

        .form-group input::placeholder {
            color: #94a3b8;
            font-size: 13px;
        }

        .btn-submit {
            width: 100%;
            padding: 11px;
            background: linear-gradient(135deg, #1e3a8a 0%, #0284c7 100%);
            border: none;
            color: #ffffff;
            font-size: 14px;
            font-weight: 600;
            border-radius: 6px;
            cursor: pointer;
            transition: opacity 0.15s ease;
            margin-top: 8px;
            box-shadow: 0 4px 10px rgba(30, 58, 138, 0.2);
        }

        .btn-submit:hover {
            opacity: 0.9;
        }

        .form-footer {
            text-align: center;
            margin-top: 18px;
            font-size: 13px;
            color: #64748b;
        }

        .form-footer a {
            color: #0d9488;
            text-decoration: none;
            font-weight: 600;
        }

        .form-footer a:hover {
            text-decoration: underline;
        }

        .alert {
            padding: 10px;
            border-radius: 6px;
            font-size: 13px;
            margin-bottom: 15px;
            font-weight: 500;
            text-align: center;
        }
        .alert-danger { background-color: #fef2f2; color: #991b1b; border: 1px solid #fca5a5; }
    </style>
</head>
<body>

<div class="register-card">
    <div class="brand-header">
        <div class="store-icon">🛒⚙️</div>
        <h1>Utility Store Management</h1>
        <p>Create an account to manage your store</p>
    </div>

    <!-- Error Messages Display Box -->
    <?php if (!empty($message)): ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>
    
    <form action="register.php" method="POST">
        <div class="form-group">
            <label for="full_name">Full Name</label>
            <input type="text" id="full_name" name="full_name" placeholder="e.g., Ali Khan" required>
        </div>

        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" placeholder="name@example.com" required>
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="Min. 8 characters" required>
        </div>

        <div class="form-group">
            <label for="confirm_password">Confirm Password</label>
            <input type="password" id="confirm_password" name="confirm_password" placeholder="Repeat your password" required>
        </div>

        <button type="submit" class="btn-submit">Register Account</button>
        
        <div class="form-footer">
            Already have an account? <a href="login.php">Sign In</a>
        </div>
    </form>
</div>

</body>
</html>