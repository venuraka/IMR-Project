<?php
include('dbcon.php');

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Check if supplier is used in any products
    $check_sql = "SELECT COUNT(*) as count FROM Product WHERE SupplierID = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row['count'] > 0) {
        header('Location: supplier.php?error=Cannot delete supplier. Products exist with this supplier.');
        exit();
    }

    $sql = "DELETE FROM Supplier WHERE SupplierID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        header('Location: supplier.php?success=Supplier deleted successfully');
    } else {
        header('Location: supplier.php?error=Failed to delete supplier');
    }
    exit();
}
