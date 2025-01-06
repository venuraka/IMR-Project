<?php
include('dbcon.php');

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Prepare the delete statement
    $stmt = $conn->prepare("DELETE FROM Discount WHERE ProductID = ?");
    $stmt->bind_param("s", $id);
    
    if ($stmt->execute()) {
        header('Location: discount.php?success=Discount deleted successfully');
    } else {
        header('Location: discount.php?error=Error deleting discount: ' . $conn->error);
    }
} else {
    header('Location: discount.php?error=No discount ID specified');
}

$stmt->close();
$conn->close();
?>