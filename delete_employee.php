<?php
include('dbcon.php');

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Check if employee is associated with any sales
    $check_sql = "SELECT COUNT(*) as count FROM Sale WHERE EmployeeID = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row['count'] > 0) {
        header('Location: employee.php?error=Cannot delete employee. Sales records exist for this employee.');
        exit();
    }

    $sql = "DELETE FROM Employee WHERE EmployeeId = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        header('Location: employee.php?success=Employee deleted successfully');
    } else {
        header('Location: employee.php?error=Failed to delete employee');
    }
    exit();
}
