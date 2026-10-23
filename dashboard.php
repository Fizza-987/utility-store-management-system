<?php
// dashboard.php - Inside admin folder
require_once "../config/database.php";
require_once "../config/session.php";
require_once "../includes/auth.php";

requireLogin();

include "../includes/header.php";

// =======================
// Dashboard Counts
// =======================

$totalCategories = $conn->query("SELECT COUNT(*) FROM Categories")->fetchColumn();
$totalBrands = $conn->query("SELECT COUNT(*) FROM Brands")->fetchColumn();
$totalProducts = $conn->query("SELECT COUNT(*) FROM Products")->fetchColumn();
$totalStock = $conn->query("SELECT IFNULL(SUM(stock),0) FROM Products")->fetchColumn();

$lowStock = $conn->query("
    SELECT COUNT(*)
    FROM Products
    WHERE stock <= reorder_level
")->fetchColumn();

$outStock = $conn->query("
    SELECT COUNT(*)
    FROM Products
    WHERE stock=0
")->fetchColumn();

$recentProducts = $conn->query("
    SELECT product_name, stock, selling_price, status
    FROM Products
    ORDER BY product_id DESC
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
.dashboard-card{
    border:none;
    border-radius:15px;
    color:white;
    transition:.3s;
}
.dashboard-card:hover{
    transform:translateY(-5px);
}
.card-body h2{
    font-weight:bold;
}
.table{
    background:white;
}
.chart-box{
    background:white;
    padding:20px;
    border-radius:15px;
    box-shadow:0 0 10px rgba(0,0,0,.08);
    margin-top:25px;
}
</style>

<div class="container-fluid">
    <h2 class="mb-4">📊 Dashboard</h2>

    <div class="row g-4">
        <div class="col-md-3">
            <div class="card dashboard-card bg-primary shadow">
                <div class="card-body">
                    <h6>Total Categories</h6>
                    <h2><?= $totalCategories ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card dashboard-card bg-success shadow">
                <div class="card-body">
                    <h6>Total Brands</h6>
                    <h2><?= $totalBrands ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card dashboard-card bg-warning shadow">
                <div class="card-body">
                    <h6>Total Products</h6>
                    <h2><?= $totalProducts ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card dashboard-card bg-danger shadow text-light">
                <div class="card-body">
                    <h6>Total Stock</h6>
                    <h2><?= $totalStock ?></h2>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-4">
            <div class="card dashboard-card bg-info shadow">
                <div class="card-body">
                    <h6>Low Stock Products</h6>
                    <h2><?= $lowStock ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card dashboard-card bg-secondary shadow">
                <div class="card-body">
                    <h6>Out Of Stock</h6>
                    <h2><?= $outStock ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card dashboard-card bg-dark shadow">
                <div class="card-body">
                    <h6>Store Status</h6>
                    <h4>Running</h4>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-lg-6">
            <div class="chart-box">
                <h5>Stock Summary</h5>
                <canvas id="stockChart"></canvas>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="chart-box">
                <h5>Inventory Status</h5>
                <canvas id="inventoryChart"></canvas>
            </div>
        </div>
    </div>

    <div class="chart-box mt-4">
        <h4 class="mb-3">Recent Products</h4>
        <table class="table table-bordered table-hover">
            <thead class="table-dark">
                <tr>
                    <th>Product</th>
                    <th>Stock</th>
                    <th>Price</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if(count($recentProducts) > 0){ ?>
                    <?php foreach($recentProducts as $row){ ?>
                        <tr>
                            <td><?= htmlspecialchars($row['product_name']) ?></td>
                            <td><?= $row['stock'] ?></td>
                            <td>Rs. <?= number_format($row['selling_price'],2) ?></td>
                            <td><?= htmlspecialchars($row['status']) ?></td>
                        </tr>
                    <?php } ?>
                <?php } else { ?>
                    <tr>
                        <td colspan="4" class="text-center">No Products Found</td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<script>
new Chart(document.getElementById('stockChart'), {
    type: 'bar',
    data: {
        labels: ['Categories', 'Brands', 'Products', 'Stock'],
        datasets: [{
            label: 'Store Statistics',
            data: [
                <?= $totalCategories ?>,
                <?= $totalBrands ?>,
                <?= $totalProducts ?>,
                <?= $totalStock ?>
            ],
            backgroundColor: ['#0d6efd', '#198754', '#ffc107', '#dc3545']
        }]
    }
});

new Chart(document.getElementById('inventoryChart'), {
    type: 'doughnut',
    data: {
        labels: ['Available', 'Low Stock', 'Out Of Stock'],
        datasets: [{
            data: [
                <?= max($totalProducts - $lowStock - $outStock, 0) ?>,
                <?= $lowStock ?>,
                <?= $outStock ?>
            ],
            backgroundColor: ['#198754', '#ffc107', '#dc3545']
        }]
    }
});
</script>

<?php include "../includes/footer.php"; ?>