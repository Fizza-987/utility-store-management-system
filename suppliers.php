<?php
require_once "../config/database.php";
require_once "../config/session.php";
require_once "../includes/auth.php";

requireLogin();

// =========================
// Add Supplier
// =========================
if(isset($_POST['add_supplier'])){

    $supplier_name = trim($_POST['supplier_name']);
    $contact_person = trim($_POST['contact_person']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $address = trim($_POST['address']);

    $sql = "INSERT INTO Suppliers
    (
        supplier_name,
        contact_person,
        phone,
        email,
        address
    )

    VALUES
    (
        :supplier_name,
        :contact_person,
        :phone,
        :email,
        :address
    )";

    $stmt = $conn->prepare($sql);

    $stmt->execute([

        ':supplier_name'=>$supplier_name,
        ':contact_person'=>$contact_person,
        ':phone'=>$phone,
        ':email'=>$email,
        ':address'=>$address

    ]);

    $success = "Supplier Added Successfully.";

}

// =========================
// Delete Supplier
// =========================
if(isset($_GET['delete'])){

    $id = intval($_GET['delete']);

    $delete = $conn->prepare("
        DELETE FROM Suppliers
        WHERE supplier_id = :id
    ");

    $delete->execute([
        ':id'=>$id
    ]);

    header("Location: suppliers.php");
    exit();

}

// =========================
// Supplier List
// =========================

$list = $conn->query("
SELECT *
FROM Suppliers
ORDER BY supplier_id DESC
");

$suppliers = $list->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>

<html>

<head>

<title>Suppliers</title>

<style>

body{
font-family:Arial;
margin:30px;
}

table{
width:100%;
border-collapse:collapse;
}

table,th,td{
border:1px solid #ccc;
}

th,td{
padding:10px;
}

input,textarea{
width:100%;
padding:8px;
margin-bottom:10px;
}

button{
padding:10px 20px;
}

.success{
color:green;
}

</style>

</head>

<body>

<h2>Supplier Management</h2>

<?php
if(isset($success)){
echo "<p class='success'>$success</p>";
}
?>

<form method="POST">

<input
type="text"
name="supplier_name"
placeholder="Supplier Name"
required>

<input
type="text"
name="contact_person"
placeholder="Contact Person"
required>

<input
type="text"
name="phone"
placeholder="Phone">

<input
type="email"
name="email"
placeholder="Email">

<textarea
name="address"
placeholder="Address"></textarea>

<button
type="submit"
name="add_supplier">

Add Supplier

</button>

</form>

<hr>

<h2>Supplier List</h2>

<table>

<tr>

<th>ID</th>
<th>Supplier</th>
<th>Contact Person</th>
<th>Phone</th>
<th>Email</th>
<th>Action</th>

</tr>

<?php foreach($suppliers as $row){ ?>

<tr>

<td><?= $row['supplier_id']; ?></td>

<td><?= htmlspecialchars($row['supplier_name']); ?></td>

<td><?= htmlspecialchars($row['contact_person']); ?></td>

<td><?= htmlspecialchars($row['phone']); ?></td>

<td><?= htmlspecialchars($row['email']); ?></td>

<td>

<a href="?delete=<?= $row['supplier_id']; ?>"
onclick="return confirm('Delete Supplier?')">

Delete

</a>

</td>

</tr>

<?php } ?>

</table>

</body>

</html>