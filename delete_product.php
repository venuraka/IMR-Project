<?php
include('dbcon.php');

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Check if product is used in any sales
    $check_sql = "SELECT COUNT(*) as count FROM SaleDetail WHERE ProductID = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row['count'] > 0) {
        header('Location: product.php?error=Cannot delete product. Sales records exist for this product.');
        exit();
    }

    $sql = "DELETE FROM Product WHERE ProductID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        header('Location: product.php?success=Product deleted successfully');
    } else {
        header('Location: product.php?error=Failed to delete product');
    }
    exit();
}
