<?php
include('dbcon.php');

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    mysqli_begin_transaction($conn);

    try {
        // Delete from StockProduct first (due to foreign key)
        $sql = "DELETE FROM StockProduct WHERE StockID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();

        // Delete from StockSaleReport if exists
        $sql = "DELETE FROM StockSaleReport WHERE StockID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();

        // Delete from Stock
        $sql = "DELETE FROM Stock WHERE StockID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();

        mysqli_commit($conn);
        header('Location: stock.php?success=Stock entry deleted successfully');
    } catch (Exception $e) {
        mysqli_rollback($conn);
        header('Location: stock.php?error=Failed to delete stock entry');
    }
    exit();
}
