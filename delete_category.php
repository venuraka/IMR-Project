<?php
include('dbcon.php');

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Check if category is used in any products
    $check_sql = "SELECT COUNT(*) as count FROM Product WHERE CategoryID = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row['count'] > 0) {
        header('Location: category.php?error=Cannot delete category. Products exist in this category.');
        exit();
    }

    $sql = "DELETE FROM Category WHERE CategoryID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        header('Location: category.php?success=Category deleted successfully');
    } else {
        header('Location: category.php?error=Failed to delete category');
    }
    exit();
}
