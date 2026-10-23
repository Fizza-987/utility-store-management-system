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
// 1. ADD BRAND ACTION
// ==========================================
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_brand'])) {
    $brand_name = trim($_POST['brand_name']);

    if (!empty($brand_name)) {
        try {
            // Check duplicate brand name
            $check = $conn->prepare("SELECT COUNT(*) FROM Brands WHERE brand_name = :name");
            $check->execute([':name' => $brand_name]);
            
            if ($check->fetchColumn() > 0) {
                $message = "Brand partner already exists!";
                $messageType = "danger";
            } else {
                // Secure Insert Query execution
                $query = "INSERT INTO Brands (brand_name) VALUES (:name)";
                $stmt = $conn->prepare($query);
                
                if ($stmt->execute([':name' => $brand_name])) {
                    // Self redirect using current page name dynamically to avoid directory issues
                    header("Location: " . $_SERVER['PHP_SELF'] . "?msg=added");
                    exit();
                } else {
                    $message = "Failed to execute transaction process.";
                    $messageType = "danger";
                }
            }
        } catch (PDOException $e) {
            $message = "Database Error: " . $e->getMessage();
            $messageType = "danger";
        }
    } else {
        $message = "Brand name is required!";
        $messageType = "danger";
    }
}

// ==========================================
// 2. DELETE BRAND ACTION
// ==========================================
if (isset($_GET['delete'])) {
    $delete_id = $_GET['delete'];
    try {
        $delStmt = $conn->prepare("DELETE FROM Brands WHERE brand_id = :id");
        if ($delStmt->execute([':id' => $delete_id])) {
            header("Location: " . $_SERVER['PHP_SELF'] . "?msg=deleted");
            exit();
        }
    } catch (PDOException $e) {
        $message = "Cannot delete brand! It might be linked to operational product records.";
        $messageType = "danger";
    }
}

// Success message triggers
if (isset($_GET['msg'])) {
    if ($_GET['msg'] == 'deleted') {
        $message = "Brand partner removed successfully!";
        $messageType = "success";
    } elseif ($_GET['msg'] == 'added') {
        $message = "Brand registered successfully!";
        $messageType = "success";
    }
}

// ==========================================
// 3. FETCH ALL BRANDS
// ==========================================
try {
    $brands = $conn->query("SELECT * FROM Brands ORDER BY brand_id DESC")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $brands = [];
}

include "../includes/header.php";
?>

<!-- Bootstrap 5 and Icons CDN -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

<style>
    body { background-color: #f8fafc; font-family: 'Segoe UI', system-ui, sans-serif; overflow-x: hidden; }
    .custom-card { border: none; border-radius: 16px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05); background: #ffffff; }
    .matrix-header { background: #ffffff; border-bottom: 2px solid #e2e8f0; }
    .form-control { border: 1px solid #cbd5e1; border-radius: 10px; padding: 10px 14px; background-color: #f8fafc; }
    .form-control:focus { border-color: #2563eb; box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1); background-color: #ffffff; }
    .btn-matrix { background: #2563eb; border: none; border-radius: 10px; padding: 11px; font-weight: 600; color: white; }
    .btn-matrix:hover { background: #1d4ed8; color: white; }
    .table th { background-color: #0f172a !important; color: white; font-weight: 500; border: none; padding: 12px; }
    .btn-remove { color: #dc3545; border: 1px solid #f8d7da; background: #fff5f5; border-radius: 8px; padding: 4px 12px; font-size: 0.85rem; transition: all 0.2s ease; }
    .btn-remove:hover { background: #dc3545; color: white; }
</style>

<div class="container-fluid py-4">
    <div class="card custom-card matrix-header p-3 mb-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h4 class="fw-bold text-dark mb-1"><i class="bi bi-shield-shaded text-primary me-2"></i>Brands Matrix</h4>
                <p class="text-muted small mb-0">Control operational supply brand profiles and vendor partners.</p>
            </div>
            <span class="badge bg-light text-dark border px-3 py-2 rounded-3 fw-semibold shadow-sm">
                Active Partners: <?= count($brands) ?>
            </span>
        </div>
    </div>

    <?php if (!empty($message)): ?>
        <div class="alert alert-<?= $messageType ?> alert-dismissible fade show border-0 rounded-3 shadow-sm mb-4" role="alert">
            <i class="bi <?php echo ($messageType == 'success') ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill'; ?> me-2"></i>
            <?= htmlspecialchars($message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <!-- Add Brand Form -->
        <div class="col-xl-4">
            <div class="card custom-card p-3">
                <div class="card-body">
                    <h5 class="fw-bold text-dark mb-3"><i class="bi bi-patch-plus text-primary me-2"></i>Register Brand</h5>
                    
                    <!-- CRITICAL FIX: Action is blank ("") so it never goes to wrong directory -->
                    <form action="" method="POST">
                        <div class="mb-4">
                            <label class="form-label small fw-semibold text-secondary">Brand / Manufacturer Name</label>
                            <input type="text" name="brand_name" class="form-control" placeholder="e.g., National, Nestle, Unilever" required>
                        </div>
                        <button type="submit" name="add_brand" class="btn btn-matrix w-100">
                            <i class="bi bi-save me-2"></i>Link Brand Node
                        </button>
                    </form>
                    
                </div>
            </div>
        </div>

        <!-- Brands Table -->
        <div class="col-xl-8">
            <div class="card custom-card p-3">
                <div class="card-body">
                    <h5 class="fw-bold text-dark mb-3"><i class="bi bi-building-gear text-primary me-2"></i>Partner Directory</h5>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-3" style="border-top-left-radius: 12px; border-bottom-left-radius: 12px; width: 20%;">ID</th>
                                    <th style="width: 60%;">Brand Name</th>
                                    <th class="text-center" style="border-top-right-radius: 12px; border-bottom-right-radius: 12px; width: 20%;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($brands) > 0): ?>
                                    <?php foreach ($brands as $row): ?>
                                        <tr>
                                            <td class="fw-semibold text-secondary ps-3">#BRND-<?= $row['brand_id'] ?></td>
                                            <td class="fw-bold text-dark"><i class="bi bi-bookmark-star text-warning me-1"></i> <?= htmlspecialchars($row['brand_name']) ?></td>
                                            <td class="text-center">
                                                <a href="?delete=<?= $row['brand_id'] ?>" 
                                                   class="btn btn-remove d-inline-flex align-items-center gap-1 text-decoration-none"
                                                   onclick="return confirm('Are you sure you want to delete this brand partner?');">
                                                     <i class="bi bi-trash3-fill"></i> Remove
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="3" class="text-center py-5 text-muted">
                                            <i class="bi bi-patch-question fs-1 d-block mb-2 text-secondary"></i> Brand directory empty. Add inventory partners.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include "../includes/footer.php"; ?>