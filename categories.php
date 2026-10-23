<?php
// Core configuration files link ho rahi hain
require_once "../config/database.php";
require_once "../config/session.php";
require_once "../includes/auth.php";

// Authorization check
requireLogin();

$message = "";
$messageType = "";

// ==========================================
// 1. ADD CATEGORY ACTION
// ==========================================
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_category'])) {
    $category_name = trim($_POST['category_name']);

    if (!empty($category_name)) {
        try {
            // Check duplicate category name
            $check = $conn->prepare("SELECT COUNT(*) FROM Categories WHERE category_name = :name");
            $check->execute([':name' => $category_name]);
            
            if ($check->fetchColumn() > 0) {
                $message = "Category already exists!";
                $messageType = "danger";
            } else {
                // Secure Insert Query execution
                $query = "INSERT INTO Categories (category_name) VALUES (:name)";
                $stmt = $conn->prepare($query);
                
                if ($stmt->execute([':name' => $category_name])) {
                    header("Location: " . $_SERVER['PHP_SELF'] . "?msg=added");
                    exit();
                } else {
                    $message = "Failed to register category node.";
                    $messageType = "danger";
                }
            }
        } catch (PDOException $e) {
            $message = "Database Error: " . $e->getMessage();
            $messageType = "danger";
        }
    } else {
        $message = "Category name is required!";
        $messageType = "danger";
    }
}

// ==========================================
// 2. DELETE CATEGORY ACTION
// ==========================================
if (isset($_GET['delete'])) {
    $delete_id = $_GET['delete'];
    try {
        $delStmt = $conn->prepare("DELETE FROM Categories WHERE category_id = :id");
        if ($delStmt->execute([':id' => $delete_id])) {
            header("Location: " . $_SERVER['PHP_SELF'] . "?msg=deleted");
            exit();
        }
    } catch (PDOException $e) {
        $message = "Cannot delete category! It might be linked to product inventory rows.";
        $messageType = "danger";
    }
}

// Success message triggers
if (isset($_GET['msg'])) {
    if ($_GET['msg'] == 'deleted') {
        $message = "Category removed successfully!";
        $messageType = "success";
    } elseif ($_GET['msg'] == 'added') {
        $message = "Category registered successfully!";
        $messageType = "success";
    }
}

// ==========================================
// 3. FETCH ALL CATEGORIES
// ==========================================
try {
    $categories = $conn->query("SELECT * FROM Categories ORDER BY category_id DESC")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $categories = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categories - Utility Store Management</title>
    
    <style>
        /* Modern UI Reset & Typography */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, sans-serif;
        }

        body {
            background-color: #f8fafc;
            display: flex;
            min-height: 100vh;
            color: #1e293b;
        }

        /* 1. Ultra Sleek Sidebar */
        .sidebar {
            width: 260px;
            background-color: #0f172a;
            color: #94a3b8;
            display: flex;
            flex-direction: column;
            padding: 24px 16px;
            flex-shrink: 0;
        }

        .sidebar-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            color: #ffffff;
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 32px;
            padding-left: 8px;
        }

        .sidebar-menu {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .sidebar-menu li a {
            display: flex;
            align-items: center;
            gap: 12px;
            color: #94a3b8;
            text-decoration: none;
            padding: 12px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .sidebar-menu li a:hover {
            background-color: #1e293b;
            color: #f8fafc;
        }

        .sidebar-menu li.active a {
            background-color: #3b82f6;
            color: #ffffff;
            font-weight: 600;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.25);
        }

        /* 2. Main Content Wrapper */
        .main-content {
            flex-grow: 1;
            padding: 30px;
            overflow-y: auto;
        }

        /* Top Welcome Bar */
        .top-navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #ffffff;
            padding: 16px 24px;
            border-radius: 12px;
            margin-bottom: 24px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            border: 1px solid #e2e8f0;
        }

        .user-greeting {
            font-weight: 600;
            font-size: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-logout {
            background-color: #ef4444;
            color: white;
            padding: 8px 16px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 13.5px;
            font-weight: 600;
            transition: background 0.2s;
        }
        .btn-logout:hover {
            background-color: #dc2626;
        }

        /* Hero / Matrix Card Header */
        .matrix-hero {
            background: #ffffff;
            padding: 24px;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            margin-bottom: 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }

        .matrix-title h2 {
            font-size: 20px;
            color: #0f172a;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .matrix-title p {
            color: #64748b;
            font-size: 13.5px;
            margin-top: 4px;
        }

        .badge-count {
            background-color: #f1f5f9;
            color: #334155;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12.5px;
            font-weight: 600;
            border: 1px solid #e2e8f0;
        }

        /* Status Alert Boxes */
        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 24px;
        }
        .alert-success { background-color: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .alert-danger { background-color: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }

        /* 3. Grid Dashboard Layout split */
        .dashboard-grid {
            display: grid;
            grid-template-columns: 1fr 1.6fr;
            gap: 24px;
            align-items: start;
        }

        .card {
            background: #ffffff;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            padding: 24px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }

        .card-title {
            font-size: 16px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Forms Styling */
        .form-label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #475569;
            margin-bottom: 6px;
        }

        .form-control {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font-size: 14px;
            background-color: #f8fafc;
            transition: all 0.2s;
            margin-bottom: 16px;
        }

        .form-control:focus {
            outline: none;
            border-color: #3b82f6;
            background-color: #ffffff;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .btn-primary {
            width: 100%;
            background-color: #2563eb;
            color: white;
            padding: 11px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            transition: background 0.2s;
        }

        .btn-primary:hover {
            background-color: #1d4ed8;
        }

        /* 4. Elegant Table Design */
        .table-responsive {
            width: 100%;
            overflow-x: auto;
        }

        .custom-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            font-size: 14px;
        }

        .custom-table th {
            background-color: #0f172a;
            color: #ffffff;
            padding: 12px 16px;
            font-weight: 600;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .custom-table th:first-child { border-top-left-radius: 8px; border-bottom-left-radius: 8px; }
        .custom-table th:last-child { border-top-right-radius: 8px; border-bottom-right-radius: 8px; }

        .custom-table td {
            padding: 14px 16px;
            border-bottom: 1px solid #f1f5f9;
            color: #334155;
        }

        .custom-table tr:hover td {
            background-color: #f8fafc;
        }

        .brand-id {
            color: #64748b;
            font-weight: 500;
        }

        .brand-name {
            font-weight: 600;
            color: #1e293b;
        }

        .btn-remove {
            color: #ef4444;
            background: #fef2f2;
            border: 1px solid #fee2e2;
            padding: 6px 12px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 12.5px;
            font-weight: 600;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .btn-remove:hover {
            background: #fee2e2;
            color: #b91c1c;
        }
    </style>
</head>
<body>

<!-- Left Professional Sidebar -->
<div class="sidebar">
    <div class="sidebar-brand">
        🏪 Utility Store
    </div>
    <ul class="sidebar-menu">
        <li><a href="dashboard.php">📊 Dashboard</a></li>
        <li class="active"><a href="categories.php">📁 Categories</a></li>
        <li><a href="brands.php">🏷️ Brands</a></li>
        <li><a href="products.php">📦 Products</a></li>
        <li><a href="inventory.php">📉 Inventory</a></li>
        <li><a href="reports.php">📋 Reports</a></li>
    </ul>
</div>

<!-- Main Right Content Side -->
<div class="main-content">
    
    <!-- Top Greeting Bar -->
    <div class="top-navbar">
        <div class="user-greeting">👋 Welcome, Admin</div>
        <a href="logout.php" class="btn-logout">Logout</a>
    </div>

    <!-- Feedback Message Alerts -->
    <?php if (!empty($message)): ?>
        <div class="alert alert-<?= $messageType ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <!-- Matrix Title Bar -->
    <div class="matrix-hero">
        <div class="matrix-title">
            <h2>📁 Categories Matrix</h2>
            <p>Control stock segments, inventory aisles, and operational categories.</p>
        </div>
        <div class="badge-count">Active Categories: <?= count($categories) ?></div>
    </div>

    <!-- Layout Columns Grid -->
    <div class="dashboard-grid">
        
        <!-- Left Side Form Column -->
        <div class="card">
            <div class="card-title">➕ Register Category</div>
            <form action="" method="POST">
                <label class="form-label" for="category_name">Category Name</label>
                <input class="form-control" type="text" id="category_name" name="category_name" placeholder="e.g., Beverages, Grocery, Cosmetics" required>
                <button type="submit" name="add_category" class="btn-primary">📥 Link Category Node</button>
            </form>
        </div>

        <!-- Right Side Directory Table Column -->
        <div class="card">
            <div class="card-title">🗂️ Category Directory</div>
            <div class="table-responsive">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Category Name</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($categories) > 0): ?>
                            <?php foreach ($categories as $row): ?>
                                <tr>
                                    <td class="brand-id">#CAT-<?= $row['category_id'] ?></td>
                                    <td class="brand-name">🔸 <?= htmlspecialchars($row['category_name']) ?></td>
                                    <td>
                                        <a href="?delete=<?= $row['category_id'] ?>" 
                                           class="btn-remove"
                                           onclick="return confirm('Are you sure you want to delete this category?');">
                                            🗑️ Remove
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" style="text-align: center; padding: 30px; color: #64748b;">
                                    No categories found. Add your first item.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

</body>
</html>