<?php
include('dbcon.php');

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    $sql = "DELETE FROM RegularCustomer WHERE CustomerID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        header('Location: regular_customer.php?success=Regular customer status removed successfully');
    } else {
        header('Location: regular_customer.php?error=Failed to remove regular customer status');
    }
    exit();
}
