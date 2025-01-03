<?php
include('dbcon.php');
if (isset($_GET['id'])) {
    $CId = $_GET['id'];

    // Start a transaction for consistent updates
    mysqli_begin_transaction($conn);

    try {
        // Step 1: Delete the specified customer
        $deleteSql = "DELETE FROM customer WHERE CustomerID = '$CId'";
        $deleteResult = mysqli_query($conn, $deleteSql);

        if (!$deleteResult) {
            throw new Exception("Error deleting customer: " . mysqli_error($conn));
        }

        // Step 2: Decrease CustomerID by 1 for all subsequent customers
        $updateSql = "UPDATE customer SET CustomerID = CustomerID - 1 WHERE CustomerID > '$CId'";
        $updateResult = mysqli_query($conn, $updateSql);

        if (!$updateResult) {
            throw new Exception("Error updating customer IDs: " . mysqli_error($conn));
        }

        // Commit the transaction
        mysqli_commit($conn);

        // Success message and redirect
        echo "<script>alert('Customer data has been deleted and IDs updated successfully!'); window.location.href = 'customer.php';</script>";
        exit();
    } catch (Exception $e) {
        // Rollback transaction in case of error
        mysqli_rollback($conn);
        echo "<script>alert('Error: " . $e->getMessage() . "');</script>";
    } finally {
        mysqli_close($conn);
    }
} else {
    header("Location: customer.php");
    exit();
}
?>
