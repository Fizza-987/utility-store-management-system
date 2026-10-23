<?php
// Strict Error Reporting taake koi bhi error chhupe nahi
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Absolute Paths Mappings
require_once __DIR__ . "/../config/session.php";
require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../config/constants.php";
require_once __DIR__ . "/../includes/auth.php";

// Authorization Check
requireLogin();

// Include Layout Header
include __DIR__ . "/../includes/header.php";

$message = "";
$message_type = "success";

// ===============================
// DELETE INVENTORY LOG RECORD
// ===============================
if (isset($_POST['delete_report_log'])) {
    $delete_log_id = (int)$_POST['delete_log_id'];
    
    try {
        $deleteQuery = $conn->prepare("DELETE FROM Inventory_Logs WHERE log_id = ?");
        $deleteQuery->execute([$delete_log_id]);
        
        $message = "Transaction log entry deleted successfully.";
        $message_type = "success";
    } catch (Exception $e) {
        $message = "Error deleting record: " . $e->getMessage();
        $message_type = "danger";
    }
}

// Fetch Safe Transaction Reports Data
try {
    $query = "SELECT l.*, p.product_name, c.category_name, b.brand_name 
              FROM Inventory_Logs l
              LEFT JOIN Products p ON l.product_id = p.product_id
              LEFT JOIN Categories c ON p.category_id = c.category_id
              LEFT JOIN Brands b ON p.brand_id = b.brand_id
              ORDER BY l.log_id DESC";
    $reportData = $conn->query($query)->fetchAll();
} catch (PDOException $e) {
    echo "<div class='alert alert-danger m-3'>Database Error: " . $e->getMessage() . "</div>";
    $reportData = [];
}
?>

<div class="container-fluid mt-4">
    <div class="card p-4 shadow-sm bg-white border-0">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="mb-0">📑 Inventory Transaction Reports</h3>
            <button onclick="window.print()" class="btn btn-secondary btn-sm px-3 fw-bold">🖨️ Print Report</button>
        </div>

        <!-- Notification Message System -->
        <?php if(!empty($message)){ ?>
            <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show">
                <?= htmlspecialchars($message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php } ?>
        
        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Product Name</th>
                        <th>Category</th>
                        <th>Brand</th>
                        <th>Transaction Type</th>
                        <th>Quantity</th>
                        <th>Remarks</th>
                        <th>Date & Time</th>
                        <th class="text-center no-print">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($reportData)): ?>
                        <tr><td colspan="9" class="text-center text-muted py-3">No transactions recorded yet in the system.</td></tr>
                    <?php else: ?>
                        <?php foreach ($reportData as $index => $row): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td class="fw-bold"><?= htmlspecialchars($row['product_name'] ?? 'Unknown Product') ?></td>
                                <td><?= htmlspecialchars($row['category_name'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($row['brand_name'] ?? '-') ?></td>
                                <td>
                                    <?php 
                                    $typeValue = $row['transaction_type'] ?? $row['type'] ?? 'IN';
                                    if (strtoupper($typeValue) == 'STOCK IN' || strtoupper($typeValue) == 'IN'): 
                                    ?>
                                        <span class="badge bg-success px-2 py-1.5">📥 Stock IN</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger px-2 py-1.5">📤 Stock OUT</span>
                                    <?php endif; ?>
                                </td>
                                <td class="fw-bold"><?= htmlspecialchars($row['quantity'] ?? 0) ?></td>
                                <td class="text-muted small"><?= htmlspecialchars($row['remarks'] ?? '-') ?></td>
                                <td>
                                    <?php 
                                    $dateValue = $row['transaction_date'] ?? $row['created_at'] ?? $row['date'] ?? null;
                                    echo $dateValue ? date("d-M-Y h:i A", strtotime($dateValue)) : '-';
                                    ?>
                                </td>
                                <td class="text-center no-print">
                                    <form method="POST" onsubmit="return confirm('Are you sure you want to permanently delete this report entry?');" style="display:inline;">
                                        <input type="hidden" name="delete_log_id" value="<?= $row['log_id']; ?>">
                                        <button type="submit" name="delete_report_log" class="btn btn-sm btn-outline-danger" style="padding: 2px 8px; font-size: 12px; border-radius: 6px;">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Print utility style to automatically hide action column during printing -->
<style>
@media print {
    .no-print {
        display: none !important;
    }
}
</style>

<?php 
// Include Footer to close open tags properly
include __DIR__ . "/../includes/footer.php"; 
?>