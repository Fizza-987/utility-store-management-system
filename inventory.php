<?php
session_start();

include(__DIR__ . '/../config/database.php');
include(__DIR__ . '/../includes/header.php');

$message = "";
$message_type = "success";

// ===============================
// INVENTORY UPDATE (STOCK IN / OUT)
// ===============================

if (isset($_POST['update_inventory'])) {

    $product_id = (int)$_POST['product_id'];
    $type       = $_POST['type'];
    $quantity   = (int)$_POST['quantity'];
    $note       = trim($_POST['note']);

    if ($product_id <= 0 || $quantity <= 0) {

        $message = "Please enter valid quantity.";
        $message_type = "danger";

    } else {

        try {

            $conn->beginTransaction();

            // Current Stock
            $stmt = $conn->prepare("
                SELECT quantity
                FROM Inventory
                WHERE product_id = ?
            ");

            $stmt->execute([$product_id]);

            $stock = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($stock) {

                $currentStock = (int)$stock['quantity'];

                if ($type == "IN") {

                    $newStock = $currentStock + $quantity;

                } else {

                    if ($quantity > $currentStock) {

                        throw new Exception("Insufficient Stock.");

                    }

                    $newStock = $currentStock - $quantity;

                }

                $update = $conn->prepare("
                    UPDATE Inventory
                    SET quantity = ?,
                        last_updated = NOW()
                    WHERE product_id = ?
                ");

                $update->execute([
                    $newStock,
                    $product_id
                ]);

            } else {

                if ($type == "OUT") {

                    throw new Exception("Inventory record not found.");

                }

                $insert = $conn->prepare("
                    INSERT INTO Inventory
                    (
                        product_id,
                        quantity
                    )
                    VALUES
                    (?,?)
                ");

                $insert->execute([
                    $product_id,
                    $quantity
                ]);

            }

            // Inventory Logs
            $log = $conn->prepare("
                INSERT INTO Inventory_Logs
                (
                    product_id,
                    transaction_type,
                    quantity,
                    remarks
                )
                VALUES
                (?,?,?,?)
            ");

            $log->execute([
                $product_id,
                $type,
                $quantity,
                $note
            ]);

            $conn->commit();

            $message = "Inventory Updated Successfully.";
            $message_type = "success";

        } catch (Exception $e) {

            $conn->rollBack();
            $message = $e->getMessage();
            $message_type = "danger";

        }

    }

}

// ===============================
// REMOVE PRODUCT FROM ACTIVE INVENTORY
// ===============================
if (isset($_POST['reset_stock'])) {
    $reset_product_id = (int)$_POST['reset_product_id'];
    
    try {
        $conn->beginTransaction();

        // Inventory table se completely remove takay grid se gayab ho jaye
        $deleteQuery = $conn->prepare("DELETE FROM Inventory WHERE product_id = ?");
        $deleteQuery->execute([$reset_product_id]);

        // Inventory Logs me history track karne k liye record save krein
        $logReset = $conn->prepare("
            INSERT INTO Inventory_Logs (product_id, transaction_type, quantity, remarks)
            VALUES (?, 'OUT', 0, 'Product removed from active inventory view')
        ");
        $logReset->execute([$reset_product_id]);

        $conn->commit();
        $message = "Product record removed from active inventory view successfully.";
        $message_type = "success";
    } catch (Exception $e) {
        $conn->rollBack();
        $message = "Error removing product: " . $e->getMessage();
        $message_type = "danger";
    }
}

// ===============================
// DELETE INVENTORY LOG RECORD
// ===============================
if (isset($_POST['delete_log'])) {
    $delete_log_id = (int)$_POST['delete_log_id'];
    
    try {
        $deleteQuery = $conn->prepare("DELETE FROM Inventory_Logs WHERE log_id = ?");
        $deleteQuery->execute([$delete_log_id]);
        
        $message = "History record deleted successfully.";
        $message_type = "success";
    } catch (Exception $e) {
        $message = "Error deleting record: " . $e->getMessage();
        $message_type = "danger";
    }
}

// ===============================
// FETCH PRODUCTS
// ===============================

$productQuery = $conn->prepare("
    SELECT
        p.product_id,
        p.product_name,
        c.category_name,
        b.brand_name,
        IFNULL(i.quantity,0) quantity,
        i.last_updated
    FROM Products p
    LEFT JOIN Categories c ON p.category_id=c.category_id
    LEFT JOIN Brands b ON p.brand_id=b.brand_id
    LEFT JOIN Inventory i ON p.product_id=i.product_id
    ORDER BY p.product_name
");

$productQuery->execute();
$products = $productQuery->fetchAll(PDO::FETCH_ASSOC);

// ===============================
// FETCH INVENTORY LOGS
// ===============================

$logQuery = $conn->prepare("
    SELECT
        l.*,
        p.product_name
    FROM Inventory_Logs l
    INNER JOIN Products p ON l.product_id=p.product_id
    ORDER BY l.log_id DESC
");

$logQuery->execute();
$logs = $logQuery->fetchAll(PDO::FETCH_ASSOC);

?>
<div class="container-fluid mt-4">

    <div class="row">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h3 class="mb-0">📦 Inventory Management</h3>
                </div>

                <div class="card-body">
                    <?php if(!empty($message)){ ?>
                        <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show">
                            <?php echo $message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php } ?>

                    <form method="POST">
                        <div class="row">
                            <div class="col-md-4">
                                <label class="form-label">Product</label>
                                <select name="product_id" class="form-select" required>
                                    <option value="">Select Product</option>
                                    <?php foreach($products as $row){ ?>
                                        <option value="<?= $row['product_id']; ?>">
                                            <?= htmlspecialchars($row['product_name']); ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">Type</label>
                                <select name="type" class="form-select">
                                    <option value="IN">Stock IN</option>
                                    <option value="OUT">Stock OUT</option>
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">Quantity</label>
                                <input type="number" name="quantity" class="form-control" required>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Remarks</label>
                                <input type="text" name="note" class="form-control">
                            </div>

                            <div class="col-md-1 d-grid">
                                <label>&nbsp;</label>
                                <button type="submit" name="update_inventory" class="btn btn-success">Save</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <br>

    <!-- PRODUCTS TABLE -->
    <div class="card shadow">
        <div class="card-header bg-dark text-white">
            <div class="row">
                <div class="col-md-6">
                    <h4 class="mb-0">📋 Current Inventory</h4>
                </div>
                <div class="col-md-6">
                    <input type="text" id="searchBox" class="form-control" placeholder="Search Product">
                </div>
            </div>
        </div>

        <div class="card-body table-responsive">
            <table class="table table-bordered table-hover" id="inventoryTable">
                <thead class="table-primary">
                    <tr>
                        <th>#</th>
                        <th>Product</th>
                        <th>Category</th>
                        <th>Brand</th>
                        <th>Stock</th>
                        <th>Status</th>
                        <th>Updated</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $i = 1;
                foreach($products as $row){
                    $qty = $row['quantity'];
                ?>
                <tr>
                    <td><?= $i++; ?></td>
                    <td><?= htmlspecialchars($row['product_name']); ?></td>
                    <td><?= htmlspecialchars($row['category_name']); ?></td>
                    <td><?= htmlspecialchars($row['brand_name']); ?></td>
                    <td><?= $qty; ?></td>
                    <td>
                    <?php
                    if($qty == 0){
                        echo "<span class='badge bg-danger'>Out of Stock</span>";
                    } elseif($qty <= 10){
                        echo "<span class='badge bg-warning text-dark'>Low Stock</span>";
                    } else {
                        echo "<span class='badge bg-success'>In Stock</span>";
                    }
                    ?>
                    </td>
                    <td><?= $row['last_updated'] ?? "-"; ?></td>
                    <td class="text-center">
                        <form method="POST" onsubmit="return confirm('Are you sure you want to completely remove this product from active inventory view?');" style="display:inline;">
                            <input type="hidden" name="reset_product_id" value="<?= $row['product_id']; ?>">
                            <button type="submit" name="reset_stock" class="btn btn-sm btn-outline-danger" style="padding: 2px 8px; font-size: 12px;">
                                Clear
                            </button>
                        </form>
                    </td>
                </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <br>

    <!-- INVENTORY LOGS -->
    <div class="card shadow">
        <div class="card-header bg-success text-white">
            <h4 class="mb-0">📜 Inventory History</h4>
        </div>

        <div class="card-body table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Product</th>
                        <th>Type</th>
                        <th>Quantity</th>
                        <th>Remarks</th>
                        <th>Date</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach($logs as $log){ ?>
                    <tr>
                        <td><?= $log['log_id']; ?></td>
                        <td><?= htmlspecialchars($log['product_name']); ?></td>
                        <td>
                        <?php
                        if($log['transaction_type'] == "IN"){
                            echo "<span class='badge bg-success'>Stock IN</span>";
                        } else {
                            echo "<span class='badge bg-danger'>Stock OUT</span>";
                        }
                        ?>
                        </td>
                        <td><?= $log['quantity']; ?></td>
                        <td><?= htmlspecialchars($log['remarks']); ?></td>
                        <td><?= date("d-M-Y h:i A", strtotime($log['transaction_date'])); ?></td>
                        <td class="text-center">
                            <form method="POST" onsubmit="return confirm('Are you sure you want to permanently delete this log entry?');" style="display:inline;">
                                <input type="hidden" name="delete_log_id" value="<?= $log['log_id']; ?>">
                                <button type="submit" name="delete_log" class="btn btn-sm btn-outline-danger" style="padding: 2px 8px; font-size: 12px;">
                                    Remove
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<br>

<script>
// Product Search Script
document.getElementById("searchBox").addEventListener("keyup", function(){
    let value = this.value.toLowerCase();
    let rows = document.querySelectorAll("#inventoryTable tbody tr");
    rows.forEach(function(row){
        row.style.display = row.innerText.toLowerCase().includes(value) ? "" : "none";
    });
});
</script>

<style>
body { background: #f4f6f9; }
.card { border: none; border-radius: 12px; }
.card-header { font-weight: 600; }
.table th { text-align: center; vertical-align: middle; }
.table td { vertical-align: middle; }
.badge { padding: 8px 12px; font-size: 13px; }
.btn { border-radius: 8px; }
.form-control, .form-select { border-radius: 8px; }
.table tbody tr:hover { background: #f8f9fa; transition: .3s; }
.shadow { box-shadow: 0 0 15px rgba(0,0,0,.08)!important; }
</style>

<?php include(__DIR__ . '/../includes/footer.php'); ?>