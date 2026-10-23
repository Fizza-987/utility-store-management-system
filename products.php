<?php
// Core configuration files link ho rahi hain (one level up paths)
require_once "../config/database.php";
require_once "../config/session.php";
require_once "../includes/auth.php";

// Authorization check
requireLogin();

$message = "";
$messageType = "";

// ==========================================
// 1. FETCH ALL CATEGORIES & BRANDS FOR DROPDOWNS (Status check removed to prevent crashes)
// ==========================================
try {
    $categories = $conn->query("SELECT category_id, category_name FROM Categories ORDER BY category_name ASC")->fetchAll(PDO::FETCH_ASSOC);
    $brands = $conn->query("SELECT brand_id, brand_name FROM Brands ORDER BY brand_name ASC")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $message = "Error fetching dependencies: " . $e->getMessage();
    $messageType = "danger";
}

// ==========================================
// 2. ADD PRODUCT ACTION (BACKEND LOGIC - MAPPED TO CORRECT DB COLUMNS)
// ==========================================
if (isset($_POST['add_product'])) {
    $product_name = trim($_POST['product_name']);
    $category_id = $_POST['category_id'];
    $brand_id = $_POST['brand_id'];
    
    // Mapped precisely to your scanned schema columns
    $selling_price = isset($_POST['price']) ? trim($_POST['price']) : 0;
    $stock = isset($_POST['quantity']) ? trim($_POST['quantity']) : 0;
    $status = isset($_POST['status']) ? $_POST['status'] : 'Available';

    if (!empty($product_name) && !empty($category_id) && !empty($brand_id)) {
        try {
            $check = $conn->prepare("SELECT COUNT(*) FROM Products WHERE product_name = :name");
            $check->execute([':name' => $product_name]);
            
            if ($check->fetchColumn() > 0) {
                $message = "Product name already exists in inventory!";
                $messageType = "danger";
            } else {
                // Query structures precisely targeting selling_price and stock
                $query = "INSERT INTO Products (product_name, category_id, brand_id, selling_price, stock, status) 
                          VALUES (:name, :cat_id, :brand_id, :selling_price, :stock, :status)";
                $stmt = $conn->prepare($query);
                
                $params = [
                    ':name' => $product_name,
                    ':cat_id' => $category_id,
                    ':brand_id' => $brand_id,
                    ':selling_price' => $selling_price,
                    ':stock' => $stock,
                    ':status' => $status
                ];

                if ($stmt->execute($params)) {
                    $message = "Product registered successfully in Matrix Directory!";
                    $messageType = "success";
                    // Refresh data array
                    header("Location: products.php?msg=added");
                    exit();
                }
            }
        } catch (PDOException $e) {
            $message = "Database Insertion Error: " . $e->getMessage();
            $messageType = "danger";
        }
    } else {
        $message = "All core fields are required!";
        $messageType = "danger";
    }
}

// ==========================================
// 3. DELETE PRODUCT ACTION
// ==========================================
if (isset($_GET['delete'])) {
    $delete_id = $_GET['delete'];
    try {
        $delStmt = $conn->prepare("DELETE FROM Products WHERE product_id = :id");
        if ($delStmt->execute([':id' => $delete_id])) {
            header("Location: products.php?msg=deleted");
            exit();
        }
    } catch (PDOException $e) {
        $message = "Cannot delete product template!";
        $messageType = "danger";
    }
}

// Success message templates
if (isset($_GET['msg'])) {
    if ($_GET['msg'] == 'deleted') {
        $message = "Product removed from system matrix successfully!";
        $messageType = "success";
    } elseif ($_GET['msg'] == 'added') {
        $message = "Product registered successfully in Matrix Directory!";
        $messageType = "success";
    }
}

// ==========================================
// 4. READ PRODUCTS WITH INNER JOINS
// ==========================================
try {
    $sql = "SELECT p.*, c.category_name, b.brand_name 
            FROM Products p
            LEFT JOIN Categories c ON p.category_id = c.category_id
            LEFT JOIN Brands b ON p.brand_id = b.brand_id
            ORDER BY p.product_id DESC";
    $products = $conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $products = [];
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
    .form-control, .form-select { border: 1px solid #cbd5e1; border-radius: 10px; padding: 10px 14px; background-color: #f8fafc; }
    .form-control:focus, .form-select:focus { border-color: #2563eb; box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1); background-color: #ffffff; }
    .btn-matrix { background: #2563eb; border: none; border-radius: 10px; padding: 11px; font-weight: 600; color: white; }
    .btn-matrix:hover { background: #1d4ed8; color: white; }
    .table th { background-color: #0f172a !important; color: white; font-weight: 500; border: none; padding: 12px; }
    .badge-active { background-color: #dcfce7; color: #15803d; font-size: 0.8rem; font-weight: 600; }
    .badge-inactive { background-color: #fee2e2; color: #991b1b; font-size: 0.8rem; font-weight: 600; }
    .badge-stock { background-color: #e2e8f0; color: #334155; font-size: 0.85rem; }
    .btn-remove { color: #dc3545; border: 1px solid #f8d7da; background: #fff5f5; border-radius: 8px; padding: 4px 12px; font-size: 0.85rem; transition: all 0.2s ease; }
    .btn-remove:hover { background: #dc3545; color: white; }
</style>

<div class="container-fluid py-4">
    <div class="card custom-card matrix-header p-3 mb-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h4 class="fw-bold text-dark mb-1"><i class="bi bi-box-seam-fill text-primary me-2"></i>Products System Matrix</h4>
                <p class="text-muted small mb-0">Enterprise catalogue control for active product specifications and inventory stock.</p>
            </div>
            <span class="badge bg-light text-dark border px-3 py-2 rounded-3 fw-semibold shadow-sm">
                Total SKUs: <?= count($products) ?>
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
        <!-- Left Side: Add Form -->
        <div class="col-xl-4">
            <div class="card custom-card p-3">
                <div class="card-body">
                    <h5 class="fw-bold text-dark mb-3"><i class="bi bi-plus-square text-primary me-2"></i>New Registration</h5>
                    <form action="products.php" method="POST">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-secondary">Product Name</label>
                            <input type="text" name="product_name" class="form-control" placeholder="e.g., Milk Pak 1L, Lux Soap" required>
                        </div>
                        
                        <div class="row g-3 mb-3">
                            <div class="col-6">
                                <label class="form-label small fw-semibold text-secondary">Category Label</label>
                                <select name="category_id" class="form-select" required>
                                    <option value="">Select...</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?= $cat['category_id'] ?>"><?= htmlspecialchars($cat['category_name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-semibold text-secondary">Brand Partner</label>
                                <select name="brand_id" class="form-select" required>
                                    <option value="">Select...</option>
                                    <?php foreach ($brands as $br): ?>
                                        <option value="<?= $br['brand_id'] ?>"><?= htmlspecialchars($br['brand_name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-6">
                                <label class="form-label small fw-semibold text-secondary">Unit Price (PKR)</label>
                                <input type="number" step="0.01" name="price" class="form-control" placeholder="0.00" min="0" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-semibold text-secondary">Initial Stock Qty</label>
                                <input type="number" name="quantity" class="form-control" placeholder="0" min="0" required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label small fw-semibold text-secondary">Status</label>
                            <select name="status" class="form-select">
                                <option value="Available">Available</option>
                                <option value="Out of Stock">Out of Stock</option>
                            </select>
                        </div>

                        <button type="submit" name="add_product" class="btn btn-matrix w-100">
                            <i class="bi bi-save me-2"></i>Add Product Variant
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Right Side: Table -->
        <div class="col-xl-8">
            <div class="card custom-card p-3">
                <div class="card-body">
                    <h5 class="fw-bold text-dark mb-3"><i class="bi bi-cpu text-primary me-2"></i>Products Directory Matrix</h5>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-3" style="border-top-left-radius: 12px; border-bottom-left-radius: 12px;">SKU</th>
                                    <th>Product Particulars</th>
                                    <th>Category/Brand</th>
                                    <th>Price</th>
                                    <th>Stock</th>
                                    <th>Status</th>
                                    <th class="text-center" style="border-top-right-radius: 12px; border-bottom-right-radius: 12px;">Control Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($products) > 0): ?>
                                    <?php foreach ($products as $row): ?>
                                        <tr>
                                            <td class="fw-semibold text-secondary ps-3">#PROD-<?= $row['product_id'] ?></td>
                                            <td>
                                                <span class="fw-bold text-dark d-block"><?= htmlspecialchars($row['product_name']) ?></span>
                                            </td>
                                            <td>
                                                <span class="small d-block text-primary fw-medium"><?= htmlspecialchars($row['category_name'] ?? 'Uncategorized') ?></span>
                                                <span class="text-muted extra-small" style="font-size: 0.75rem;"><i class="bi bi-tag"></i> <?= htmlspecialchars($row['brand_name'] ?? 'Generic') ?></span>
                                            </td>
                                            <td class="fw-bold text-success">
                                                <?php 
                                                // Mapped directly to your actual db structure 'selling_price'
                                                $prodPrice = $row['selling_price'] ?? ($row['price'] ?? 0);
                                                ?>
                                                Rs. <?= number_format((float)$prodPrice, 2) ?>
                                            </td>
                                            <td>
                                                <?php 
                                                // Mapped directly to your actual db structure 'stock'
                                                $prodQty = $row['stock'] ?? ($row['quantity'] ?? 0);
                                                ?>
                                                <span class="badge badge-stock px-2 py-1.5 rounded-3 fw-semibold">
                                                    <?= $prodQty ?> units
                                                </span>
                                            </td>
                                            <td>
                                                <?php 
                                                $prodStatus = (isset($row['status']) && !empty($row['status'])) ? strtolower($row['status']) : 'available';
                                                ?>
                                                <span class="badge px-2.5 py-1.5 rounded-pill <?= ($prodStatus == 'available' || $prodStatus == 'active') ? 'badge-active' : 'badge-inactive' ?>">
                                                    <?= ucfirst($prodStatus) ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <a href="products.php?delete=<?= $row['product_id'] ?>" 
                                                   class="btn btn-remove d-inline-flex align-items-center gap-1 text-decoration-none"
                                                   onclick="return confirm('Are you sure you want to delete this product template?');">
                                                    <i class="bi bi-trash3-fill"></i> Remove
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-5 text-muted">
                                            <i class="bi bi-box-x fs-1 d-block mb-2 text-secondary"></i> Matrix directory empty. Populate product parameters on the left.
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